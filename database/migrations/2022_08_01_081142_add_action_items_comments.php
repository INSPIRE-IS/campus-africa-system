<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActionItemsComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('action_lists', function (Blueprint $table) {
            $table->string('year', 5)->nullable();
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->integer('bookingId')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('action_lists', function (Blueprint $table) {
            $table->dropColumn('year');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('bookingId');
        });
    }
}
