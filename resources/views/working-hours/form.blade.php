<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.working_hours.title') }}</p>
            <h2 class="mt-2 text-3xl font-semibold text-white">{{ $workingHour->exists ? __('ui.working_hours.edit_title') : __('ui.working_hours.create_title') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ $workingHour->exists ? route('working-hours.update', $workingHour) : route('working-hours.store') }}" class="space-y-6 rounded-3xl border border-white/10 bg-white/5 p-6">
        @csrf
        @if ($workingHour->exists)
            @method('PUT')
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-300/30 bg-rose-300/10 px-4 py-3 text-sm text-rose-200">
                {{ __('ui.common.review_errors') }}
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <select name="scope_type" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    <option value="global" @selected(old('scope_type', $workingHour->scope_type ?: 'global') === 'global')>{{ __('ui.working_hours.global') }}</option>
                    <option value="project" @selected(old('scope_type', $workingHour->scope_type) === 'project')>{{ __('ui.working_hours.project') }}</option>
                </select>
                <x-field-error for="scope_type" class="mt-2" />
            </div>
            <div>
                <select name="project_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    <option value="">{{ __('ui.working_hours.no_project') }}</option>
                    @foreach ($projectOptions as $id => $name)
                        <option value="{{ $id }}" @selected(old('project_id', $workingHour->project_id) == $id)>{{ $name }}</option>
                    @endforeach
                </select>
                <x-field-error for="project_id" class="mt-2" />
            </div>
            <div>
                <select name="weekday" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    @foreach (range(0, 6) as $key)
                        <option value="{{ $key }}" @selected(old('weekday', $workingHour->weekday ?? 1) == $key)>{{ __("ui.weekdays.$key") }}</option>
                    @endforeach
                </select>
                <x-field-error for="weekday" class="mt-2" />
            </div>
            <div>
                <input type="number" step="0.25" name="net_hours" value="{{ old('net_hours', $workingHour->net_hours ?? 8) }}" placeholder="{{ __('ui.working_hours.net_hours') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="net_hours" class="mt-2" />
            </div>
            <div>
                <input type="time" name="start_time" value="{{ old('start_time', $workingHour->start_time) }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="start_time" class="mt-2" />
            </div>
            <div>
                <input type="time" name="end_time" value="{{ old('end_time', $workingHour->end_time) }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="end_time" class="mt-2" />
            </div>
            <div>
                <input type="time" name="lunch_start_time" value="{{ old('lunch_start_time', $workingHour->lunch_start_time) }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="lunch_start_time" class="mt-2" />
            </div>
            <div>
                <input type="time" name="lunch_end_time" value="{{ old('lunch_end_time', $workingHour->lunch_end_time) }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="lunch_end_time" class="mt-2" />
            </div>
            <div>
                <select name="is_working_day" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    <option value="1" @selected(old('is_working_day', $workingHour->exists ? (int) $workingHour->is_working_day : 1) == 1)>{{ __('ui.working_hours.working_day') }}</option>
                    <option value="0" @selected(old('is_working_day', (int) $workingHour->is_working_day) == 0)>{{ __('ui.working_hours.non_working_day') }}</option>
                </select>
                <x-field-error for="is_working_day" class="mt-2" />
            </div>
        </div>

        <button class="rounded-2xl bg-sky-400 px-5 py-3 text-sm font-semibold text-slate-950">{{ $workingHour->exists ? __('ui.working_hours.save') : __('ui.working_hours.create') }}</button>
    </form>
</x-app-layout>
