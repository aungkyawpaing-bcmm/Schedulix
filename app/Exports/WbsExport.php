<?php

namespace App\Exports;

use App\Exports\Sheets\HolidaysSheet;
use App\Exports\Sheets\WbsWorkbookSheet;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class WbsExport implements WithMultipleSheets
{
    public function __construct(
        private readonly Project $project,
        private readonly array $grid,
        private readonly array $options,
    ) {
    }

    public function sheets(): array
    {
        return [
            new WbsWorkbookSheet($this->project, $this->grid, $this->options),
            new HolidaysSheet($this->project),
        ];
    }

    public function toSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheets = $this->sheets();

        foreach ($sheets as $index => $sheetBuilder) {
            $worksheet = $index === 0
                ? $spreadsheet->getActiveSheet()
                : $spreadsheet->createSheet($index);

            $worksheet->setTitle($sheetBuilder->title());
            $sheetBuilder->populate($worksheet);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }
}
