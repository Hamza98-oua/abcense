<x-app-layout>
    <x-slot name="title">Dashboard Admin - AbsencePortal</x-slot>

    <div class="space-y-8">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Dashboard Admin</h1>
                <p class="text-sm text-slate-500 mt-1">Suivi global des absences et gestion des affectations.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Absences recentes</span>
                <span class="text-2xl font-bold text-slate-900">{{ $absences->count() }}</span>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Formateurs</span>
                <span class="text-2xl font-bold text-slate-900">{{ $formateurs->count() }}</span>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Gestionnaires</span>
                <span class="text-2xl font-bold text-slate-900">{{ $gestionnaires->count() }}</span>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Poles</span>
                <span class="text-2xl font-bold text-slate-900">{{ $poles->count() }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Table des Formateurs -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Affectations Formateurs</h2>
                            <p class="text-xs text-slate-500 mt-1">Groupes et modules attribués à chaque formateur.</p>
                        </div>
                        <a href="{{ route('admin.formateurs.assigner.form') }}" class="bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] transition-all text-white px-3.5 py-2 rounded-xl text-xs font-semibold shadow-sm shrink-0">
                            Gérer tout
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 font-semibold text-[10px] uppercase tracking-wider">
                                    <th class="px-6 py-3">Formateur</th>
                                    <th class="px-6 py-3">Groupes & Modules</th>
                                    <th class="px-6 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                                @forelse($formateurs as $formateur)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-900">{{ $formateur->name }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $formateur->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 space-y-1">
                                            <div>
                                                <span class="font-semibold text-slate-500">Groupes :</span> 
                                                @if($formateur->groupes->isNotEmpty())
                                                    <span class="text-slate-800">{{ $formateur->groupes->pluck('nom')->join(', ') }}</span>
                                                @else
                                                    <span class="text-slate-400 italic">Aucun</span>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-500">Modules :</span> 
                                                @if($formateur->modules->isNotEmpty())
                                                    <span class="text-slate-800">{{ $formateur->modules->pluck('nom')->join(', ') }}</span>
                                                @else
                                                    <span class="text-slate-400 italic">Aucun</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('admin.formateurs.assigner.form', ['formateur_id' => $formateur->id]) }}" class="text-indigo-650 hover:text-indigo-850 font-bold transition-all hover:underline">
                                                Modifier
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-slate-400 italic">Aucun formateur trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Table des Gestionnaires -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Pôles Gestionnaires</h2>
                            <p class="text-xs text-slate-500 mt-1">Pôles de compétence affectés aux gestionnaires.</p>
                        </div>
                        <a href="{{ route('admin.gestionnaires.assigner.form') }}" class="bg-emerald-650 hover:bg-emerald-750 active:scale-[0.98] transition-all text-white px-3.5 py-2 rounded-xl text-xs font-semibold shadow-sm shrink-0">
                            Gérer tout
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 font-semibold text-[10px] uppercase tracking-wider">
                                    <th class="px-6 py-3">Gestionnaire</th>
                                    <th class="px-6 py-3">Pôle Affecté</th>
                                    <th class="px-6 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                                @forelse($gestionnaires as $gestionnaire)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-900">{{ $gestionnaire->name }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $gestionnaire->email }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($gestionnaire->pole)
                                                <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                    {{ $gestionnaire->pole->nom }}
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-50 text-slate-400 border border-slate-100">
                                                    Aucun pôle
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('admin.gestionnaires.assigner.form', ['gestionnaire_id' => $gestionnaire->id]) }}" class="text-emerald-600 hover:text-emerald-800 font-bold transition-all hover:underline">
                                                Modifier
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-slate-400 italic">Aucun gestionnaire trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-900">Absences recentes</h2>
                <p class="text-xs text-slate-500 mt-1">Les 80 dernieres absences enregistrees dans la plateforme.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 font-semibold text-xs uppercase tracking-wider">
                            <th class="px-6 py-4">Stagiaire</th>
                            <th class="px-6 py-4">Groupe / Pole</th>
                            <th class="px-6 py-4">Module</th>
                            <th class="px-6 py-4">Formateur</th>
                            <th class="px-6 py-4 text-center">Duree</th>
                            <th class="px-6 py-4">Justification</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($absences as $absence)
                            @php
                                $seance = $absence->seance;
                                $module = $seance->module;
                                $science = $seance->science ?: $module?->science;
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $absence->stagiaire->nom }} {{ $absence->stagiaire->prenom }}</div>
                                    <div class="text-xs text-slate-400">CEF : {{ $absence->stagiaire->cef }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $absence->stagiaire->groupe->nom }}</div>
                                    <div class="text-xs text-slate-400">{{ $absence->stagiaire->groupe->pole->nom }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ $module->nom ?? 'Module non renseigne' }}</div>
                                    <div class="text-xs text-slate-400">{{ $science->nom ?? 'Science non renseignee' }}</div>
                                </td>
                                <td class="px-6 py-4">{{ $seance->formateur->name ?? 'Non renseigne' }}</td>
                                <td class="px-6 py-4 text-center font-bold">{{ number_format($seance->duree_heures, 1) }} h</td>
                                <td class="px-6 py-4">
                                    @if($absence->justification && $absence->justification->est_valide)
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">Validee</span>
                                    @elseif($absence->justification)
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-100">En attente</span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100">Non justifiee</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">Aucune absence enregistree.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
