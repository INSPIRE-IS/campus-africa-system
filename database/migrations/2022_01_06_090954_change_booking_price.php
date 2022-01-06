<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeBookingPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('property', function (Blueprint $table) {
            $table->float('booking_rate', 10, 2)->default(0)->change();
            $table->float('nsfas_booking_rate', 10, 2)->default(0)->change();
            $table->float('bursary_booking_rate', 10, 2)->default(0)->change();
        });
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('property', function (Blueprint $table) {
            $table->string('nsfas_booking_rate', 10)->change();
            $table->string('bursary_booking_rate', 100)->change();
            $table->string('booking_rate', 100)->change();
        });
    }
}
