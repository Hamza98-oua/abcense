<x-app-layout>
    <x-slot name="title">Saisie d'Appel Rapide - AbsencePortal</x-slot>

    <div class="max-w-4xl mx-auto space-y-8">
        <!-- En-tête de page -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Saisie de Présence</h1>
                <p class="text-sm text-slate-500 mt-1">Sélectionnez le groupe et validez les présences en un clic.</p>
            </div>

            <!-- Sélecteur de groupe -->
            <form action="{{ route('formateur.dashboard') }}" method="GET" id="groupForm" class="w-full md:w-auto">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <label for="groupe_id" class="text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap self-center">Groupe :</label>
                    <select name="groupe_id" id="groupe_id" onchange="document.getElementById('groupForm').submit()" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all cursor-pointer font-medium">
                        @foreach ($groupes as $g)
                            <option value="{{ $g->id }}" {{ $selectedGroupe && $selectedGroupe->id == $g->id ? 'selected' : '' }}>
                                {{ $g->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        @if ($selectedGroupe)
            <!-- Formulaire principal de l'appel -->
            <form action="{{ route('formateur.valider') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="groupe_id" value="{{ $selectedGroupe->id }}">

                <!-- Détails de la séance -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="date_debut" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Date et Heure de Début</label>
                        <input type="datetime-local" name="date_debut" id="date_debut" required 
                               value="{{ now()->format('Y-m-d\TH:i') }}" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                    </div>

                    <div class="space-y-2">
                        <label for="science_id" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Science</label>
                        <select name="science_id" id="science_id"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all cursor-pointer">
                            <option value="">(Choisir une science)</option>
                            @foreach ($sciences as $science)
                                <option value="{{ $science->id }}" {{ request('science_id') == $science->id ? 'selected' : '' }}>
                                    {{ $science->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="module_id" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Module</label>
                        <select name="module_id" id="module_id"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all cursor-pointer">
                            <option value="">(Choisir un module)</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module->id }}" {{ request('module_id') == $module->id ? 'selected' : '' }}>
                                    {{ $module->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="duree_heures" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Durée de la séance (heures)</label>
                        <select name="duree_heures" id="duree_heures" required 
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all">
                            <option value="2.50" selected>2,5 heures (Séance standard)</option>
                            <option value="5.00">5,0 heures (Double séance)</option>
                            <option value="1.00">1,0 heure</option>
                            <option value="1.50">1,5 heure</option>
                            <option value="2.00">2,0 heures</option>
                        </select>
                    </div>
                </div>

                <!-- Liste des stagiaires -->
                <div class="space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wider px-1">
                            Stagiaires du groupe {{ $selectedGroupe->nom }} ({{ $stagiaires->count() }})
                        </h2>

                        <div class="w-full sm:w-auto">
                            <label for="filter_cef" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                                Filtrer par CEF
                            </label>
                                <input
                                type="text"
                                id="filter_cef"
                                placeholder="Ex: CEF-..."
                                value="{{ request('cef') }}"
                                onchange="document.getElementById('cefFilterValue').value = this.value; document.getElementById('cefFilterForm').submit();"
                                class="w-full sm:w-72 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
                            >
                            
                            <form id="cefFilterForm" action="{{ route('formateur.dashboard') }}" method="GET" class="hidden">
                                <input type="hidden" name="groupe_id" value="{{ $selectedGroupe->id }}">
                                <input type="hidden" name="cef" id="cefFilterValue" value="{{ request('cef') }}">
                            </form>
                        </div>
                    </div>
                    
                    @if ($stagiaires->isEmpty())
                        <div class="bg-white border border-slate-100 rounded-2xl p-8 text-center text-slate-500">
                            Aucun stagiaire n'est enregistré dans ce groupe.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($stagiaires as $stagiaire)
                                <!-- Carte Stagiaire interactive via AlpineJS -->
                                <div x-data="{ present: true }" 
                                     @click="present = !present"
                                     :class="present 
                                        ? 'bg-emerald-50/50 border-emerald-200 hover:border-emerald-300 ring-2 ring-emerald-500/0 hover:ring-emerald-500/5' 
                                        : 'bg-rose-50/50 border-rose-200 hover:border-rose-300 ring-2 ring-rose-500/0 hover:ring-rose-500/5'"
                                     class="flex items-center justify-between p-4 border rounded-2xl cursor-pointer transition-all duration-200 select-none shadow-sm">
                                    
                                    <div class="flex items-center gap-3">
                                        <!-- Avatar initiales -->
                                        <div :class="present ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white'" 
                                             class="h-10 w-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-200">
                                            {{ substr($stagiaire->prenom, 0, 1) }}{{ substr($stagiaire->nom, 0, 1) }}
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-900 text-sm leading-tight">{{ $stagiaire->nom }} {{ $stagiaire->prenom }}</h3>
                                            <p class="text-xs text-slate-400 mt-0.5">CEF : {{ $stagiaire->cef }}</p>
                                            @if(!empty($stagiaire->email))
                                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $stagiaire->email }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Badge d'état -->
                                    <div>
                                        <span x-show="present" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 transition-all">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Présent
                                        </span>
                                        <span x-show="!present" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 transition-all">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Absent
                                        </span>
                                    </div>

                                    <!-- Input caché transmis dans le formulaire -->
                                    <input type="checkbox" name="presents[]" value="{{ $stagiaire->id }}" x-model="present" class="hidden">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if (!$stagiaires->isEmpty())
                    <!-- Bouton de validation -->
                    <div class="flex justify-end pt-4">
                        <button type="submit" class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] transition-all text-white font-semibold text-sm py-3.5 px-8 rounded-xl shadow-lg shadow-emerald-600/10 flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Valider l'Appel de la Séance
                        </button>
                    </div>
                @endif
            </form>
        @else
            <div class="bg-white border border-slate-100 rounded-2xl p-12 text-center text-slate-500 shadow-sm">
                <p class="font-medium">Vous n'avez aucun groupe assigné.</p>
                <p class="text-xs text-slate-400 mt-1">Veuillez contacter l'administrateur pour configurer vos attributions.</p>
            </div>
        @endif
    </div>
</x-app-layout>
