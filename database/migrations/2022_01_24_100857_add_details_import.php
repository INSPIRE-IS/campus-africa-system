<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailsImport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_audit_log', function (Blueprint $table) {
            $table->string('file_name')->nullable();
            $table->integer('actionUserId')->nullable();
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
            $table->dropColumn('file_name');
            $table->dropColumn('actionUserId');
        });
    }
}
