<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.notifications.title') }}</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">{{ __('ui.notifications.subtitle') }}</h2>
            </div>
            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('notifications.generate', 'daily') }}">@csrf<button class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-white">{{ __('ui.notifications.generate_daily') }}</button></form>
                <form method="POST" action="{{ route('notifications.generate', 'overdue') }}">@csrf<button class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-white">{{ __('ui.notifications.generate_overdue') }}</button></form>
                <form method="POST" action="{{ route('notifications.generate', 'risk') }}">@csrf<button class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-white">{{ __('ui.notifications.generate_risk') }}</button></form>
                <form method="POST" action="{{ route('notifications.generate', 'summary') }}">@csrf<button class="rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-slate-950">{{ __('ui.notifications.generate_summary') }}</button></form>
            </div>
        </div>
    </x-slot>

    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="overflow-hidden rounded-2xl border border-white/10">
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-white/5 text-left text-slate-300">
                    <tr>
                        <th class="px-4 py-3">{{ __('ui.common.type') }}</th>
                        <th class="px-4 py-3">{{ __('ui.notifications.user') }}</th>
                        <th class="px-4 py-3">{{ __('ui.common.project') }}</th>
                        <th class="px-4 py-3">{{ __('ui.notifications.assignment') }}</th>
                        <th class="px-4 py-3">{{ __('ui.common.status') }}</th>
                        <th class="px-4 py-3">{{ __('ui.notifications.scheduled') }}</th>
                        <th class="px-4 py-3">{{ __('ui.notifications.sent') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($notifications as $notification)
                        <tr class="text-slate-200">
                            <td class="px-4 py-3">{{ __("ui.notification_types.$notification->type") }}</td>
                            <td class="px-4 py-3">{{ $notification->user?->name }}</td>
                            <td class="px-4 py-3">{{ $notification->project?->name ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $notification->assignment?->wbsItem?->wbs_number ?: '-' }}</td>
                            <td class="px-4 py-3">{{ __("ui.statuses.$notification->status") }}</td>
                            <td class="px-4 py-3">{{ $notification->scheduled_for?->format('Y-m-d H:i') ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $notification->sent_at?->format('Y-m-d H:i') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">{{ __('ui.notifications.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $notifications->links() }}</div>
    </div>
</x-app-layout>
