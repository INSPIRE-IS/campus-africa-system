<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentCompatibilityScore;
use App\Models\StudentsInfo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use SimpleXLSXGen;

class ExportCompatibilitySimpleXlsx extends Command
{
    protected $signature = 'download:compatibility_docs {year} {email} {--building=} {--top=} ';
    // php artisan download:compatibility_docs 2026 jason@inspireonline.co.za
    // php artisan download:compatibility_docs 2026 jason@inspireonline.co.za --building=8
    // php artisan download:compatibility_docs 2026 jason@inspireonline.co.za --top=3
    protected $description = 'Export compatibility report per building (multi-sheet XLSX) using SimpleXLSXGen';

    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $year = $this->argument('year') ?? date('Y');
        $email = $this->argument('email') ?? date('Y');
        $buildingFilter = $this->option('building') ?? null; // optional building filter
        $topN = $this->option('top') ?? 5; // number of matches per student

        $this->info("🔄 Generating compatibility report for academic year {$year}...");
        Log::info("Downloading compatibility docs for {$year} and {$buildingFilter}");

        $xlsx = new \Shuchkin\SimpleXLSXGen();
        $sheetCount = 0;

        // Load all matches for the year
        $matches = StudentCompatibilityScore::where('academic_year', $year)
            ->orderByDesc('compatibility_score')
            ->get();

        // Load all eligible students with booking info
        $students = StudentsInfo::select('id', 'userUid', 'bookingId', 'userFirstName', 'userLastname', 'year', 'gender')
            ->with([
                'booking' => function ($q) use ($buildingFilter) {
                    $q->select('id', 'property', 'roomType', 'year', 'userId');
                    if ($buildingFilter) {
                        $q->where('property', $buildingFilter);
                    }
                },
                'booking.residence' => function ($q) {
                    $q->select('id', 'residence');
                },
                'booking.roomType' => function ($q) {
                    $q->select('id', 'property_id', 'name');
                },
            ])
            ->where('year', $year)
            ->get()
            ->keyBy('id');

        if ($students->isEmpty()) {
            $this->warn("⚠️ No students found for {$year}.");
            return;
        }

        // Group matches by Student A
        $matchesByStudentA = [];
        foreach ($matches as $m) {
            $a = $students->get($m->student_a_id);
            $b = $students->get($m->student_b_id);
            if (!$a || !$b || !$a->booking) continue;

            // Optional building filter (already filtered via eager load)
            if ($buildingFilter && $a->booking->property != $buildingFilter) continue;
            Log::info("Checking {$a->booking->property}");
            if (in_array($a->booking->property, [3, 4, 7])) continue;

            $matchesByStudentA[$a->id][] = [
                'b' => $b,
                'score' => $m->compatibility_score,
            ];
        }

        // Group by building for sheets
        $groupedByBuilding = [];

        foreach ($students as $a) {
            if (!$a->booking) continue;

            $propertyId = $a->booking->property;
            $buildingName = $a->booking->property->residence ?? "Building {$propertyId}";

            // Initialize group
            if (!isset($groupedByBuilding[$propertyId])) {
                $groupedByBuilding[$propertyId] = [];
            }

            $studentMatches = $matchesByStudentA[$a->id] ?? [];

            // Sort matches descending by score
            usort($studentMatches, fn($x, $y) => $y['score'] <=> $x['score']);

            // Take top N matches
            $studentMatches = array_slice($studentMatches, 0, $topN);

            if (empty($studentMatches)) {
                // No matches — add placeholder row
                $groupedByBuilding[$propertyId][] = [
                    'a' => $a,
                    'b' => null,
                    'score' => null,
                ];
            } else {
                foreach ($studentMatches as $match) {
                    $groupedByBuilding[$propertyId][] = [
                        'a' => $a,
                        'b' => $match['b'],
                        'score' => $match['score'],
                    ];
                }
            }
        }

        // Build sheets
        foreach ($groupedByBuilding as $propertyId => $rows) {
            $buildingName = $rows[0]['a']->booking->residence->residence ?? "Building {$propertyId}";
            $sheetData = [["Student A", "Student B", "Score (%)", "Tier"]];

            // Add rows
            foreach ($rows as $row) {
                $score = $row['score'] !== null ? round($row['score'], 1) : 'N/A';
                $tier = $row['score'] !== null ? match (true) {
                    $score >= 90 => "100",
                    $score >= 70 => "75",
                    $score >= 50 => "50",
                    $score >= 25 => "25",
                    default => "Low",
                } : 'None';

                $sheetData[] = [
                    "{$row['a']->userFirstName} {$row['a']->userLastname}",
                    $row['b'] ? "{$row['b']->userFirstName} {$row['b']->userLastname}" : '',
                    $score,
                    $tier,
                ];
            }

            // Sort alphabetically by Student A
            $header = array_shift($sheetData);
            usort($sheetData, fn($a, $b) => strcmp($a[0], $b[0]));
            array_unshift($sheetData, $header);

            $xlsx->addSheet($sheetData, $buildingName);
            $sheetCount++;
            $this->info("✅ Added sheet for {$buildingName} (" . count($rows) . " rows)");
        }

