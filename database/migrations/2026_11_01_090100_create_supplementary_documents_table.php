<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplementaryDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('supplementary_documents', function (Blueprint $table) {
            $table->id();
            $table->integer('user_uid');
            $table->string('year', 4);
            $table->string('title', 150);
            $table->string('original_file_name');
            $table->string('file_path');
            $table->integer('uploaded_by');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_uid', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('supplementary_documents');
    }
}
