<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-white">
        <div class="mx-auto flex min-h-screen max-w-6xl items-center px-6 py-12">
            <div class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8 shadow-2xl shadow-sky-950/30 backdrop-blur">
                    <p class="text-sm uppercase tracking-[0.4em] text-sky-200/70">WBS-Generator</p>
                    <h1 class="mt-4 text-5xl font-semibold leading-tight">Web-based work breakdown system for IT delivery teams.</h1>
                    <p class="mt-6 max-w-2xl text-lg text-slate-300">
                        This build includes the Laravel foundation, authentication, core schema, and the first management modules needed before scheduling and Excel-style tracking.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('login') }}" class="rounded-2xl bg-sky-400 px-5 py-3 font-semibold text-slate-950 transition hover:bg-sky-300">Sign in</a>
                        <a href="{{ route('password.request') }}" class="rounded-2xl border border-white/10 px-5 py-3 font-semibold text-white transition hover:bg-white/5">Reset password</a>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-white/10 bg-gradient-to-br from-sky-400/20 via-slate-900 to-emerald-400/10 p-8">
                    <h2 class="text-xl font-semibold">Current build slice</h2>
                    <ul class="mt-6 space-y-3 text-sm text-slate-200">
                        <li>Authentication with login and password reset</li>
                        <li>Project, PIC, task master, holiday, and working-hour modules</li>
                        <li>Route shell for WBS, assignments, schedule, exports, notifications, and settings</li>
                        <li>Seeded owner account and demo project data</li>
                    </ul>
                </div>
            </div>
        </div>
    </body>
</html>
