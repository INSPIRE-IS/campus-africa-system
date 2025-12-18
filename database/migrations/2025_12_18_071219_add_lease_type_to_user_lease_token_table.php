<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeaseTypeToUserLeaseTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_lease_token', function (Blueprint $table) {
            $table->enum('lease_type', ['lease', 'temp_lease'])->default('lease')->after('leaseToken');
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
            $table->dropColumn('lease_type');
        });
    }
}
