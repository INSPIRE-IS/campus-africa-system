<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentCompatibilityScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_compatibility_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_a_id')->constrained('students_info')->onDelete('cascade');
            $table->foreignId('student_b_id')->constrained('students_info')->onDelete('cascade');
            $table->float('compatibility_score', 5, 2)->default(0);
            $table->year('academic_year');
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['student_a_id', 'student_b_id', 'academic_year'])->name('compatibility_scores_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_compatibility_scores');
    }
}
