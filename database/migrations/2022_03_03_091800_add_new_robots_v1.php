<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewRobotsV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('rob_10_fee')->default(0);
            $table->tinyInteger('rob_15_id')->default(0);
            $table->tinyInteger('rob_20_card')->default(0);
            $table->tinyInteger('rob_25_reg')->default(0);
            $table->tinyInteger('rob_30_funds')->default(0);
            $table->tinyInteger('rob_35_info')->default(1);
            $table->tinyInteger('rob_40_bed')->default(1);
            $table->tinyInteger('rob_45_other')->default(2);
            $table->tinyInteger('rob_50_approve')->default(0);
            $table->tinyInteger('rob_55_lease')->default(0);
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
            $table->dropColumn('rob_10_fee');
            $table->dropColumn('rob_15_id');
            $table->dropColumn('rob_20_card');
            $table->dropColumn('rob_25_reg');
            $table->dropColumn('rob_30_funds');
            $table->dropColumn('rob_35_info');
            $table->dropColumn('rob_40_bed');
            $table->dropColumn('rob_45_other');
            $table->dropColumn('rob_50_approve');
            $table->dropColumn('rob_55_lease');
        });
    }
}
