<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.wbs.title') }}</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">{{ $project->name }}</h2>
                <p class="mt-2 text-sm text-slate-300">{{ __('ui.wbs.subtitle') }}</p>
            </div>
            <div class="flex flex-wrap items-end gap-3">
                <form method="GET" action="{{ route('wbs-builder.search') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="wbs-project-id" class="mb-2 block text-sm font-medium text-slate-300">{{ __('ui.common.project') }}</label>
                        <select id="wbs-project-id" name="project_id" class="min-w-[260px] rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-white">
                            @foreach ($projects as $selectableProject)
                                <option value="{{ $selectableProject->id }}" @selected($selectableProject->id === $project->id)>
                                    {{ $selectableProject->name }} ({{ $selectableProject->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/5">{{ __('ui.common.search') }}</button>
                </form>
                <a href="{{ route('wbs-builder.create', $project) }}" class="rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-slate-950">
                    {{ __('ui.wbs.add_item') }}
                </a>
                <a href="{{ route('assignments.index', $project) }}" class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/5">
                    {{ __('ui.wbs.open_assignments') }}
                </a>
            </div>
        </div>
    </x-slot>

    <section class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-white">{{ __('ui.wbs.tree') }}</h3>
                <p class="text-sm text-slate-400">{{ __('ui.wbs.tree_note') }}</p>
            </div>
            <span class="rounded-full border border-white/10 px-3 py-1 text-xs uppercase tracking-[0.25em] text-slate-300">
                {{ __('ui.wbs.item_count', ['count' => $project->wbsItems->count()]) }}
            </span>
        </div>

        <div class="overflow-hidden rounded-2xl border border-white/10">
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-white/5 text-left text-slate-300">
                    <tr>
                        <th class="px-4 py-3">{{ __('ui.wbs.wbs_no') }}</th>
                        <th class="px-4 py-3">{{ __('ui.wbs.item') }}</th>
                        <th class="px-4 py-3">{{ __('ui.common.type') }}</th>
                        <th class="px-4 py-3">{{ __('ui.wbs.assignable') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('ui.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($project->wbsItems->sortBy('wbs_number') as $item)
                        <tr class="text-slate-200">
                            <td class="px-4 py-3 font-semibold text-white">{{ $item->wbs_number }}</td>
                            <td class="px-4 py-3">
                                <div style="padding-left: {{ max(($item->level - 1) * 18, 0) }}px">
                                    <p class="font-medium text-white">{{ $item->item_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $item->taskMaster?->name ?: __('ui.wbs.item') }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ __("ui.wbs_item_types.$item->item_type") }}</td>
                            <td class="px-4 py-3">{{ $item->is_assignable ? __('ui.common.yes') : __('ui.common.no') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('wbs-builder.edit', $item) }}" class="text-sky-300">{{ __('ui.common.edit') }}</a>
                                    <form method="POST" action="{{ route('wbs-builder.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-300">{{ __('ui.common.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">{{ __('ui.wbs.no_items') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
