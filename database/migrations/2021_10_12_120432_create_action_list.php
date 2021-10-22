<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_lists', function (Blueprint $table) {
            $table->id();
            $table->integer('userUid')->nullable();
            $table->text('action')->nullable();
            $table->integer('actionUserUid')->nullable();
            $table->string('actionUserDisplayName')->nullable();
            $table->integer('actionUserRole')->nullable();
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
        Schema::dropIfExists('action_lists');
    }
}
