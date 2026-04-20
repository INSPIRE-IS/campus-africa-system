<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpouseFieldsToAssurityTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assurity_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('assurity_tokens', 'spouse_required')) {
                $table->unsignedTinyInteger('spouse_required')->nullable()->after('signature_url');
            }
            if (!Schema::hasColumn('assurity_tokens', 'spouse_not_required_reason')) {
                $table->text('spouse_not_required_reason')->nullable()->after('spouse_required');
            }
            if (!Schema::hasColumn('assurity_tokens', 'spouse_signature_url')) {
                $table->string('spouse_signature_url')->nullable()->after('spouse_not_required_reason');
            }
            if (!Schema::hasColumn('assurity_tokens', 'signed_at')) {
                $table->string('signed_at')->nullable()->after('spouse_signature_url');
            }
            if (!Schema::hasColumn('assurity_tokens', 'signed_on')) {
                $table->date('signed_on')->nullable()->after('signed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assurity_tokens', function (Blueprint $table) {
            if (Schema::hasColumn('assurity_tokens', 'signed_on')) {
                $table->dropColumn('signed_on');
            }
            if (Schema::hasColumn('assurity_tokens', 'signed_at')) {
                $table->dropColumn('signed_at');
            }
            if (Schema::hasColumn('assurity_tokens', 'spouse_signature_url')) {
                $table->dropColumn('spouse_signature_url');
            }
            if (Schema::hasColumn('assurity_tokens', 'spouse_not_required_reason')) {
                $table->dropColumn('spouse_not_required_reason');
            }
            if (Schema::hasColumn('assurity_tokens', 'spouse_required')) {
                $table->dropColumn('spouse_required');
            }
        });
    }
}

