<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCheckboxData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('c_credit_risk')->nullable();
            $table->tinyInteger('c_credit_check')->nullable();
            $table->tinyInteger('b_meeting')->nullable();
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
            $table->dropColumn('c_credit_risk');
            $table->dropColumn('c_credit_check');
            $table->dropColumn('b_meeting');
        });
    }
}
