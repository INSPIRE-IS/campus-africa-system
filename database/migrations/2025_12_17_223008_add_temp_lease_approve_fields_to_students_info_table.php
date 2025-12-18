<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTempLeaseApproveFieldsToStudentsInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('students_info', function (Blueprint $table) {
            $table->boolean('rob_51_temp_lease_approve')->default(0)->after('rob_50_approve');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students_info', function (Blueprint $table) {
            $table->dropColumn('rob_51_temp_lease_approve');
        });
    }
}
