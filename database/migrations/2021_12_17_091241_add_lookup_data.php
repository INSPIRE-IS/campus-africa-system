<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLookupData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lookups', function (Blueprint $table) {
            $table->date('lease_from_date')->nullable();
            $table->string('lease_from_time', 10)->nullable();
            $table->date('lease_to_date')->nullable();
            $table->string('lease_to_time', 10)->nullable();
            $table->string('deafault', 100)->nullable();
            $table->string('class', 100)->nullable();
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
            $table->dropColumn('lease_from_date');
            $table->dropColumn('lease_from_time');
            $table->dropColumn('lease_to_date');
            $table->dropColumn('lease_to_time');
            $table->dropColumn('deafault');
            $table->dropColumn('class');
        });
    }
}
