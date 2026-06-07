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
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-5">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Assigner groupes et modules</h2>
                    <p class="text-xs text-slate-500 mt-1">Choisissez un formateur, ses groupes et les modules qu'il peut assurer.</p>
                </div>

                <form action="{{ route('admin.formateurs.assigner') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="space-y-2">
                        <label for="formateur_id" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Formateur</label>
                        <select name="formateur_id" id="formateur_id" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Choisir un formateur</option>
                            @foreach($formateurs as $formateur)
                                <option value="{{ $formateur->id }}">
                                    {{ $formateur->name }} - Groupes: {{ $formateur->groupes->pluck('nom')->join(', ') ?: 'aucun' }} - Modules: {{ $formateur->modules->pluck('nom')->join(', ') ?: 'aucun' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="groupe_ids" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Groupes</label>
                        <select name="groupe_ids[]" id="groupe_ids" multiple
                                class="w-full min-h-36 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($groupes as $groupe)
                                <option value="{{ $groupe->id }}">{{ $groupe->nom }} - {{ $groupe->pole->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="module_ids" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Modules</label>
                        <select name="module_ids[]" id="module_ids" multiple
                                class="w-full min-h-36 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($modules as $module)
                                <option value="{{ $module->id }}">{{ $module->nom }} - {{ $module->science->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow-sm">
                        Enregistrer les affectations
                    </button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-5">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Assigner un pole</h2>
                    <p class="text-xs text-slate-500 mt-1">Associez chaque gestionnaire a son pole de competence.</p>
                </div>

                <form action="{{ route('admin.gestionnaires.assigner') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="space-y-2">
                        <label for="gestionnaire_id" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Gestionnaire</label>
                        <select name="gestionnaire_id" id="gestionnaire_id" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Choisir un gestionnaire</option>
                            @foreach($gestionnaires as $gestionnaire)
                                <option value="{{ $gestionnaire->id }}">
                                    {{ $gestionnaire->name }} - Pole actuel: {{ $gestionnaire->pole->nom ?? 'aucun' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="pole_id" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Pole</label>
                        <select name="pole_id" id="pole_id"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Aucun pole</option>
                            @foreach($poles as $pole)
                                <option value="{{ $pole->id }}">{{ $pole->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow-sm">
                        Enregistrer le pole
                    </button>
                </form>
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
