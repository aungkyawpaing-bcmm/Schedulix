<?php

namespace Tests\Feature;

use App\Models\Export;
use App\Models\Holiday;
use App\Models\Project;
use App\Models\User;
use App\Services\ExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class WbsExportFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_wbs_export_uses_sample_like_workbook_structure(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@wbs-generator.test')->firstOrFail();
        $project = Project::query()->where('code', 'WBS-DEMO')->firstOrFail();

        Holiday::query()->updateOrCreate(
            ['holiday_date' => '2026-04-30'],
            [
                'name' => 'Template Gazette Holiday',
                'holiday_type' => 'gazetted',
                'timezone' => 'Asia/Yangon',
                'is_active' => true,
            ]
        );

        $this->actingAs($owner);

        $response = app(ExportService::class)->export($project, [
            'export_type' => 'xlsx',
            'include_formula' => true,
            'include_critical_path' => true,
            'export_locale' => 'ja',
            'file_name' => 'sample-like-export',
        ]);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);

        $export = Export::query()->where('file_name', 'sample-like-export.xlsx')->latest('id')->firstOrFail();
        $workbook = IOFactory::load(storage_path('app/private/'.$export->file_path));

        $this->assertSame(['WBS', 'Holidays'], $workbook->getSheetNames());

        $wbsSheet = $workbook->getSheetByName('WBS');
        $this->assertNotNull($wbsSheet);
        $this->assertSame('月別工数', $wbsSheet->getCell('Q1')->getValue());
        $this->assertSame('日別工数', $wbsSheet->getCell('U1')->getValue());
        $this->assertSame('V16', $wbsSheet->getFreezePane());
        $this->assertSame('Platform', $wbsSheet->getCell('B14')->getValue());
        $this->assertSame('作業項目', $wbsSheet->getCell('D14')->getValue());
        $this->assertSame('区分', $wbsSheet->getCell('U14')->getValue());
        $this->assertSame('April', $wbsSheet->getCell('V13')->getValue());
        $this->assertSame('wed', $wbsSheet->getCell('V15')->getValue());
        $this->assertSame('web', $wbsSheet->getCell('B16')->getValue());
        $this->assertNull($wbsSheet->getCell('L16')->getValue());
        $this->assertSame('Requirements Validation', $wbsSheet->getCell('D16')->getValue());
        $this->assertSame('予定', $wbsSheet->getCell('U16')->getValue());
        $this->assertSame('実績', $wbsSheet->getCell('U17')->getValue());

        $this->assertStringStartsWith('=SUMPRODUCT(', (string) $wbsSheet->getCell('V4')->getValue());
        $this->assertStringStartsWith('=SUM(V16:', (string) $wbsSheet->getCell('N16')->getValue());
        $this->assertStringEndsWith('16)', (string) $wbsSheet->getCell('N16')->getValue());
        $this->assertSame('=N16', $wbsSheet->getCell('N17')->getValue());
        $this->assertSame('yyyy-mm-dd', $wbsSheet->getStyle('Q16')->getNumberFormat()->getFormatCode());
        $this->assertSame('yyyy-mm-dd', $wbsSheet->getStyle('R16')->getNumberFormat()->getFormatCode());
        $this->assertSame('yyyy-mm-dd', $wbsSheet->getStyle('Q17')->getNumberFormat()->getFormatCode());
        $this->assertSame('yyyy-mm-dd', $wbsSheet->getStyle('R17')->getNumberFormat()->getFormatCode());
        $this->assertNotEmpty($wbsSheet->getStyle('V16')->getConditionalStyles());
        $this->assertNotEmpty($wbsSheet->getStyle('V17')->getConditionalStyles());

        $holidaySheet = $workbook->getSheetByName('Holidays');
        $this->assertNotNull($holidaySheet);
        $this->assertNotNull($holidaySheet->getCell('A1')->getValue());
        $this->assertIsNumeric($holidaySheet->getCell('A1')->getValue());
        $this->assertSame('yyyy-mm-dd', $holidaySheet->getStyle('A1')->getNumberFormat()->getFormatCode());
        $this->assertSame('FFD9D9D9', $holidaySheet->getStyle('A1')->getFill()->getStartColor()->getARGB());
        $this->assertSame('FFD9D9D9', $wbsSheet->getStyle('BB4')->getFill()->getStartColor()->getARGB());
        $this->assertSame('FFD9D9D9', $wbsSheet->getStyle('BB13')->getFill()->getStartColor()->getARGB());
        $this->assertSame('FFD9D9D9', $wbsSheet->getStyle('BB16')->getFill()->getStartColor()->getARGB());
        $this->assertSame('FFD9D9D9', $wbsSheet->getStyle('AY4')->getFill()->getStartColor()->getARGB());
        $this->assertSame('FFD9D9D9', $wbsSheet->getStyle('AY13')->getFill()->getStartColor()->getARGB());
        $this->assertSame('FFD9D9D9', $wbsSheet->getStyle('AY16')->getFill()->getStartColor()->getARGB());
        $this->assertNotEmpty($wbsSheet->getStyle('AY4')->getConditionalStyles());
    }
}
