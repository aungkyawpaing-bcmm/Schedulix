<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.projects.title') }}</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">{{ __('ui.projects.subtitle') }}</h2>
            </div>
            <a href="{{ route('projects.create') }}" class="rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-slate-950">{{ __('ui.projects.create') }}</a>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-3xl border border-white/10 bg-white/5">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-slate-300">
                <tr>
                    <th class="px-4 py-3">{{ __('ui.common.project') }}</th>
                    <th class="px-4 py-3">{{ __('ui.projects.pm') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.dates') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse ($projects as $project)
                    <tr class="text-slate-200">
                        <td class="px-4 py-4">
                            <p class="font-semibold text-white">{{ $project->name }}</p>
                            <p class="text-slate-400">{{ $project->code }}</p>
                        </td>
                        <td class="px-4 py-4">{{ $project->projectManager?->name }}</td>
                        <td class="px-4 py-4">{{ $project->expected_start_date?->format('Y-m-d') }} to {{ $project->expected_end_date?->format('Y-m-d') }}</td>
                        <td class="px-4 py-4">{{ __("ui.statuses.$project->status") }}</td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('projects.edit', $project) }}" class="text-sky-300">{{ __('ui.common.edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">{{ __('ui.projects.no_projects') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $projects->links() }}</div>
</x-app-layout>