        if ($sheetCount === 0) {
            $this->warn('⚠️ No sheets generated — check if bookings have properties linked.');
            return;
        }

        $filename = "compatibility_report_{$year}_" . now()->format('Ymd_His') . ".xlsx";
        $savePath = storage_path("app/{$filename}");
        $xlsx->saveAs($savePath);

        $this->info("🎉 Report generated successfully: {$savePath}");

        // Public URL (if your 'storage/app' is NOT public, move file to 'storage/app/public')
        $publicUrl = "https://apply.campusafrica.co.za/{$filename}";

        $this->info("🎉 Report generated successfully: {$savePath}");

        // Mail::raw("Your XLSX report is ready for download: {$publicUrl}", function ($message) use ($email) {
        //     $message->to($email)->subject('Your XLSX Report');
        // });
        Mail::raw("Your XLSX report is attached.", function ($message) use ($email, $savePath, $filename) {
            $message->to($email)
                ->subject('Your XLSX Report')
                ->attach($savePath, [
                    'as' => $filename,
                    'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]);
        });
        $this->info("Download link sent to: {$email}");
    }


    public function handle1()
    {
        ini_set('memory_limit', '1024M');
        $this->info(ini_get('memory_limit'));
        $year = $this->argument('year');
        $testing = 0;
        $this->info("🔄 Generating compatibility report for academic year {$year}...");
        Log::info("Generating compatibility report for academic year {$year}...");

        $xlsx = new \Shuchkin\SimpleXLSXGen();
        $sheetCount = 0;

        // Load all matches for the year
        $matches = StudentCompatibilityScore::where('academic_year', $year)
            ->orderByDesc('compatibility_score')
            ->get();

        if ($matches->isEmpty()) {
            $this->warn("⚠️ No compatibility scores found for {$year}.");
            return;
        }

        // Preload student + booking data
        $students = StudentsInfo::select(
            'id',
            'userUid',
            'bookingId',
            'userFirstName',
            'userLastname',
            'year',
            'gender'
        )
            ->with([
                'booking' => function ($q) {
                    $q->select('id', 'property', 'roomType', 'year', 'userId');
                },
                'booking.residence' => function ($q) {
                    $q->select('id', 'residence');
                },
                'booking.roomType' => function ($q) {
                    $q->select('id', 'property_id', 'name');
                },
            ])
            ->where('year', $year)
            ->get()
            ->keyBy('id');

        // Group matches by property ID
        $grouped = [];

        foreach ($matches as $m) {
            $a = $students->get($m->student_a_id);
            $b = $students->get($m->student_b_id);

            if (!$a || !$b) continue;

            $propertyId = $a->booking->property ?? null;
            if (!$propertyId) continue;

            $grouped[$propertyId][] = [
                'a' => $a,
                'b' => $b,
                'score' => $m->compatibility_score,
            ];
        }

        // Build one sheet per property
        foreach ($grouped as $propertyId => $rows) {

            $buildingName = optional($rows[0]['a']->booking->residence)->residence ?? "Building {$propertyId}";
            $sheetData = [
                ["Student A", "Student B", "Score (%)", "Tier"]
            ];

            foreach ($rows as $row) {
                if ($testing == 1 && isset($row)) {
                    Log::info($row['a']->booking->residence->residence); // 
                    $testing = 0;
                }


                $score = round($row['score'], 1);
                $tier = match (true) {
                    $score >= 90 => "100",
                    $score >= 70 => "75",
                    $score >= 50 => "50",
                    // $score >= 25 => "25",
                    default => "Low",
                };

                $sheetData[] = [
                    "{$row['a']->userFirstName} {$row['a']->userLastname}",
                    "{$row['b']->userFirstName} {$row['b']->userLastname}",
                    $score,
                    $tier,
                ];
            }
            usort($sheetData, function ($a, $b) {
                return strcmp($a[0], $b[0]); // sort alphabetically by Student A name
            });
            $xlsx->addSheet($sheetData, $buildingName);
            $sheetCount++;
            $this->info("✅ Added sheet for {$buildingName} (" . count($rows) . " matches)");
        }

        if ($sheetCount === 0) {
            $this->warn('⚠️ No sheets generated — check if bookings have properties linked.');
            return;
        }

        $filename = "compatibility_report_{$year}_" . now()->format('Ymd_His') . ".xlsx";
        $savePath = storage_path("app/{$filename}");
        $xlsx->saveAs($savePath);

        $this->info("🎉 Report generated successfully: {$savePath}");
    }
}
