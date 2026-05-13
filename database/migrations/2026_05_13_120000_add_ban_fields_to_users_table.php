<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBanFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'banned_at')) {
                $table->timestamp('banned_at')->nullable()->index();
            }
            if (!Schema::hasColumn('users', 'banned_reason')) {
                $table->text('banned_reason')->nullable();
            }
            if (!Schema::hasColumn('users', 'banned_by')) {
                $table->unsignedBigInteger('banned_by')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'banned_at')) {
                $table->dropIndex(['banned_at']);
                $table->dropColumn('banned_at');
            }
            if (Schema::hasColumn('users', 'banned_reason')) {
                $table->dropColumn('banned_reason');
            }
            if (Schema::hasColumn('users', 'banned_by')) {
                $table->dropColumn('banned_by');
            }
        });
    }
}

