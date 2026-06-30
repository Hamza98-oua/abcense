<x-app-layout>
    <x-slot name="title">Affecter Groupes & Modules - AbsencePortal</x-slot>

    <div class="space-y-8" x-data="{
        selectedFormateurId: '{{ $selectedFormateurId ?? '' }}',
        formateursData: @js($formateursData),
        selectedGroupes: [],
        selectedModules: [],

        init() {
            this.$watch('selectedFormateurId', (val) => {
                this.updateSelections(val);
            });
            if (this.selectedFormateurId) {
                this.updateSelections(this.selectedFormateurId);
            }
        },

        updateSelections(id) {
            if (!id || !this.formateursData[id]) {
                this.selectedGroupes = [];
                this.selectedModules = [];
                return;
            }
            this.selectedGroupes = [...this.formateursData[id].groupe_ids];
            this.selectedModules = [...this.formateursData[id].module_ids];
        }
    }">
        <!-- En-tête -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Retour au Tableau de Bord
                </a>
                <h1 class="text-2xl font-bold text-slate-900">Affecter Groupes & Modules aux Formateurs</h1>
                <p class="text-sm text-slate-500 mt-1">Configurez les classes et les modules qu'un enseignant est habilité à animer.</p>
            </div>
        </div>

        <!-- Formulaire principal -->
        <form action="{{ route('admin.formateurs.assigner') }}" method="POST" class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-100 shadow-sm space-y-6">
            @csrf

            <!-- Sélection du Formateur -->
            <div class="space-y-2 max-w-md">
                <label for="formateur_id" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Formateur à configurer</label>
                <select name="formateur_id" id="formateur_id" required x-model="selectedFormateurId"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                    <option value="">Choisir un formateur</option>
                    @foreach($formateurs as $formateur)
                        <option value="{{ $formateur->id }}">{{ $formateur->name }} ({{ $formateur->email }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Si aucun formateur sélectionné -->
            <div x-show="!selectedFormateurId" class="py-12 text-center border-2 border-dashed border-slate-200 rounded-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <p class="text-sm font-semibold text-slate-500">Veuillez sélectionner un formateur ci-dessus pour modifier ses affectations.</p>
            </div>

            <!-- Contenu dynamique après sélection -->
            <div x-show="selectedFormateurId" class="space-y-8" style="display: none;">
                
                <!-- Section Groupes -->
                <div class="space-y-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">1. Groupes de stagiaires autorisés</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Cochez les groupes dont ce formateur pourra faire l'appel d'absence.</p>
                    </div>

                    <div class="space-y-6">
                        @foreach($groupes->groupBy('pole_id') as $poleId => $poleGroupes)
                            @php
                                $pole = $poleGroupes->first()->pole;
                            @endphp
                            <div class="space-y-3 bg-slate-50/75 p-5 rounded-2xl border border-slate-100">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 uppercase tracking-wider">
                                    Pôle : {{ $pole->nom }}
                                </span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                    @foreach($poleGroupes as $groupe)
                                        <label class="flex items-start gap-3 bg-white p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 hover:shadow-xs transition-all select-none">
                                            <input type="checkbox" name="groupe_ids[]" value="{{ $groupe->id }}" x-model.number="selectedGroupes"
                                                   class="mt-1 rounded border-slate-350 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                            <div>
                                                <span class="block text-sm font-bold text-slate-700 leading-tight">{{ $groupe->nom }}</span>
                                                <span class="block text-[11px] text-slate-400 mt-0.5">{{ $pole->nom }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- Section Modules -->
                <div class="space-y-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">2. Modules autorisés</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Cochez les modules d'enseignement que ce formateur est habilité à dispenser.</p>
                    </div>

                    <div class="space-y-6">
                        @foreach($modules->groupBy('science_id') as $scienceId => $scienceModules)
                            @php
                                $science = $scienceModules->first()->science;
                            @endphp
                            <div class="space-y-3 bg-slate-50/75 p-5 rounded-2xl border border-slate-100">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 uppercase tracking-wider">
                                    Matière : {{ $science->nom ?? 'Autre' }}
                                </span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                    @foreach($scienceModules as $module)
                                        <label class="flex items-start gap-3 bg-white p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 hover:shadow-xs transition-all select-none">
                                            <input type="checkbox" name="module_ids[]" value="{{ $module->id }}" x-model.number="selectedModules"
                                                   class="mt-1 rounded border-slate-350 text-indigo-650 focus:ring-indigo-550 w-4 h-4">
                                            <div>
                                                <span class="block text-sm font-bold text-slate-700 leading-tight">{{ $module->nom }}</span>
                                                <span class="block text-[11px] text-slate-400 mt-0.5">{{ $science->nom ?? 'Non défini' }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Bouton validation -->
                <div class="pt-4 flex items-center gap-3">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] transition-all text-white px-6 py-3 rounded-xl text-sm font-semibold shadow-md">
                        Enregistrer les modifications
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3 rounded-xl text-sm font-semibold transition-all">
                        Annuler
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
