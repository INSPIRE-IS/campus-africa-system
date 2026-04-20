<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYearToUserLeaseToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_lease_token', function (Blueprint $table) {
            if (!Schema::hasColumn('user_lease_token', 'role')) {
                $table->string('role')->default('student');
            }

            if (!Schema::hasColumn('user_lease_token', 'year')) {
                $table->string('year')->nullable();
            }

            if (!Schema::hasColumn('user_lease_token', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_lease_token', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->dropColumn('year');
            $table->dropSoftDeletes();
        });
    }
}
