<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetricsCapturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metrics_captures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('metrics_feature_id');
            $table->string('bucket', 16);
            $table->dateTime('bucket_start');
            $table->string('key', 191)->nullable();
            $table->unsignedInteger('count')->default(0);
            $table->unsignedBigInteger('sum')->default(0);
            $table->unsignedInteger('min')->nullable();
            $table->unsignedInteger('max')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['metrics_feature_id', 'bucket', 'bucket_start']);
            $table->index(['metrics_feature_id', 'key']);
            $table->foreign('metrics_feature_id')
                ->references('id')
                ->on('metrics_features')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('metrics_captures');
    }
}
