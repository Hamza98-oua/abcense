<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pole;
use App\Models\Groupe;
use App\Models\Stagiaire;
use App\Models\Seance;
use App\Models\Absence;
use Carbon\Carbon;

class AbsenceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $formateur;
    protected $gestionnaire;
    protected $groupe;
    protected $stagiaire1;
    protected $stagiaire2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create pole
        $pole = Pole::create(['nom' => 'Digital & Technologie']);

        // Create users
        $this->formateur = User::create([
            'name' => 'Jean Formateur',
            'email' => 'formateur@test.com',
            'password' => bcrypt('password123'),
            'role' => 'formateur'
        ]);

        $this->gestionnaire = User::create([
            'name' => 'Salah Gestionnaire',
            'email' => 'gestionnaire@test.com',
            'password' => bcrypt('password123'),
            'role' => 'gestionnaire',
            'pole_id' => $pole->id
        ]);

        // Create groupe and assign formateur
        $this->groupe = Groupe::create(['nom' => 'DEV-102', 'pole_id' => $pole->id]);
        $this->formateur->groupes()->attach($this->groupe->id);

        // Create stagiaires
        $this->stagiaire1 = Stagiaire::create([
            'nom' => 'ALAMI',
            'prenom' => 'Yassine',
            'email' => 'yassine.alami@test.com',
            'cef' => 'CEF-YAS-0001',
            'groupe_id' => $this->groupe->id
        ]);

        $this->stagiaire2 = Stagiaire::create([
            'nom' => 'BENJELLOUN',
            'prenom' => 'Sofia',
            'email' => 'sofia.benj@test.com',
            'cef' => 'CEF-SOF-0002',
            'groupe_id' => $this->groupe->id
        ]);
    }

    /**
     * Test sequence progression of daily session calls.
     */
    public function test_formateur_daily_session_progression(): void
    {
        $this->actingAs($this->formateur);

        // 1. Initially, suggested session should be 1
        $response = $this->get(route('formateur.dashboard', ['groupe_id' => $this->groupe->id]));
        $response->assertStatus(200);
        $response->assertViewHas('suggestedSessionNum', 1);

        // 2. Submit call for Séance 1: stagiaire1 present, stagiaire2 absent
        $response = $this->post(route('formateur.valider'), [
            'groupe_id' => $this->groupe->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'num_seance' => 1,
            'statuses' => [
                $this->stagiaire1->id => 'present',
                $this->stagiaire2->id => 'absent',
            ]
        ]);

        $response->assertRedirect(route('formateur.dashboard', [
            'groupe_id' => $this->groupe->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'num_seance' => 2
        ]));
        
        // Assert session & absence created correctly
        $seance1 = Seance::where('groupe_id', $this->groupe->id)
            ->where('num_seance', 1)
            ->first();
        $this->assertNotNull($seance1);
        $this->assertEquals(2.5, $seance1->duree_heures);

        $absence1 = Absence::where('seance_id', $seance1->id)->first();
        $this->assertNotNull($absence1);
        $this->assertEquals($this->stagiaire2->id, $absence1->stagiaire_id);
        $this->assertEquals('absence', $absence1->type);
        $this->assertFalse($absence1->autorisation_suivante);

        // 3. Requesting dashboard now should auto-suggest Séance 2
        $response = $this->get(route('formateur.dashboard', ['groupe_id' => $this->groupe->id]));
        $response->assertStatus(200);
        $response->assertViewHas('suggestedSessionNum', 2);

        // 4. Submit call for Séance 2: stagiaire1 retard, stagiaire2 present
        $response = $this->post(route('formateur.valider'), [
            'groupe_id' => $this->groupe->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'num_seance' => 2,
            'statuses' => [
                $this->stagiaire1->id => 'retard',
                $this->stagiaire2->id => 'present',
            ]
        ]);

        $response->assertRedirect(route('formateur.dashboard', [
            'groupe_id' => $this->groupe->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'num_seance' => 3
        ]));

        $seance2 = Seance::where('groupe_id', $this->groupe->id)
            ->where('num_seance', 2)
            ->first();
        $this->assertNotNull($seance2);

        $absence2 = Absence::where('seance_id', $seance2->id)->first();
        $this->assertNotNull($absence2);
        $this->assertEquals($this->stagiaire1->id, $absence2->stagiaire_id);
        $this->assertEquals('retard', $absence2->type);

        // 5. Requesting dashboard now should suggest Séance 3
        $response = $this->get(route('formateur.dashboard', ['groupe_id' => $this->groupe->id]));
        $response->assertStatus(200);
        $response->assertViewHas('suggestedSessionNum', 3);
    }

    /**
     * Test gestionnaire authorization of next session entry.
     */
    public function test_gestionnaire_can_authorize_next_session(): void
    {
        // 1. Create a session and an absence
        $seance = Seance::create([
            'groupe_id' => $this->groupe->id,
            'formateur_id' => $this->formateur->id,
            'num_seance' => 1,
            'date_debut' => Carbon::today()->setTime(8, 30, 0),
            'duree_heures' => 2.5,
            'est_validee' => true
        ]);

        $absence = Absence::create([
            'stagiaire_id' => $this->stagiaire2->id,
            'seance_id' => $seance->id,
            'type' => 'absence',
            'autorisation_suivante' => false
        ]);

        // 2. Act as gestionnaire and authorize
        $this->actingAs($this->gestionnaire);

        $response = $this->post(route('gestionnaire.absences.autoriser', ['absence' => $absence->id]));
        $response->assertRedirect();
        
        // 3. Assert database has updated status
        $absence->refresh();
        $this->assertTrue($absence->autorisation_suivante);
    }

    /**
     * Test that previous session statuses (absent/retard) are loaded in the view data.
     */
    public function test_formateur_dashboard_previous_session_warning(): void
    {
        $this->actingAs($this->formateur);

        // 1. Create a previous session and an absence
        $seance1 = Seance::create([
            'groupe_id' => $this->groupe->id,
            'formateur_id' => $this->formateur->id,
            'num_seance' => 1,
            'date_debut' => Carbon::today()->setTime(8, 30, 0),
            'duree_heures' => 2.5,
            'est_validee' => true
        ]);

        $absence = Absence::create([
            'stagiaire_id' => $this->stagiaire2->id,
            'seance_id' => $seance1->id,
            'type' => 'absence',
            'autorisation_suivante' => false
        ]);

        // 2. Load formateur dashboard
        $response = $this->get(route('formateur.dashboard', ['groupe_id' => $this->groupe->id]));
        $response->assertStatus(200);

        // 3. Assert previousSessionStatuses contains correct information for session 2
        $response->assertViewHas('previousSessionStatuses');
        $prevStatuses = $response->viewData('previousSessionStatuses');

        $this->assertArrayHasKey(2, $prevStatuses);
        $this->assertArrayHasKey($this->stagiaire2->id, $prevStatuses[2]);
        $this->assertEquals('absence', $prevStatuses[2][$this->stagiaire2->id]);
    }

    /**
     * Test that previous session warnings are NOT displayed if justified or permitted.
     */
    public function test_formateur_dashboard_previous_session_warning_excluded_if_justified_or_permitted(): void
    {
        $this->actingAs($this->formateur);

        // 1. Create a previous session
        $seance1 = Seance::create([
            'groupe_id' => $this->groupe->id,
            'formateur_id' => $this->formateur->id,
            'num_seance' => 1,
            'date_debut' => Carbon::today()->setTime(8, 30, 0),
            'duree_heures' => 2.5,
            'est_validee' => true
        ]);

        // 2. Create a permitted absence
        $absence1 = Absence::create([
            'stagiaire_id' => $this->stagiaire1->id,
            'seance_id' => $seance1->id,
            'type' => 'absence',
            'autorisation_suivante' => true
        ]);

        // 3. Create a justified absence
        $absence2 = Absence::create([
            'stagiaire_id' => $this->stagiaire2->id,
            'seance_id' => $seance1->id,
            'type' => 'absence',
            'autorisation_suivante' => false
        ]);

        \App\Models\Justification::create([
            'absence_id' => $absence2->id,
            'motif' => 'Medical reason',
            'est_valide' => true
        ]);

        // 4. Load formateur dashboard
        $response = $this->get(route('formateur.dashboard', ['groupe_id' => $this->groupe->id]));
        $response->assertStatus(200);

        // 5. Assert neither of them are in the warnings for Séance 2
        $response->assertViewHas('previousSessionStatuses');
        $prevStatuses = $response->viewData('previousSessionStatuses');

        $this->assertArrayHasKey(2, $prevStatuses);
        $this->assertEmpty($prevStatuses[2]);
    }

    /**
     * Test that a gestionnaire can justify the latest absence and it becomes validated immediately.
     */
    public function test_gestionnaire_can_justify_latest_absence_which_becomes_valid_immediately(): void
    {
        // 1. Create a session and an absence
        $seance = Seance::create([
            'groupe_id' => $this->groupe->id,
            'formateur_id' => $this->formateur->id,
            'num_seance' => 1,
            'date_debut' => Carbon::today()->setTime(8, 30, 0),
            'duree_heures' => 2.5,
            'est_validee' => true
        ]);

        $absence = Absence::create([
            'stagiaire_id' => $this->stagiaire2->id,
            'seance_id' => $seance->id,
            'type' => 'absence',
            'autorisation_suivante' => true // Set true to verify it is reset to false once justified
        ]);

        // 2. Act as gestionnaire and justify
        $this->actingAs($this->gestionnaire);

        $response = $this->post(route('gestionnaire.justifier', ['absence' => $absence->id]), [
            'motif' => 'Reason for absence'
        ]);

        $response->assertRedirect();
        
        // 3. Assert justification created and is validated directly
        $justification = \App\Models\Justification::where('absence_id', $absence->id)->first();
        $this->assertNotNull($justification);
        $this->assertTrue($justification->est_valide);

        // 4. Assert that authorisation_suivante is reset to false
        $absence->refresh();
        $this->assertFalse($absence->autorisation_suivante);
    }

    /**
     * Test that the gestionnaire dashboard displays total hours of non-justified absences.
     */
    public function test_gestionnaire_dashboard_displays_non_justified_absences(): void
    {
        $this->actingAs($this->gestionnaire);

        // Create a session and a non-justified absence
        $seance = Seance::create([
            'groupe_id' => $this->groupe->id,
            'formateur_id' => $this->formateur->id,
            'num_seance' => 1,
            'date_debut' => Carbon::today()->setTime(8, 30, 0),
            'duree_heures' => 2.5,
            'est_validee' => true
        ]);

        $absence = Absence::create([
            'stagiaire_id' => $this->stagiaire2->id,
            'seance_id' => $seance->id,
            'type' => 'absence'
        ]);

        $response = $this->get(route('gestionnaire.dashboard'));
        $response->assertStatus(200);

        // Stagiaire 2 should have 2.5h of non-justified absences
        $this->assertEquals(2.5, $this->stagiaire2->heures_absence_non_justifiee);
    }

    /**
     * Test that the detailed show page displays all absences and correct totals.
     */
    public function test_gestionnaire_show_page_displays_all_absences_and_correct_totals_with_filtering(): void
    {
        $this->actingAs($this->gestionnaire);

        $seance1 = Seance::create([
            'groupe_id' => $this->groupe->id,
            'formateur_id' => $this->formateur->id,
            'num_seance' => 1,
            'date_debut' => Carbon::today()->setTime(8, 30, 0),
            'duree_heures' => 2.5,
            'est_validee' => true
        ]);

        $seance2 = Seance::create([
            'groupe_id' => $this->groupe->id,
            'formateur_id' => $this->formateur->id,
            'num_seance' => 2,
            'date_debut' => Carbon::today()->setTime(11, 0, 0),
            'duree_heures' => 2.5,
            'est_validee' => true
        ]);

        // Non-justified
        $absence1 = Absence::create([
            'stagiaire_id' => $this->stagiaire2->id,
            'seance_id' => $seance1->id,
            'type' => 'absence'
        ]);

        // Justified
        $absence2 = Absence::create([
            'stagiaire_id' => $this->stagiaire2->id,
            'seance_id' => $seance2->id,
            'type' => 'absence'
        ]);

        \App\Models\Justification::create([
            'absence_id' => $absence2->id,
            'motif' => 'Excused',
            'est_valide' => true
        ]);

        $response = $this->get(route('gestionnaire.stagiaires.show', $this->stagiaire2->id));
        $response->assertStatus(200);

        $response->assertViewHas('totalAbsencesCount', 2);
        $response->assertViewHas('totalHoursJustified', 2.5);
        $response->assertViewHas('totalHoursUnjustified', 2.5);
        $response->assertViewHas('totalHours', 5.0);

        // Filter: unjustified
        $responseFilter = $this->get(route('gestionnaire.stagiaires.show', [
            'stagiaire' => $this->stagiaire2->id,
            'status' => 'unjustified'
        ]));
        $responseFilter->assertStatus(200);
        $absences = $responseFilter->viewData('absences');
        $this->assertCount(1, $absences);
        $this->assertEquals($absence1->id, $absences->first()->id);
    }
}
