<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-[#17130e] antialiased">
        <div class="min-h-screen bg-[#f4efe6] text-[#17130e]">
            <div class="fixed inset-0 pointer-events-none bg-[linear-gradient(90deg,rgba(23,19,14,0.045)_1px,transparent_1px),radial-gradient(circle_at_78%_12%,rgba(157,63,49,0.18),transparent_28rem),linear-gradient(135deg,#f4efe6,#ebe1d1_54%,#ded1bd)] bg-[length:72px_100%,auto,auto]"></div>
            <div class="fixed inset-0 pointer-events-none opacity-40 [mask-image:linear-gradient(to_bottom,transparent,black_12%,black_88%,transparent)] bg-[linear-gradient(rgba(23,19,14,0.026)_1px,transparent_1px),linear-gradient(90deg,rgba(23,19,14,0.018)_1px,transparent_1px)] bg-[length:100%_7px,42px_100%]"></div>

            <div class="relative grid min-h-screen lg:grid-cols-[0.92fr_1.08fr]">
                <section class="hidden border-r border-[#17130e]/15 px-[5vw] py-8 lg:flex lg:flex-col lg:justify-between">
                    <a href="{{ route('themes.editorial-black') }}" class="grid gap-1">
                        <span class="font-serif text-xl">Atelier Digital</span>
                        <span class="font-mono text-xs uppercase tracking-[0.18em] text-[#877766]">Portfolio dossier / Admin</span>
                    </a>

                    <div class="max-w-xl">
                        <p class="mb-4 font-mono text-xs uppercase tracking-[0.22em] text-[#bf5945]">Private archive</p>
                        <h1 class="font-serif text-6xl leading-[0.92] text-[#17130e] xl:text-7xl">Entrada editorial para administrar el archivo.</h1>
                        <p class="mt-6 max-w-md text-lg leading-8 text-[#3f362d]">
                            Accede al panel para publicar notas, revisar suscripciones y mantener el portafolio bajo control.
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-3 font-mono text-[0.68rem] uppercase text-[#877766]">
                        <span class="border-t border-[#17130e]/20 pt-3">SQLite</span>
                        <span class="border-t border-[#17130e]/20 pt-3">Breeze Auth</span>
                        <span class="border-t border-[#17130e]/20 pt-3">Single User</span>
                    </div>
                </section>

                <main class="flex min-h-screen items-center justify-center px-5 py-10">
                    <div class="w-full max-w-md">
                        <div class="mb-8 lg:hidden">
                            <a href="{{ route('themes.editorial-black') }}" class="grid gap-1">
                                <span class="font-serif text-2xl">Atelier Digital</span>
                                <span class="font-mono text-xs uppercase tracking-[0.18em] text-[#877766]">Admin privado</span>
                            </a>
                        </div>

                        <div class="border border-[#17130e]/15 bg-[#fffaf1]/85 p-6 shadow-[0_24px_80px_rgba(23,19,14,0.12)] backdrop-blur sm:p-8">
                            {{ $slot }}
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
