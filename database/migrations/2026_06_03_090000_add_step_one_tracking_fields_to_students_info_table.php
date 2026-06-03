<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStepOneTrackingFieldsToStudentsInfoTable extends Migration
{
    public function up()
    {
        Schema::table('students_info', function (Blueprint $table) {
            if (!Schema::hasColumn('students_info', 'last_step_completed')) {
                $table->string('last_step_completed', 30)->nullable();
            }

            if (!Schema::hasColumn('students_info', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable();
            }

            if (!Schema::hasColumn('students_info', 'marketing_consent')) {
                $table->boolean('marketing_consent')->default(false);
            }

            if (!Schema::hasColumn('students_info', 'marketing_consent_at')) {
                $table->timestamp('marketing_consent_at')->nullable();
            }

            if (!Schema::hasColumn('students_info', 'marketing_consent_channel')) {
                $table->string('marketing_consent_channel', 20)->default('email');
            }
        });
    }

    public function down()
    {
        Schema::table('students_info', function (Blueprint $table) {
            $columns = [
                'last_step_completed',
                'last_activity_at',
                'marketing_consent',
                'marketing_consent_at',
                'marketing_consent_channel',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('students_info', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
