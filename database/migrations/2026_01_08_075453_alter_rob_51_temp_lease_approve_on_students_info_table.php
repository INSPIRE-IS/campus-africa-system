<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterRob51TempLeaseApproveOnStudentsInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('students_info', function (Blueprint $table) {
            // Drop old boolean column
            $table->dropColumn('rob_51_temp_lease_approve');
        });

        // Change column type & default
        Schema::table('students_info', function (Blueprint $table) {
            $table->unsignedTinyInteger('rob_51_temp_lease_approve')
                ->default(3)
                ->comment('0=Rejected, 1=Approved, 2=Pending, 3=Not Set')
                ->after('rob_50_approve');
        });

        // Set all existing records to 3 (safe as per your logic)
        DB::table('students_info')->update([
            'rob_51_temp_lease_approve' => 3
        ]);
        $users = User::where('role_bm_report', 1)->get();

        foreach ($users as $user) {
            $user->role_allow_generate_temp_lease = 1;
            $user->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students_info', function (Blueprint $table) {
            $table->dropColumn('rob_51_temp_lease_approve');
        });

        Schema::table('students_info', function (Blueprint $table) {
            $table->boolean('rob_51_temp_lease_approve')
                ->default(0)
                ->after('rob_50_approve');
        });
    }
}
