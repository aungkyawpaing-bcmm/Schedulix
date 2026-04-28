<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.working_hours.title') }}</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">{{ __('ui.working_hours.subtitle') }}</h2>
            </div>
            <a href="{{ route('working-hours.create') }}" class="rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-slate-950">{{ __('ui.working_hours.create') }}</a>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-3xl border border-white/10 bg-white/5">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-slate-300">
                <tr>
                    <th class="px-4 py-3">{{ __('ui.working_hours.scope') }}</th>
                    <th class="px-4 py-3">{{ __('ui.working_hours.weekday') }}</th>
                    <th class="px-4 py-3">{{ __('ui.working_hours.hours') }}</th>
                    <th class="px-4 py-3">{{ __('ui.working_hours.net') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach ($workingHours as $workingHour)
                    <tr class="text-slate-200">
                        <td class="px-4 py-4">{{ $workingHour->scope_type === 'project' ? ($workingHour->project?->name ?? __('ui.working_hours.project')) : __('ui.working_hours.global') }}</td>
                        <td class="px-4 py-4">{{ __("ui.weekdays.$workingHour->weekday") }}</td>
                        <td class="px-4 py-4">{{ $workingHour->start_time ?: '-' }} to {{ $workingHour->end_time ?: '-' }}</td>
                        <td class="px-4 py-4">{{ $workingHour->net_hours }}</td>
                        <td class="px-4 py-4 text-right"><a href="{{ route('working-hours.edit', $workingHour) }}" class="text-sky-300">{{ __('ui.common.edit') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $workingHours->links() }}</div>
</x-app-layout>
