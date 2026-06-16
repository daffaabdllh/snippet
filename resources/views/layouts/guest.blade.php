<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=Outfit:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Trix Editor WYSIWYG Assets -->
        <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
        <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>

        <style>
            body {
                font-family: 'Outfit', sans-serif;
            }
            .grid-bg {
                background-image: linear-gradient(to right, rgba(255,255,255,0.03) 1px, transparent 1px),
                                  linear-gradient(to bottom, rgba(255,255,255,0.03) 1px, transparent 1px);
                background-size: 48px 48px;
            }
        </style>
    </head>
    <body class="text-zinc-900 antialiased bg-white min-h-screen">
        <div class="min-h-screen grid grid-cols-1 lg:grid-cols-12">
            
            <!-- Left Side: Dark Hero Section (Hidden on mobile) -->
            <div class="hidden lg:flex lg:col-span-5 bg-[#0a0b10] relative flex-col justify-between p-16 text-white overflow-hidden border-r border-zinc-900">
                <!-- Grid pattern -->
                <div class="absolute inset-0 grid-bg opacity-100 pointer-events-none"></div>
                
                <!-- Glowing Aura Lights -->
                <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] rounded-full bg-teal-500/10 blur-[130px] pointer-events-none"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-cyan-500/10 blur-[130px] pointer-events-none"></div>

                <!-- Top: Logo -->
                <div class="z-10 flex items-center gap-2.5">
                    <x-application-logo class="w-8 h-8 text-teal-400 fill-current" />
                    <span class="text-xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-teal-400 to-cyan-400">
                        snippet
                    </span>
                </div>

                <!-- Middle: Copywriting -->
                <div class="z-10 max-w-md my-auto space-y-6">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-teal-500/10 border border-teal-500/20 text-teal-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            snippet Pastebin App v1.0
                        </span>
                    </div>
                    <h1 class="text-4xl font-extrabold tracking-tight leading-[1.15] text-white">
                        Bagi Kode & Catatan Anda Lebih Efisien
                    </h1>
                    <p class="text-zinc-400 text-sm leading-relaxed">
                        Pantau catatan secara real-time, kelola snippet pemrograman, atur hak akses publik, dan bagikan kode dengan penyorotan sintaksis yang indah dalam satu platform terintegrasi.
                    </p>
                </div>

                <!-- Bottom: Copyright Footer -->
                <div class="z-10 text-xs text-zinc-500">
                    &copy; {{ date('Y') }} snippet. Hak Cipta Dilindungi.
                </div>
            </div>

            <!-- Right Side: Content Area (Form) -->
            <div class="col-span-1 lg:col-span-7 flex flex-col justify-between p-8 sm:p-12 relative bg-white min-h-screen">

                <!-- Centered Form Wrapper -->
                <div class="my-auto w-full max-w-[400px] md:max-w-xl lg:max-w-2xl mx-auto py-12">
                    {{ $slot }}
                </div>

                <!-- Bottom Footer (Spacer to center the form perfectly) -->
                <div class="h-8"></div>
            </div>

        </div>
        <x-toast />
    </body>
</html>
