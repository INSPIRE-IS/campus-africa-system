<?php

namespace Tests\Unit;

use App\Support\MonthlyPopDocument;
use Tests\TestCase;

class MonthlyPopDocumentTest extends TestCase
{
    public function test_it_creates_a_file_id_for_the_due_month(): void
    {
        $this->assertSame('2026-11-01', MonthlyPopDocument::dueDateForReminderDate('2026-10-25')->toDateString());
        $this->assertSame('2027-01-01', MonthlyPopDocument::dueDateForReminderDate('2026-12-25')->toDateString());
        $this->assertSame('monthly_pop_2026_11', MonthlyPopDocument::fileIdForDueDate('2026-11-01'));
    }

    public function test_it_recognises_period_and_legacy_monthly_pop_documents(): void
    {
        $this->assertTrue(MonthlyPopDocument::isMonthlyPop('monthly_pop_2026_11'));
        $this->assertTrue(MonthlyPopDocument::isMonthlyPop('other_monthly_pop'));
        $this->assertFalse(MonthlyPopDocument::isMonthlyPop('other_sponsor_id'));
        $this->assertFalse(MonthlyPopDocument::isMonthlyPop('monthly_pop_invalid'));
    }

    public function test_it_formats_period_and_label(): void
    {
        $this->assertSame('2026-11', MonthlyPopDocument::period('monthly_pop_2026_11'));
        $this->assertSame('Monthly Proof of Payment - November 2026', MonthlyPopDocument::label('monthly_pop_2026_11'));
        $this->assertSame('Monthly Proof of Payment', MonthlyPopDocument::label('other_monthly_pop'));
    }
}
