<x-app-layout>
    <!-- Prism.js syntax highlighting assets -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>

    @php
        $user = auth()->user();
        $showProfileModal = $errors->has('name') || $errors->has('email') || 
                            $errors->updatePassword->isNotEmpty() || 
                            $errors->userDeletion->isNotEmpty();

        // Fallback ke data mock jika variabel $notes belum dikirim dari Controller
        $myNotes = $notes ?? collect([
            (object) [
                'id' => 1,
                'title' => 'Alpine.js Loading Button State',
                'content' => '<form x-data="{ loading: false }" @submit="loading = true">' . "\n" . '  <button :disabled="loading">' . "\n" . '    // spinner dan state tombol...' . "\n" . '  </button>' . "\n" . '</form>',
                'slug' => 'alpbtn',
                'created_at' => now()->subHours(2)
            ],
            (object) [
                'id' => 2,
                'title' => 'Custom SMTP Mail Configuration',
                'content' => "Config::set('mail.mailers.smtp.password', \$appPassword);\nMail::to(\$user)->send(new ResetPasswordMail());",
                'slug' => 'smtpcf',
                'created_at' => now()->subDay()
            ]
        ]);
    @endphp

    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    <div x-data="{
        activeNote: { id: '', title: '', content: '', slug: '', created_at: '', url: '' },
        deletingNote: { id: '', title: '' },
        limit: 12,
        stripHtml(html) {
            let tmp = document.createElement('div');
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || '';
        }
    }"
    x-init="
        $watch('activeNote.content', value => {
            $nextTick(() => {
                if (window.Prism && value) {
                    document.querySelectorAll('#modal-note-content pre code').forEach((element) => {
                        Prism.highlightElement(element);
                    });
                }
            });
        });

        // Intersection Observer for lazy loading notes
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                limit += 12;
            }
        }, { rootMargin: '200px' });
        if ($refs.loadMoreSentinel) {
            observer.observe($refs.loadMoreSentinel);
        }
    "
    class="space-y-8">

        <!-- Google Keep-style Quick Note Creator Bar -->
        <div class="flex justify-center mb-6">
            <button x-on:click="$dispatch('open-modal', 'create-note')" class="w-full max-w-xl bg-white border border-slate-200/80 hover:border-slate-300 rounded-xl px-4 py-3 shadow-sm hover:shadow flex items-center justify-between text-slate-400 text-sm font-medium transition-all duration-150">
                <span>Tulis Catatan Baru...</span>
                <div class="flex items-center text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"></path>
                    </svg>
                </div>
            </button>
        </div>

        <!-- 3. Daftar Catatan (Google Grid Layout with Centering & Lazy Loading) -->
        <div class="grid grid-cols-[repeat(auto-fill,240px)] justify-center gap-6 w-full">
            @forelse ($myNotes as $index => $note)
                <div 
                    x-show="(@js($note->title ?: 'Tanpa Judul').toLowerCase().includes($store.search.query.toLowerCase())) && ($store.search.query !== '' || {{ $index }} < limit)"
                    x-on:click="
                        activeNote = { 
                            id: '{{ $note->id }}', 
                            title: @js($note->title ?: 'Tanpa Judul'), 
                            content: @js($note->content), 
                            slug: '{{ $note->slug }}', 
                            created_at: '{{ $note->created_at instanceof \Carbon\Carbon ? $note->created_at->diffForHumans() : \Carbon\Carbon::parse($note->created_at)->diffForHumans() }}', 
                            url: '{{ url('/note/' . $note->slug) }}' 
                        }; 
                        $dispatch('open-modal', 'view-note');
                    "
                    class="w-[240px] h-[240px] bg-white border border-slate-200/60 rounded-2xl p-5 hover:shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:border-teal-500/30 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group relative cursor-pointer"
                >
                    <div class="flex-1 flex flex-col min-h-0">
                        <div class="flex items-center justify-between gap-2 mb-2 shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-mono font-medium bg-slate-50 text-slate-400 border border-slate-100/80 truncate max-w-[110px]">
                                /{{ $note->slug }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium shrink-0">
                                {{ $note->created_at instanceof \Carbon\Carbon ? $note->created_at->diffForHumans() : \Carbon\Carbon::parse($note->created_at)->diffForHumans() }}
                            </span>
                        </div>
                        
                        <h3 class="text-sm font-semibold text-slate-800 group-hover:text-teal-600 transition-colors duration-150 truncate shrink-0">
                            {{ $note->title ?: 'Tanpa Judul' }}
                        </h3>
                        
                        <!-- Preview Catatan (Harmonious Monospace Preview) -->
                        <div class="mt-2.5 flex-1 min-h-0 overflow-hidden relative">
                            <pre class="whitespace-pre-wrap font-mono text-[10px] text-slate-500 leading-relaxed select-none tracking-tight break-all">{{ Str::limit(strip_tags($note->content), 185) }}</pre>
                            <div class="absolute bottom-0 left-0 right-0 h-12 bg-gradient-to-t from-white via-white/80 to-transparent pointer-events-none"></div>
                        </div>
                    </div>

                    <!-- Actions (Visible only on Hover to match Google Keep) -->
                    <div class="flex items-center justify-between mt-2 pt-1.5 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <div class="flex items-center gap-1">
                            <!-- Tombol Salin Tautan Catatan -->
                            <button 
                                x-on:click.stop="navigator.clipboard.writeText('{{ url('/note/' . $note->slug) }}'); $dispatch('toast', { message: 'Tautan berhasil disalin!', type: 'success' })" 
                                title="Salin Tautan Catatan" 
                                class="p-1.5 text-slate-400 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition-colors duration-150"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="flex items-center gap-1">
                            <!-- Tombol Konfirmasi Hapus Catatan (Trash Icon) -->
                            <button 
                                type="button" 
                                x-on:click.stop="
                                    deletingNote = { 
                                        id: '{{ $note->id }}', 
                                        title: @js($note->title ?: 'Tanpa Judul') 
                                    }; 
                                    $dispatch('open-modal', 'confirm-delete-note');
                                " 
                                title="Hapus Catatan"
                                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors duration-150"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="w-full py-12 text-center bg-slate-50/50 border border-slate-200/60 rounded-2xl col-span-full">
                    <p class="text-sm text-slate-500 font-medium">Anda belum membuat catatan apapun.</p>
                </div>
            @endforelse

            <!-- Sentinel for lazy loading more notes -->
            <div x-ref="loadMoreSentinel" x-show="$store.search.query === '' && limit < {{ $myNotes->count() }}" class="w-full h-4 col-span-full"></div>
        </div>

    <!-- Modal Create Note -->
    <x-modal name="create-note" :show="$errors->any()" focusable>
        <form method="POST" action="{{ route('notes.store') }}" class="p-6 space-y-4" x-data="{ loading: false }" @submit="loading = true">
            @csrf

            <div class="mb-4">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight leading-tight">Buat Catatan Baru</h2>
                <p class="text-xs text-slate-500 mt-1">Tulis catatan cepat atau tempel kode pemrograman Anda untuk dibagikan secara instan.</p>
            </div>

            <!-- Judul Catatan -->
            <div>
                <x-input-label for="modal_title" :value="__('Judul Catatan')" class="text-slate-700 text-sm font-medium mb-1.5" />
                <x-text-input id="modal_title" name="title" type="text" class="block w-full" placeholder="Masukkan judul catatan... (opsional)" :value="old('title')" />
                <x-input-error :messages="$errors->get('title')" class="mt-1" />
            </div>

            <!-- Isi Catatan (WYSIWYG Trix Editor) -->
            <div class="trix-editor-container">
                <x-input-label for="modal_content" :value="__('Isi Catatan / Kode')" class="text-slate-700 text-sm font-medium mb-1.5" />
                <input id="modal_content" type="hidden" name="content" value="{{ old('content') }}" required>
                <trix-editor input="modal_content" class="trix-content block w-full bg-slate-50/50 border border-slate-200 text-slate-900 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 rounded-lg shadow-sm px-4 py-3 text-sm transition-all duration-200 min-h-[200px] max-h-[300px] overflow-y-auto" placeholder="Tulis catatan atau tempel kode pemrograman Anda di sini..."></trix-editor>
                <x-input-error :messages="$errors->get('content')" class="mt-1" />
            </div>

            <div class="flex justify-end items-center gap-3 pt-2">
                <x-secondary-button x-on:click="$dispatch('close')" ::disabled="loading">
                    {{ __('Batal') }}
                </x-secondary-button>

                <button type="submit" :disabled="loading" class="flex justify-center items-center gap-2 px-4 py-2.5 border border-transparent rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-500 hover:to-cyan-500 shadow-md hover:shadow-teal-500/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-teal-500 transition-all duration-150 transform active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg x-show="loading" class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-show="!loading">Simpan & Bagikan</span>
                    <span x-show="loading">Menyimpan...</span>
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Lihat Catatan (View Note Modal) -->
    <x-modal name="view-note" focusable>
        <div class="p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center flex-wrap gap-2 text-xs font-mono text-slate-400">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-medium bg-teal-50 text-teal-700 border border-teal-100/60" x-text="'/' + activeNote.slug"></span>
                        <span>•</span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span x-text="activeNote.created_at"></span>
                        </span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-tight" x-text="activeNote.title"></h2>
                </div>
                <!-- Tombol Salin Tautan -->
                <button 
                    x-on:click="navigator.clipboard.writeText(activeNote.url); $dispatch('toast', { message: 'Tautan berhasil disalin!', type: 'success' })" 
                    class="flex items-center justify-center gap-2 px-3 py-2 border border-slate-200/80 text-xs font-semibold text-slate-700 rounded-lg bg-white hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500/20 active:scale-[0.98] transition-all shadow-sm shrink-0"
                >
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                    </svg>
                    Salin Tautan
                </button>
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
                        x-on:click="navigator.clipboard.writeText(stripHtml(activeNote.content)); $dispatch('toast', { message: 'Isi catatan berhasil disalin!', type: 'success' })"
                        title="Salin Isi Catatan"
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1 hover:bg-slate-200 rounded"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                        </svg>
                    </button>
                </div>
                <!-- Editor Body -->
                <div id="modal-note-content" class="trix-content p-5 bg-white overflow-auto max-h-[380px] text-sm text-slate-700 leading-relaxed" x-html="activeNote.content"></div>
            </div>

            <div class="flex justify-end pt-3 border-t border-slate-100">
                <button 
                    type="button"
                    x-on:click="$dispatch('close')"
                    class="px-4 py-2 border border-slate-200/80 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-lg bg-white hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500/20 active:scale-[0.98] transition-all shadow-sm"
                >
                    Tutup
                </button>
            </div>
        </div>
    </x-modal>

    <!-- Modal Konfirmasi Hapus Catatan (Delete Confirmation Modal) -->
    <x-modal name="confirm-delete-note" focusable>
        <form method="POST" :action="'/note/' + deletingNote.id" class="p-6 space-y-4" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            @method('DELETE')

            <div class="mb-4">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight leading-tight text-rose-600">Konfirmasi Hapus Catatan</h2>
                <p class="text-sm text-slate-600 mt-3">
                    Apakah Anda yakin ingin menghapus catatan <strong class="text-slate-800" x-text="deletingNote.title"></strong>?
                </p>
                <p class="text-xs text-rose-500 mt-2 font-medium bg-rose-50 border border-rose-100 rounded-lg p-2.5">
                    Tindakan ini bersifat permanen dan catatan akan dihapus selamanya dari sistem. Anda tidak akan dapat memulihkannya kembali.
                </p>
            </div>

            <div class="flex justify-end items-center gap-3 pt-4 border-t border-slate-100">
                <x-secondary-button x-on:click="$dispatch('close')" ::disabled="loading">
                    Batal
                </x-secondary-button>

                <button type="submit" :disabled="loading" class="flex justify-center items-center gap-2 px-4 py-2.5 border border-transparent rounded-lg text-sm font-semibold text-white bg-rose-600 hover:bg-rose-500 shadow-md hover:shadow-rose-500/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-rose-500 transition-all duration-150 transform active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg x-show="loading" class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-show="!loading">Ya, Hapus</span>
                    <span x-show="loading">Menghapus...</span>
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Edit Profile (Profile Settings Modal) -->
    <x-modal name="edit-profile" :show="$showProfileModal" focusable>
        <div x-data="{ tab: '{{ $errors->updatePassword->isNotEmpty() ? 'password' : ($errors->userDeletion->isNotEmpty() ? 'delete' : 'profile') }}' }" class="p-6">
            <div class="mb-5 flex justify-between items-center border-b border-slate-100 pb-3">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Pengaturan Akun</h2>
                <button type="button" x-on:click="$dispatch('close')" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Tabs Navigation -->
            <div class="flex gap-2 border-b border-slate-200/60 mb-6 pb-px">
                <button 
                    type="button"
                    x-on:click="tab = 'profile'" 
                    :class="tab === 'profile' ? 'text-teal-600 border-teal-500 font-semibold' : 'text-slate-500 border-transparent hover:text-slate-800 hover:border-slate-300'"
                    class="py-2.5 px-3 border-b-2 text-sm font-medium transition-all focus:outline-none -mb-px"
                >
                    Informasi Profil
                </button>
                <button 
                    type="button"
                    x-on:click="tab = 'password'" 
                    :class="tab === 'password' ? 'text-teal-600 border-teal-500 font-semibold' : 'text-slate-500 border-transparent hover:text-slate-800 hover:border-slate-300'"
                    class="py-2.5 px-3 border-b-2 text-sm font-medium transition-all focus:outline-none -mb-px"
                >
                    Perbarui Kata Sandi
                </button>
                <button 
                    type="button"
                    x-on:click="tab = 'delete'" 
                    :class="tab === 'delete' ? 'text-rose-600 border-rose-500 font-semibold' : 'text-slate-500 border-transparent hover:text-rose-600 hover:border-rose-300'"
                    class="py-2.5 px-3 border-b-2 text-sm font-medium transition-all focus:outline-none -mb-px"
                >
                    Hapus Akun
                </button>
            </div>

            <!-- Tab Contents -->
            <div>
                <!-- Tab: Profile Information -->
                <div x-show="tab === 'profile'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform scale-98" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-4">
                    <form method="post" action="{{ route('profile.update') }}" class="space-y-4" x-data="{ loading: false }" @submit="loading = true">
                        @csrf
                        @method('patch')

                        <div>
                            <x-input-label for="profile_name" :value="__('Name')" class="text-slate-700 text-sm font-medium mb-1.5" />
                            <x-text-input id="profile_name" name="name" type="text" class="block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                            <x-input-error class="mt-1" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="profile_email" :value="__('Email')" class="text-slate-700 text-sm font-medium mb-1.5" />
                            <x-text-input id="profile_email" name="email" type="email" class="block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                            <x-input-error class="mt-1" :messages="$errors->get('email')" />

                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="mt-2 p-3 bg-amber-50 border border-amber-100 rounded-lg">
                                    <p class="text-xs text-amber-800">
                                        {{ __('Your email address is unverified.') }}
                                        <button form="send-verification" class="underline hover:text-amber-900 font-semibold focus:outline-none">
                                            {{ __('Click here to re-send the verification email.') }}
                                        </button>
                                    </p>
                                    @if (session('status') === 'verification-link-sent')
                                        <p class="mt-1.5 text-xs font-semibold text-green-600">
                                            {{ __('A new verification link has been sent to your email address.') }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-end items-center gap-3 pt-3 border-t border-slate-100">
                            <x-secondary-button x-on:click="$dispatch('close')" ::disabled="loading">
                                Batal
                            </x-secondary-button>
                            <button type="submit" :disabled="loading" class="flex justify-center items-center gap-2 px-4 py-2.5 border border-transparent rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-500 hover:to-cyan-500 shadow-md hover:shadow-teal-500/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-all duration-150 transform active:scale-[0.98] disabled:opacity-50">
                                <span x-show="!loading">Simpan Perubahan</span>
                                <span x-show="loading">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab: Update Password -->
                <div x-show="tab === 'password'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform scale-98" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-4">
                    <form method="post" action="{{ route('password.update') }}" class="space-y-4" x-data="{ loading: false }" @submit="loading = true">
                        @csrf
                        @method('put')

                        <div>
                            <x-input-label for="profile_current_password" :value="__('Current Password')" class="text-slate-700 text-sm font-medium mb-1.5" />
                            <div class="relative" x-data="{ show: false }">
                                <x-text-input id="profile_current_password" name="current_password" ::type="show ? 'text' : 'password'" class="block w-full pr-10" autocomplete="current-password" />
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors duration-150">
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"></path>
                                    </svg>
                                    <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="profile_password" :value="__('New Password')" class="text-slate-700 text-sm font-medium mb-1.5" />
                            <div class="relative" x-data="{ show: false }">
                                <x-text-input id="profile_password" name="password" ::type="show ? 'text' : 'password'" class="block w-full pr-10" autocomplete="new-password" />
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors duration-150">
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"></path>
                                    </svg>
                                    <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="profile_password_confirmation" :value="__('Confirm Password')" class="text-slate-700 text-sm font-medium mb-1.5" />
                            <div class="relative" x-data="{ show: false }">
                                <x-text-input id="profile_password_confirmation" name="password_confirmation" ::type="show ? 'text' : 'password'" class="block w-full pr-10" autocomplete="new-password" />
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors duration-150">
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"></path>
                                    </svg>
                                    <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
                        </div>

                        <div class="flex justify-end items-center gap-3 pt-3 border-t border-slate-100">
                            <x-secondary-button x-on:click="$dispatch('close')" ::disabled="loading">
                                Batal
                            </x-secondary-button>
                            <button type="submit" :disabled="loading" class="flex justify-center items-center gap-2 px-4 py-2.5 border border-transparent rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-500 hover:to-cyan-500 shadow-md hover:shadow-teal-500/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-all duration-150 transform active:scale-[0.98] disabled:opacity-50">
                                <span x-show="!loading">Perbarui Kata Sandi</span>
                                <span x-show="loading">Memperbarui...</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab: Delete Account -->
                <div x-show="tab === 'delete'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform scale-98" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-4">
                    <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4" x-data="{ loading: false }" @submit="loading = true">
                        @csrf
                        @method('delete')

                        <div class="p-4 bg-rose-50 border border-rose-100 rounded-xl text-rose-800 space-y-2">
                            <h3 class="text-sm font-bold">Apakah Anda yakin ingin menghapus akun Anda?</h3>
                            <p class="text-xs leading-relaxed">
                                Setelah akun Anda dihapus, semua sumber daya dan data di dalamnya akan dihapus secara permanen. Silakan masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun Anda secara permanen.
                            </p>
                        </div>

                        <div>
                            <x-input-label for="delete_password" :value="__('Kata Sandi Konfirmasi')" class="text-slate-700 text-sm font-medium mb-1.5" />
                            <div class="relative" x-data="{ show: false }">
                                <x-text-input id="delete_password" name="password" ::type="show ? 'text' : 'password'" class="block w-full pr-10" placeholder="Masukkan kata sandi Anda..." required />
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors duration-150">
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"></path>
                                    </svg>
                                    <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1" />
                        </div>

                        <div class="flex justify-end items-center gap-3 pt-3 border-t border-slate-100">
                            <x-secondary-button x-on:click="$dispatch('close')" ::disabled="loading">
                                Batal
                            </x-secondary-button>
                            <button type="submit" :disabled="loading" class="flex justify-center items-center gap-2 px-4 py-2.5 border border-transparent rounded-lg text-sm font-semibold text-white bg-rose-600 hover:bg-rose-500 shadow-md hover:shadow-rose-500/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all duration-150 transform active:scale-[0.98] disabled:opacity-50">
                                <span x-show="!loading">Hapus Akun Secara Permanen</span>
                                <span x-show="loading">Menghapus...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-modal>
    </div>
    </div>
</x-app-layout>
