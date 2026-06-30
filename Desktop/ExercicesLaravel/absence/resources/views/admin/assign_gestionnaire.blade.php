<x-app-layout>
    <x-slot name="title">Assigner Pôle au Gestionnaire - AbsencePortal</x-slot>

    <div class="space-y-8" x-data="{
        selectedGestionnaireId: '{{ $selectedGestionnaireId ?? '' }}',
        gestionnairesData: @js($gestionnairesData),
        selectedPoleId: '',

        init() {
            this.$watch('selectedGestionnaireId', (val) => {
                this.updateSelection(val);
            });
            if (this.selectedGestionnaireId) {
                this.updateSelection(this.selectedGestionnaireId);
            }
        },

        updateSelection(id) {
            if (!id || !this.gestionnairesData[id]) {
                this.selectedPoleId = '';
                return;
            }
            this.selectedPoleId = this.gestionnairesData[id].pole_id || '';
        }
    }">
        <!-- En-tête -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-800 transition-colors mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Retour au Tableau de Bord
                </a>
                <h1 class="text-2xl font-bold text-slate-900">Assigner un pôle de compétences aux Gestionnaires</h1>
                <p class="text-sm text-slate-500 mt-1">Associez chaque gestionnaire à son pôle de compétence pour limiter son périmètre d'action.</p>
            </div>
        </div>

        <!-- Formulaire principal -->
        <form action="{{ route('admin.gestionnaires.assigner') }}" method="POST" class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-100 shadow-sm max-w-2xl space-y-6">
            @csrf

            <!-- Sélection du Gestionnaire -->
            <div class="space-y-2">
                <label for="gestionnaire_id" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Gestionnaire à configurer</label>
                <select name="gestionnaire_id" id="gestionnaire_id" required x-model="selectedGestionnaireId"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                    <option value="">Choisir un gestionnaire</option>
                    @foreach($gestionnaires as $gestionnaire)
                        <option value="{{ $gestionnaire->id }}">{{ $gestionnaire->name }} ({{ $gestionnaire->email }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Si aucun gestionnaire sélectionné -->
            <div x-show="!selectedGestionnaireId" class="py-12 text-center border-2 border-dashed border-slate-200 rounded-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="text-sm font-semibold text-slate-500">Veuillez sélectionner un gestionnaire ci-dessus pour modifier son pôle d'affectation.</p>
            </div>

            <!-- Contenu dynamique après sélection -->
            <div x-show="selectedGestionnaireId" class="space-y-6" style="display: none;">
                <!-- Sélection du Pôle -->
                <div class="space-y-2">
                    <label for="pole_id" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Pôle de compétence associé</label>
                    <p class="text-xs text-slate-400">Le gestionnaire ne verra et ne gérera que les groupes et stagiaires de ce pôle.</p>
                    <select name="pole_id" id="pole_id" x-model="selectedPoleId"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                        <option value="">Aucun pôle (Accès restreint)</option>
                        @foreach($poles as $pole)
                            <option value="{{ $pole->id }}">{{ $pole->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Bouton validation -->
                <div class="pt-4 flex items-center gap-3">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] transition-all text-white px-6 py-3 rounded-xl text-sm font-semibold shadow-md">
                        Enregistrer le Pôle
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3 rounded-xl text-sm font-semibold transition-all">
                        Annuler
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
