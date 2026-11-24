<?php

namespace Tests\Unit;

use App\Support\BedsReportFilename;
use PHPUnit\Framework\TestCase;

class BedsReportFilenameTest extends TestCase
{
    public function test_it_replaces_all_whitespace_and_unsafe_characters(): void
    {
        $filename = BedsReportFilename::make(
            '2027',
            "Amani 49\u{00A0}Jorissen/Old Building",
            '20261124_184644'
        );

        $this->assertSame(
            'Bed_Allocations_Export_2027_Amani_49_Jorissen_Old_Building_20261124_184644.xlsx',
            $filename
        );
        $this->assertTrue(BedsReportFilename::isValid($filename));
    }

    public function test_it_uses_a_safe_fallback_for_an_empty_property_name(): void
    {
        $filename = BedsReportFilename::make('2027', null, '20261124_184644');

        $this->assertSame('Bed_Allocations_Export_2027_Property_20261124_184644.xlsx', $filename);
    }

    public function test_it_rejects_names_that_cannot_be_a_generated_report_filename(): void
    {
        $this->assertFalse(BedsReportFilename::isValid('../report.xlsx'));
        $this->assertFalse(BedsReportFilename::isValid('Bed_Allocations_Export_2027_All.xlsx'));
    }
}
