<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBurserDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('users', function (Blueprint $table) {
            $table->string('userFirstName', 100)->change();
            $table->string('userLastname', 100)->change();
            $table->string('userTitle', 4)->change();
            $table->string('userDob', 12)->change();
            $table->string('userCell', 15)->change();
            $table->string('userId', 13)->change();
            $table->string('userNationality', 100)->change();
            $table->string('userPassport', 15)->change();
            $table->string('institutionStudy', 100)->change();
            $table->string('studentNo', 30)->change();
            $table->string('year', 4)->change();
            $table->string('sponFirstName', 100)->change();
            $table->string('sponLastName', 100)->change();
            $table->string('sponCell', 15)->change();
            $table->string('sponId', 13)->change();

            $table->string('kinFirstName', 100)->change();
            $table->string('kinLastName', 100)->change();
            $table->string('kinTitle', 4)->change();
            $table->string('kinCellNo', 15)->change();
            $table->string('kinId', 13)->change();


            $table->string('bAddress1', 100)->nullable();
            $table->string('bAddress2', 50)->nullable();
            $table->string('bAddressCity', 50)->nullable();
            $table->string('bAddressPostal', 4)->nullable();
            $table->string('bPayment', 50)->nullable();
            $table->string('bint', 50)->nullable();
            $table->string('bIntName', 100)->nullable(); 
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
            $table->string('userFirstName')->change();
            $table->string('userLastname')->change();
            $table->string('userTitle')->change();
            $table->string('userDob')->change();
            $table->string('userCell')->change();
            $table->string('userId')->change();
            $table->string('userNationality')->change();
            $table->string('userPassport')->change();
            $table->string('institutionStudy')->change();
            $table->string('studentNo')->change();
            $table->string('year')->change();
            $table->string('sponFirstName')->change();
            $table->string('sponLastName')->change();
            $table->string('sponCell')->change();
            $table->string('sponId')->change();

            $table->string('kinFirstName')->change();
            $table->string('kinLastName')->change();
            $table->string('kinTitle')->change();
            $table->string('kinCellNo')->change();
            $table->string('kinId')->change();

            $table->dropColumn('bAddress1');
            $table->dropColumn('bAddress2');
            $table->dropColumn('bAddressCity');
            $table->dropColumn('bAddressPostal');
            $table->dropColumn('bPayment');
            $table->dropColumn('bint');
            $table->dropColumn('bIntName');
        });
    }
}
