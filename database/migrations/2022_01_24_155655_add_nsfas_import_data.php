<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNsfasImportData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_audit_log', function (Blueprint $table) {
            $table->integer('total_records')->nullable();
            $table->integer('status')->nullable();
            $table->integer('matched')->nullable();
            $table->integer('unmatched')->nullable();
        });

        Schema::table('import_log_student_list', function (Blueprint $table) {
            $table->integer('status')->nullable();
            $table->string('institution')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import_audit_log', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('total_records');
            $table->dropColumn('matched');
            $table->dropColumn('unmatched');
        });

        Schema::table('import_log_student_list', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('institution');
        });
    }
}
