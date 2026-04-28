<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.assignments.title') }}</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">{{ $project->name }}</h2>
                <p class="mt-2 text-sm text-slate-300">{{ __('ui.assignments.subtitle') }}</p>
            </div>
            <div class="flex flex-wrap items-end gap-3">
                <form method="GET" action="{{ route('assignments.search') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="assignments-project-id" class="mb-2 block text-sm font-medium text-slate-300">{{ __('ui.common.project') }}</label>
                        <select id="assignments-project-id" name="project_id" class="min-w-[260px] rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-white">
                            @foreach ($projects as $selectableProject)
                                <option value="{{ $selectableProject->id }}" @selected($selectableProject->id === $project->id)>
                                    {{ $selectableProject->name }} ({{ $selectableProject->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/5">{{ __('ui.common.search') }}</button>
                </form>
                <a href="{{ route('assignments.create', $project) }}" class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/5">{{ __('ui.assignments.create') }}</a>
                <a href="{{ route('wbs-builder.index', $project) }}" class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/5">{{ __('ui.assignments.open_wbs') }}</a>
                <a href="{{ route('schedule.show', $project) }}" class="rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-slate-950">{{ __('ui.assignments.open_schedule') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h3 class="text-lg font-semibold text-white">{{ __('ui.assignments.existing') }}</h3>
            <p class="mt-1 text-sm text-slate-400">{{ __('ui.assignments.existing_note') }}</p>

            <div class="mt-4 overflow-hidden rounded-2xl border border-white/10">
                <table class="min-w-full divide-y divide-white/10 text-sm">
                    <thead class="bg-white/5 text-left text-slate-300">
                        <tr>
                            <th class="px-4 py-3">{{ __('ui.assignments.wbs') }}</th>
                            <th class="px-4 py-3">{{ __('ui.assignments.pic') }}</th>
                            <th class="px-4 py-3">{{ __('ui.assignments.role') }}</th>
                            <th class="px-4 py-3">{{ __('ui.assignments.priority') }}</th>
                            <th class="px-4 py-3">{{ __('ui.assignments.planned') }}</th>
                            <th class="px-4 py-3">{{ __('ui.assignments.dependency') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('ui.common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($project->assignments as $assignment)
                            <tr class="text-slate-200">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-white">{{ $assignment->wbsItem?->wbs_number }}</p>
                                    <p class="text-slate-400">{{ $assignment->wbsItem?->item_name }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $assignment->pic?->name }}</td>
                                <td class="px-4 py-3">{{ __("ui.roles.$assignment->assigned_role") }}</td>
                                <td class="px-4 py-3">{{ __("ui.priorities.$assignment->priority") }}</td>
                                <td class="px-4 py-3">
                                    {{ $assignment->planned_hours }}h
                                    @if ($assignment->schedule?->planned_start_date)
                                        <p class="text-xs text-slate-400">{{ $assignment->schedule->planned_start_date->format('Y-m-d') }} to {{ $assignment->schedule->planned_end_date?->format('Y-m-d') }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    {{ $assignment->dependencies->first()?->dependsOn?->wbsItem?->wbs_number ?: __('ui.assignments.none') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('assignments.edit', $assignment) }}" class="text-sky-300">{{ __('ui.common.edit') }}</a>
                                        <form method="POST" action="{{ route('assignments.recalculate', $assignment) }}">
                                            @csrf
                                            <button type="submit" class="text-emerald-300">{{ __('ui.assignments.recalculate') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">{{ __('ui.assignments.no_assignments') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h3 class="text-lg font-semibold text-white">{{ __('ui.assignments.auto_status') }}</h3>
            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-4">
                    <p class="text-sm text-slate-400">{{ __('ui.assignments.auto_assignments') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ $project->assignments->count() }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-4">
                    <p class="text-sm text-slate-400">{{ __('ui.assignments.auto_scheduled') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ $project->assignments->where('status', 'scheduled')->count() }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-4">
                    <p class="text-sm text-slate-400">{{ __('ui.assignments.auto_critical') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ $project->assignments->where('is_critical', true)->count() }}</p>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
