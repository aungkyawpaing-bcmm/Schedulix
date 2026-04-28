<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.task_master.title') }}</p>
            <h2 class="mt-2 text-3xl font-semibold text-white">{{ $task->exists ? __('ui.task_master.edit_title') : __('ui.task_master.create_title') }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ $task->exists ? route('task-master.update', $task) : route('task-master.store') }}" class="space-y-6 rounded-3xl border border-white/10 bg-white/5 p-6">
        @csrf
        @if ($task->exists)
            @method('PUT')
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-300/30 bg-rose-300/10 px-4 py-3 text-sm text-rose-200">
                {{ __('ui.common.review_errors') }}
            </div>
        @endif

        @if (! empty($isLocked))
            <div class="rounded-2xl border border-amber-300/20 bg-amber-300/10 px-4 py-3 text-sm text-amber-100">
                {{ __('ui.task_master.locked_note') }}
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <input name="task_code" value="{{ old('task_code', $task->task_code) }}" placeholder="{{ __('ui.task_master.task_code') }}" @disabled(! empty($isLocked)) class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white disabled:opacity-50" />
                <x-field-error for="task_code" class="mt-2" />
            </div>
            <div>
                <input name="name" value="{{ old('name', $task->name) }}" placeholder="{{ __('ui.task_master.task_name') }}" @disabled(! empty($isLocked)) class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white disabled:opacity-50" />
                <x-field-error for="name" class="mt-2" />
            </div>
            <div>
                <select name="content_item_type" @disabled(! empty($isLocked)) class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white disabled:opacity-50">
                    @foreach (config('wbs.content_item_types') as $type)
                        <option value="{{ $type }}" @selected(old('content_item_type', $task->content_item_type ?: 'copy') === $type)>{{ __("ui.content_item_types.$type") }}</option>
                    @endforeach
                </select>
                <x-field-error for="content_item_type" class="mt-2" />
            </div>
            <div>
                <select name="platform" @disabled(! empty($isLocked)) class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white disabled:opacity-50">
                    @foreach (config('wbs.platforms') as $platform)
                        <option value="{{ $platform }}" @selected(old('platform', $task->platform ?: 'web') === $platform)>{{ __("ui.platforms.$platform") }}</option>
                    @endforeach
                </select>
                <x-field-error for="platform" class="mt-2" />
            </div>
            <div>
                <select name="is_active" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    <option value="1" @selected(old('is_active', $task->exists ? (int) $task->is_active : 1) == 1)>{{ __('ui.statuses.active') }}</option>
                    <option value="0" @selected(old('is_active', (int) $task->is_active) == 0)>{{ __('ui.statuses.inactive') }}</option>
                </select>
                <x-field-error for="is_active" class="mt-2" />
            </div>
        </div>
        <div>
            <textarea name="description" rows="4" @disabled(! empty($isLocked)) class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white disabled:opacity-50" placeholder="{{ __('ui.common.description') }}">{{ old('description', $task->description) }}</textarea>
            <x-field-error for="description" class="mt-2" />
        </div>

        <button class="rounded-2xl bg-sky-400 px-5 py-3 text-sm font-semibold text-slate-950">{{ $task->exists ? __('ui.task_master.save') : __('ui.task_master.create') }}</button>
    </form>
</x-app-layout>
