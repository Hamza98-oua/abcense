<x-app-layout>
    <x-slot name="title">Absences de {{ $stagiaire->nom }} {{ $stagiaire->prenom }} - AbsencePortal</x-slot>

    <div class="space-y-8" x-data="{ openJustifyForm: null }">
        <!-- Lien Retour -->
        <div>
            <a href="{{ route('gestionnaire.dashboard') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour au Tableau de Bord
            </a>
        </div>

        <!-- Profil du Stagiaire & Totaux -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Carte Profil -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="h-16 w-16 rounded-full bg-slate-100 text-slate-600 font-black flex items-center justify-center text-xl overflow-hidden shrink-0">
                    @if(!empty($stagiaire->image))
                        <img src="{{ asset('storage/' . $stagiaire->image) }}" alt="{{ $stagiaire->nom }} {{ $stagiaire->prenom }}" class="h-full w-full object-cover" />
                    @else
                        {{ substr($stagiaire->prenom, 0, 1) }}{{ substr($stagiaire->nom, 0, 1) }}
                    @endif
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900 leading-tight">{{ $stagiaire->nom }} {{ $stagiaire->prenom }}</h2>
                    <p class="text-xs text-slate-400 mt-1">Groupe : <span class="font-bold text-slate-700">{{ $stagiaire->groupe->nom }}</span></p>
                    <p class="text-[11px] text-slate-400">CEF : <span class="font-bold text-slate-700">{{ $stagiaire->cef }}</span></p>
                    @if(!empty($stagiaire->email))
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $stagiaire->email }}</p>
                    @endif
                </div>
            </div>

            <!-- Carte Stats Globales -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-6">
                <!-- Nombre Total d'Absences -->
                <div>
                    <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Total Absences</span>
                    <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ $totalAbsencesCount }}</span>
                    <span class="text-xs text-slate-400 block mt-0.5">séance(s)</span>
                </div>
                <!-- Durée Totale d'Absences -->
                <div>
                    <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Durée Totale</span>
                    <span class="text-2xl font-extrabold text-slate-900 mt-1 block">{{ number_format($totalHours, 1) }} h</span>
                    <span class="text-xs text-slate-400 block mt-0.5">d'absences au total</span>
                </div>
                <!-- Absences Justifiées -->
                <div>
                    <span class="block text-[10px] font-semibold text-emerald-600 uppercase tracking-wider">Justifiées</span>
                    <span class="text-2xl font-extrabold text-emerald-600 mt-1 block">{{ number_format($totalHoursJustified, 1) }} h</span>
                    <span class="text-xs text-emerald-500/80 block mt-0.5">heures justifiées</span>
                </div>
                <!-- Absences Non Justifiées -->
                <div>
                    <span class="block text-[10px] font-semibold text-rose-600 uppercase tracking-wider">Non Justifiées</span>
                    <span class="text-2xl font-extrabold text-rose-600 mt-1 block">{{ number_format($totalHoursUnjustified, 1) }} h</span>
                    <span class="text-xs text-rose-500/80 block mt-0.5">heures non justifiées</span>
                </div>
            </div>
        </div>

        <!-- Filtres des Absences -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Filtrer l'historique d'absences</h3>
            <form action="{{ route('gestionnaire.stagiaires.show', $stagiaire->id) }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <!-- Statut -->
                <div class="space-y-1">
                    <label for="status" class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Statut</label>
                    <select name="status" id="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all cursor-pointer">
                        <option value="">Tous les statuts</option>
                        <option value="justified" {{ $statusFilter === 'justified' ? 'selected' : '' }}>Absences Justifiées</option>
                        <option value="unjustified" {{ $statusFilter === 'unjustified' ? 'selected' : '' }}>Absences Non Justifiées</option>
                    </select>
                </div>

                <!-- Nom de Module / Science -->
                <div class="space-y-1">
                    <label for="search_module" class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Module / Science</label>
                    <input type="text" name="search_module" id="search_module" placeholder="Rechercher par module..." value="{{ $searchModule }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                </div>

                <!-- Date Début -->
                <div class="space-y-1">
                    <label for="date_debut" class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Depuis le</label>
                    <input type="date" name="date_debut" id="date_debut" value="{{ $dateDebut }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all cursor-pointer">
                </div>

                <!-- Date Fin -->
                <div class="space-y-1">
                    <label for="date_fin" class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Jusqu'au</label>
                    <input type="date" name="date_fin" id="date_fin" value="{{ $dateFin }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all cursor-pointer">
                </div>

                <!-- Actions du Filtre -->
                <div class="col-span-1 sm:col-span-2 lg:col-span-4 flex justify-end gap-2 pt-2">
                    @if($statusFilter || $searchModule || $dateDebut || $dateFin)
                        <a href="{{ route('gestionnaire.stagiaires.show', $stagiaire->id) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-semibold transition-all">
                            Réinitialiser
                        </a>
                    @endif
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-xl text-xs font-semibold transition-all shadow-md">
                        Filtrer
                    </button>
                </div>
            </form>
        </div>

        <!-- Liste des Absences -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 font-semibold text-xs uppercase tracking-wider">
                            <th class="px-6 py-4">Séance</th>
                            <th class="px-6 py-4">Module / Science</th>
                            <th class="px-6 py-4 text-center">Durée</th>
                            <th class="px-6 py-4 text-center">Statut</th>
                            <th class="px-6 py-4">Détails de Justification</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @php
                            // L'absence la plus récente parmi toutes celles du stagiaire
                            $latestAbsenceId = $stagiaire->absences()->join('seances', 'absences.seance_id', '=', 'seances.id')->orderBy('seances.date_debut', 'desc')->first()?->id;
                        @endphp
                        @forelse($absences as $absence)
                            @php
                                $isLatest = ($absence->id === $latestAbsenceId);
                                $isJustified = ($absence->justification && $absence->justification->est_valide);
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <!-- Séance -->
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-950">{{ $absence->seance->date_debut->format('d/m/Y') }}</div>
                                    <div class="text-xs text-slate-400">{{ $absence->seance->date_debut->format('H:i') }}</div>
                                    @if($absence->seance->num_seance)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100 mt-1">
                                            Séance {{ $absence->seance->num_seance }}
                                        </span>
                                    @endif
                                </td>
                                <!-- Module / Science -->
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-800">{{ $absence->seance->module->nom ?? 'Module non renseigné' }}</div>
                                    <div class="text-xs text-slate-400">{{ $absence->seance->science->nom ?? ($absence->seance->module->science->nom ?? 'Science non renseignée') }}</div>
                                </td>
                                <!-- Durée -->
                                <td class="px-6 py-4 text-center font-semibold text-slate-900">{{ number_format($absence->seance->duree_heures, 1) }} h</td>
                                <!-- Statut -->
                                <td class="px-6 py-4 text-center">
                                    @if($isJustified)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Justifié
                                        </span>
                                    @elseif($absence->justification && !$absence->justification->est_valide)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                            À valider
                                        </span>
                                    @elseif($absence->autorisation_suivante)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                            Entrée Autorisée
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                                            Non Justifié
                                        </span>
                                    @endif
                                </td>
                                <!-- Justification -->
                                <td class="px-6 py-4 text-xs">
                                    @if($absence->justification)
                                        <div class="space-y-1">
                                            <div><span class="font-bold text-slate-500 uppercase text-[10px]">Motif :</span> {{ $absence->justification->motif }}</div>
                                            @if($absence->justification->fichier_joint)
                                                <a href="{{ asset('storage/' . $absence->justification->fichier_joint) }}" target="_blank" class="text-indigo-650 hover:underline font-bold inline-flex items-center gap-0.5">
                                                    Ouvrir le justificatif
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">Aucune justification</span>
                                    @endif
                                </td>
                                <!-- Actions -->
                                <td class="px-6 py-4 text-right text-xs">
                                    @if($isLatest && !$isJustified)
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openJustifyForm = (openJustifyForm === {{ $absence->id }} ? null : {{ $absence->id }})" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg font-bold transition-all">
                                                Justifier
                                            </button>

                                            @if(!$absence->autorisation_suivante)
                                                <form action="{{ route('gestionnaire.absences.autoriser', $absence->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-lg font-bold transition-all">
                                                        Autoriser entrée
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        <!-- Formulaire en ligne (déroulant) -->
                                        <div x-show="openJustifyForm === {{ $absence->id }}" x-cloak class="mt-3 bg-slate-50 border border-slate-200 p-4 rounded-xl text-left space-y-3">
                                            <h4 class="text-xs font-bold text-slate-800 uppercase">Justification pour la séance</h4>
                                            <form action="{{ route('gestionnaire.justifier', $absence->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                                @csrf
                                                <div class="space-y-1">
                                                    <label class="block text-[10px] font-bold text-slate-400 uppercase">Motif</label>
                                                    <textarea name="motif" required placeholder="Motif officiel..." class="w-full text-xs bg-white border border-slate-200 rounded-lg p-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"></textarea>
                                                </div>
                                                <div class="space-y-1">
                                                    <label class="block text-[10px] font-bold text-slate-400 uppercase">Fichier justificatif (JPEG, PNG, PDF)</label>
                                                    <input type="file" name="fichier" class="block w-full text-xs text-slate-500 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 file:cursor-pointer">
                                                </div>
                                                <div class="flex justify-end gap-2 pt-1">
                                                    <button type="button" @click="openJustifyForm = null" class="px-2.5 py-1 text-slate-500 font-semibold">Annuler</button>
                                                    <button type="submit" class="bg-indigo-650 hover:bg-indigo-700 text-white font-semibold px-4 py-1.5 rounded-lg shadow">Valider</button>
                                                </div>
                                            </form>
                                        </div>
                                    @elseif($absence->justification && !$absence->justification->est_valide)
                                        <form action="{{ route('gestionnaire.justification.valider', $absence->justification->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold py-1.5 px-3 rounded-lg shadow-sm">
                                                Valider le justificatif
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400 italic">Aucune action possible</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    Aucune absence ne correspond à vos filtres.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
