<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('bookingId')->nullable();
            $table->integer('booking_fee')->nullable();
            $table->tinyInteger('booking_fee_waived')->default(0);
            $table->tinyInteger('rob_b_waived')->default(0);
            $table->integer('loan_admin_fee')->nullable();
            $table->tinyInteger('loan_admin_fee_waived')->default(0);
            $table->tinyInteger('rob_loan_waived')->default(0);
            $table->integer('deposit')->nullable();
            $table->tinyInteger('deposit_waived')->default(0);
            $table->tinyInteger('rob_deposit_waived')->default(0);
            $table->integer('monthly_rental')->nullable();
            $table->integer('monthly_rental_total')->nullable();
            $table->integer('total_funded')->nullable();
            $table->integer('total_top_up')->nullable();
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
        Schema::dropIfExists('booking_payments');
    }
}
