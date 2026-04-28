<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.settings.title') }}</p>
            <h2 class="mt-2 text-3xl font-semibold text-white">{{ __('ui.settings.subtitle') }}</h2>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <section class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h3 class="text-lg font-semibold text-white">{{ __('ui.settings.preferences') }}</h3>
            <form method="POST" action="{{ route('settings.update') }}" class="mt-6 space-y-4">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-300/30 bg-rose-300/10 px-4 py-3 text-sm text-rose-200">
                        {{ __('ui.common.review_errors') }}
                    </div>
                @endif

                <div>
                    <label class="mb-2 block text-sm text-slate-300">{{ __('ui.settings.default_language') }}</label>
                    <select name="default_locale" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                        @foreach ($locales as $key => $label)
                            <option value="{{ $key }}" @selected($preferences['default_locale'] === $key)>{{ __("ui.locales.$key") }}</option>
                        @endforeach
                    </select>
                    <x-field-error for="default_locale" class="mt-2" />
                </div>

                <div>
                    <label class="mb-2 block text-sm text-slate-300">{{ __('ui.settings.default_timezone') }}</label>
                    <input name="default_timezone" value="{{ $preferences['default_timezone'] }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                    <x-field-error for="default_timezone" class="mt-2" />
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm text-slate-300">{{ __('ui.settings.date_format') }}</label>
                        <select name="date_format" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                            @foreach (['Y-m-d', 'd/m/Y', 'm/d/Y'] as $format)
                                <option value="{{ $format }}" @selected($preferences['date_format'] === $format)>{{ $format }}</option>
                            @endforeach
                        </select>
                        <x-field-error for="date_format" class="mt-2" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm text-slate-300">{{ __('ui.settings.rows_per_page') }}</label>
                        <select name="rows_per_page" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected((int) $preferences['rows_per_page'] === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                        <x-field-error for="rows_per_page" class="mt-2" />
                    </div>
                </div>

                <button class="rounded-2xl bg-sky-400 px-5 py-3 text-sm font-semibold text-slate-950">{{ __('ui.settings.save') }}</button>
            </form>
        </section>

        <section class="space-y-6">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-lg font-semibold text-white">{{ __('ui.settings.role_summary') }}</h3>
                <div class="mt-4 space-y-2 text-sm text-slate-300">
                    @foreach ($roles as $role)
                        <p>{{ __("ui.roles.$role") }}</p>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-lg font-semibold text-white">{{ __('ui.settings.notes') }}</h3>
                <div class="mt-4 space-y-3 text-sm text-slate-300">
                    <p>{{ __('ui.settings.note_1') }}</p>
                    <p>{{ __('ui.settings.note_2') }}</p>
                    <p>{{ __('ui.settings.note_3') }}</p>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
