<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonthlyPopFieldsToBookingPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_payments', function (Blueprint $table) {
            $table->date('monthly_pop_due_date')->nullable()->after('nsfas_rate');
            $table->decimal('monthly_pop_amount', 10, 2)->nullable()->after('monthly_pop_due_date');
            $table->boolean('send_to_student')->default(0)->after('monthly_pop_amount');
            $table->boolean('send_to_sponsor')->default(0)->after('send_to_student');
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
            $table->dropColumn(['monthly_pop_due_date', 'monthly_pop_amount', 'send_to_student', 'send_to_sponsor']);
        });
    }
}
