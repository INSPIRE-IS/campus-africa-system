<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('userAddress1')->nullable();
            $table->string('userAddress2')->nullable();
            $table->string('userAddressCity')->nullable();
            $table->string('userAddressPostal')->nullable();
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
            $table->dropColumn('userAddress1');
            $table->dropColumn('userAddress2');
            $table->dropColumn('userAddressCity');
            $table->dropColumn('userAddressPostal');
        });
    }
}
