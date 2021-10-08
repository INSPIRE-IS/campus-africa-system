<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function ($table) {
            $table->integer('userRole')->default(0);

            $table->string('userFirstName')->nullable();
            $table->string('userLastname')->nullable();
            $table->string('userTitle')->nullable();
            $table->string('userDob')->nullable();
            $table->string('userCell')->nullable();
            $table->string('userEmail')->nullable();
            $table->string('userId')->nullable();
            $table->string('userNationality')->nullable();
            $table->string('userPassport')->nullable();
            $table->string('institutionStudy')->nullable();
            $table->string('studentNo')->nullable();
            $table->string('institutionCampus')->nullable();
            $table->string('faculty')->nullable();
            $table->string('year')->nullable();

            $table->string('sponFirstName')->nullable();
            $table->string('sponLastName')->nullable();
            $table->string('sponCell')->nullable();
            $table->string('sponEmail')->nullable();
            $table->string('sponId')->nullable();

            $table->string('burCompanyName')->nullable();
            $table->string('burRegNo')->nullable();
            $table->string('burContactPerson')->nullable();
            $table->string('burContactNo')->nullable();
            $table->string('burEmail')->nullable();

            $table->string('kinFirstName')->nullable();
            $table->string('kinLastName')->nullable();
            $table->string('kinTitle')->nullable();
            $table->string('kinId')->nullable();
            $table->string('kinAddressStr')->nullable();
            $table->string('kinAddressSuburb')->nullable();
            $table->string('kinAddressCity')->nullable();
            $table->string('kinAddressPost')->nullable();
            $table->string('kinRelationship')->nullable();
            $table->string('kinTelNo')->nullable();
            $table->string('kinCellNo')->nullable();
            $table->string('kinWorkNo')->nullable();
            $table->string('kinEmail')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('userFirstName');
            $table->dropColumn('userLastname');
            $table->dropColumn('userTitle');
            $table->dropColumn('userDob');
            $table->dropColumn('userCell');
            $table->dropColumn('userEmail');
            $table->dropColumn('userId');
            $table->dropColumn('userNationality');
            $table->dropColumn('userPassport');
            $table->dropColumn('institutionStudy');
            $table->dropColumn('studentNo');
            $table->dropColumn('institutionCampus');
            $table->dropColumn('faculty');
            $table->dropColumn('year');

            $table->dropColumn('sponFirstName');
            $table->dropColumn('sponLastName');
            $table->dropColumn('sponCell');
            $table->dropColumn('sponEmail');
            $table->dropColumn('sponId');

            $table->dropColumn('burCompanyName');
            $table->dropColumn('burRegNo');
            $table->dropColumn('burContactPerson');
            $table->dropColumn('burContactNo');
            $table->dropColumn('burEmail');

            $table->dropColumn('kinFirstName');
            $table->dropColumn('kinLastName');
            $table->dropColumn('kinTitle');
            $table->dropColumn('kinId');
            $table->dropColumn('kinAddressStr');
            $table->dropColumn('kinAddressSuburb');
            $table->dropColumn('kinAddressCity');
            $table->dropColumn('kinAddressPost');
            $table->dropColumn('kinRelationship');
            $table->dropColumn('kinTelNo');
            $table->dropColumn('kinCellNo');
            $table->dropColumn('kinWorkNo');
            $table->dropColumn('kinEmail');
        });
    }
}
