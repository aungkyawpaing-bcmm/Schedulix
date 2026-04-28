<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.task_master.title') }}</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">{{ __('ui.task_master.subtitle') }}</h2>
            </div>
            <a href="{{ route('task-master.create') }}" class="rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-slate-950">{{ __('ui.task_master.create') }}</a>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-3xl border border-white/10 bg-white/5">
        <table class="min-w-full divide-y divide-white/10 text-sm">
            <thead class="bg-white/5 text-left text-slate-300">
                <tr>
                    <th class="px-4 py-3">{{ __('ui.task_master.task') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.type') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.platform') }}</th>
                    <th class="px-4 py-3">{{ __('ui.common.status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach ($tasks as $task)
                    <tr class="text-slate-200">
                        <td class="px-4 py-4">
                            <p class="font-semibold text-white">{{ $task->name }}</p>
                            <p class="text-slate-400">{{ $task->task_code }}</p>
                        </td>
                        <td class="px-4 py-4">{{ __("ui.content_item_types.$task->content_item_type") }}</td>
                        <td class="px-4 py-4">{{ __("ui.platforms.$task->platform") }}</td>
                        <td class="px-4 py-4">{{ $task->is_active ? __('ui.statuses.active') : __('ui.statuses.inactive') }}</td>
                        <td class="px-4 py-4 text-right"><a href="{{ route('task-master.edit', $task) }}" class="text-sky-300">{{ __('ui.common.edit') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $tasks->links() }}</div>
</x-app-layout>
