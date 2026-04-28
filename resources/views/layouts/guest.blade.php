<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Schedulix') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="schedulix-guest-shell min-h-screen flex flex-col items-center pt-6 sm:justify-center sm:pt-0">
            <div>
                <a href="/">
                    <x-application-logo />
                </a>
            </div>

            <div class="schedulix-panel w-full overflow-hidden px-6 py-4 sm:mt-6 sm:max-w-md sm:rounded-[1.75rem]">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
