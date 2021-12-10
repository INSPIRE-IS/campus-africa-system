<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserAccessRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('role_create_app')->nullable();
            $table->integer('role_edit_app')->nullable();
            $table->integer('role_upload_docs')->nullable();
            $table->integer('role_approve_docs')->nullable();
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
            $table->dropColumn('role_create_app');
            $table->dropColumn('role_edit_app');
            $table->dropColumn('role_upload_docs');
            $table->dropColumn('role_approve_docs');
        });
    }
}
