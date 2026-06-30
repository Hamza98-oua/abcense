<x-app-layout>
    <x-slot name="title">Tableau de Bord Gestionnaire - AbsencePortal</x-slot>

    <!-- Structure globale de la page avec un store AlpineJS pour les modales -->
    <div x-data="{ 
        openHistoryModal: false, 
        openMissingModal: false,
        currentTrainee: { nom: '', prenom: '', id: '' },
        traineeAbsences: [],
        missingItems: [],
        openJustifyForm: null,

        showHistory(trainee, absences) {
            this.currentTrainee = trainee;
            this.traineeAbsences = absences;
            this.openHistoryModal = true;
            this.openJustifyForm = null;
        },

        showMissingModules(trainee, missingItems) {
            this.currentTrainee = trainee;
            this.missingItems = missingItems || [];
            this.openMissingModal = true;
        }
    }" class="space-y-8">

        <!-- En-tête avec les statistiques rapides -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Stagiaires -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="bg-indigo-50 text-indigo-600 p-3.5 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Stagiaires</span>
                    <span class="text-2xl font-bold text-slate-900 leading-tight">{{ $totalStagiaires }}</span>
                </div>
            </div>

            <!-- Stables (0h successive) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="bg-emerald-50 text-emerald-600 p-3.5 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Profils Stables</span>
                    <span class="text-2xl font-bold text-slate-900 leading-tight">{{ $totalStagiaires - ($nbAlerteWarning + $nbAlerteCritical) }}</span>
                </div>
            </div>

            <!-- Alerte Warning -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="bg-amber-50 text-amber-600 p-3.5 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Alertes Modérées</span>
                    <span class="text-2xl font-bold text-amber-600 leading-tight">{{ $nbAlerteWarning }}</span>
                </div>
            </div>

            <!-- Alerte Critique -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="bg-rose-50 text-rose-600 p-3.5 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Décrochages Critiques</span>
                    <span class="text-2xl font-bold text-rose-600 leading-tight">{{ $nbAlerteCritical }}</span>
                </div>
            </div>
        </div>

        <!-- Filtres et Barre de recherche -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <form id="gestionnaireFilterForm" action="{{ route('gestionnaire.dashboard') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <!-- Recherche -->
                <div class="space-y-2">
                    <label for="search" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Recherche</label>
                    <input type="text" name="search" id="search" placeholder="Nom, prénom, email..."
                           value="{{ $search }}"
                           autocomplete="off"
                           oninput="(function(){const f=document.getElementById('gestionnaireFilterForm');clearTimeout(window.__gestionnaireSearchT);window.__gestionnaireSearchT=setTimeout(()=>f.submit(),350);})()"
                           onkeydown="if(event.key==='Enter'){event.preventDefault();document.getElementById('gestionnaireFilterForm').submit();}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                </div>

                <!-- Groupe -->
                <div class="space-y-2">
                    <label for="groupe_id" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Groupe</label>
                    <select name="groupe_id" id="groupe_id" onchange="document.getElementById('gestionnaireFilterForm').submit()"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all cursor-pointer">
                        <option value="">Tous les groupes</option>
                        @foreach ($groupes as $g)
                            <option value="{{ $g->id }}" {{ $selectedGroupeId == $g->id ? 'selected' : '' }}>{{ $g->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- État Alerte -->
                <div class="space-y-2">
                    <label for="alert" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Niveau d'Alerte</label>
                    <select name="alert" id="alert" onchange="document.getElementById('gestionnaireFilterForm').submit()"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all cursor-pointer">
                        <option value="">Tous les états</option>
                        <option value="stable" {{ $alertFilter === 'stable' ? 'selected' : '' }}>Stables (0h successive)</option>
                        <option value="warning" {{ $alertFilter === 'warning' ? 'selected' : '' }}>Modéré (> 0h et < 7.5h)</option>
                        <option value="critical" {{ $alertFilter === 'critical' ? 'selected' : '' }}>Critique (>= 7.5h)</option>
                    </select>
                </div>

                <!-- Boutons (uniquement effacer si besoin) -->
                <div class="flex gap-2">
                    @if($search || $selectedGroupeId || $alertFilter)
                        <a href="{{ route('gestionnaire.dashboard') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-medium transition-all flex items-center justify-center">
                            Effacer
                        </a>
                    @endif
                </div>
            </form>
        </div>


        <!-- Tableau Datatable -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-400 font-semibold text-xs uppercase tracking-wider">
                            <th class="px-6 py-4">Stagiaire</th>
                            <th class="px-6 py-4">Groupe</th>
                            <th class="px-6 py-4 text-center">Absences Non Justifiées</th>
                            <th class="px-6 py-4 text-center">Indicateur Décrochage</th>
                            <th class="px-6 py-4">Dernière Absence</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse ($stagiaires as $stagiaire)
                            @php
                                $successives = $stagiaire->heures_absences_successives;
                                // Calcul du code couleur pour les absences successives (Décrochage)
                                if ($successives == 0) {
                                    $badgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-100';
                                    $dotClass = 'bg-emerald-500';
                                } elseif ($successives < 7.5) {
                                    $badgeClass = 'bg-amber-50 text-amber-800 border-amber-100';
                                    $dotClass = 'bg-amber-500';
                                } else {
                                    $badgeClass = 'bg-rose-50 text-rose-800 border-rose-100';
                                    $dotClass = 'bg-rose-500 animate-pulse';
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <!-- Infos Stagiaire -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-full bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-xs overflow-hidden">
                                            @if(!empty($stagiaire->image))
                                                <img
                                                    src="{{ asset('storage/' . $stagiaire->image) }}"
                                                    alt="{{ $stagiaire->nom }} {{ $stagiaire->prenom }}"
                                                    class="h-full w-full object-cover"
                                                />
                                            @else
                                                {{ substr($stagiaire->prenom, 0, 1) }}{{ substr($stagiaire->nom, 0, 1) }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $stagiaire->nom }} {{ $stagiaire->prenom }}</div>
                                            <div class="text-xs text-slate-400">CEF : {{ $stagiaire->cef }}</div>
                                            @if(!empty($stagiaire->email))
                                                <div class="text-[11px] text-slate-400">{{ $stagiaire->email }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <!-- Groupe -->
                                <td class="px-6 py-4 font-medium">{{ $stagiaire->groupe->nom }}</td>
                                <!-- Total Absences Non Justifiées -->
                                <td class="px-6 py-4 text-center font-bold text-slate-900">{{ number_format($stagiaire->heures_absence_non_justifiee, 1) }} h</td>
                                <!-- Décrochage -->
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                        {{ number_format($successives, 1) }} h
                                    </span>
                                </td>
                                <!-- Dernière Absence -->
                                <td class="px-6 py-4 text-slate-500">{{ $stagiaire->derniere_absence_relative }}</td>
                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <!-- Bouton pour ouvrir l'historique complet -->
                                    @php
                                        $sortedAbsences = $stagiaire->absences->sortByDesc(function($abs) {
                                            return $abs->seance->date_debut;
                                        });
                                        $latestAbsenceId = $sortedAbsences->first()?->id;
                                    @endphp
                                    <button @click="showHistory(
                                        { nom: '{{ addslashes($stagiaire->nom) }}', prenom: '{{ addslashes($stagiaire->prenom) }}', id: {{ $stagiaire->id }} },
                                        {{ $sortedAbsences->map(function($abs) use ($latestAbsenceId) {
                                            return [
                                                'id' => $abs->id,
                                                'is_latest' => $abs->id === $latestAbsenceId,
                                                'date' => $abs->seance->date_debut->format('d/m/Y'),
                                                'heure' => $abs->seance->date_debut->format('H:i'),
                                                'duree' => $abs->seance->duree_heures,
                                                'num_seance' => $abs->seance->num_seance,
                                                'type' => $abs->type,
                                                'autorisation_suivante' => $abs->autorisation_suivante,
                                                'justification' => $abs->justification ? [
                                                    'id' => $abs->justification->id,
                                                    'motif' => $abs->justification->motif,
                                                    'fichier' => $abs->justification->fichier_joint ? asset('storage/' . $abs->justification->fichier_joint) : null,
                                                    'valide' => $abs->justification->est_valide
                                                ] : null
                                            ];
                                        })->values()->toJson() }}
                                    )" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all">
                                        Gérer
                                    </button>

                                    <!-- Bouton Show details page -->
                                    <a href="{{ route('gestionnaire.stagiaires.show', $stagiaire->id) }}"
                                       class="ml-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all inline-block">
                                        Show
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    Aucun stagiaire ne correspond aux critères de filtrage.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section Import Excel et Justificatifs en attente -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Formulaire d'Import Excel -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-50 text-indigo-600 p-2.5 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-slate-900">Importation de Fiche d'Absences Excel</h2>
                </div>
                <p class="text-xs text-slate-500">
                    Importez en masse les absences à partir d'un classeur Excel (`.xlsx`, `.xls` ou `.csv`).
                    Le fichier doit comporter les en-têtes exacts : **CEF**, **nom** (optionnel), **prenom** (optionnel), **date_debut_seance** (ex: 2026-06-07 08:30:00), et optionnellement **duree_heures**.
                    Colonnes optionnelles : **email**, **image**.
                </p>

                <form action="{{ route('gestionnaire.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4 pt-2">
                    @csrf
                    <div class="flex items-center gap-3">
                        <input type="file" name="excel_file" required
                               class="flex-grow text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 file:cursor-pointer">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] transition-all text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-md">
                            Importer
                        </button>
                    </div>
                </form>
            </div>

            <!-- Légende -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <h2 class="text-base font-bold text-slate-900">Politique d'Alerte Décrochage</h2>
                <p class="text-xs text-slate-500">
                    Les indicateurs de décrochage se basent sur les absences consécutives du stagiaire depuis sa dernière présence physique validée par un formateur.
                </p>
                <div class="space-y-3 pt-1">
                    <div class="flex items-center gap-3">
                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <div class="text-xs">
                            <span class="font-bold text-emerald-800">Profil Stable (0.0 h) :</span> Présent lors du dernier appel validé.
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-3.5 h-3.5 rounded-full bg-amber-500 shrink-0"></span>
                        <div class="text-xs">
                            <span class="font-bold text-amber-800">Alerte Modérée (2.5 h à 5.0 h) :</span> Le stagiaire a manqué 1 ou 2 séances d'affilée. Dossier à surveiller.
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-3.5 h-3.5 rounded-full bg-rose-500 shrink-0"></span>
                        <div class="text-xs">
                            <span class="font-bold text-rose-800">Décrochage Critique (>= 7.5 h) :</span> Le stagiaire a manqué au moins 3 séances d'affilée. Contact d'urgence requis.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= MODALE D'HISTORIQUE ET JUSTIFICATION (AlpineJS) ================= -->
        <div x-show="openHistoryModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             style="display: none;">

            
            <div @click.away="openHistoryModal = false" class="bg-white rounded-2xl w-full max-w-2xl border border-slate-100 shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
                <!-- Header -->
                <div class="bg-slate-900 px-6 py-4 text-white flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold" x-text="'Dossier Absences : ' + currentTrainee.prenom + ' ' + currentTrainee.nom"></h3>
                        <p class="text-xs text-slate-400">Suivi et enregistrement des justificatifs</p>
                    </div>
                    <button @click="openHistoryModal = false" class="text-slate-400 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Corps de la modale -->
                <div class="p-6 overflow-y-auto space-y-4 flex-grow">
                    <template x-if="traineeAbsences.length === 0">
                        <div class="text-center py-12 text-slate-400 text-sm">
                            Ce stagiaire a un dossier d'assiduité irréprochable (aucune absence).
                        </div>
                    </template>

                    <template x-if="traineeAbsences.length > 0">
                        <div class="space-y-4">
                            <template x-for="absence in traineeAbsences" :key="absence.id">
                                <div class="bg-slate-50 p-4 border border-slate-100 rounded-xl space-y-3">
                                    <!-- Entête Absence -->
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2">
                                                <span :class="absence.type === 'retard' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-rose-100 text-rose-800 border-rose-200'" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase border" x-text="absence.type === 'retard' ? 'Retard' : 'Absence'"></span>
                                                <span x-show="absence.num_seance" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200" x-text="'Séance ' + absence.num_seance + ' (' + (absence.num_seance == 1 ? '08:30-11:00' : absence.num_seance == 2 ? '11:00-13:30' : absence.num_seance == 3 ? '13:30-16:00' : '16:00-18:30') + ')'"></span>
                                            </div>
                                            <div class="text-xs font-semibold text-slate-400">
                                                Date : <span class="text-slate-800 font-bold" x-text="absence.date"></span> à <span class="text-slate-800 font-bold" x-text="absence.heure"></span> (Durée : <span x-text="parseFloat(absence.duree).toFixed(1) + 'h'"></span>)
                                            </div>
                                        </div>
                                        <div>
                                            <!-- Statut justificatif -->
                                            <span x-show="absence.justification && absence.justification.valide" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                Justifié
                                            </span>
                                            <span x-show="absence.justification && !absence.justification.valide" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                                En attente de validation
                                            </span>
                                            <span x-show="!absence.justification && absence.autorisation_suivante" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                                Autorisé séance suivante
                                            </span>
                                            <span x-show="!absence.justification && !absence.autorisation_suivante" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                                                Non justifié
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Détails Justificatif -->
                                    <div x-show="absence.justification" class="border-t border-slate-200/50 pt-2 space-y-2">
                                        <div class="text-xs"><strong class="text-slate-600">Motif :</strong> <span class="text-slate-600" x-text="absence.justification?.motif"></span></div>
                                        <div x-show="absence.justification?.fichier" class="text-xs">
                                            <strong class="text-slate-600">Pièce jointe :</strong> 
                                            <a :href="absence.justification?.fichier" target="_blank" class="text-indigo-650 hover:underline inline-flex items-center gap-1 font-semibold">
                                                Ouvrir le document
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                            </a>
                                        </div>
                                        
                                        <!-- Actions de validation (Gestionnaire) -->
                                        <div x-show="absence.justification && !absence.justification.valide" class="flex justify-end pt-1">
                                            <form :action="'/gestionnaire/justifications/' + absence.justification?.id + '/valider'" method="POST">
                                                @csrf
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold py-1.5 px-3.5 rounded-lg shadow-sm">
                                                    Valider ce justificatif
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Formulaire d'ajout de justificatif / autorisation -->
                                    <div x-show="absence.is_latest && !absence.justification" class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <button x-show="openJustifyForm !== absence.id" 
                                                    @click="openJustifyForm = absence.id" 
                                                    class="text-xs text-indigo-650 hover:underline font-semibold flex items-center gap-1">
                                                + Ajouter un justificatif
                                            </button>
                                            
                                            <span x-show="openJustifyForm !== absence.id && !absence.autorisation_suivante" class="text-slate-300">|</span>

                                            <form x-show="openJustifyForm !== absence.id && !absence.autorisation_suivante" :action="'/gestionnaire/absences/' + absence.id + '/autoriser'" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs text-emerald-600 hover:underline font-semibold flex items-center gap-1">
                                                    + Autoriser entrée séance suivante (sans justificatif)
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <!-- Formulaire révélé -->
                                        <div x-show="openJustifyForm === absence.id" class="border-t border-slate-200/50 pt-3 mt-2">
                                            <form :action="'/gestionnaire/absences/' + absence.id + '/justifier'" method="POST" enctype="multipart/form-data" class="space-y-3">
                                                @csrf
                                                <div class="space-y-1">
                                                    <label class="block text-[11px] font-bold text-slate-500 uppercase">Motif de l'absence</label>
                                                    <textarea name="motif" required placeholder="Saisir la raison..." class="w-full text-xs bg-white border border-slate-200 rounded-lg p-2 focus:ring-1 focus:ring-indigo-500 focus:outline-none"></textarea>
                                                </div>
                                                <div class="space-y-1">
                                                    <label class="block text-[11px] font-bold text-slate-500 uppercase">Fichier joint (optionnel)</label>
                                                    <input type="file" name="fichier" class="block w-full text-xs text-slate-500 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 file:cursor-pointer">
                                                </div>
                                                <div class="flex justify-end gap-2 text-xs pt-1">
                                                    <button type="button" @click="openJustifyForm = null" class="px-2.5 py-1 text-slate-500 font-semibold">Annuler</button>
                                                    <button type="submit" class="bg-indigo-650 hover:bg-indigo-700 text-white font-semibold px-3 py-1 rounded-lg">Enregistrer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                <!-- Footer de la modale -->
                <div class="bg-slate-50 border-t border-slate-100 px-6 py-3 text-right">
                    <button @click="openHistoryModal = false" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold py-2 px-5 rounded-xl">
                        Fermer
                    </button>
                </div>
            </div>
        </div>

        <!-- ================= MODALE SCIENCES / MODULES MANQUES ================= -->
        <div x-show="openMissingModal"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             style="display: none;">

            <div @click.away="openMissingModal = false" class="bg-white rounded-2xl w-full max-w-2xl border border-slate-100 shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
                <div class="bg-slate-900 px-6 py-4 text-white flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold" x-text="'Absences par module : ' + currentTrainee.prenom + ' ' + currentTrainee.nom"></h3>
                        <p class="text-xs text-slate-400">Seances validees manquees, regroupees par science et module</p>
                    </div>
                    <button @click="openMissingModal = false" class="text-slate-400 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto space-y-4 flex-grow">
                    <template x-if="missingItems.length === 0">
                        <div class="text-center py-12 text-slate-400 text-sm">
                            Aucune absence validee avec science/module pour ce stagiaire.
                        </div>
                    </template>

                    <template x-if="missingItems.length > 0">
                        <div class="space-y-3">
                            <template x-for="item in missingItems" :key="item.science + '-' + item.module">
                                <div class="bg-slate-50 p-4 border border-slate-100 rounded-xl space-y-3">
                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                        <div>
                                            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider" x-text="item.science"></div>
                                            <div class="text-sm font-bold text-slate-900" x-text="item.module"></div>
                                            <div class="text-xs text-slate-500 mt-1" x-text="'Derniere absence : ' + item.derniere_absence"></div>
                                        </div>
                                        <div class="flex gap-2">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100" x-text="parseFloat(item.total_heures).toFixed(1) + ' h'"></span>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200" x-text="item.nb_seances + ' seance(s)'"></span>
                                        </div>
                                    </div>

                                    <div class="border-t border-slate-200/60 pt-3">
                                        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Dernieres seances manquees</div>
                                        <div class="space-y-1">
                                            <template x-for="detail in item.details" :key="detail.date">
                                                <div class="flex justify-between text-xs text-slate-600">
                                                    <span x-text="detail.date"></span>
                                                    <span class="font-semibold" x-text="parseFloat(detail.duree).toFixed(1) + ' h'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="bg-slate-50 border-t border-slate-100 px-6 py-3 text-right">
                    <button @click="openMissingModal = false" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold py-2 px-5 rounded-xl">
                        Fermer
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
