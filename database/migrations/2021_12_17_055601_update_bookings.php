<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBookings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings_data', function (Blueprint $table) {
            $table->date('lease_from_date')->nullable();
            $table->string('lease_from_time', 10)->nullable();
            $table->date('lease_to_date')->nullable();
            $table->string('lease_to_time', 10)->nullable();
        });

        Schema::create('property', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('residence', 100)->nullable();
            $table->string('booking_rate', 100)->nullable();
            $table->string('owner', 100)->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings_data', function (Blueprint $table) {
            $table->dropColumn('lease_from_date');
            $table->dropColumn('lease_from_time');
            $table->dropColumn('lease_to_date');
            $table->dropColumn('lease_to_time');
        });

        Schema::dropIfExists('property');
    }
}
