<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_payment', function (Blueprint $table) {
            $table->id();
            $table->string('userUid')->nullable();
            $table->string('roomType')->nullable();
            $table->string('roomId')->nullable();
            $table->string('bookingAmount')->nullable();
            $table->string('depositAmount')->nullable();
            $table->string('paymentType')->nullable();
            $table->string('cardholderName')->nullable();
            $table->string('cardNumber')->nullable();
            $table->string('expDate')->nullable();
            $table->string('cvv')->nullable();
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
        Schema::dropIfExists('booking_payment');
    }
}
