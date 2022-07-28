<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeaseInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lookups', function (Blueprint $table) {
            $table->string('month_from', 20)->nullable();
            $table->string('day_from', 20)->nullable();
            $table->string('month_to', 20)->nullable();
            $table->string('day_to', 20)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lookups', function (Blueprint $table) {
            $table->dropColumn('month_from');
            $table->dropColumn('day_from');
            $table->dropColumn('month_to');
            $table->dropColumn('day_to');
        });
    }
}
