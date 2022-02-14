<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImportStudentInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_log_student_list', function (Blueprint $table) {
            $table->string('student_number', 30)->nullable();
            $table->string('student_id_number', 13)->nullable();
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
            $table->dropColumn('student_number');
            $table->dropColumn('student_id_number');
        });
    }
}
