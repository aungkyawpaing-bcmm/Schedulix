@php
    $detailTableBaseWidth = 2172;
    $detailTableWidth = $detailTableBaseWidth + ($grid['dates']->count() * 76);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-sky-200/70">{{ __('ui.schedule.title') }}</p>
                <h2 class="mt-2 text-3xl font-semibold text-white">{{ $project->name }}</h2>
                <p class="mt-2 text-sm text-slate-300">{{ __('ui.schedule.subtitle') }}</p>
            </div>
            <div class="flex flex-wrap items-end gap-3">
                <form method="GET" action="{{ route('schedule.search') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="schedule-project-id" class="mb-2 block text-sm font-medium text-slate-300">{{ __('ui.common.project') }}</label>
                        <select id="schedule-project-id" name="project_id" class="min-w-[260px] rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-white">
                            @foreach ($projects as $selectableProject)
                                <option value="{{ $selectableProject->id }}" @selected($selectableProject->id === $project->id)>
                                    {{ $selectableProject->name }} ({{ $selectableProject->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/5">{{ __('ui.common.search') }}</button>
                </form>
                <a href="{{ route('assignments.index', $project) }}" class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/5">{{ __('ui.schedule.assigned_list') }}</a>
                <form method="POST" action="{{ route('exports.store', $project) }}">
                    @csrf
                    <input type="hidden" name="export_type" value="xlsx" />
                    <input type="hidden" name="include_formula" value="1" />
                    <input type="hidden" name="include_critical_path" value="1" />
                    <input type="hidden" name="export_locale" value="{{ app()->getLocale() }}" />
                    <button class="rounded-2xl bg-sky-400 px-4 py-3 text-sm font-semibold text-slate-950">{{ __('ui.schedule.export_excel') }}</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <section class="min-w-0 space-y-6">
                <div class="schedulix-panel rounded-3xl p-6">
                    <h3 class="text-lg font-semibold text-white">{{ __('ui.schedule.monthly_summary') }}</h3>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-white/10">
                        <table class="min-w-full divide-y divide-white/10 text-sm">
                            <thead class="bg-white/5 text-left text-slate-300">
                                <tr>
                                    <th class="px-4 py-3">PIC</th>
                                    <th class="px-4 py-3">{{ __('ui.common.planned') }}</th>
                                    <th class="px-4 py-3">{{ __('ui.common.actual') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                @foreach ($grid['monthlySummary'] as $row)
                                    <tr class="text-slate-200">
                                        <td class="px-4 py-3">{{ $row['pic'] }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $row['planned'], 2) }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $row['actual'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="schedulix-panel rounded-3xl p-6">
                    <h3 class="text-lg font-semibold text-white">{{ __('ui.schedule.daily_summary') }}</h3>
                    <div class="mt-4 overflow-x-auto rounded-2xl border border-white/10">
                        <table class="min-w-max divide-y divide-white/10 text-sm">
                            <thead class="bg-white/5 text-left text-slate-300">
                                <tr>
                                    <th class="schedulix-sticky schedulix-sticky-header-divider px-4 py-3" style="--schedulix-left: 0px; min-width: 180px; width: 180px;">PIC</th>
                                    <th class="schedulix-sticky schedulix-sticky-header-divider px-4 py-3" style="--schedulix-left: 180px; min-width: 96px; width: 96px;">{{ __('ui.common.category') }}</th>
                                    @foreach ($grid['dates'] as $date)
                                        @php($dateKey = $date->toDateString())
                                        @php($isToday = $dateKey === $grid['todayPanel']['today'])
                                        @php($isHoliday = $grid['holidayDates']->contains($dateKey) || $date->isWeekend())
                                        <th class="schedulix-date-header px-3 py-3 text-center {{ $isHoliday ? 'schedulix-holiday-column' : '' }} {{ $isToday ? 'schedulix-today' : '' }}">
                                            <span class="block text-xs uppercase tracking-[0.15em]">{{ $date->format('M') }}</span>
                                            <span class="mt-1 block font-semibold">{{ $date->format('m-d') }}</span>
                                            <span class="mt-1 block text-xs lowercase">{{ $date->format('D') }}</span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                @foreach ($grid['dailySummary'] as $summary)
                                    <tr class="text-slate-200">
                                        <td rowspan="2" class="schedulix-sticky schedulix-sticky-cell-divider px-4 py-3 font-semibold text-white" style="--schedulix-left: 0px; min-width: 180px; width: 180px;">{{ $summary['pic'] }}</td>
                                        <td class="schedulix-sticky schedulix-sticky-cell-divider px-4 py-3 schedulix-plan-label" style="--schedulix-left: 180px; min-width: 96px; width: 96px;">{{ __('ui.common.planned') }}</td>
                                        @foreach ($grid['dates'] as $date)
                                            @php($meta = $summary['date_meta'][$date->toDateString()])
                                            <td class="schedulix-date-cell px-3 py-3 text-center {{ $meta['is_holiday'] ? 'schedulix-holiday-column' : '' }} {{ $meta['is_today'] ? 'schedulix-today' : '' }} {{ (float) $summary['planned'][$date->toDateString()] > 0 && ! $meta['is_holiday'] ? 'schedulix-planned-cell' : '' }}">
                                                {{ (float) $summary['planned'][$date->toDateString()] > 0 ? number_format((float) $summary['planned'][$date->toDateString()], 2) : '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                    <tr class="schedulix-row-alt text-slate-200">
                                        <td class="schedulix-sticky schedulix-sticky-cell-divider px-4 py-3 schedulix-actual-label" style="--schedulix-left: 180px; min-width: 96px; width: 96px;">{{ __('ui.common.actual') }}</td>
                                        @foreach ($grid['dates'] as $date)
                                            @php($meta = $summary['date_meta'][$date->toDateString()])
                                            <td class="schedulix-date-cell px-3 py-3 text-center {{ $meta['is_holiday'] ? 'schedulix-holiday-column' : '' }} {{ $meta['is_today'] ? 'schedulix-today' : '' }} {{ (float) $summary['actual'][$date->toDateString()] > 0 && ! $meta['is_holiday'] ? 'schedulix-actual-cell' : '' }}">
                                                {{ (float) $summary['actual'][$date->toDateString()] > 0 ? number_format((float) $summary['actual'][$date->toDateString()], 2) : '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <aside class="min-w-0 space-y-6">
                <section class="schedulix-panel rounded-3xl p-6">
                    <h3 class="text-lg font-semibold text-white">{{ __('ui.schedule.today') }}</h3>
                    <div class="mt-4 space-y-4">
                        <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-4">
                            <p class="text-sm text-slate-400">{{ __('ui.common.current_date') }}</p>
                            <p class="mt-2 text-2xl font-semibold text-white">{{ $grid['todayPanel']['today'] }}</p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3 xl:grid-cols-1">
                            <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-4">
                                <p class="text-sm text-slate-400">{{ __('ui.schedule.today_tasks') }}</p>
                                <p class="mt-2 text-3xl font-semibold text-white">{{ $grid['todayPanel']['today_task_count'] }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-4">
                                <p class="text-sm text-slate-400">{{ __('ui.schedule.overdue') }}</p>
                                <p class="mt-2 text-3xl font-semibold text-white">{{ $grid['todayPanel']['overdue_count'] }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-4">
                                <p class="text-sm text-slate-400">{{ __('ui.schedule.critical_today') }}</p>
                                <p class="mt-2 text-3xl font-semibold text-white">{{ $grid['todayPanel']['critical_tasks_today'] }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="schedulix-panel rounded-3xl p-6">
                    <h3 class="text-lg font-semibold text-white">{{ __('ui.schedule.legend') }}</h3>
                    <div class="mt-4 space-y-3 text-sm text-slate-300">
                        <p><span class="inline-block h-3 w-3 rounded bg-sky-400/40"></span> {{ __('ui.schedule.today_column') }}</p>
                        <p><span class="inline-block h-3 w-3 rounded bg-slate-300"></span> {{ __('ui.schedule.holiday_column') }}</p>
                        <p><span class="inline-block h-3 w-3 rounded bg-yellow-200"></span> {{ __('ui.schedule.planned_cells') }}</p>
                        <p><span class="inline-block h-3 w-3 rounded bg-emerald-200"></span> {{ __('ui.schedule.actual_cells') }}</p>
                        <p><span class="inline-block h-3 w-3 rounded bg-rose-400/40"></span> {{ __('ui.schedule.overrun') }}</p>
                    </div>
                </section>
            </aside>
        </div>

        <form
            method="POST"
            action="{{ route('schedule.progress.store', $project) }}"
            class="schedulix-panel min-w-0 rounded-3xl p-6"
            x-data="scheduleTableScroller()"
            x-init="init()"
        >
            @csrf

            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-rose-300/30 bg-rose-300/10 px-4 py-3 text-sm text-rose-200">
                    {{ __('ui.common.review_errors') }}
                </div>
            @endif

            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-white">{{ __('ui.schedule.detail') }}</h3>
                    <p class="text-sm text-slate-400">{{ __('ui.schedule.detail_note') }}</p>
                </div>
                <button class="rounded-2xl bg-sky-400 px-5 py-3 text-sm font-semibold text-slate-950">{{ __('ui.schedule.save_progress') }}</button>
            </div>

            <div class="mt-4 overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                <div
                    x-ref="mainScroll"
                    @scroll="syncFromMain"
                    class="schedulix-table-wrap block h-[62vh] w-full max-w-full overflow-x-scroll overflow-y-auto"
                >
                    <table
                        x-ref="detailTable"
                        class="schedulix-detail-table text-sm"
                        style="width: {{ $detailTableWidth }}px; min-width: {{ $detailTableWidth }}px;"
                    >
                    <colgroup>
                        <col style="width: 132px;">
                        <col style="width: 108px;">
                        <col style="width: 300px;">
                        <col style="width: 140px;">
                        <col style="width: 96px;">
                        <col style="width: 140px;">
                        <col style="width: 112px;">
                        <col style="width: 112px;">
                        <col style="width: 112px;">
                        <col style="width: 128px;">
                        <col style="width: 128px;">
                        <col style="width: 156px;">
                        <col style="width: 128px;">
                        <col style="width: 156px;">
                        <col style="width: 112px;">
                        <col style="width: 112px;">
                        @foreach ($grid['dates'] as $date)
                            <col style="width: 76px;">
                        @endforeach
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="schedulix-sticky schedulix-sticky-header-divider px-4 py-3.5" style="--schedulix-left: 0px;">{{ __('ui.common.platform') }}</th>
                            <th class="schedulix-sticky schedulix-sticky-header-divider px-4 py-3.5" style="--schedulix-left: 132px;">{{ __('ui.wbs.wbs_no') }}</th>
                            <th class="schedulix-sticky schedulix-sticky-header-divider px-5 py-3.5" style="--schedulix-left: 240px;">{{ __('ui.schedule.task_name') }}</th>
                            <th class="schedulix-sticky schedulix-sticky-header-divider px-4 py-3.5" style="--schedulix-left: 540px;">{{ __('ui.schedule.content_item_type') }}</th>
                            <th class="px-4 py-3.5">{{ __('ui.common.category') }}</th>
                            <th class="px-4 py-3.5">{{ __('ui.schedule.plan_rest_hours') }}</th>
                            <th class="px-4 py-3.5">{{ __('ui.schedule.variance_hours') }}</th>
                            <th class="px-4 py-3.5">{{ __('ui.schedule.planned_hours') }}</th>
                            <th class="px-4 py-3.5">{{ __('ui.schedule.digestion_hours') }}</th>
                            <th class="px-4 py-3.5">{{ __('ui.schedule.actual_hours') }}</th>
                            <th class="px-4 py-3.5">{{ __('ui.schedule.planned_start') }}</th>
                            <th class="px-4 py-3.5">{{ __('ui.schedule.actual_start') }}</th>
                            <th class="px-4 py-3.5">{{ __('ui.schedule.planned_end') }}</th>
                            <th class="px-4 py-3.5">{{ __('ui.schedule.actual_end') }}</th>
                            <th class="px-4 py-3.5">{{ __('ui.schedule.remaining_hours') }}</th>
                            <th class="px-4 py-3.5">{{ __('ui.schedule.progress_percent') }}</th>
                            @foreach ($grid['dates'] as $date)
                                @php($dateKey = $date->toDateString())
                                @php($isToday = $dateKey === $grid['todayPanel']['today'])
                                @php($isHoliday = $grid['holidayDates']->contains($dateKey) || $date->isWeekend())
                                <th class="schedulix-date-header {{ $isToday ? 'schedulix-today' : '' }} {{ $isHoliday ? 'schedulix-holiday-column' : '' }} px-2 py-3.5">
                                    <span class="block text-[11px] uppercase tracking-[0.15em]">{{ $date->format('M') }}</span>
                                    <span class="mt-1 block font-semibold">{{ $date->format('m-d') }}</span>
                                    <span class="mt-1 block text-[11px] lowercase">{{ $date->format('D') }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($grid['detailRows'] as $row)
                            @php($assignment = $row['assignment'])
                            @php($schedule = $row['schedule'])
                            @php($rowIsComplete = $row['is_complete'])
                            @php($hasWarning = $row['has_missing_actuals'] || $row['has_short_actuals'])
                            <tr class="text-slate-200 {{ $rowIsComplete ? 'schedulix-row-complete' : '' }}">
                                <td rowspan="2" class="schedulix-sticky schedulix-sticky-cell-divider px-4 py-4 align-top" style="--schedulix-left: 0px;">{{ $assignment->wbsItem?->platform ? __("ui.platforms.".$assignment->wbsItem->platform) : '-' }}</td>
                                <td rowspan="2" class="schedulix-sticky schedulix-sticky-cell-divider px-4 py-4 align-top font-semibold text-white" style="--schedulix-left: 132px;">{{ $assignment->wbsItem?->wbs_number }}</td>
                                <td rowspan="2" class="schedulix-sticky schedulix-sticky-cell-divider px-5 py-4 align-top" style="--schedulix-left: 240px;">
                                    <p class="font-semibold text-white">{{ $assignment->wbsItem?->item_name }}</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.2em] text-slate-400">{{ $assignment->pic?->name }}</p>
                                </td>
                                <td class="schedulix-sticky schedulix-sticky-cell-divider px-4 py-4" style="--schedulix-left: 540px;">{{ $assignment->wbsItem?->content_item_type ? __("ui.content_item_types.".$assignment->wbsItem->content_item_type) : '-' }}</td>
                                <td class="px-4 py-4 font-semibold text-slate-400 schedulix-plan-label">{{ __('ui.common.planned') }}</td>
                                <td class="px-4 py-4">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        inputmode="decimal"
                                        name="plan_rest_hours[{{ $assignment->id }}]"
                                        value="{{ old("plan_rest_hours.{$assignment->id}", $row['plan_rest_hours'] !== null ? number_format((float) $row['plan_rest_hours'], 2, '.', '') : null) }}"
                                        class="schedulix-number-input rounded-2xl px-3 py-2 text-white"
                                    />
                                    <x-field-error :for="'plan_rest_hours.'.$assignment->id" class="mt-2" />
                                </td>
                                <td class="px-4 py-4">{{ number_format((float) $row['variance_hours'], 2) }}</td>
                                <td class="px-4 py-4 {{ $hasWarning ? 'schedulix-warning-text' : '' }}">{{ number_format((float) $row['planned_hours'], 2) }}</td>
                                <td class="px-4 py-4">{{ number_format((float) $row['digestion_hours'], 2) }}</td>
                                <td class="px-4 py-4">{{ number_format((float) $row['actual_total_hours'], 2) }}</td>
                                <td class="px-4 py-4 {{ $hasWarning ? 'schedulix-warning-text' : '' }}">{{ $schedule?->planned_start_date?->format('Y-m-d') ?: '-' }}</td>
                                <td class="px-4 py-4">
                                    <input type="date" name="actual_start_dates[{{ $assignment->id }}]" value="{{ old("actual_start_dates.{$assignment->id}", optional($schedule?->actual_start_date)->format('Y-m-d')) }}" class="schedulix-date-input rounded-2xl px-3 py-2 text-white" />
                                    <x-field-error :for="'actual_start_dates.'.$assignment->id" class="mt-2" />
                                </td>
                                <td class="px-4 py-4 {{ $hasWarning ? 'schedulix-warning-text' : '' }}">{{ $schedule?->planned_end_date?->format('Y-m-d') ?: '-' }}</td>
                                <td class="px-4 py-4">
                                    <input type="date" name="actual_end_dates[{{ $assignment->id }}]" value="{{ old("actual_end_dates.{$assignment->id}", optional($schedule?->actual_end_date)->format('Y-m-d')) }}" class="schedulix-date-input rounded-2xl px-3 py-2 text-white" />
                                    <x-field-error :for="'actual_end_dates.'.$assignment->id" class="mt-2" />
                                </td>
                                <td class="px-4 py-4">{{ $row['remaining_hours'] === null ? '-' : number_format((float) $row['remaining_hours'], 2) }}</td>
                                <td class="px-4 py-4 {{ ($row['progress_percent'] ?? 0) > 100 ? 'schedulix-warning-text' : '' }}">{{ $row['progress_percent'] === null ? '-' : number_format((float) $row['progress_percent'], 2) }}</td>
                                @foreach ($grid['dates'] as $date)
                                    @php($dateKey = $date->toDateString())
                                    @php($meta = $row['date_meta'][$dateKey])
                                    <td class="schedulix-date-cell px-2 py-4 {{ $meta['is_today'] ? 'schedulix-today' : '' }} {{ $meta['is_holiday'] || $date->isWeekend() ? 'schedulix-holiday-column' : '' }} {{ $meta['planned_has_value'] && ! $meta['is_holiday'] ? 'schedulix-planned-cell' : '' }}">
                                        {{ $meta['planned_has_value'] ? number_format((float) $row['planned_map'][$dateKey], 2) : '-' }}
                                    </td>
                                @endforeach
                            </tr>
                            <tr class="schedulix-row-alt text-slate-200 {{ $rowIsComplete ? 'schedulix-row-complete' : '' }}">
                                <td class="schedulix-sticky schedulix-sticky-cell-divider px-4 py-4" style="--schedulix-left: 540px;">{{ $assignment->wbsItem?->content_item_type ? __("ui.content_item_types.".$assignment->wbsItem->content_item_type) : '-' }}</td>
                                <td class="px-4 py-4 font-semibold text-slate-400 schedulix-actual-label">{{ __('ui.common.actual') }}</td>
                                <td class="px-4 py-4">{{ $row['plan_rest_hours'] === null ? '-' : number_format((float) $row['plan_rest_hours'], 2) }}</td>
                                <td class="px-4 py-4">{{ number_format((float) $row['variance_hours'], 2) }}</td>
                                <td class="px-4 py-4 {{ $hasWarning ? 'schedulix-warning-text' : '' }}">{{ number_format((float) $row['planned_hours'], 2) }}</td>
                                <td class="px-4 py-4">{{ number_format((float) $row['digestion_hours'], 2) }}</td>
                                <td class="px-4 py-4">{{ number_format((float) $row['actual_total_hours'], 2) }}</td>
                                <td class="px-4 py-4">{{ $schedule?->planned_start_date?->format('Y-m-d') ?: '-' }}</td>
                                <td class="px-4 py-4">{{ $schedule?->actual_start_date?->format('Y-m-d') ?: ($row['first_actual_date'] ?: '-') }}</td>
                                <td class="px-4 py-4">{{ $schedule?->planned_end_date?->format('Y-m-d') ?: '-' }}</td>
                                <td class="px-4 py-4">{{ $schedule?->actual_end_date?->format('Y-m-d') ?: ($row['last_actual_date'] ?: '-') }}</td>
                                <td class="px-4 py-4">{{ $row['remaining_hours'] === null ? '-' : number_format((float) $row['remaining_hours'], 2) }}</td>
                                <td class="px-4 py-4 {{ ($row['progress_percent'] ?? 0) > 100 ? 'schedulix-warning-text' : '' }}">{{ $row['progress_percent'] === null ? '-' : number_format((float) $row['progress_percent'], 2) }}</td>
                                @foreach ($grid['dates'] as $date)
                                    @php($dateKey = $date->toDateString())
                                    @php($meta = $row['date_meta'][$dateKey])
                                    <td class="schedulix-date-cell px-2 py-4 {{ $meta['is_today'] ? 'schedulix-today' : '' }} {{ $meta['is_holiday'] || $date->isWeekend() ? 'schedulix-holiday-column' : '' }} {{ $meta['actual_has_value'] && ! $meta['is_holiday'] ? 'schedulix-actual-cell' : '' }}">
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            inputmode="decimal"
                                            name="actual_hours[{{ $assignment->id }}][{{ $dateKey }}]"
                                            value="{{ old("actual_hours.{$assignment->id}.{$dateKey}", (float) $row['actual_map'][$dateKey] > 0 ? number_format((float) $row['actual_map'][$dateKey], 2, '.', '') : null) }}"
                                            class="w-16 rounded-xl border-0 bg-transparent px-2 py-2 text-center text-white"
                                        />
                                        <x-field-error :for="'actual_hours.'.$assignment->id.'.'.$dateKey" class="mt-2" />
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 16 + $grid['dates']->count() }}" class="px-4 py-10 text-center text-sm text-slate-400">{{ __('ui.schedule.no_assignments') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
                <div
                    x-ref="bottomScroll"
                    @scroll="syncFromBottom"
                    class="schedulix-table-scrollbar block w-full max-w-full overflow-x-scroll border-t border-white/10"
                >
                    <div
                        :style="spacerWidth ? `width: ${spacerWidth}px` : ''"
                        style="width: {{ $detailTableWidth }}px;"
                        class="h-4"
                    ></div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
