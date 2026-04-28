<table>
    <thead>
        <tr>
            <th colspan="{{ 15 + $grid['dates']->count() }}">WBS Export - {{ $project->name }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="4">Monthly Summary</td>
        </tr>
        <tr>
            <td>PIC</td>
            <td>Planned</td>
            <td>Actual</td>
            <td></td>
        </tr>
        @foreach ($grid['monthlySummary'] as $row)
            <tr>
                <td>{{ $row['pic'] }}</td>
                <td>{{ $row['planned'] }}</td>
                <td>{{ $row['actual'] }}</td>
                <td></td>
            </tr>
        @endforeach

        <tr><td colspan="{{ 15 + $grid['dates']->count() }}"></td></tr>

        <tr>
            <td>Platform</td>
            <td>WBS No.</td>
            <td>Task/Subtask</td>
            <td>Content Type</td>
            <td>Plan Rest Hours</td>
            <td>Variance</td>
            <td>Planned Hours</td>
            <td>Digestion Hours</td>
            <td>Actual Total</td>
            <td>Planned Start</td>
            <td>Actual Start</td>
            <td>Planned End</td>
            <td>Actual End</td>
            <td>Remaining Hours</td>
            <td>Progress %</td>
            @foreach ($grid['dates'] as $date)
                <td>{{ $date->format('Y-m-d') }}</td>
            @endforeach
        </tr>

        @foreach ($grid['detailRows'] as $row)
            @php($assignment = $row['assignment'])
            @php($schedule = $row['schedule'])
            <tr>
                <td>{{ $assignment->wbsItem?->platform }}</td>
                <td>{{ $assignment->wbsItem?->wbs_number }}</td>
                <td>{{ $assignment->wbsItem?->item_name }}</td>
                <td>{{ $assignment->wbsItem?->content_item_type }}</td>
                <td>{{ $assignment->plan_rest_hours }}</td>
                <td>{{ number_format((float) ($schedule?->actual_total_hours ?? 0) - (float) ($assignment->plan_rest_hours ?? 0), 2) }}</td>
                <td>{{ $schedule?->planned_hours ?? $assignment->planned_hours }}</td>
                <td>{{ $schedule?->digestion_hours ?? 0 }}</td>
                <td>{{ $schedule?->actual_total_hours ?? 0 }}</td>
                <td>{{ $schedule?->planned_start_date?->format('Y-m-d') }}</td>
                <td>{{ $schedule?->actual_start_date?->format('Y-m-d') }}</td>
                <td>{{ $schedule?->planned_end_date?->format('Y-m-d') }}</td>
                <td>{{ $schedule?->actual_end_date?->format('Y-m-d') }}</td>
                <td>{{ $schedule?->remaining_hours ?? $assignment->planned_hours }}</td>
                <td>{{ $schedule?->progress_percent ?? 0 }}</td>
                @foreach ($grid['dates'] as $date)
                    <td>{{ $row['planned_map'][$date->toDateString()] }}</td>
                @endforeach
            </tr>
            <tr>
                <td colspan="15">Actual row</td>
                @foreach ($grid['dates'] as $date)
                    <td>{{ $row['actual_map'][$date->toDateString()] }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
