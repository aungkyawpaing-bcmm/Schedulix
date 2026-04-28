<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.holidays.title') }}</p>
            <h2 class="mt-2 text-3xl font-semibold text-white">{{ $holiday->exists ? __('ui.holidays.edit_title') : __('ui.holidays.create_title') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ $holiday->exists ? route('holidays.update', $holiday) : route('holidays.store') }}" class="space-y-6 rounded-3xl border border-white/10 bg-white/5 p-6">
        @csrf
        @if ($holiday->exists)
            @method('PUT')
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-300/30 bg-rose-300/10 px-4 py-3 text-sm text-rose-200">
                {{ __('ui.common.review_errors') }}
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <input name="name" value="{{ old('name', $holiday->name) }}" placeholder="{{ __('ui.holidays.holiday_name') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="name" class="mt-2" />
            </div>
            <div>
                <input type="date" name="holiday_date" value="{{ old('holiday_date', optional($holiday->holiday_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="holiday_date" class="mt-2" />
            </div>
            <div>
                <select name="holiday_type" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    @foreach (config('wbs.holiday_types') as $type)
                        <option value="{{ $type }}" @selected(old('holiday_type', $holiday->holiday_type ?: 'gazetted') === $type)>{{ __("ui.holiday_types.$type") }}</option>
                    @endforeach
                </select>
                <x-field-error for="holiday_type" class="mt-2" />
            </div>
            <div>
                <input name="timezone" value="{{ old('timezone', $holiday->timezone ?: 'Asia/Yangon') }}" placeholder="{{ __('ui.common.timezone') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="timezone" class="mt-2" />
            </div>
            <div>
                <select name="is_active" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    <option value="1" @selected(old('is_active', $holiday->exists ? (int) $holiday->is_active : 1) == 1)>{{ __('ui.statuses.active') }}</option>
                    <option value="0" @selected(old('is_active', (int) $holiday->is_active) == 0)>{{ __('ui.statuses.inactive') }}</option>
                </select>
                <x-field-error for="is_active" class="mt-2" />
            </div>
        </div>
        <div>
            <textarea name="notes" rows="4" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" placeholder="{{ __('ui.holidays.notes') }}">{{ old('notes', $holiday->notes) }}</textarea>
            <x-field-error for="notes" class="mt-2" />
        </div>

        <button class="rounded-2xl bg-sky-400 px-5 py-3 text-sm font-semibold text-slate-950">{{ $holiday->exists ? __('ui.holidays.save') : __('ui.holidays.create') }}</button>
    </form>
</x-app-layout>
