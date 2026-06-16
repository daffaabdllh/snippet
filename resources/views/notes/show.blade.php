<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $note->title ?: 'Catatan' }} - {{ config('app.name', 'Snippet') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Outfit:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Prism.js syntax highlighting assets -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="text-slate-900 antialiased bg-slate-50/50 min-h-screen flex flex-col relative overflow-x-hidden">
    <!-- Glowing Ambient Background Lights -->
    <div class="absolute top-[-10%] left-[-10%] w-[600px] h-[600px] rounded-full bg-teal-400/5 blur-[120px] pointer-events-none"></div>
    <div class="absolute top-[20%] right-[-15%] w-[600px] h-[600px] rounded-full bg-cyan-400/5 blur-[120px] pointer-events-none"></div>

    <!-- Header / Navbar -->
    <header class="sticky top-0 z-30 w-full bg-white/70 backdrop-blur-md border-b border-slate-200/60 px-4 sm:px-8 py-3.5 flex items-center justify-between">
        <div class="max-w-7xl mx-auto w-full flex items-center justify-between">
            <!-- Logo -->
            <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="flex items-center gap-2.5">
                <x-application-logo class="w-7 h-7 text-teal-600 fill-current" />
                <span class="text-lg font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-teal-600 to-cyan-600">
                    snippet
                </span>
            </a>

            <!-- Top Actions -->
            <div class="flex items-center gap-3 sm:gap-4 text-xs font-semibold">
                @auth
                    <span class="hidden sm:inline text-slate-400 font-normal">Membaca sebagai:</span>
                    <span class="text-slate-600 bg-slate-100 border border-slate-200/50 px-2.5 py-1 rounded-lg">{{ auth()->user()->name }}</span>
                    <a href="{{ route('dashboard') }}" class="flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 text-white hover:bg-teal-500 shadow-sm transition-all duration-150">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-800 transition-colors">Log in</a>
                    <a href="{{ route('register') }}" class="flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg bg-teal-600 text-white hover:bg-teal-500 shadow-sm transition-all duration-150">
                        Mulai Sekarang
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Reading Area -->
    <main class="max-w-4xl mx-auto py-10 px-4 sm:px-6 relative z-10 flex-1 w-full">
        <article class="bg-white border border-slate-200/50 rounded-3xl p-6 sm:p-10 shadow-[0_15px_40px_rgba(0,0,0,0.015)] space-y-6">
            
            <!-- Note Metadata Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                <div class="space-y-2">
                    <div class="flex items-center flex-wrap gap-2 text-xs text-slate-400 font-mono">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-medium bg-slate-50 text-slate-400 border border-slate-100">/{{ $note->slug }}</span>
                        <span>•</span>
                        <span class="flex items-center gap-1.5 font-sans font-medium">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $note->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        {{ $note->title ?: 'Tanpa Judul' }}
                    </h1>
                </div>

                <!-- Author Info Card -->
                <div class="flex items-center gap-2.5 shrink-0 bg-slate-50 border border-slate-100/80 rounded-2xl p-2.5 pr-4">
                    <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 flex items-center justify-center font-bold text-sm select-none border border-teal-500/20">
                        {{ strtoupper(substr($note->user ? $note->user->name : 'Tamu', 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400 leading-none">Bagikan Oleh</p>
                        <p class="text-xs font-bold text-slate-700 mt-1 leading-none">{{ $note->user ? $note->user->name : 'Tamu' }}</p>
                    </div>
                </div>
            </div>

            <!-- Code/Note Content Box (Mac-style terminal preview) -->
            <div class="border border-slate-200/80 rounded-2xl overflow-hidden shadow-sm my-4 bg-slate-50/50 flex flex-col">
                <!-- Mac-style window control header -->
                <div class="bg-slate-100/80 px-4 py-2.5 flex items-center justify-between border-b border-slate-200/60 shrink-0">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500/80 inline-block"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400/80 inline-block"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/80 inline-block"></span>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400 tracking-wider select-none uppercase">Note Content</span>
                    <!-- Clipboard/Copy content shortcut inside the editor box -->
                    <button 
                        x-data="{ 
                            copied: false,
                            stripHtml(html) {
                                let tmp = document.createElement('div');
                                tmp.innerHTML = html;
                                return tmp.textContent || tmp.innerText || '';
                            }
                        }"
                        x-on:click="navigator.clipboard.writeText(stripHtml(@js($note->content))); copied = true; setTimeout(() => copied = false, 2000); $dispatch('toast', { message: 'Isi catatan berhasil disalin!', type: 'success' })"
                        title="Salin Isi Catatan"
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1 hover:bg-slate-200 rounded flex items-center gap-1 text-[10px] font-medium"
                    >
                        <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                        </svg>
                        <svg x-show="copied" class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path>
                        </svg>
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" class="text-emerald-500" style="display: none;">Copied!</span>
                    </button>
                </div>
                <!-- Editor Body -->
                <div class="trix-content p-5 bg-white overflow-auto max-h-[550px] text-sm text-slate-700 leading-relaxed">{!! $note->content !!}</div>
            </div>

            <!-- Actions Footer (Hanya jika pembuat catatan login) -->
            @auth
                @if ($note->user_id === auth()->id())
                    <div class="flex items-center justify-end pt-5 border-t border-slate-100">
                        <form action="{{ route('notes.destroy', $note->id) }}" method="POST" x-data="{ deleting: false }" @submit="deleting = true" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit" 
                                :disabled="deleting" 
                                class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2.5 border border-transparent text-xs font-semibold text-white bg-rose-600 hover:bg-rose-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500/20 active:scale-[0.98] transition-all shadow-md hover:shadow-rose-600/10 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg x-show="deleting" class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg x-show="!deleting" class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path>
                                </svg>
                                <span x-show="!deleting">Hapus Catatan</span>
                                <span x-show="deleting">Menghapus...</span>
                            </button>
                        </form>
                    </div>
                @endif
            @endauth
        </article>

        <footer class="mt-8 text-center text-xs text-slate-400">
            <p>Dibuat secara instan menggunakan <a href="{{ url('/') }}" class="font-semibold text-teal-600 hover:underline">snippet</a> • Berbagi kode & catatan dengan mudah</p>
        </footer>
    </main>

    <x-toast />
</body>
</html>
