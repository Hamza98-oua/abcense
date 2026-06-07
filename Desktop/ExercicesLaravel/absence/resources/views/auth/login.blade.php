<x-app-layout>
    <x-slot name="title">Connexion - AbsencePortal</x-slot>

    <div class="max-w-md mx-auto my-12 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
        <!-- En-tête -->
        <div class="bg-slate-900 px-6 py-8 text-center text-white">
            <div class="inline-flex bg-indigo-600 p-3 rounded-2xl mb-4 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold tracking-tight">Portail de Connexion</h2>
            <p class="text-slate-400 text-xs mt-1">Espace réservé au personnel de l'établissement</p>
        </div>

        <!-- Corps de la carte -->
        <div class="p-6">
            @if (session('success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <p class="text-sm text-slate-500 mb-6 text-center">
                Connectez-vous avec votre email et votre mot de passe.
            </p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                <div class="space-y-2">
                    <label for="email" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
                    />
                </div>

                <div class="space-y-2">
                    <label for="password" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Mot de passe</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        autocomplete="current-password"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
                    />
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] transition-all text-white font-medium text-sm py-3 px-4 rounded-xl shadow-lg shadow-indigo-600/10 mt-6 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Se connecter
                </button>
            </form>
        </div>

        <!-- Pied de carte -->
        <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 text-center">
            <span class="text-xs text-slate-400">Pour le premier test, utilisez <strong>dupont@absence.com</strong> ou <strong>gestion.digital@absence.com</strong> (mot de passe : <strong>password123</strong>)</span>
        </div>
    </div>
</x-app-layout>

