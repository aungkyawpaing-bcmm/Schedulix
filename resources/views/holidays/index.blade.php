<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.holidays.title') }}</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">{{ __('ui.holidays.subtitle') }}</h2>
            </div>
            <a href="{{ route('holidays.create') }}" class="rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-slate-950">{{ __('ui.holidays.create') }}</a>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-3xl border border-white/10 bg-white/5">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-slate-300">
                <tr>
                    <th class="px-4 py-3">{{ __('ui.holidays.holiday') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.date') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.type') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach ($holidays as $holiday)
                    <tr class="text-slate-200">
                        <td class="px-4 py-4">{{ $holiday->name }}</td>
                        <td class="px-4 py-4">{{ $holiday->holiday_date?->format('Y-m-d') }}</td>
                        <td class="px-4 py-4">{{ __("ui.holiday_types.$holiday->holiday_type") }}</td>
                        <td class="px-4 py-4 text-right"><a href="{{ route('holidays.edit', $holiday) }}" class="text-sky-300">{{ __('ui.common.edit') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $holidays->links() }}</div>
</x-app-layout>
