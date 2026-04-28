<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.dashboard.title') }}</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">{{ __('ui.dashboard.subtitle') }}</h2>
            </div>
            <a href="{{ route('projects.create') }}" class="rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-sky-300">
                {{ __('ui.dashboard.new_project') }}
            </a>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            __('ui.dashboard.total_projects') => $summary['total_projects'],
            __('ui.dashboard.ongoing_projects') => $summary['ongoing_projects'],
            __('ui.dashboard.completed_projects') => $summary['completed_projects'],
            __('ui.dashboard.overdue_tasks') => $summary['overdue_tasks'],
            __('ui.dashboard.today_tasks') => $summary['today_tasks'],
            __('ui.dashboard.exports') => $summary['export_count'],
        ] as $label => $value)
            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                <p class="text-sm text-slate-400">{{ $label }}</p>
                <p class="mt-3 text-4xl font-semibold text-white">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <section class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h3 class="text-lg font-semibold text-white">{{ __('ui.dashboard.recent_projects') }}</h3>
            <div class="mt-4 space-y-3">
                @forelse ($summary['recent_projects'] as $project)
                    <div class="rounded-2xl border border-white/10 bg-slate-900/50 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-white">{{ $project->name }}</p>
                                <p class="text-sm text-slate-400">{{ $project->code }} • {{ __("ui.statuses.$project->status") }}</p>
                            </div>
                            <a href="{{ route('projects.edit', $project) }}" class="text-sm font-medium text-sky-300">{{ __('ui.dashboard.open') }}</a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">{{ __('ui.dashboard.no_projects') }}</p>
                @endforelse
            </div>
        </section>

        <section class="space-y-6">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-lg font-semibold text-white">{{ __('ui.dashboard.latest_alerts') }}</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($summary['alerts'] as $alert)
                        <div class="rounded-2xl border border-amber-300/20 bg-amber-300/5 px-4 py-3">
                            <p class="font-semibold text-white">{{ $alert->title }}</p>
                            <p class="mt-1 text-sm text-slate-300">{{ $alert->message }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">{{ __('ui.dashboard.no_alerts') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <h3 class="text-lg font-semibold text-white">{{ __('ui.dashboard.recent_activity') }}</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($summary['recent_activities'] as $activity)
                        <div class="rounded-2xl border border-white/10 bg-slate-900/50 px-4 py-3">
                            <p class="font-semibold text-white">{{ str($activity->action)->replace('-', ' ')->title() }}</p>
                            <p class="mt-1 text-sm text-slate-300">
                                {{ class_basename($activity->auditable_type) }}
                                @if ($activity->user)
                                    • {{ $activity->user->name }}
                                @endif
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">{{ __('ui.dashboard.no_activity') }}</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
