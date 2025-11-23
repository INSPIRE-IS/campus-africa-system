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
            $table->string('role')->default('student'); // 'student' or 'parent'
            $table->string('year', 4)->nullable();
            $table->softDeletes();
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
