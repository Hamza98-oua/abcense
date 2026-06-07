<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Science;
use App\Models\Module;
use App\Models\Groupe;

class ScienceModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Seed volontaire minimal : crée 2 sciences et quelques modules.
        // Tu pourras ensuite configurer le programme attendu pour chaque groupe via migrations/seeds.

        $science1 = Science::firstOrCreate(['nom' => 'Science 1']);
        $science2 = Science::firstOrCreate(['nom' => 'Science 2']);


        $modules = [
            ['nom' => 'Module A', 'science' => $science1],
            ['nom' => 'Module B', 'science' => $science1],
            ['nom' => 'Module C', 'science' => $science2],
        ];

        foreach ($modules as $m) {
            Module::firstOrCreate(
                ['nom' => $m['nom']],
                ['science_id' => $m['science']->id]
            );
        }

        // Associer au besoin le programme à tous les groupes existants.
        // (optionnel, mais aide pour tester l'UI)
        $allGroupes = Groupe::all();
        if ($allGroupes->count() > 0) {
            foreach ($allGroupes as $groupe) {
                $groupe->sciences()->syncWithoutDetaching([$science1->id, $science2->id]);

                $moduleA = Module::where('nom', 'Module A')->first();
                $moduleB = Module::where('nom', 'Module B')->first();
                $moduleC = Module::where('nom', 'Module C')->first();

                $ids = collect([$moduleA, $moduleB, $moduleC])->filter()->pluck('id')->all();
                $groupe->modules()->syncWithoutDetaching($ids);
            }
        }
    }
}

