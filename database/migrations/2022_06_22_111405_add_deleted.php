<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeleted extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_audit_log', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('import_log_student_list', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
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
            $table->dropColumn('deleted_at');
        });

        Schema::table('import_log_student_list', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
}
