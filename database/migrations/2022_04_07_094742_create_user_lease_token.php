<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLeaseToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_lease_token', function (Blueprint $table) {
            $table->id();
            $table->integer('userUid')->nullable();
            $table->string('leaseToken')->nullable();
            $table->tinyInteger('active')->default(1);
            $table->tinyInteger('signed')->default(0);
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
        Schema::dropIfExists('user_lease_token');
    }
}
