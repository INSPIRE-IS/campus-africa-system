<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('userCellCode', 5)->nullable();
            $table->string('kinCellCode', 5)->nullable();
            $table->string('kinWorkCode', 5)->nullable();
            $table->string('kinTelCode', 5)->nullable();
            $table->string('burContactCode', 5)->nullable();
            $table->string('sponCellCode', 5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('userCellCode');
            $table->dropColumn('kinCellCode');
            $table->dropColumn('kinWorkCode');
            $table->dropColumn('kinTelCode');
            $table->dropColumn('burContactCode');
            $table->dropColumn('sponCellCode');
        });
    }
}
