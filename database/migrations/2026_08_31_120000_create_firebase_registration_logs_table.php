<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFirebaseRegistrationLogsTable extends Migration
{
    public function up()
    {
        Schema::create('firebase_registration_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('students_info_id')->nullable()->index();
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->string('year', 10)->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('firebase_uid')->nullable()->index();
            $table->string('status', 30)->index();
            $table->string('stage', 80)->nullable()->index();
            $table->text('message')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('firebase_registration_logs');
    }
}
