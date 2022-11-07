<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStudentTypeProp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('institution_properties', function (Blueprint $table) {
            $table->integer('cash')->nullable();
            $table->integer('bursary')->nullable();
            $table->integer('nsfas')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('institution_properties', function (Blueprint $table) {
            $table->dropColumn('cash');
            $table->dropColumn('bursary');
            $table->dropColumn('nsfas');
        });
    }
}
