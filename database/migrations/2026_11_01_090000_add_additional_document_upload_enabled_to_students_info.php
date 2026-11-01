<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalDocumentUploadEnabledToStudentsInfo extends Migration
{
    public function up()
    {
        Schema::table('students_info', function (Blueprint $table) {
            $table->boolean('additional_document_upload_enabled')->default(true);
        });
    }

    public function down()
    {
        Schema::table('students_info', function (Blueprint $table) {
            $table->dropColumn('additional_document_upload_enabled');
        });
    }
}
