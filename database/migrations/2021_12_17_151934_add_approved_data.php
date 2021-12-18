<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovedData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('id_data_approved')->nullable();
            $table->tinyInteger('student_card_approved')->nullable();
            $table->tinyInteger('registration_approved')->nullable();
            $table->tinyInteger('bursary_approved')->nullable();
            $table->tinyInteger('bank_approved')->nullable();
            $table->tinyInteger('address_approved')->nullable();
            $table->tinyInteger('kin_approved')->nullable();
            $table->tinyInteger('parent_approved')->nullable();
            $table->tinyInteger('burser_approved')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id_data_approved');
            $table->dropColumn('student_card_approved');
            $table->dropColumn('registration_approved');
            $table->dropColumn('bursary_approved');
            $table->dropColumn('bank_approved');
            $table->dropColumn('address_approved');
            $table->dropColumn('kin_approved');
            $table->dropColumn('parent_approved');
            $table->dropColumn('burser_approved');
        });
    }
}
