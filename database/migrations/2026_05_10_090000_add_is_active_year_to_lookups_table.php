<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddIsActiveYearToLookupsTable extends Migration
{
    public function up()
    {
        Schema::table('lookups', function (Blueprint $table) {
            // Only meaningful for group_id=4 (Year). Default off so existing installs keep behavior until we set it.
            $table->boolean('is_active_year')->default(false)->after('lookup_name');
            $table->index(['group_id', 'is_active_year'], 'lookups_group_active_year_idx');
        });

        // Bootstrap a sane default for *this* rollout (2026). If this migration is ever run in a
        // different year, forcing a guessed active year is riskier than stopping and asking ops
        // to explicitly pick one.
        $currentYear = (int) date('Y');
        if ($currentYear !== 2026) {
            throw new RuntimeException(
                "AddIsActiveYearToLookupsTable: Refusing to auto-select active year for {$currentYear}. " .
                "After running this migration, please mark exactly one Year lookup row (group_id=4) as active."
            );
        }

        $yearValue = (string) $currentYear;
        $updated = DB::table('lookups')
            ->where('group_id', 4)
            ->where('lookup_name', $yearValue)
            ->update(['is_active_year' => 1]);

        if ($updated === 0) {
            throw new RuntimeException(
                "AddIsActiveYearToLookupsTable: No Year lookup row found for {$yearValue}. " .
                "Create a Year lookup row first, then set it active."
            );
        }

        DB::table('lookups')
            ->where('group_id', 4)
            ->where('lookup_name', '!=', $yearValue)
            ->update(['is_active_year' => 0]);
    }

    public function down()
    {
        Schema::table('lookups', function (Blueprint $table) {
            $table->dropIndex('lookups_group_active_year_idx');
            $table->dropColumn('is_active_year');
        });
    }
}

