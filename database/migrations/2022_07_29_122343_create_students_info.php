<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students_info', function (Blueprint $table) {
            $table->id();
            $table->integer('userUid')->nullable();
            $table->integer('bookingId')->nullable();
            $table->string('userFirstName')->nullable();
            $table->string('userLastname')->nullable();
            $table->string('userTitle', 4)->nullable();
            $table->string('userDob', 12)->nullable();
            $table->string('userCellCode', 5)->nullable();
            $table->string('userCell', 15)->nullable();
            $table->string('userEmail')->nullable();
            $table->string('userId', 13)->nullable();
            $table->string('userNationality', 100)->nullable();
            $table->string('userPassport', 15)->nullable();
            $table->string('institutionStudy', 100)->nullable();
            $table->string('studentNo', 30)->nullable();
            $table->string('institutionCampus')->nullable();
            $table->string('faculty')->nullable();
            $table->string('year', 4)->nullable();
            $table->string('yearOfStudy', 20)->nullable();
            $table->string('studentType', 20)->nullable();
            $table->string('userAddress1')->nullable();
            $table->string('userAddress2')->nullable();
            $table->string('userAddressCity')->nullable();
            $table->string('userAddressPostal', 5)->nullable();
            $table->string('bookingRef')->nullable();
            $table->string('bookingStatus', 50)->nullable();
            $table->string('applicationStatus', 50)->nullable();
            $table->string('sponFirstName', 100)->nullable();
            $table->string('sponLastName', 100)->nullable();
            $table->string('sponCellCode', 5)->nullable();
            $table->string('sponCell', 15)->nullable();
            $table->string('sponEmail')->nullable();
            $table->string('pNationality')->nullable();
            $table->string('pPassportNo', 15)->nullable();
            $table->string('sponId', 13)->nullable();
            $table->string('pAddress1')->nullable();
            $table->string('pAddress2')->nullable();
            $table->string('pAddressCity')->nullable();
            $table->string('pAddressPostal', 5)->nullable();
            $table->tinyInteger('c_credit_risk')->nullable();
            $table->tinyInteger('c_credit_check')->nullable();
            $table->string('burCompanyName')->nullable();
            $table->string('burRegNo', 100)->nullable();
            $table->string('burContactPerson')->nullable();
            $table->string('burContactCode', 5)->nullable();
            $table->string('burContactNo', 13)->nullable();
            $table->string('burEmail')->nullable();
            $table->tinyInteger('b_meeting')->nullable();
            $table->string('bAddress1')->nullable();
            $table->string('bAddress2')->nullable();
            $table->string('bAddressCity')->nullable();
            $table->string('bAddressPostal', 5)->nullable();
            $table->string('bPayment', 50)->nullable();
            $table->string('bint', 50)->nullable();
            $table->string('bIntName', 100)->nullable();
            $table->string('kinFirstName', 100)->nullable();
            $table->string('kinLastName', 100)->nullable();
            $table->string('kinTitle', 4)->nullable();
            $table->string('kinAddressStr')->nullable();
            $table->string('kinAddressSuburb')->nullable();
            $table->string('kinAddressCity')->nullable();
            $table->string('kinAddressPost', 5)->nullable();
            $table->string('kinRelationship')->nullable();
            $table->string('kinTelCode', 5)->nullable();
            $table->string('kinTelNo', 15)->nullable();
            $table->string('kinCellCode', 5)->nullable();
            $table->string('kinCellNo', 15)->nullable();
            $table->string('kinWorkCode', 5)->nullable();
            $table->string('kinWorkNo', 15)->nullable();
            $table->string('kinEmail')->nullable();
            $table->string('kinNationality')->nullable();
            $table->string('kinPassportNo', 15)->nullable();
            $table->tinyInteger('app_address')->nullable();
            $table->tinyInteger('app_address_p')->nullable();
            $table->tinyInteger('app_parent_kin')->nullable();
            $table->tinyInteger('id_data_approved')->nullable();
            $table->tinyInteger('student_card_approved')->nullable();
            $table->tinyInteger('registration_approved')->nullable();
            $table->tinyInteger('bursary_approved')->nullable();
            $table->tinyInteger('bank_approved')->nullable();
            $table->tinyInteger('address_approved')->nullable();
            $table->tinyInteger('kin_approved')->nullable();
            $table->tinyInteger('parent_approved')->nullable();
            $table->tinyInteger('burser_approved')->nullable();
            $table->string('overall_status')->nullable();
            $table->tinyInteger('email_info')->nullable();
            $table->tinyInteger('nsfas_bursar')->nullable();
            $table->tinyInteger('rob_10_fee')->nullable();
            $table->tinyInteger('rob_15_id')->nullable();
            $table->tinyInteger('rob_20_card')->nullable();
            $table->tinyInteger('rob_25_reg')->nullable();
            $table->tinyInteger('rob_30_funds')->nullable();
            $table->tinyInteger('rob_35_info')->nullable();
            $table->tinyInteger('rob_40_bed')->nullable();
            $table->tinyInteger('rob_45_other')->nullable();
            $table->tinyInteger('rob_50_approve')->nullable();
            $table->tinyInteger('rob_55_lease')->nullable();
            $table->tinyInteger('email_data_approved')->nullable();
            $table->tinyInteger('rob_60_cancelled')->nullable();
            $table->tinyInteger('onboard_complete')->nullable();
            $table->tinyInteger('email_verified')->nullable();
            $table->tinyInteger('fee_approved')->nullable();
            $table->tinyInteger('app_source')->nullable();
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
        Schema::dropIfExists('students_info');
    }
}
