<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('id');
            $table->index('userRole');
        });

        Schema::table('bookings_data', function (Blueprint $table) {
            $table->index('id');
            $table->index('userId');
            $table->index('property');
            $table->index('roomType');
            $table->index('year');
        });

        Schema::table('students_info', function (Blueprint $table) {
            $table->index('userUid');
            $table->index('bookingId');
            $table->index('studentNo');
            $table->index('year');
        });

        Schema::table('property', function (Blueprint $table) {
            $table->index('id');
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->index('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_id_index');
            $table->dropIndex('users_userRole_index');
        });

        Schema::table('bookings_data', function (Blueprint $table) {
            $table->dropIndex('bookings_id_index');
            $table->dropIndex('bookings_data_userId_index');
            $table->dropIndex('bookings_data_property_index');
            $table->dropIndex('bookings_data_roomType_index');
            $table->dropIndex('bookings_data_year_index');
        });

        Schema::table('students_info', function (Blueprint $table) {
            $table->dropIndex('students_info_userUid_index');
            $table->dropIndex('students_info_bookingId_index');
            $table->dropIndex('students_info_studentNo_index');
            $table->dropIndex('students_info_year_index');
        });

        Schema::table('property', function (Blueprint $table) {
            $table->dropIndex('property_id_index');
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->dropIndex('room_types_id_index');
        });
    }
}
