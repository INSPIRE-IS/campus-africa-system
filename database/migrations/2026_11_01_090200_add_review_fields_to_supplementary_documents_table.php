<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReviewFieldsToSupplementaryDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('supplementary_documents', function (Blueprint $table) {
            $table->string('status', 20)->default('Uploaded')->after('uploaded_by');
            $table->integer('reviewed_by')->nullable()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('rejection_reason')->nullable()->after('reviewed_at');
        });
    }

    public function down()
    {
        Schema::table('supplementary_documents', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'reviewed_by',
                'reviewed_at',
                'rejection_reason',
            ]);
        });
    }
}
