<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatPropertyData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('property_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('residence', 100)->nullable();
            $table->string('meta_info_1', 100)->nullable();
            $table->string('meta_info_2', 100)->nullable();
            $table->string('floor', 20)->nullable();
            $table->tinyInteger('unit')->nullable();
            $table->string('room', 20)->nullable();
            $table->string('bed', 10)->nullable();
            $table->string('bed_code', 50)->nullable();
            $table->tinyInteger('inventory')->nullable();
            $table->string('room_type', 50)->nullable();
            $table->string('room_rate', 50)->nullable();
            $table->tinyInteger('room_available')->nullable();
            $table->tinyInteger('user_id_room')->nullable();
            $table->timestamps();
        });

        Schema::table('bookings_data', function (Blueprint $table) {
            $table->integer('propertyId')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('property_data');

        Schema::table('bookings_data', function (Blueprint $table) {
            $table->dropColumn('propertyId');
        });
    }
}
