<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRolesSplit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('role_non_funding')->default(0);
            $table->tinyInteger('role_funding')->default(0);
            $table->tinyInteger('role_app_lease')->default(0);
            $table->tinyInteger('template')->default(0);
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
            $table->dropColumn('role_non_funding');
            $table->dropColumn('role_funding');
            $table->dropColumn('role_app_lease');
            $table->dropColumn('template');
        });
    }
}
