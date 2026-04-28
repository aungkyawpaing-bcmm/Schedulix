<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.projects.title') }}</p>
            <h2 class="mt-2 text-3xl font-semibold text-white">{{ $project->exists ? __('ui.projects.edit_title') : __('ui.projects.create_title') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ $project->exists ? route('projects.update', $project) : route('projects.store') }}" class="space-y-6 rounded-3xl border border-white/10 bg-white/5 p-6">
        @csrf
        @if ($project->exists)
            @method('PUT')
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-300/30 bg-rose-300/10 px-4 py-3 text-sm text-rose-200">
                {{ __('ui.common.review_errors') }}
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('ui.projects.name') }}</label>
                <input name="name" value="{{ old('name', $project->name) }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="name" class="mt-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('ui.projects.code') }}</label>
                <input name="code" value="{{ old('code', $project->code) }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="code" class="mt-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('ui.projects.manager') }}</label>
                <select name="project_manager_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    @foreach ($managers as $manager)
                        <option value="{{ $manager->id }}" @selected(old('project_manager_id', $project->project_manager_id) == $manager->id)>{{ $manager->name }}</option>
                    @endforeach
                </select>
                <x-field-error for="project_manager_id" class="mt-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('ui.common.status') }}</label>
                <select name="status" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    @foreach (config('wbs.project_statuses') as $status)
                        <option value="{{ $status }}" @selected(old('status', $project->status ?: 'draft') === $status)>{{ __("ui.statuses.$status") }}</option>
                    @endforeach
                </select>
                <x-field-error for="status" class="mt-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('ui.projects.expected_start') }}</label>
                <input type="date" name="expected_start_date" value="{{ old('expected_start_date', optional($project->expected_start_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="expected_start_date" class="mt-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('ui.projects.expected_end') }}</label>
                <input type="date" name="expected_end_date" value="{{ old('expected_end_date', optional($project->expected_end_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="expected_end_date" class="mt-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('ui.projects.team_size') }}</label>
                <input type="number" name="team_size" value="{{ old('team_size', $project->team_size) }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="team_size" class="mt-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('ui.common.timezone') }}</label>
                <input name="timezone" value="{{ old('timezone', $project->timezone ?: 'Asia/Yangon') }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
                <x-field-error for="timezone" class="mt-2" />
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.projects.default_locale') }}</label>
            <select name="locale_default" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                @foreach (config('wbs.supported_locales') as $key => $label)
                    <option value="{{ $key }}" @selected(old('locale_default', $project->locale_default ?: 'en') === $key)>{{ __("ui.locales.$key") }}</option>
                @endforeach
            </select>
            <x-field-error for="locale_default" class="mt-2" />
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.common.overview') }}</label>
            <textarea name="overview" rows="4" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">{{ old('overview', $project->overview) }}</textarea>
            <x-field-error for="overview" class="mt-2" />
        </div>

        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.common.objective') }}</label>
            <textarea name="objective" rows="4" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">{{ old('objective', $project->objective) }}</textarea>
            <x-field-error for="objective" class="mt-2" />
        </div>

        <button class="rounded-2xl bg-sky-400 px-5 py-3 text-sm font-semibold text-slate-950">{{ $project->exists ? __('ui.common.save_changes') : __('ui.projects.create') }}</button>
    </form>
</x-app-layout>
