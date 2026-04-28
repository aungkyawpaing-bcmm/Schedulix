<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ __('ui.app_name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="schedulix-shell bg-white font-sans antialiased" x-data="sidebarShell()" :class="{ 'schedulix-sidebar-collapsed': collapsed }">
        <div class="schedulix-shell-bg min-h-screen">
            <div class="mx-auto flex min-h-screen max-w-7xl gap-6 px-4 py-4 sm:px-6 lg:px-8">
                @include('layouts.navigation')

                <div class="min-w-0 flex-1 space-y-6">
                    @isset($header)
                        <header class="schedulix-panel rounded-3xl px-6 py-5">
                            {{ $header }}
                        </header>
                    @endisset

                    @if (session('status'))
                        <div class="rounded-2xl border border-white/10 bg-sky-400/20 px-4 py-3 text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="rounded-2xl border border-white/10 bg-white/80 px-4 py-3 text-sm text-slate-400">
                            <p class="font-semibold text-slate-900">{{ __('ui.common.review_errors') }}</p>
                            <div class="mt-2 space-y-1">
                                @foreach ($errors->all() as $message)
                                    <p>{{ $message }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <main>
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
