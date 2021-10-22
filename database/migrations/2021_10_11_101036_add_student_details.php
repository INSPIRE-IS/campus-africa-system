<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStudentDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('roomBlock')->nullable();
            $table->string('roomNo')->nullable();
            $table->string('roomType')->nullable();
            $table->string('price')->nullable();
            $table->string('studentType')->nullable();
            $table->string('bookingRef')->nullable();
            $table->string('bookingStatus')->nullable();
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
            $table->dropColumn('roomBlock');
            $table->dropColumn('roomNo');
            $table->dropColumn('roomType');
            $table->dropColumn('price');
            $table->dropColumn('studentType');
            $table->dropColumn('bookingRef');
            $table->dropColumn('bookingStatus');
        });
    }
}
