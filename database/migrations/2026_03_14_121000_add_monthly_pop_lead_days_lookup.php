<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMonthlyPopLeadDaysLookup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('lookups')->updateOrInsert(
            ['group_id' => '14'],
            [
                'group_id' => '14',
                'lookup_name' => 'Monthly POP Lead Days',
                'day_from' => '7',
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('lookups')->where('group_id', '14')->delete();
    }
}
