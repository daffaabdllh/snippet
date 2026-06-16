<x-guest-layout>
    <!-- Top-Right Corner Navigation links -->
    <div class="absolute top-8 right-8 flex items-center gap-4 text-xs font-semibold z-20">
        @auth
            <a href="{{ route('dashboard') }}" class="text-teal-600 hover:underline">Dashboard</a>
            <span class="text-slate-300">|</span>
            <span class="text-slate-500">{{ auth()->user()->name }}</span>
        @else
            <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-800 transition-colors">Log in</a>
            <span class="text-slate-300">|</span>
            <a href="{{ route('register') }}" class="text-teal-600 hover:underline">Sign up</a>
        @endauth
    </div>

    <!-- Title and Subtitle -->
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-slate-900 tracking-tight leading-tight">Buat Catatan Baru</h2>
        <p class="text-sm text-slate-500 mt-1.5">Tulis catatan cepat atau tempel kode pemrograman Anda untuk dibagikan secara instan.</p>
    </div>

    <form method="POST" action="{{ route('notes.store') }}" class="space-y-4" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <!-- Judul Catatan -->
        <div>
            <x-input-label for="title" :value="__('Judul Catatan')" class="text-slate-700 text-sm font-medium mb-1.5" />
            <x-text-input id="title" name="title" type="text" class="block w-full" placeholder="Masukkan judul catatan... (opsional)" :value="old('title')" />
            <x-input-error :messages="$errors->get('title')" class="mt-1" />
        </div>

        <!-- Isi Catatan (WYSIWYG Trix Editor) -->
        <div class="trix-editor-container">
            <x-input-label for="content" :value="__('Isi Catatan / Kode')" class="text-slate-700 text-sm font-medium mb-1.5" />
            <input id="content" type="hidden" name="content" value="{{ old('content') }}" required>
            <trix-editor input="content" class="trix-content block w-full bg-slate-50/50 border border-slate-200 text-slate-900 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 rounded-lg shadow-sm px-4 py-3 text-sm transition-all duration-200 min-h-[250px] max-h-[400px] overflow-y-auto" placeholder="Tulis catatan atau tempel kode pemrograman Anda di sini..."></trix-editor>
            <x-input-error :messages="$errors->get('content')" class="mt-1" />
        </div>

        <!-- Tombol Submit -->
        <div class="pt-2">
            <button type="submit" :disabled="loading" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-500 hover:to-cyan-500 hover:shadow-lg hover:shadow-teal-600/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-teal-500 transition-all duration-150 transform active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-show="!loading">Simpan & Bagikan Catatan</span>
                <span x-show="loading">Menyimpan...</span>
            </button>
        </div>
    </form>
</x-guest-layout>
