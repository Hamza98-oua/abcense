<x-app-layout>
<x-slot name="title">Saisie d'Appel Rapide - AbsencePortal</x-slot>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saisie des Absences - Formateur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Style pour l'effet de sélection des cartes de séances */
        .seance-card input[type="radio"]:checked + .card-content {
            border-color: #2563eb;
            background-color: #eff6ff;
            color: #1e40af;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

    <div class="container mx-auto p-4 max-w-5xl">
        
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center border border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Espace Formateur</h1>
                <p class="text-sm text-gray-500 mt-1">Gestion et saisie quotidienne des présences (Séance de 2,5 heures)</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center bg-blue-50 text-blue-700 px-4 py-2 rounded-lg text-sm font-semibold gap-2">
                <span>📅 Date :</span>
                <input type="date" id="select_date" value="{{ $date }}" class="bg-transparent border-none outline-none text-blue-800 font-semibold cursor-pointer" onchange="window.location.href = '?groupe_id=' + document.getElementById('groupe_id').value + '&date=' + this.value;">
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('formateur.valider') }}" method="POST">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-gray-200">
                <h2 class="text-lg font-bold text-gray-700 mb-4 flex items-center">
                    <span class="bg-blue-600 text-white w-6 h-6 rounded-full inline-flex items-center justify-center text-sm mr-2">1</span>
                    Informations sur le cours
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="groupe_id" class="block text-sm font-medium text-gray-700 mb-2">Groupe</label>
                        <select name="groupe_id" id="groupe_id" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                            <option value="" disabled {{ !$groupe ? 'selected' : '' }}>-- Choisir le groupe --</option>
                            @foreach($groupes as $g)
                                <option value="{{ $g->id }}" {{ ($groupe && $groupe->id == $g->id) ? 'selected' : '' }}>{{ $g->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="module_id" class="block text-sm font-medium text-gray-700 mb-2">Module</label>
                        <select name="module_id" id="module_id" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 text-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                            @if($groupe)
                                <option value="" disabled selected>-- Choisir le module --</option>
                                @foreach($modules as $m)
                                    <option value="{{ $m->id }}">{{ $m->nom }}</option>
                                @endforeach
                            @else
                                <option value="" disabled selected>-- Sélectionnez d'abord un groupe --</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Sélectionner la Séance du jour</label>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        @php
                            $currentSession = request('num_seance', $suggestedSessionNum);
                        @endphp
                        
                        <label class="seance-card cursor-pointer">
                            <input type="radio" name="num_seance" value="1" class="hidden" required @checked($currentSession == 1)>
                            <div class="card-content border border-gray-200 rounded-xl p-4 text-center hover:bg-gray-50 transition shadow-sm bg-white">
                                <span class="block font-bold text-base">Séance 1</span>
                                <span class="text-xs text-gray-400 block mt-1">08:30 - 11:00</span>
                            </div>
                        </label>

                        <label class="seance-card cursor-pointer">
                            <input type="radio" name="num_seance" value="2" class="hidden" @checked($currentSession == 2)>
                            <div class="card-content border border-gray-200 rounded-xl p-4 text-center hover:bg-gray-50 transition shadow-sm bg-white">
                                <span class="block font-bold text-base">Séance 2</span>
                                <span class="text-xs text-gray-400 block mt-1">11:00 - 13:30</span>
                            </div>
                        </label>

                        <label class="seance-card cursor-pointer">
                            <input type="radio" name="num_seance" value="3" class="hidden" @checked($currentSession == 3)>
                            <div class="card-content border border-gray-200 rounded-xl p-4 text-center hover:bg-gray-50 transition shadow-sm bg-white">
                                <span class="block font-bold text-base">Séance 3</span>
                                <span class="text-xs text-gray-400 block mt-1">13:30 - 16:00</span>
                            </div>
                        </label>

                        <label class="seance-card cursor-pointer">
                            <input type="radio" name="num_seance" value="4" class="hidden" @checked($currentSession == 4)>
                            <div class="card-content border border-gray-200 rounded-xl p-4 text-center hover:bg-gray-50 transition shadow-sm bg-white">
                                <span class="block font-bold text-base">Séance 4</span>
                                <span class="text-xs text-gray-400 block mt-1">16:00 - 18:30</span>
                            </div>
                        </label>

                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold text-gray-700 flex items-center">
                        <span class="bg-blue-600 text-white w-6 h-6 rounded-full inline-flex items-center justify-center text-sm mr-2">2</span>
                        Feuille de Présence
                    </h2>
                    <span class="text-xs text-gray-400 italic">Sélectionnez le statut de chaque stagiaire</span>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-left">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Stagiaire</th>
                                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Statut</th>
                            </tr>
                        </thead>
                        <tbody id="stagiaires-list" class="bg-white divide-y divide-gray-200 text-sm text-gray-700">
                            @if($groupe && $stagiaires->isNotEmpty())
                                @foreach($stagiaires as $stagiaire)
                                    @php
                                        $prevStatus = $previousSessionStatuses[$currentSession][$stagiaire->id] ?? null;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            {{ $stagiaire->nom }} {{ $stagiaire->prenom }}
                                            @if($prevStatus)
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                    ⚠️ Attention: {{ $prevStatus === 'absence' ? 'Absent' : 'En retard' }} à la séance précédente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-2">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="statuses[{{ $stagiaire->id }}]" value="present" class="hidden peer" checked>
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-150 text-gray-500 peer-checked:bg-green-100 peer-checked:text-green-700 transition">
                                                        Présent
                                                    </span>
                                                </label>
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="statuses[{{ $stagiaire->id }}]" value="retard" class="hidden peer">
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-150 text-gray-500 peer-checked:bg-amber-100 peer-checked:text-amber-700 transition">
                                                        Retard
                                                    </span>
                                                </label>
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="statuses[{{ $stagiaire->id }}]" value="absent" class="hidden peer">
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-150 text-gray-500 peer-checked:bg-red-100 peer-checked:text-red-700 transition">
                                                        Absent
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @elseif($groupe)
                                <tr>
                                    <td colspan="2" class="px-6 py-8 text-center text-gray-400 italic">
                                        Aucun stagiaire dans ce groupe.
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="2" class="px-6 py-8 text-center text-gray-400 italic">
                                        Veuillez d'abord sélectionner un groupe pour charger la liste des stagiaires.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow transition">
                        💾 Enregistrer l'appel
                    </button>
                </div>
            </div>

        </form>
    </div>

    <script>
        document.getElementById('groupe_id').addEventListener('change', function() {
            const date = document.getElementById('select_date').value;
            window.location.href = `?groupe_id=${this.value}&date=${date}`;
        });
    </script>
</body>
</html>
</x-app-layout>