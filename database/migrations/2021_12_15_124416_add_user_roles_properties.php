<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserRolesProperties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('role_braamlofts')->nullable();
            $table->integer('role_80_jorissen')->nullable();
            $table->integer('role_49_jorissen')->nullable();
            $table->integer('role_dunvista_mansions')->nullable();
            $table->integer('role_wynton_joy')->nullable();
            $table->integer('role_126_siemert')->nullable();
            $table->integer('role_ymca')->nullable();
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
            $table->dropColumn('role_braamlofts');
            $table->dropColumn('role_80_jorissen');
            $table->dropColumn('role_49_jorissen');
            $table->dropColumn('role_dunvista_mansions');
            $table->dropColumn('role_wynton_joy');
            $table->dropColumn('role_126_siemert');
            $table->dropColumn('role_ymca');
        });
    }
}
