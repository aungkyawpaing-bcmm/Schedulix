<?php

namespace App\Exports\Sheets;

use App\Models\Holiday;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WbsWorkbookSheet implements FromArray, WithEvents, WithTitle
{
    private const DAILY_START_COLUMN = 22; // V
    private const PLATFORM_COLUMN = 'B';
    private const NUMBER_COLUMN = 'C';
    private const TASK_START_COLUMN = 'D';
    private const TASK_END_COLUMN = 'I';
    private const CONTENT_COLUMN = 'J';
    private const PIC_COLUMN = 'K';
    private const PLAN_REST_COLUMN = 'L';
    private const VARIANCE_COLUMN = 'M';
    private const PLANNED_COLUMN = 'N';
    private const DIGESTION_COLUMN = 'O';
    private const ACTUAL_TOTAL_COLUMN = 'P';
    private const START_COLUMN = 'Q';
    private const END_COLUMN = 'R';
    private const REMAINING_COLUMN = 'S';
    private const PROGRESS_COLUMN = 'T';
    private const CATEGORY_COLUMN = 'U';

    public function __construct(
        private readonly Project $project,
        private readonly array $grid,
        private readonly array $options,
    ) {
    }

    public function array(): array
    {
        return [[]];
    }

    public function title(): string
    {
        return 'WBS';
    }

    public function populate(Worksheet $sheet): void
    {
        $this->buildSheet($sheet);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $this->populate($event->sheet->getDelegate());
            },
        ];
    }

    private function buildSheet(Worksheet $sheet): void
    {
        $labels = $this->labels();
        $useFormulas = (bool) ($this->options['include_formula'] ?? true);
        $includeCriticalPath = (bool) ($this->options['include_critical_path'] ?? false);
        $dates = collect($this->grid['dates'] ?? [])->values();
        $detailRows = collect($this->grid['detailRows'] ?? [])->values();
        $dailySummary = collect($this->grid['dailySummary'] ?? [])->values();
        $people = $dailySummary->pluck('pic')->filter()->unique()->values();

        if ($people->isEmpty()) {
            $people = collect([$labels['unassigned']]);
        }

        $holidayMap = Holiday::query()
            ->where('is_active', true)
            ->whereBetween('holiday_date', [
                $this->project->expected_start_date,
                $this->project->expected_end_date,
            ])
            ->get()
            ->mapWithKeys(fn (Holiday $holiday) => [
                Carbon::parse($holiday->holiday_date, $this->project->timezone)->toDateString() => $holiday->holiday_type,
            ])
            ->all();

        $picStartRow = 4;
        $totalPlannedRow = $picStartRow + ($people->count() * 2);
        $totalActualRow = $totalPlannedRow + 1;
        $todayRow = $totalActualRow + 1;
        $monthLabelRow = $todayRow + 1;
        $headerRow = $monthLabelRow + 1;
        $subHeaderRow = $headerRow + 1;
        $dataStartRow = $subHeaderRow + 1;
        $detailEndRow = max($dataStartRow, $dataStartRow + ($detailRows->count() * 2) - 1);
        $lastDateColumnIndex = self::DAILY_START_COLUMN + max($dates->count() - 1, 0);
        $lastDateColumn = Coordinate::stringFromColumnIndex($lastDateColumnIndex);
        $tableEndRow = max($subHeaderRow, $detailEndRow);
        $selectedMonthDate = $dates->first() instanceof Carbon
            ? $dates->first()
            : Carbon::parse($this->project->expected_start_date, $this->project->timezone);
        $selectedMonth = $selectedMonthDate->month;
        $selectedMonthLabel = $this->monthLabelValue($selectedMonthDate);
        $currentDate = now($this->project->timezone)->toDateString();

        $sheet->setShowGridLines(false);
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Aptos')->setSize(9);
        $sheet->getSheetView()->setZoomScale(85);
        $sheet->freezePane('V'.$dataStartRow);
        $sheet->setAutoFilter(self::PLATFORM_COLUMN.$headerRow.':'.$lastDateColumn.$tableEndRow);

        $this->applyColumnWidths($sheet, $lastDateColumnIndex);
        $this->applyRowHeights($sheet, $headerRow, $subHeaderRow, $dataStartRow, $detailEndRow);

        $sheet->setCellValue('Q1', $labels['monthly_hours']);
        $sheet->setCellValue('U1', $labels['daily_hours']);
        $sheet->setCellValue('Q2', $labels['pic']);
        $sheet->setCellValue('R2', $labels['planned']);
        $sheet->setCellValue('S2', $labels['actual']);
        $sheet->setCellValue('Q3', $labels['month']);
        $sheet->setCellValue('R3', $selectedMonthLabel);
        $sheet->setCellValue('S3', $useFormulas ? '=R3' : $selectedMonthLabel);
        $sheet->setCellValue(self::TASK_START_COLUMN.$todayRow, $labels['today']);
        $sheet->setCellValue('E'.$todayRow, Carbon::parse($currentDate, $this->project->timezone));
        $sheet->setCellValue(self::PLATFORM_COLUMN.$monthLabelRow, $labels['detail']);
        $sheet->setCellValue(self::PIC_COLUMN.$monthLabelRow, $labels['month']);

        $this->styleSummarySection($sheet, $picStartRow, $totalActualRow, $todayRow, $monthLabelRow);
        $this->buildMonthlyAndDailySummary(
            sheet: $sheet,
            people: $people,
            dailySummary: $dailySummary,
            dates: $dates,
            picStartRow: $picStartRow,
            totalPlannedRow: $totalPlannedRow,
            totalActualRow: $totalActualRow,
            selectedMonth: $selectedMonth,
            lastDateColumn: $lastDateColumn,
            monthLabelRow: $monthLabelRow,
            dataStartRow: $dataStartRow,
            detailEndRow: $detailEndRow,
            useFormulas: $useFormulas,
            labels: $labels,
        );
        $this->buildHeader($sheet, $labels, $monthLabelRow, $headerRow, $subHeaderRow, $dates, $holidayMap, $currentDate);
        $this->applyHolidaySummaryColumnFills($sheet, $dates, $holidayMap, $picStartRow, $totalActualRow);
        $this->applyGazettedHolidayConditionalFormatting($sheet, $dates, $picStartRow, $detailEndRow);
        $this->buildDetailRows(
            sheet: $sheet,
            labels: $labels,
            detailRows: $detailRows,
            dates: $dates,
            holidayMap: $holidayMap,
            dataStartRow: $dataStartRow,
            detailEndRow: $detailEndRow,
            currentDate: $currentDate,
            lastDateColumn: $lastDateColumn,
            includeCriticalPath: $includeCriticalPath,
            useFormulas: $useFormulas,
        );
    }

    private function buildMonthlyAndDailySummary(
        Worksheet $sheet,
        Collection $people,
        Collection $dailySummary,
        Collection $dates,
        int $picStartRow,
        int $totalPlannedRow,
        int $totalActualRow,
        int $selectedMonth,
        string $lastDateColumn,
        int $monthLabelRow,
        int $dataStartRow,
        int $detailEndRow,
        bool $useFormulas,
        array $labels,
    ): void {
        $plannedSummaryRows = [];
        $actualSummaryRows = [];

        foreach ($people as $index => $personName) {
            $plannedRow = $picStartRow + ($index * 2);
            $actualRow = $plannedRow + 1;
            $summaryRow = $dailySummary->firstWhere('pic', $personName) ?? [];

            $plannedSummaryRows[] = $plannedRow;
            $actualSummaryRows[] = $actualRow;

            $sheet->setCellValue('Q'.$plannedRow, $personName);
            $sheet->setCellValue('Q'.$actualRow, $useFormulas ? '=Q'.$plannedRow : $personName);
            $sheet->setCellValue('U'.$plannedRow, $labels['planned']);
            $sheet->setCellValue('U'.$actualRow, $labels['actual']);

            if ($useFormulas && $detailEndRow >= $dataStartRow) {
                $sheet->setCellValue(
                    'R'.$plannedRow,
                    sprintf(
                        '=SUMPRODUCT(($V$%1$d:$%2$s$%1$d=R$3)*($V%3$d:$%2$s%3$d))',
                        $monthLabelRow,
                        $lastDateColumn,
                        $plannedRow,
                    )
                );
                $sheet->setCellValue(
                    'S'.$plannedRow,
                    sprintf(
                        '=SUMPRODUCT(($V$%1$d:$%2$s$%1$d=S$3)*($V%3$d:$%2$s%3$d))',
                        $monthLabelRow,
                        $lastDateColumn,
                        $actualRow,
                    )
                );
            } else {
                $sheet->setCellValue('R'.$plannedRow, $this->sumMonthValues(collect($summaryRow['planned'] ?? []), $dates, $selectedMonth));
                $sheet->setCellValue('S'.$plannedRow, $this->sumMonthValues(collect($summaryRow['actual'] ?? []), $dates, $selectedMonth));
            }

            foreach ($dates as $dateIndex => $date) {
                $column = Coordinate::stringFromColumnIndex(self::DAILY_START_COLUMN + $dateIndex);
                $dateKey = $date->toDateString();

                if ($useFormulas && $detailEndRow >= $dataStartRow) {
                    $sheet->setCellValue(
                        $column.$plannedRow,
                        sprintf(
                            '=SUMPRODUCT(($K$%2$d:$K$%3$d=$Q%4$d)*($U$%2$d:$U$%3$d=$U%4$d)*(%1$s$%2$d:%1$s$%3$d))',
                            $column,
                            $dataStartRow,
                            $detailEndRow,
                            $plannedRow,
                        )
                    );
                    $sheet->setCellValue(
                        $column.$actualRow,
                        sprintf(
                            '=SUMPRODUCT(($K$%2$d:$K$%3$d=$Q%4$d)*($U$%2$d:$U$%3$d=$U%4$d)*(%1$s$%2$d:%1$s$%3$d))',
                            $column,
                            $dataStartRow,
                            $detailEndRow,
                            $actualRow,
                        )
                    );
                } else {
                    $sheet->setCellValue($column.$plannedRow, (float) ($summaryRow['planned'][$dateKey] ?? 0));
                    $sheet->setCellValue($column.$actualRow, (float) ($summaryRow['actual'][$dateKey] ?? 0));
                }
            }
        }

        $sheet->setCellValue('Q'.$totalPlannedRow, $labels['total']);
        $sheet->setCellValue('Q'.$totalActualRow, $labels['total']);
        $sheet->setCellValue('U'.$totalPlannedRow, $labels['planned']);
        $sheet->setCellValue('U'.$totalActualRow, $labels['actual']);
        $sheet->setCellValue('R'.$totalPlannedRow, '=SUM('.implode(',', array_map(fn ($row) => 'R'.$row, $plannedSummaryRows)).')');
        $sheet->setCellValue('S'.$totalPlannedRow, '=SUM('.implode(',', array_map(fn ($row) => 'S'.$row, $plannedSummaryRows)).')');

        for ($columnIndex = self::DAILY_START_COLUMN; $columnIndex <= Coordinate::columnIndexFromString($lastDateColumn); $columnIndex++) {
            $column = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->setCellValue(
                $column.$totalPlannedRow,
                '=SUM('.implode(',', $people->keys()->map(fn ($key) => $column.($picStartRow + ($key * 2)))->all()).')'
            );
            $sheet->setCellValue(
                $column.$totalActualRow,
                '=SUM('.implode(',', $people->keys()->map(fn ($key) => $column.($picStartRow + ($key * 2) + 1))->all()).')'
            );
        }

        $sheet->getStyle('Q'.$picStartRow.':'.$lastDateColumn.$totalActualRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('Q'.$picStartRow.':'.$lastDateColumn.$totalActualRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('Q'.$picStartRow.':'.$lastDateColumn.$totalActualRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('Q'.$picStartRow.':Q'.$totalActualRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('R'.$picStartRow.':'.$lastDateColumn.$totalActualRow)->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle('Q'.$totalPlannedRow.':'.$lastDateColumn.$totalActualRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2F3E9');
        $sheet->getStyle('Q'.$totalPlannedRow.':'.$lastDateColumn.$totalActualRow)->getFont()->setBold(true)->getColor()->setARGB('FF173B2D');
    }

    private function buildHeader(
        Worksheet $sheet,
        array $labels,
        int $monthLabelRow,
        int $headerRow,
        int $subHeaderRow,
        Collection $dates,
        array $holidayMap,
        string $currentDate,
    ): void {
        foreach ([
            self::PLATFORM_COLUMN,
            self::NUMBER_COLUMN,
            self::CONTENT_COLUMN,
            self::PIC_COLUMN,
            self::REMAINING_COLUMN,
            self::PROGRESS_COLUMN,
            self::CATEGORY_COLUMN,
        ] as $column) {
            $sheet->mergeCells($column.$headerRow.':'.$column.$subHeaderRow);
        }

        $sheet->mergeCells(self::TASK_START_COLUMN.$headerRow.':'.self::TASK_END_COLUMN.$subHeaderRow);

        $sheet->setCellValue(self::PLATFORM_COLUMN.$headerRow, $labels['platform']);
        $sheet->setCellValue(self::NUMBER_COLUMN.$headerRow, $labels['number']);
        $sheet->setCellValue(self::TASK_START_COLUMN.$headerRow, $labels['work_item']);
        $sheet->setCellValue(self::CONTENT_COLUMN.$headerRow, $labels['content_type']);
        $sheet->setCellValue(self::PIC_COLUMN.$headerRow, $labels['assignee']);
        $sheet->setCellValue(self::PLAN_REST_COLUMN.$headerRow, $labels['plan_rest']);
        $sheet->setCellValue(self::VARIANCE_COLUMN.$headerRow, $labels['variance']);
        $sheet->setCellValue(self::PLANNED_COLUMN.$headerRow, $labels['planned_hours']);
        $sheet->setCellValue(self::DIGESTION_COLUMN.$headerRow, $labels['digestion']);
        $sheet->setCellValue(self::ACTUAL_TOTAL_COLUMN.$headerRow, $labels['actual_hours']);
        $sheet->setCellValue(self::START_COLUMN.$headerRow, $labels['planned_start']);
        $sheet->setCellValue(self::START_COLUMN.$subHeaderRow, $labels['actual_start']);
        $sheet->setCellValue(self::END_COLUMN.$headerRow, $labels['planned_end']);
        $sheet->setCellValue(self::END_COLUMN.$subHeaderRow, $labels['actual_end']);
        $sheet->setCellValue(self::REMAINING_COLUMN.$headerRow, $labels['remaining']);
        $sheet->setCellValue(self::PROGRESS_COLUMN.$headerRow, $labels['progress']);
        $sheet->setCellValue(self::CATEGORY_COLUMN.$headerRow, $labels['category']);

        $sheet->setCellValue(self::PLAN_REST_COLUMN.$subHeaderRow, $labels['rest_h']);
        $sheet->setCellValue(self::VARIANCE_COLUMN.$subHeaderRow, $labels['hours_short']);
        $sheet->setCellValue(self::PLANNED_COLUMN.$subHeaderRow, $labels['hours_short']);
        $sheet->setCellValue(self::DIGESTION_COLUMN.$subHeaderRow, $labels['hours_short']);
        $sheet->setCellValue(self::ACTUAL_TOTAL_COLUMN.$subHeaderRow, $labels['hours_short']);

        foreach ($dates as $index => $date) {
            $column = Coordinate::stringFromColumnIndex(self::DAILY_START_COLUMN + $index);
            $dateString = $date->toDateString();
            $holidayType = $holidayMap[$dateString] ?? null;
            $fillColor = 'FFE8F4EC';
            $fontColor = 'FF1A3B2A';

            if ($this->isHolidayColumn($date, $holidayType)) {
                $fillColor = 'FFD9D9D9';
                $fontColor = 'FF6B7280';
            } elseif ($dateString === $currentDate) {
                $fillColor = 'FFBDE7C8';
                $fontColor = 'FF0F5132';
            }

            $sheet->setCellValue($column.$monthLabelRow, $this->monthLabelValue($date));
            $sheet->setCellValue($column.$headerRow, $date);
            $sheet->setCellValue($column.$subHeaderRow, $this->dayLabelValue($date));
            $sheet->getStyle($column.$headerRow)->getNumberFormat()->setFormatCode('mm-dd');
            $sheet->getStyle($column.$monthLabelRow.':'.$column.$subHeaderRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($fillColor);
            $sheet->getStyle($column.$monthLabelRow.':'.$column.$subHeaderRow)->getFont()->setBold(true)->getColor()->setARGB($fontColor);
        }

        $sheet->getStyle(self::PLATFORM_COLUMN.$headerRow.':'.Coordinate::stringFromColumnIndex(self::DAILY_START_COLUMN + max($dates->count() - 1, 0)).$subHeaderRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle(self::PLATFORM_COLUMN.$headerRow.':'.self::CATEGORY_COLUMN.$subHeaderRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDDF0E4');
        $sheet->getStyle(self::PLATFORM_COLUMN.$headerRow.':'.self::CATEGORY_COLUMN.$subHeaderRow)->getFont()->setBold(true)->getColor()->setARGB('FF183B2A');
        $sheet->getStyle(self::PLATFORM_COLUMN.$headerRow.':'.Coordinate::stringFromColumnIndex(self::DAILY_START_COLUMN + max($dates->count() - 1, 0)).$subHeaderRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
    }

    private function buildDetailRows(
        Worksheet $sheet,
        array $labels,
        Collection $detailRows,
        Collection $dates,
        array $holidayMap,
        int $dataStartRow,
        int $detailEndRow,
        string $currentDate,
        string $lastDateColumn,
        bool $includeCriticalPath,
        bool $useFormulas,
    ): void {
        foreach ($detailRows as $index => $row) {
            $plannedRow = $dataStartRow + ($index * 2);
            $actualRow = $plannedRow + 1;
            $assignment = $row['assignment'];
            $schedule = $row['schedule'];
            $wbsItem = $assignment->wbsItem;
            $plannedMap = collect($row['planned_map'] ?? []);
            $actualMap = collect($row['actual_map'] ?? []);
            $sheet->mergeCells(self::PLATFORM_COLUMN.$plannedRow.':'.self::PLATFORM_COLUMN.$actualRow);
            $sheet->mergeCells(self::NUMBER_COLUMN.$plannedRow.':'.self::NUMBER_COLUMN.$actualRow);
            $sheet->mergeCells(self::TASK_START_COLUMN.$plannedRow.':'.self::TASK_END_COLUMN.$actualRow);
            $sheet->mergeCells(self::CONTENT_COLUMN.$plannedRow.':'.self::CONTENT_COLUMN.$actualRow);

            $sheet->setCellValue(self::PLATFORM_COLUMN.$plannedRow, (string) ($wbsItem?->platform ?? 'web'));
            $sheet->setCellValue(self::NUMBER_COLUMN.$plannedRow, (string) ($wbsItem?->wbs_number ?? ''));
            $sheet->setCellValue(self::TASK_START_COLUMN.$plannedRow, (string) ($wbsItem?->item_name ?? $assignment->remark ?? 'Untitled'));
            $sheet->setCellValue(self::CONTENT_COLUMN.$plannedRow, (string) ($wbsItem?->content_item_type ?? $wbsItem?->item_type ?? 'task'));
            $sheet->setCellValue(self::PIC_COLUMN.$plannedRow, $assignment->pic?->name ?? $labels['unassigned']);
            $sheet->setCellValue(self::PIC_COLUMN.$actualRow, '=K'.$plannedRow);

            $sheet->setCellValue(self::PLAN_REST_COLUMN.$plannedRow, null);
            $sheet->setCellValue(self::PLAN_REST_COLUMN.$actualRow, '=L'.$plannedRow);
            $sheet->setCellValue(self::VARIANCE_COLUMN.$plannedRow, '=SUM(V'.$actualRow.':'.$lastDateColumn.$actualRow.')-L'.$plannedRow);
            $sheet->setCellValue(self::VARIANCE_COLUMN.$actualRow, '=M'.$plannedRow);
            $sheet->setCellValue(self::PLANNED_COLUMN.$plannedRow, '=SUM(V'.$plannedRow.':'.$lastDateColumn.$plannedRow.')');
            $sheet->setCellValue(self::PLANNED_COLUMN.$actualRow, '=N'.$plannedRow);
            $sheet->setCellValue(self::DIGESTION_COLUMN.$plannedRow, '=SUM(V'.$actualRow.':'.$lastDateColumn.$actualRow.')');
            $sheet->setCellValue(self::DIGESTION_COLUMN.$actualRow, '=O'.$plannedRow);
            $sheet->setCellValue(self::ACTUAL_TOTAL_COLUMN.$plannedRow, '=O'.$plannedRow);
            $sheet->setCellValue(self::ACTUAL_TOTAL_COLUMN.$actualRow, '=P'.$plannedRow);
            $sheet->setCellValue(self::START_COLUMN.$plannedRow, $this->excelDateValue($schedule?->planned_start_date));
            $sheet->setCellValue(self::END_COLUMN.$plannedRow, $this->excelDateValue($schedule?->planned_end_date));
            $sheet->setCellValue(
                self::START_COLUMN.$actualRow,
                $useFormulas
                    ? '=IF(COUNTIF(V'.$actualRow.':'.$lastDateColumn.$actualRow.',">0")=0,"",INT(INDEX(V$14:'.$lastDateColumn.'$14,MATCH(TRUE,INDEX(V'.$actualRow.':'.$lastDateColumn.$actualRow.'>0,0),0))))'
                    : $this->excelDateValue($schedule?->actual_start_date)
            );
            $sheet->setCellValue(
                self::END_COLUMN.$actualRow,
                $useFormulas
                    ? '=IF(COUNTIF(V'.$actualRow.':'.$lastDateColumn.$actualRow.',">0")=0,"",INT(LOOKUP(2,1/(V'.$actualRow.':'.$lastDateColumn.$actualRow.'>0),V$14:'.$lastDateColumn.'$14)))'
                    : $this->excelDateValue($schedule?->actual_end_date)
            );
            $sheet->setCellValue(self::REMAINING_COLUMN.$plannedRow, '=IF(N'.$plannedRow.'=0,"",N'.$plannedRow.'-O'.$plannedRow.')');
            $sheet->setCellValue(self::REMAINING_COLUMN.$actualRow, '=S'.$plannedRow);
            $sheet->setCellValue(self::PROGRESS_COLUMN.$plannedRow, '=IF(S'.$plannedRow.'="","",IF(S'.$plannedRow.'=0,1,IF(O'.$plannedRow.'=0,"",O'.$plannedRow.'/(O'.$plannedRow.'+S'.$plannedRow.'))))');
            $sheet->setCellValue(self::PROGRESS_COLUMN.$actualRow, '=T'.$plannedRow);
            $sheet->setCellValue(self::CATEGORY_COLUMN.$plannedRow, $labels['planned']);
            $sheet->setCellValue(self::CATEGORY_COLUMN.$actualRow, $labels['actual']);

            $fixedPlannedFill = 'FFF7FAF8';
            $fixedActualFill = 'FFFFFFFF';

            for ($columnIndex = Coordinate::columnIndexFromString(self::PLATFORM_COLUMN); $columnIndex <= Coordinate::columnIndexFromString(self::CATEGORY_COLUMN); $columnIndex++) {
                $column = Coordinate::stringFromColumnIndex($columnIndex);
                $sheet->getStyle($column.$plannedRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($fixedPlannedFill);
                $sheet->getStyle($column.$actualRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($fixedActualFill);
            }

            foreach ($dates as $dateIndex => $date) {
                $column = Coordinate::stringFromColumnIndex(self::DAILY_START_COLUMN + $dateIndex);
                $dateKey = $date->toDateString();
                $plannedHoursForDay = (float) ($plannedMap[$dateKey] ?? 0);
                $actualHoursForDay = (float) ($actualMap[$dateKey] ?? 0);
                $holidayType = $holidayMap[$dateKey] ?? null;
                $plannedFill = 'FFFFFFFF';
                $actualFill = 'FFFFFFFF';

                if ($this->isHolidayColumn($date, $holidayType)) {
                    $plannedFill = 'FFD9D9D9';
                    $actualFill = 'FFD9D9D9';
                } elseif ($dateKey === $currentDate) {
                    $plannedFill = 'FFCCF0D8';
                    $actualFill = 'FFE6F8EB';
                }

                if (! $this->isHolidayColumn($date, $holidayType) && $plannedHoursForDay > 0) {
                    $plannedFill = 'FFFFF2A8';
                }

                if (! $this->isHolidayColumn($date, $holidayType) && $actualHoursForDay > 0) {
                    $actualFill = 'FFC9F0C9';
                }

                $sheet->setCellValue($column.$plannedRow, $plannedHoursForDay > 0 ? $plannedHoursForDay : null);
                $sheet->setCellValue($column.$actualRow, $actualHoursForDay > 0 ? $actualHoursForDay : null);
                $sheet->getStyle($column.$plannedRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($plannedFill);
                $sheet->getStyle($column.$actualRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($actualFill);
            }

            $this->applyReactiveDateValueFormatting($sheet, $plannedRow, $actualRow, $dates, $holidayMap);
            $this->applyRowConditionalFormatting($sheet, $plannedRow, $actualRow, $lastDateColumn);

            if ($includeCriticalPath && $assignment->is_critical) {
                $sheet->getStyle(self::PLATFORM_COLUMN.$plannedRow.':'.self::CATEGORY_COLUMN.$actualRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFF9F1C7');
            }

            $sheet->getStyle(self::PLATFORM_COLUMN.$plannedRow.':'.$lastDateColumn.$actualRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle(self::TASK_START_COLUMN.$plannedRow.':'.self::TASK_END_COLUMN.$actualRow)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle(self::PLATFORM_COLUMN.$plannedRow.':'.self::CATEGORY_COLUMN.$actualRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle(self::TASK_START_COLUMN.$plannedRow.':'.self::TASK_END_COLUMN.$actualRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle(self::PLAN_REST_COLUMN.$plannedRow.':'.$lastDateColumn.$actualRow)->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle(self::START_COLUMN.$plannedRow.':'.self::END_COLUMN.$actualRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD2);
            $sheet->getStyle(self::PROGRESS_COLUMN.$plannedRow.':'.self::PROGRESS_COLUMN.$actualRow)->getNumberFormat()->setFormatCode('0%');
        }

        if ($detailEndRow >= $dataStartRow) {
            $sheet->getStyle(self::PLATFORM_COLUMN.$dataStartRow.':'.$lastDateColumn.$detailEndRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
    }

    private function applyRowConditionalFormatting(Worksheet $sheet, int $plannedRow, int $actualRow, string $lastDateColumn): void
    {
        $redFont = $this->redFontConditional('OR(COUNT($V'.$actualRow.':$'.$lastDateColumn.'$'.$actualRow.')=0,SUM($V'.$actualRow.':$'.$lastDateColumn.'$'.$actualRow.')<$N'.$plannedRow.')');
        $redProgress = $this->redFontConditional('$T'.$plannedRow.'>1');
        $greyRow = $this->greyRowConditional('$T'.$plannedRow.'=1');

        foreach (['N', 'Q', 'R'] as $column) {
            $sheet->getStyle($column.$plannedRow)->setConditionalStyles([$redFont]);
            $sheet->getStyle($column.$actualRow)->setConditionalStyles([$redFont]);
        }

        $sheet->getStyle('T'.$plannedRow)->setConditionalStyles([$redProgress]);
        $sheet->getStyle('T'.$actualRow)->setConditionalStyles([$redProgress]);

        $existingPlanned = $sheet->getStyle(self::PLATFORM_COLUMN.$plannedRow.':'.$lastDateColumn.$plannedRow)->getConditionalStyles();
        $existingActual = $sheet->getStyle(self::PLATFORM_COLUMN.$actualRow.':'.$lastDateColumn.$actualRow)->getConditionalStyles();
        $existingPlanned[] = $greyRow;
        $existingActual[] = $greyRow;
        $sheet->getStyle(self::PLATFORM_COLUMN.$plannedRow.':'.$lastDateColumn.$plannedRow)->setConditionalStyles($existingPlanned);
        $sheet->getStyle(self::PLATFORM_COLUMN.$actualRow.':'.$lastDateColumn.$actualRow)->setConditionalStyles($existingActual);
    }

    private function applyReactiveDateValueFormatting(Worksheet $sheet, int $plannedRow, int $actualRow, Collection $dates, array $holidayMap): void
    {
        foreach ($dates as $index => $date) {
            $holidayType = $holidayMap[$date->toDateString()] ?? null;

            if ($this->isHolidayColumn($date, $holidayType)) {
                continue;
            }

            $column = Coordinate::stringFromColumnIndex(self::DAILY_START_COLUMN + $index);

            $plannedConditionals = $sheet->getStyle($column.$plannedRow)->getConditionalStyles();
            $plannedConditionals[] = $this->valueFillConditional('LEN(TRIM('.$column.$plannedRow.'&""))>0', 'FFFFF2A8');
            $sheet->getStyle($column.$plannedRow)->setConditionalStyles($plannedConditionals);

            $actualConditionals = $sheet->getStyle($column.$actualRow)->getConditionalStyles();
            $actualConditionals[] = $this->valueFillConditional('LEN(TRIM('.$column.$actualRow.'&""))>0', 'FFC9F0C9');
            $sheet->getStyle($column.$actualRow)->setConditionalStyles($actualConditionals);
        }
    }

    private function redFontConditional(string $formula): Conditional
    {
        $conditional = new Conditional();
        $conditional->setConditionType(Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition($formula);
        $conditional->getStyle()->getFont()->setBold(true)->getColor()->setARGB('FFB91C1C');

        return $conditional;
    }

    private function valueFillConditional(string $formula, string $argb): Conditional
    {
        $conditional = new Conditional();
        $conditional->setConditionType(Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition($formula);
        $conditional->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($argb);

        return $conditional;
    }

    private function greyRowConditional(string $formula): Conditional
    {
        $conditional = new Conditional();
        $conditional->setConditionType(Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition($formula);
        $conditional->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
        $conditional->getStyle()->getFont()->getColor()->setARGB('FF4B5563');

        return $conditional;
    }

    private function gazettedHolidayConditional(string $formula): Conditional
    {
        $conditional = new Conditional();
        $conditional->setConditionType(Conditional::CONDITION_EXPRESSION);
        $conditional->addCondition($formula);
        $conditional->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
        $conditional->getStyle()->getFont()->getColor()->setARGB('FF6B7280');

        return $conditional;
    }

    private function applyHolidaySummaryColumnFills(
        Worksheet $sheet,
        Collection $dates,
        array $holidayMap,
        int $startRow,
        int $endRow,
    ): void {
        foreach ($dates as $index => $date) {
            $holidayType = $holidayMap[$date->toDateString()] ?? null;

            if (! $this->isHolidayColumn($date, $holidayType)) {
                continue;
            }

            $column = Coordinate::stringFromColumnIndex(self::DAILY_START_COLUMN + $index);
            $sheet->getStyle($column.$startRow.':'.$column.$endRow)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FFD9D9D9');
        }
    }

    private function applyGazettedHolidayConditionalFormatting(
        Worksheet $sheet,
        Collection $dates,
        int $startRow,
        int $endRow,
    ): void {
        foreach ($dates as $index => $date) {
            $column = Coordinate::stringFromColumnIndex(self::DAILY_START_COLUMN + $index);
            $range = $column.$startRow.':'.$column.$endRow;
            $conditionals = $sheet->getStyle($range)->getConditionalStyles();
            $conditionals[] = $this->gazettedHolidayConditional('COUNTIF(Holidays!$A:$A,'.$column.'$14)>0');
            $sheet->getStyle($range)->setConditionalStyles($conditionals);
        }
    }

    private function isHolidayColumn(Carbon $date, mixed $holidayType): bool
    {
        return $holidayType !== null || $date->isWeekend();
    }

    private function excelDateValue(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return ExcelDate::PHPToExcel(Carbon::parse($value, $this->project->timezone)->startOfDay());
    }

    private function applyColumnWidths(Worksheet $sheet, int $lastDateColumnIndex): void
    {
        $widths = [
            'A' => 2.43,
            'B' => 9,
            'C' => 6,
            'D' => 8,
            'E' => 8,
            'F' => 12,
            'G' => 12,
            'H' => 12,
            'I' => 12,
            'J' => 14,
            'K' => 14,
            'L' => 10,
            'M' => 10,
            'N' => 10,
            'O' => 10,
            'P' => 10,
            'Q' => 12,
            'R' => 12,
            'S' => 10,
            'T' => 10,
            'U' => 11,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        for ($columnIndex = self::DAILY_START_COLUMN; $columnIndex <= $lastDateColumnIndex; $columnIndex++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnIndex))->setWidth(7.25);
        }
    }

    private function applyRowHeights(Worksheet $sheet, int $headerRow, int $subHeaderRow, int $dataStartRow, int $detailEndRow): void
    {
        $sheet->getRowDimension($headerRow)->setRowHeight(20);
        $sheet->getRowDimension($subHeaderRow)->setRowHeight(20);

        for ($row = $dataStartRow; $row <= $detailEndRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight($row % 2 === 0 ? 20 : 24);
        }
    }

    private function styleSummarySection(Worksheet $sheet, int $picStartRow, int $totalActualRow, int $todayRow, int $monthLabelRow): void
    {
        $sheet->getStyle('Q1:U1')->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FF16492D');
        $sheet->getStyle('Q2:S3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('Q2:S2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDDF0E4');
        $sheet->getStyle('Q2:S3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('Q'.$picStartRow.':U'.$totalActualRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FCF9');
        $sheet->getStyle('D'.$todayRow.':E'.$todayRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF3D6');
        $sheet->getStyle('D'.$todayRow.':E'.$todayRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('D'.$todayRow.':E'.$todayRow)->getFont()->setBold(true)->getColor()->setARGB('FF6E4F0B');
        $sheet->getStyle(self::PLATFORM_COLUMN.$monthLabelRow.':'.self::PIC_COLUMN.$monthLabelRow)->getFont()->setBold(true)->getColor()->setARGB('FF1A3B2A');
    }

    private function sumMonthValues(Collection $values, Collection $dates, int $selectedMonth): float
    {
        return round(
            $dates->sum(function ($date) use ($values, $selectedMonth) {
                if (! $date instanceof Carbon || $date->month !== $selectedMonth) {
                    return 0;
                }

                return (float) ($values[$date->toDateString()] ?? 0);
            }),
            2,
        );
    }

    private function labels(): array
    {
        return [
            'monthly_hours' => '月別工数',
            'daily_hours' => '日別工数',
            'pic' => 'PIC',
            'month' => '月',
            'today' => '本日',
            'detail' => '明細',
            'platform' => 'Platform',
            'number' => 'No.',
            'work_item' => '作業項目',
            'content_type' => '中身項目種類',
            'assignee' => '担当者',
            'plan_rest' => 'Plan Rest Hours',
            'variance' => '±工数',
            'planned_hours' => '予定工数',
            'digestion' => '消化工数',
            'actual_hours' => '実績工数',
            'planned_start' => '開始日(予)',
            'actual_start' => '開始日(実)',
            'planned_end' => '終了日(予)',
            'actual_end' => '終了日(実)',
            'remaining' => '残工数',
            'progress' => '進捗％',
            'category' => '区分',
            'rest_h' => 'Rest H',
            'hours_short' => '工数',
            'planned' => '予定',
            'actual' => '実績',
            'total' => '合計',
            'unassigned' => '未割当',
        ];
    }

    private function monthLabelValue(Carbon $date): string
    {
        return $date->locale('en')->translatedFormat('F');
    }

    private function dayLabelValue(Carbon $date): string
    {
        return strtolower($date->locale('en')->translatedFormat('D'));
    }
}
