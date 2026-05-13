<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaToActionListsTable extends Migration
{
    public function up()
    {
        Schema::table('action_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('action_lists', 'meta')) {
                $table->json('meta')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('action_lists', function (Blueprint $table) {
            if (Schema::hasColumn('action_lists', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
}

