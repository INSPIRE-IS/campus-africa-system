<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnlinePayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('online_payment', function (Blueprint $table) {
            $table->id();
            $table->integer('userUid')->nullable();
            $table->string('requestID')->nullable();
            $table->string('reference')->nullable();
            $table->integer('amount')->nullable();
            $table->string('checkSum')->nullable();
            $table->string('returnUrl')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('online_payment');
    }
}
