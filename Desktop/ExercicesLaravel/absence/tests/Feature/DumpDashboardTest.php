<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pole;
use App\Models\Groupe;

class DumpDashboardTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_dump_dashboard()
    {
        $pole = Pole::create(['nom' => 'Digital & Technologie']);
        $formateur = User::create([
            'name' => 'Jean Formateur',
            'email' => 'formateur@test.com',
            'password' => bcrypt('password123'),
            'role' => 'formateur'
        ]);
        $groupe = Groupe::create(['nom' => 'DEV-102', 'pole_id' => $pole->id]);
        $formateur->groupes()->attach($groupe->id);

        $response = $this->actingAs($formateur)->get(route('formateur.dashboard', ['groupe_id' => $groupe->id]));
        
        // Output the HTML content to a file in the workspace
        file_put_contents(base_path('scratch/dashboard_dump.html'), $response->getContent());
        
        $this->assertEquals(200, $response->status());
    }
}
