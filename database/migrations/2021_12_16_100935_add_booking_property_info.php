<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBookingPropertyInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings_data', function (Blueprint $table) {
            $table->integer('property_data_id')->nullable();
            $table->string('floor', 100)->nullable();
            $table->string('unit', 100)->nullable();
            $table->string('room', 100)->nullable();
            $table->string('bed', 100)->nullable();
            $table->string('bed_code', 100)->nullable();
            $table->string('room_rate', 100)->nullable();
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
            $table->dropColumn('property_data_id');
            $table->dropColumn('floor');
            $table->dropColumn('unit');
            $table->dropColumn('room');
            $table->dropColumn('bed');
            $table->dropColumn('room_rate');
            $table->dropColumn('bed_code');
        });
    }
}
