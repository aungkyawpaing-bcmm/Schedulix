<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.wbs.title') }}</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">{{ $editingItem ? __('ui.wbs.edit_title') : __('ui.wbs.add_title') }}</h2>
                <p class="mt-2 text-sm text-slate-300">{{ $project->name }}</p>
            </div>
            <div class="flex flex-wrap items-end gap-3">
                <form method="GET" action="{{ route('wbs-builder.search') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="wbs-form-project-id" class="mb-2 block text-sm font-medium text-slate-300">{{ __('ui.common.project') }}</label>
                        <select id="wbs-form-project-id" name="project_id" class="min-w-[260px] rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-white">
                            @foreach ($projects as $selectableProject)
                                <option value="{{ $selectableProject->id }}" @selected($selectableProject->id === $project->id)>
                                    {{ $selectableProject->name }} ({{ $selectableProject->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/5">{{ __('ui.common.search') }}</button>
                </form>
                <a href="{{ route('wbs-builder.index', $project) }}" class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/5">
                    {{ __('ui.wbs.back') }}
                </a>
            </div>
        </div>
    </x-slot>

    <section class="mx-auto max-w-4xl rounded-3xl border border-white/10 bg-white/5 p-6">
        <h3 class="text-lg font-semibold text-white">{{ $editingItem ? __('ui.wbs.update_title') : __('ui.wbs.create_title') }}</h3>
        <p class="mt-1 text-sm text-slate-400">{{ __('ui.wbs.form_note') }}</p>

        <div class="mt-6">
            @include('wbs-builder._form')
        </div>
    </section>
</x-app-layout>
