<?php

namespace App\Support;

use Carbon\Carbon;

class MonthlyPopDocument
{
    public const LEGACY_FILE_ID = 'other_monthly_pop';
    public const PREFIX = 'monthly_pop_';

    public static function fileIdForDueDate($dueDate): string
    {
        return self::PREFIX . Carbon::parse($dueDate)->format('Y_m');
    }

    public static function dueDateForReminderDate($reminderDate): Carbon
    {
        return Carbon::parse($reminderDate)->addMonthNoOverflow()->startOfMonth();
    }

    public static function isMonthlyPop(?string $fileId): bool
    {
        return $fileId === self::LEGACY_FILE_ID
            || self::period($fileId) !== null;
    }

    public static function period(?string $fileId): ?string
    {
        if (!preg_match('/^monthly_pop_(\d{4})_(0[1-9]|1[0-2])$/', (string) $fileId, $matches)) {
            return null;
        }

        return $matches[1] . '-' . $matches[2];
    }

    public static function label(?string $fileId): string
    {
        $period = self::period($fileId);

        if (!$period) {
            return 'Monthly Proof of Payment';
        }

        return 'Monthly Proof of Payment - ' . Carbon::createFromFormat('!Y-m', $period)->format('F Y');
    }
}
