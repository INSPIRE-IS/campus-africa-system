<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('kinNationality')->nullable();
            $table->string('kinPassportNo')->nullable();
            $table->string('pNationality')->nullable();
            $table->string('pPassportNo')->nullable();
            $table->string('pAddress1')->nullable();
            $table->string('pAddress2')->nullable();
            $table->string('pAddressCity')->nullable();
            $table->string('pAddressPostal')->nullable();
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
            $table->dropColumn('kinNationality');
            $table->dropColumn('kinPassportNo');
            $table->dropColumn('pNationality');
            $table->dropColumn('pPassportNo');
            $table->dropColumn('pAddress1');
            $table->dropColumn('pAddress2');
            $table->dropColumn('pAddressCity');
            $table->dropColumn('pAddressPostal');
        });
    }
}
