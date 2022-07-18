<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoomPriceInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn('room_map_price');
            $table->tinyInteger('pull_price')->nullable();
            $table->string('booking_price', 10)->nullable();
            $table->string('rent_price', 10)->nullable();
            $table->tinyInteger('apply_nsfas')->nullable();
            $table->tinyInteger('apply_bursary')->nullable();
            $table->tinyInteger('apply_cash')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->string('room_map_price')->nullable();
            $table->dropColumn('pull_price');
            $table->dropColumn('booking_price');
            $table->dropColumn('rent_price');
            $table->dropColumn('apply_nsfas');
            $table->dropColumn('apply_bursary');
            $table->dropColumn('apply_cash');
        });
    }
}
