<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRob65DebtAndAssurityTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('students_info', function (Blueprint $table) {
            if (!Schema::hasColumn('students_info', 'rob_65_debt')) {
                $table->unsignedTinyInteger('rob_65_debt')->default(3)->after('rob_60_cancelled');
            }
        });
        // Schema::dropIfExists('assurity_tokens');
        Schema::create('assurity_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userUid')->index();
            $table->string('assurity_token')->unique();
            $table->unsignedTinyInteger('active')->default(1);
            $table->unsignedTinyInteger('signed')->default(0);
            $table->string('signature_url')->nullable();
            $table->string('role')->nullable();
            $table->string('year')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assurity_tokens');

        Schema::table('students_info', function (Blueprint $table) {
            if (Schema::hasColumn('students_info', 'rob_65_debt')) {
                $table->dropColumn('rob_65_debt');
            }
        });
    }
}
