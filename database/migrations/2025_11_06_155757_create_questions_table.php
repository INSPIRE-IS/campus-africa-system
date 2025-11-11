<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable();
            $table->text('question_text');
            $table->enum('input_type', ['radio', 'range'])->default('radio');
            $table->integer('min_value')->nullable();
            $table->integer('max_value')->nullable();
            $table->integer('step')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('weight', 5, 2)->default(1.0); // weighting factor (1 = normal, 2 = double importance)
            $table->boolean('allow_partial_match')->default(false); // controls if 1 vs 2 counts as partial match
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');
    }
}
