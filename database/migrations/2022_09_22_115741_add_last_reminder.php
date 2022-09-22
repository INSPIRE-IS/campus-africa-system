<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastReminder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('students_info', function (Blueprint $table) {
            $table->date('fee_reminder_sent')->nullable();
            $table->date('general_reminder_sent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students_info', function (Blueprint $table) {
            $table->dropColumn('fee_reminder_sent');
            $table->dropColumn('general_reminder_sent');
        });
    }
}
