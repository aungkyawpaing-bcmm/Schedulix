<form method="POST" action="{{ $editingItem ? route('wbs-builder.update', $editingItem) : route('wbs-builder.store', $project) }}" class="space-y-4">
    @csrf
    @if ($editingItem)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-300/30 bg-rose-300/10 px-4 py-3 text-sm text-rose-200">
            {{ __('ui.common.review_errors') }}
        </div>
    @endif
    <x-field-error for="wbs" class="mt-2" />

    <div>
        <label class="mb-2 block text-sm text-slate-300">{{ __('ui.wbs.parent_item') }}</label>
        <select name="parent_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
            <option value="">{{ __('ui.wbs.root') }}</option>
            @foreach ($project->wbsItems->sortBy('wbs_number') as $item)
                @if (! $editingItem || $editingItem->id !== $item->id)
                    <option value="{{ $item->id }}" @selected(old('parent_id', $editingItem?->parent_id) == $item->id)>{{ $item->wbs_number }} - {{ $item->item_name }}</option>
                @endif
            @endforeach
        </select>
        <x-field-error for="parent_id" class="mt-2" />
    </div>

    <div>
        <label class="mb-2 block text-sm text-slate-300">{{ __('ui.wbs.item_name') }}</label>
        <input name="item_name" value="{{ old('item_name', $editingItem?->item_name) }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
        <x-field-error for="item_name" class="mt-2" />
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.wbs.item_type') }}</label>
            <select name="item_type" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                @foreach (config('wbs.wbs_item_types') as $type)
                    <option value="{{ $type }}" @selected(old('item_type', $editingItem?->item_type ?: 'task') === $type)>{{ __("ui.wbs_item_types.$type") }}</option>
                @endforeach
            </select>
            <x-field-error for="item_type" class="mt-2" />
        </div>
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.wbs.reusable_master_task') }}</label>
            <select name="task_master_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                <option value="">{{ __('ui.common.none') }}</option>
                @foreach ($taskMasters as $task)
                    <option value="{{ $task->id }}" @selected(old('task_master_id', $editingItem?->task_master_id) == $task->id)>{{ $task->task_code }} - {{ $task->name }}</option>
                @endforeach
            </select>
            <x-field-error for="task_master_id" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.wbs.content_item_type') }}</label>
            <select name="content_item_type" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                <option value="">{{ __('ui.common.none') }}</option>
                @foreach (config('wbs.content_item_types') as $type)
                    <option value="{{ $type }}" @selected(old('content_item_type', $editingItem?->content_item_type) === $type)>{{ __("ui.content_item_types.$type") }}</option>
                @endforeach
            </select>
            <x-field-error for="content_item_type" class="mt-2" />
        </div>
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.common.platform') }}</label>
            <select name="platform" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                <option value="">{{ __('ui.common.none') }}</option>
                @foreach (config('wbs.platforms') as $platform)
                    <option value="{{ $platform }}" @selected(old('platform', $editingItem?->platform) === $platform)>{{ __("ui.platforms.$platform") }}</option>
                @endforeach
            </select>
            <x-field-error for="platform" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.wbs.assignable') }}</label>
            <select name="is_assignable" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                <option value="1" @selected((int) old('is_assignable', $editingItem?->is_assignable ?? 0) === 1)>{{ __('ui.common.yes') }}</option>
                <option value="0" @selected((int) old('is_assignable', $editingItem?->is_assignable ?? 0) === 0)>{{ __('ui.common.no') }}</option>
            </select>
            <x-field-error for="is_assignable" class="mt-2" />
        </div>
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.wbs.sort_order') }}</label>
            <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $editingItem?->sort_order) }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
            <x-field-error for="sort_order" class="mt-2" />
        </div>
    </div>

    <div>
        <label class="mb-2 block text-sm text-slate-300">{{ __('ui.common.description') }}</label>
        <textarea name="description" rows="4" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">{{ old('description', $editingItem?->description) }}</textarea>
        <x-field-error for="description" class="mt-2" />
    </div>

    <div class="flex flex-wrap gap-3">
        <button class="rounded-2xl bg-sky-400 px-5 py-3 text-sm font-semibold text-slate-950">{{ $editingItem ? __('ui.wbs.save_item') : __('ui.wbs.add_item_button') }}</button>
        <a href="{{ route('wbs-builder.index', $project) }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/5">{{ __('ui.common.cancel') }}</a>
    </div>
</form>
