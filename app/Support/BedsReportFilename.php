<?php

namespace App\Support;

use Illuminate\Support\Str;

class BedsReportFilename
{
    public static function make(string $year, ?string $propertyName, string $timestamp): string
    {
        $propertyPart = preg_replace('/[\s\p{Z}]+/u', '_', $propertyName ?: 'Property');
        $propertyPart = Str::ascii($propertyPart === null ? 'Property' : $propertyPart);
        $propertyPart = preg_replace('/[^A-Za-z0-9_-]+/', '_', $propertyPart);
        $propertyPart = preg_replace('/_+/', '_', $propertyPart);
        $propertyPart = trim($propertyPart, '_-');

        if ($propertyPart === '') {
            $propertyPart = 'Property';
        }

        return "Bed_Allocations_Export_{$year}_{$propertyPart}_{$timestamp}.xlsx";
    }

    public static function isValid(string $filename): bool
    {
        return preg_match('/\ABed_Allocations_Export_\d{4}_[A-Za-z0-9_-]+_\d{8}_\d{6}\.xlsx\z/', $filename) === 1;
    }
}
