<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('online_payment', function (Blueprint $table) {
            $table->string('payment_amount')->nullable();
            $table->string('payment_transaction_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_method_detail')->nullable();
            $table->string('payment_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('online_payment', function (Blueprint $table) {
            $table->dropColumn('payment_amount');
            $table->dropColumn('payment_transaction_id');
            $table->dropColumn('payment_method');
            $table->dropColumn('payment_method_detail');
            $table->dropColumn('payment_status');
            
        });
    }
}
