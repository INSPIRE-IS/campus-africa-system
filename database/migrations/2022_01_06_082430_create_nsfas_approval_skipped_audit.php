<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNsfasApprovalSkippedAudit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_log_student_list', function (Blueprint $table) {
            $table->integer('importId')->nullable();
            $table->string('student_first_name', 100)->nullable();
            $table->string('student_last_name', 100)->nullable();
            $table->string('student_email', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import_log_student_list', function (Blueprint $table) {
            $table->dropColumn('importId');
            $table->dropColumn('student_first_name');
            $table->dropColumn('student_last_name');
            $table->dropColumn('student_email');
        });
    }
}
