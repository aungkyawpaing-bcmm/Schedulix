<?php

namespace App\Exports\Sheets;

use App\Models\Holiday;
use App\Models\Project;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HolidaysSheet implements FromArray, WithEvents, WithTitle
{
    public function __construct(private readonly Project $project)
    {
    }

    public function array(): array
    {
        return [[]];
    }

    public function title(): string
    {
        return 'Holidays';
    }

    public function populate(Worksheet $sheet): void
    {
        $sheet->setTitle('Holidays');

        $holidays = Holiday::query()
            ->where('is_active', true)
            ->whereBetween('holiday_date', [
                $this->project->expected_start_date,
                $this->project->expected_end_date,
            ])
            ->orderBy('holiday_date')
            ->get();

        $sheet->getColumnDimension('A')->setWidth(16);

        if ($holidays->isEmpty()) {
            $sheet->setCellValue('A1', __('No active holidays'));
            return;
        }

        foreach ($holidays as $index => $holiday) {
            $row = $index + 1;
            $sheet->setCellValue(
                "A{$row}",
                ExcelDate::PHPToExcel(Carbon::parse($holiday->holiday_date, $this->project->timezone)->startOfDay())
            );
        }

        $lastRow = $holidays->count();

        $sheet->getStyle("A1:A{$lastRow}")->getNumberFormat()->setFormatCode('yyyy-mm-dd');
        $sheet->getStyle("A1:A{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A1:A{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $this->populate($event->sheet->getDelegate());
            },
        ];
    }
}
