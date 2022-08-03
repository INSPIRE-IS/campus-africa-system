<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReasonWaiveReject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_payments', function (Blueprint $table) {
            $table->string('reason_booking_fee_reject')->nullable();
            $table->string('reason_admin_fee_reject')->nullable();
            $table->string('reason_deposit_reject')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking_payments', function (Blueprint $table) {
            $table->string('reason_booking_fee_reject');
            $table->string('reason_admin_fee_reject');
            $table->string('reason_deposit_reject');
        });
    }
}
