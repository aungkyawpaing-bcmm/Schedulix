<form method="POST" action="{{ $editingAssignment ? route('assignments.update', $editingAssignment) : route('assignments.store', $project) }}" class="space-y-4">
    @csrf
    @if ($editingAssignment)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-300/30 bg-rose-300/10 px-4 py-3 text-sm text-rose-200">
            {{ __('ui.common.review_errors') }}
        </div>
    @endif
    <x-field-error for="assignment" class="mt-2" />

    <div>
        <label class="mb-2 block text-sm text-slate-300">{{ __('ui.assignments.project_manager') }}</label>
        <select name="project_manager_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(old('project_manager_id', $editingAssignment?->project_manager_id ?: $project->project_manager_id) == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        <x-field-error for="project_manager_id" class="mt-2" />
    </div>

    <div>
        <label class="mb-2 block text-sm text-slate-300">{{ __('ui.assignments.project_leaders') }}</label>
        <select name="project_leader_ids[]" multiple size="4" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
            @php($selectedLeaders = collect(old('project_leader_ids', $project->members->where('project_role', 'project_leader')->pluck('user_id')->all())))
            @foreach ($leaderOptions as $user)
                <option value="{{ $user->id }}" @selected($selectedLeaders->contains($user->id))>{{ $user->name }}</option>
            @endforeach
        </select>
        <p class="mt-2 text-xs text-slate-400">{{ __('ui.assignments.project_leaders_help') }}</p>
        <x-field-error for="project_leader_ids" class="mt-2" />
        <x-field-error for="project_leader_ids.*" class="mt-2" />
    </div>

    <div>
        <label class="mb-2 block text-sm text-slate-300">{{ __('ui.assignments.wbs_item') }}</label>
        <select name="project_wbs_item_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
            @foreach ($assignableItems as $item)
                @if (! $item->children->count())
                    <option value="{{ $item->id }}" @selected(old('project_wbs_item_id', $editingAssignment?->project_wbs_item_id) == $item->id)>{{ $item->wbs_number }} - {{ $item->item_name }}</option>
                @endif
            @endforeach
        </select>
        <x-field-error for="project_wbs_item_id" class="mt-2" />
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.assignments.dependency_fs') }}</label>
            <select name="depends_on_assignment_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                <option value="">{{ __('ui.assignments.none') }}</option>
                @foreach ($project->assignments as $assignment)
                    @if (! $editingAssignment || $editingAssignment->id !== $assignment->id)
                        <option value="{{ $assignment->id }}" @selected(old('depends_on_assignment_id', $editingAssignment?->dependencies->first()?->depends_on_assignment_id) == $assignment->id)>{{ $assignment->wbsItem?->wbs_number }} - {{ $assignment->wbsItem?->item_name }}</option>
                    @endif
                @endforeach
            </select>
            <x-field-error for="depends_on_assignment_id" class="mt-2" />
        </div>
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.assignments.priority') }}</label>
            <select name="priority" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                @foreach (config('wbs.priority_levels') as $priority)
                    <option value="{{ $priority }}" @selected(old('priority', $editingAssignment?->priority ?: 'medium') === $priority)>{{ __("ui.priorities.$priority") }}</option>
                @endforeach
            </select>
            <x-field-error for="priority" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.assignments.planned_hours') }}</label>
            <input type="number" step="0.25" name="planned_hours" value="{{ old('planned_hours', $editingAssignment?->planned_hours) }}" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white" />
            <x-field-error for="planned_hours" class="mt-2" />
        </div>
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.assignments.assigned_pic') }}</label>
            <select name="assigned_pic_id" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(old('assigned_pic_id', $editingAssignment?->assigned_pic_id) == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <x-field-error for="assigned_pic_id" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.assignments.assigned_role') }}</label>
            <select name="assigned_role" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                @foreach (config('wbs.system_roles') as $role)
                    <option value="{{ $role }}" @selected(old('assigned_role', $editingAssignment?->assigned_role ?: 'member') === $role)>{{ __("ui.roles.$role") }}</option>
                @endforeach
            </select>
            <x-field-error for="assigned_role" class="mt-2" />
        </div>
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.common.status') }}</label>
            <select name="status" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                @foreach (['draft', 'scheduled', 'ongoing', 'completed'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $editingAssignment?->status ?: 'draft') === $status)>{{ __("ui.statuses.$status") }}</option>
                @endforeach
            </select>
            <x-field-error for="status" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm text-slate-300">{{ __('ui.assignments.leave_dates') }}</label>
            @php($leaveDefaults = collect(old('leave_dates', $editingAssignment ? \App\Models\AssignmentLeave::query()->where('assignment_id', $editingAssignment->id)->pluck('leave_date')->all() : []))->filter(fn ($leaveDate) => filled($leaveDate))->values()->all())
            <div x-data='{"leaveDates": @json($leaveDefaults)}' class="space-y-3">
                <template x-if="leaveDates.length === 0">
                    <p class="rounded-2xl border border-dashed border-white/10 px-4 py-3 text-sm text-slate-400">
                        {{ __('ui.assignments.leave_dates_empty') }}
                    </p>
                </template>

                <template x-for="(leaveDate, index) in leaveDates" :key="`leave-date-${index}`">
                    <div class="flex items-center gap-2">
                        <input
                            type="date"
                            name="leave_dates[]"
                            x-model="leaveDates[index]"
                            class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white"
                        />
                        <button
                            type="button"
                            @click="leaveDates.splice(index, 1)"
                            class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/5"
                        >
                            {{ __('ui.common.delete') }}
                        </button>
                    </div>
                </template>

                <button
                    type="button"
                    @click="leaveDates.push('')"
                    class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/5"
                >
                    {{ __('ui.assignments.add_leave_date') }}
                </button>
            </div>
            <x-field-error for="leave_dates" class="mt-2" />
            <x-field-error for="leave_dates.*" class="mt-2" />
        </div>
        <div class="space-y-4">
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('ui.assignments.auto_create_schedule') }}</label>
                <select name="auto_create_schedule" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    <option value="1" @selected((int) old('auto_create_schedule', $editingAssignment?->auto_create_schedule ?? 1) === 1)>{{ __('ui.common.yes') }}</option>
                    <option value="0" @selected((int) old('auto_create_schedule', $editingAssignment?->auto_create_schedule ?? 1) === 0)>{{ __('ui.common.no') }}</option>
                </select>
                <x-field-error for="auto_create_schedule" class="mt-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm text-slate-300">{{ __('ui.assignments.critical_path') }}</label>
                <select name="is_critical" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">
                    <option value="0" @selected((int) old('is_critical', $editingAssignment?->is_critical ?? 0) === 0)>{{ __('ui.common.no') }}</option>
                    <option value="1" @selected((int) old('is_critical', $editingAssignment?->is_critical ?? 0) === 1)>{{ __('ui.common.yes') }}</option>
                </select>
                <x-field-error for="is_critical" class="mt-2" />
            </div>
        </div>
    </div>

    <div>
        <label class="mb-2 block text-sm text-slate-300">{{ __('ui.assignments.remark') }}</label>
        <textarea name="remark" rows="4" class="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-white">{{ old('remark', $editingAssignment?->remark) }}</textarea>
        <x-field-error for="remark" class="mt-2" />
    </div>

    <div class="flex flex-wrap gap-3">
        <button class="rounded-2xl bg-sky-400 px-5 py-3 text-sm font-semibold text-slate-950">{{ $editingAssignment ? __('ui.assignments.save_assignment') : __('ui.assignments.save_generate') }}</button>
        <a href="{{ route('assignments.index', $project) }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/5">{{ __('ui.common.cancel') }}</a>
    </div>
</form>
