@extends('layouts.dash')

@section('content')

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="container form-card">
    
    
    <form id="changeStudType" class="form onlineForm <?php if(Auth::user()->studentType == "NSFAS"){echo "nsfasSize";}elseif(Auth::user()->studentType == "Bursary"){echo "bursarySize";}else{echo "cashSize";} ?>" autocomplete="off">
        <br/>
        <h1 class="formHeading">Complete the relevant information</h1>
        <br/>

        <div class="form-field">
            <label>Student Type * <span id="studentType_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="studentType" id='studentType' class="required">
                <option value="" selected disabled>Select a Student Type</option>
                <option value="NSFAS" <?php if(Auth::user()->studentType == "NSFAS"){echo "selected";} ?>>NSFAS</option>
                <option value="Bursary" <?php if(Auth::user()->studentType == "Bursary"){echo "selected";} ?>>Bursary</option>
                <option value="Cash / Direct" <?php if(Auth::user()->studentType == "Cash / Direct"){echo "selected";} ?>>Cash</option>
            </select>
        </div>

        @if(!$agent->isMobile())
            <div class="form-field">
                <br/><br/>
            </div>
        @endif

        <hr/>
        <br/>
        @if(!$agent->isMobile())
            <br/>
        @endif

        <div id="changeSponsorDetails" class="<?php if(Auth::user()->studentType !== "Cash / Direct"){echo "hidden";} ?>">
        <h1 class="formHeading">Sponsor Information</h1>
        <h2 style="text-transform:uppercase; font-weight:400">Parent / Gaurdians Details</h2>
        <br/>
                <div class="form-field">
                    <label>First Name * <span id="pFirstName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="pFirstName" id="pFirstName"  value="<?php if(isset($user)){ echo $user->sponFirstName;} ?>">
                </div>

                <div class="form-field">
                    <label>Last Name * <span id="pLastName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="pLastName" id="pLastName"  value="<?php if(isset($user)){ echo $user->sponLastName;} ?>">
                </div>

                <div class="form-field">
                    <label>Cellphone Number * <span id="pCell_no_error" class="hidden" style="color:red"><small>Not a valid phone number</small></span></label>
                    <input type="text" name="sponCellCode" id="sponCellCode" style="width:18% !important;" value="<?php if(isset($user) && $user->sponCellCode){ echo $user->sponCellCode;}else{echo "+27";} ?>">
                    <input type="text" name="pCell_no" id='pCell_no' style="width:77% !important" value="<?php if(isset($user)){ echo $user->sponCell;} ?>">
                </div>

                <div class="form-field">
                    <label>Email Address * <span id="pEmail_error" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
                    <input type="email" name="pEmail" id='pEmail'  value="<?php if(isset($user)){ echo $user->sponEmail;} ?>">
                </div>

                <div class="form-field">
                    <label>Nationality * <span id="pNationality_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <select name="pNationality" id='pNationality'>
                        <option value="" selected disabled>Please select one</option>
                        @foreach($lookups as $lookup)
                            @if($lookup->group_id == "5")
                                <option value="{{$lookup->lookup_name}}" <?php if(isset($user) && $user->pNationality == $lookup->lookup_name){echo "selected";} ?>>{{$lookup->lookup_name}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-field" id="pIdField">
                    <label>I.D Number * <span id="pIdNumber_error" class="hidden" style="color:red"><small>Not a valid ID number</small></span></label>
                    <input type="text" name="pIdNumber" id="pIdNumber"  value="<?php if(isset($user)){ echo $user->sponId;} ?>">
                </div>

                <div class="form-field hidden" id="pPassportField">
                    <label>Passport Number * <span id="pPassportNo_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="pPassportNo" id="pPassportNo"  value="<?php if(isset($user)){ echo $user->pPassportNo;} ?>">
                </div>

                

                <!-- <div class="form-field-big">
                    <label>Physical Address* <span id="pAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" placeholder="Street No" name="pAddress1" id="pAddress1" style="width:98% !important">
                </div> -->
                <div class="form-field-big" style="height:50px !important">
                    <label>Physical Address * <span id="pAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                </div>
                <div class="form-field-big" style="height:60px !important">
                    <li style="display:inline-flex">
                        <input type="checkbox" id="app_address_p" name="app_address_p" value="1" style="width:50px !important;" <?php if(isset($user) && $user->app_address_p == '1'){echo 'checked';}?>>
                        <label for="app_address_p" id="labelAddrP" style="width:400px">Same as My Home Address</label>
                    </li>
                </div>

                <div class="pAddress <?php if(isset($user) && $user->app_address_p == '1'){echo 'hidden';}?>" >
                    <div class="form-field-big" style="height:60px !important">
                        <input type="text" placeholder="Street No" name="pAddress1" id="pAddress1" style="width:98% !important"  value="<?php if(isset($user)){ echo $user->pAddress1;} ?>">
                    </div>
                    <div class="form-field-big" style="height:60px !important">
                    <span id="pAddress2_error" class="hidden" style="color:red"><small>This field is required</small></span>
                        <input type="text" placeholder="Suburb" name="pAddress2" id="pAddress2" style="width:98% !important"  value="<?php if(isset($user)){ echo $user->pAddress2;} ?>">
                    </div>
                    <div class="form-field" style="height:60px !important">
                    <span id="pAddressCity_error" class="hidden" style="color:red"><small>This field is required</small></span>
                        <input type="text" placeholder="City" name="pAddressCity" id='pAddressCity'   value="<?php if(isset($user)){ echo $user->pAddressCity;} ?>">
                    </div>
                    <div class="form-field" style="height:60px !important">
                    <span id="pAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal code</small></span>
                        <input type="text" placeholder="Postal Code" name="pAddressPostal" id="pAddressPostal"  value="<?php if(isset($user)){ echo $user->pAddressPostal;} ?>">
                    </div>
                </div>

                <div class="form-field-big" id="permission_c_1">
                    <li style="display:inline-flex">
                        <input type="checkbox" id="c_credit_risk" name="c_credit_risk" value="1" style="width:50px !important;" <?php if(isset($user) && $user->c_credit_risk == '1'){echo 'checked';}?>>
                        <label for="c_credit_risk" id="" style="width:100%">I understand that approval of my application is subject to my Sponsor meeting Campus Africa's accepted credit risk profile and/or some mutually agreed payment arrangement <span id="c_credit_risk_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    </li>
                </div>
                <div class="form-field-big" id="permission_c_1">
                    <li style="display:inline-flex">
                        <input type="checkbox" id="c_credit_check" name="c_credit_check" value="1" style="width:50px !important;" <?php if(isset($user) && $user->c_credit_check == '1'){echo 'checked';}?>>
                        <label for="c_credit_check" style="width:100%">I confirm that my Sponsor consents to a credit check being performed on their profile for the purposes of my application <span id="c_credit_check_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    </li>
                </div>
        </div>

        <div id="changeBursarDetails" class="<?php if(Auth::user()->studentType !== "Bursary"){echo "hidden";} ?>">

        <h1 class="formHeading">Bursar Information</h1>
        <br/>
            <div class="form-field">
                <label>Company Name * <span id="bCompanyName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                <input type="text" name="bCompanyName" id='bCompanyName'  value="<?php if(isset($user)){ echo $user->burCompanyName;} ?>">
            </div>

            <div class="form-field">
                <label>Registration Number * <span id="bRegistrationNo_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                <input type="text" name="bRegistrationNo" id="bRegistrationNo"  value="<?php if(isset($user)){ echo $user->burRegNo;} ?>">
            </div>

            <div class="form-field">
                <label>Contact Person * <span id="bContactperson_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                <input type="text" name="bContactPerson" id="bContactPerson"   value="<?php if(isset($user)){ echo $user->burContactPerson;} ?>">
            </div>

            <div class="form-field">
                <label>Contact Number * <span id="bContactNumber_error" class="hidden" style="color:red"><small>Not a valid phone number</small></span></label>

                <input type="text" name="burContactCode" id="burContactCode" style="width:18% !important;" value="<?php if(isset($user) && $user->burContactCode){ echo $user->burContactCode;}else{echo "+27";} ?>">
                    <input type="text" name="bContactNumber" id='bContactNumber' style="width:77% !important" value="<?php if(isset($user)){ echo $user->burContactNo;} ?>">
            </div>

            <div class="form-field">
                <label>Email Address * <span id="bEmail_error" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
                <input type="email" name="bEmail" id="bEmail"   value="<?php if(isset($user)){ echo $user->burEmail;} ?>">
            </div>

            @if(!$agent->isMobile())

                <div class="form-field">
                    <br/>
                </div>

            @endif

            <div class="form-field-big">
                <label>Physical Address * <span id="bAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                <input type="text" placeholder="Street No" name="bAddress1" id="bAddress1" style="width:98% !important"  value="<?php if(isset($user)){ echo $user->bAddress1;} ?>">
            </div>
            <div class="form-field-big" style="height:60px !important">
            <span id="bAddress2_error" class="hidden" style="color:red"><small>This field is required</small></span>
                <input type="text" placeholder="Suburb" name="bAddress2" id="bAddress2" style="width:98% !important"  value="<?php if(isset($user)){ echo $user->bAddress2;} ?>">
            </div>
            <div class="form-field" style="height:60px !important">
            <span id="bAddressCity_error" class="hidden" style="color:red"><small>This field is required</small></span>
                <input type="text" placeholder="City" name="bAddressCity" id='bAddressCity'  value="<?php if(isset($user)){ echo $user->bAddressCity;} ?>">
            </div>
            <div class="form-field" style="height:60px !important">
            <span id="bAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal code</small></span>
                <input type="text" placeholder="Postal Code" name="bAddressPostal" id="bAddressPostal"  value="<?php if(isset($user)){ echo $user->bAddressPostal;} ?>">
            </div>

            

            <div class="form-field">
                <label>How Is the Bursary Paid * <span id="bPayment_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                <select name="bPayment" id='bPayment' >
                <option value="" disabled selected>Please select one</option>
                    <option value="Directly to the RESIDENCE" <?php if(isset($user) && $user->bPayment == "Directly to the RESIDENCE"){ echo "selected";} ?>>Directly to the RESIDENCE</option>
                    <option value="Directly to the STUDENT" <?php if(isset($user) && $user->bPayment == "Directly to the STUDENT"){ echo "selected";} ?>>Directly to the STUDENT</option>
                    <option value="Via an INTERMEDIARY" <?php if(isset($user) && $user->bPayment == "Via an INTERMEDIARY"){ echo "selected";} ?>>Via an INTERMEDIARY</option>
                </select>
            </div>

            @if(!$agent->isMobile())
                <div class="form-field" id="placeholder">
                    <br/>
                </div>
            @endif

            <div id="viaInt" class="<?php if(isset($user) && $user->bPayment != "Via an INTERMEDIARY"){echo 'hidden';} ?>">

                <div class="form-field">
                    <label>Intermediary * <span id="bint_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <select name="bint" id='bint'>
                        <option value="" disabled selected>Please select one</option>
                        <option value="Fundi" <?php if(isset($user) && $user->bint == "Fundi"){ echo "selected";} ?>>Fundi</option>
                        <option value="Intellicard" <?php if(isset($user) && $user->bint == "Intellicard"){ echo "selected";} ?>>Intellicard</option>
                        <option value="Other" <?php if(isset($user) && $user->bint == "Other"){ echo "selected";} ?>>Other</option>
                    </select>
                </div>
            </div>

            <div id="spesificInt" class="<?php if(isset($user) && $user->bint != "Other"){ echo "hidden";} ?>">

                <div class="form-field">
                    <label>Specify Intermediary Name * <span id="bIntName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="bIntName" id="bIntName" value="<?php if(isset($user)){ echo $user->bIntName;} ?>">
                </div>

                @if(!$agent->isMobile())
                    <div class="form-field">
                        <br/>
                    </div>
                @endif
            </div>

            <div class="form-field-big" id="permission_c_2">
                    <li style="display:inline-flex">
                        <input type="checkbox" id="b_meeting" name="b_meeting" value="1" style="width:50px !important;" <?php if(isset($user) && $user->b_meeting == '1'){echo 'checked';}?>>
                        <label for="b_meeting" id="" style="width:100%">I understand that approval of my application is subject to my Bursary Provider meeting Campus Africa's internal Bursary Provider requirements and/or some mutually agreed payment arrangement <span id="b_meeting_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    </li>
                </div>
        </div>

        <button type="button" class="detailsChange" id="submitForm" style="text-transform:uppercase; background-color:#e74f26; float:none; margin-left:0px;">Update My details</button>
    </form>
</div>


<script>

    $("#studentType").on('change', function(){
        
        if($("#studentType").val() == "Cash / Direct"){
            $("#changeSponsorDetails").removeClass('hidden');
            $("#changeBursarDetails").addClass('hidden');

            $("#changeStudType").removeClass('bursarySize')
            $("#changeStudType").removeClass('nsfasSize')
            $("#changeStudType").addClass('cashSize')
            

            $('.required').each( function(){
                id = $(this)[0]["id"]
                $("#"+id).removeClass("required")
            })

            $("#pFirstName").addClass("required")
            $("#pLastName").addClass("required")
            $("#pCell_no").addClass("required")
            $("#pEmail").addClass("required")
            $("#pNationality").addClass("required")
            if($("#pNationality").val() == "" || $("#pNationality").val() == "South Africa"){
                $("#pIdNumber").addClass("required")
            }else{
                $("#pPassportNo").addClass("required")
            }
            $("#pAddress1").addClass("required")
            $("#pAddress2").addClass("required")
            $("#pAddressCity").addClass("required")
            $("#pAddressPostal").addClass("required")

        }else if($("#studentType").val() == "Bursary"){
            $("#changeSponsorDetails").addClass('hidden');
            $("#changeBursarDetails").removeClass('hidden');

            $("#changeStudType").removeClass('cashSize')
            $("#changeStudType").removeClass('nsfasSize')
            $("#changeStudType").addClass('bursarySize')

            $('.required').each( function(){
                id = $(this)[0]["id"]
                $("#"+id).removeClass("required")
            })
            $("#bCompanyName").addClass("required")
            // $("#bRegistrationNo").addClass("required")
            $("#bContactPerson").addClass("required")
            $("#bContactNumber").addClass("required")
            $("#bEmail").addClass("required")
            $("#bAddress1").addClass("required")
            $("#bAddress2").addClass("required")
            $("#bAddressCity").addClass("required")
            $("#bAddressPostal").addClass("required")
            $("#bPayment").addClass("required")

        }else if($("#studentType").val() == "NSFAS"){
            $("#changeSponsorDetails").addClass('hidden');
            $("#changeBursarDetails").addClass('hidden');

            $("#changeStudType").removeClass('cashSize')
            $("#changeStudType").removeClass('bursarySize')
            $("#changeStudType").addClass('nsfasSize')

            $('.required').each( function(){
                id = $(this)[0]["id"]
                $("#"+id).removeClass("required")
            })
        }

    })

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("input").blur(function(){
        let fieldId = $(this)[0]["id"]

        // var required = document.getElementById(fieldId).required
        var required = $("#"+fieldId).hasClass('required')

        if($("#"+fieldId).val() == "" && required == true){
            $("#"+$(this)[0]["id"]).addClass('fieldError')
            $("#"+$(this)[0]["id"]+"_error").removeClass('hidden')
        }else{
            $("#"+$(this)[0]["id"]).removeClass('fieldError')
            $("#"+$(this)[0]["id"]+"_error").addClass('hidden')
        }

    });

    $("select").blur(function(){
        let fieldId = $(this)[0]["id"]

        var required = $("#"+fieldId).hasClass('required')

        if(($("#"+fieldId).val() == null || $("#"+fieldId).val() == "") && required == true){
            $("#"+$(this)[0]["id"]).addClass('fieldError')
            $("#"+$(this)[0]["id"]+"_error").removeClass('hidden')
        }else{
            $("#"+$(this)[0]["id"]).removeClass('fieldError')
            $("#"+$(this)[0]["id"]+"_error").addClass('hidden')
        }
    });

    $("#pCell_no").on('keyup blur', function(){
        cell = $("#pCell_no").val()
        ValidCell(cell, "pCell_no", "pCell_no_error", $("#sponCellCode").val())
    })
    $("#sponCellCode").on('keyup blur', function(){
        cell = $("#pCell_no").val()
        ValidCell(cell, "pCell_no", "pCell_no_error", $("#sponCellCode").val())
    })


    $("#pAddressPostal").on('keyup blur', function(){
        postal = $("#pAddressPostal").val()

        if(/\b\d{4}\b/g.test(postal)){
            $("#pAddressPostal").removeClass("fieldError")
            $("#pAddressPostal_error").addClass('hidden')
        }else{
            $("#pAddressPostal").addClass("fieldError")
            $("#pAddressPostal_error").removeClass('hidden')
        }
        
    })

    $("#bPayment").on('change', function(){
        
        if($("#bPayment").val() == "Via an INTERMEDIARY"){
            $("#viaInt").removeClass("hidden")
            $("#bint").addClass("required")
            $("#placeholder").addClass("hidden")
            $("#spesificInt").addClass("hidden")
            $("#bIntName").removeClass("required")
        }else{
            $("#viaInt").addClass("hidden")
            $("#bint").removeClass("required")
            $("#bint").removeClass("fieldError")
            $("#placeholder").removeClass("hidden")
            $("#spesificInt").addClass("hidden")
            $("#bIntName").removeClass("required")
            $("#bIntName").removeClass("fieldError")
        }
    })

    $("#bint").on('change', function(){
        
        if($("#bint").val() == "Other"){
            $("#spesificInt").removeClass("hidden")
            $("#bIntName").addClass("required")
        }else{
            $("#spesificInt").addClass("hidden")
            $("#bIntName").removeClass("required")
        }
    })

    $("#bContactNumber").on('keyup blur', function(){
        cell = $("#bContactNumber").val()
        ValidCell(cell, "bContactNumber", "bContactNumber_error", $("#burContactCode").val())
    })
    $("#burContactCode").on('keyup blur', function(){
        cell = $("#bContactNumber").val()
        ValidCell(cell, "bContactNumber", "bContactNumber_error", $("#burContactCode").val())
    })


    $("#bAddressPostal").on('keyup blur', function(){
        postal = $("#bAddressPostal").val()

        if(/\b\d{4}\b/g.test(postal)){
            $("#bAddressPostal").removeClass("fieldError")
            $("#bAddressPostal_error").addClass('hidden')
        }else{
            $("#bAddressPostal").addClass("fieldError")
            $("#bAddressPostal_error").removeClass('hidden')
        }
        
    })

    $("#bEmail").on('keyup blur', function(){
        if(isEmail($("#bEmail").val())){
            $("#bEmail").removeClass("fieldError")
            $("#bEmail_error").addClass('hidden')
        }else{
            $("#bEmail").addClass("fieldError")
            $("#bEmail_error").removeClass('hidden')
        }
    })

    function ValidCell(cell, fieldId, fieldError, code){
        if(code === "+27"){
            if (/^[1-9][0-9]{8}$/.test(cell)) {
                $("#"+fieldId).removeClass("fieldError")
                $("#"+fieldError).addClass('hidden')
            }else{
                $("#"+fieldId).addClass("fieldError")
                $("#"+fieldError).removeClass('hidden')
            }
        }else{
            if(/^[+][0-9]{1,3}$/.test(code)){
                if(/^(0|[1-9][0-9]*)$/.test(cell)){
                    $("#"+fieldId).removeClass("fieldError")
                    $("#"+fieldError).addClass('hidden')
                }else{
                    $("#"+fieldId).addClass("fieldError")
                    $("#"+fieldError).removeClass('hidden')
                }
            }else{
                $("#"+fieldId).addClass("fieldError")
                $("#"+fieldError).removeClass('hidden')
            }
        }
        
        // if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
        //     $("#"+fieldId).removeClass("fieldError")
        //     $("#"+fieldError).addClass('hidden')
        // }else{
        //     $("#"+fieldId).addClass("fieldError")
        //     $("#"+fieldError).removeClass('hidden')
        // }
    }

    function ValidID(idNumber, idField, errorField){
        let idArr = Array.from(idNumber);
        idArr.reverse()
        let newArr = [];

        for (let i = 0; i < idArr.length; i++) {
            if((i % 2) !== 0){
                let newValue = idArr[i]*2
                if(newValue > 9){
                    value1 = (newValue.toString()).substr(0,1)
                    value2 = (newValue.toString()).substr(1, 2)
                    newValue = parseInt(value1) + parseInt(value2)

                }
                newArr.push(newValue)
            }else{
                newArr.push(idArr[i])
            }
        }


        let sum = 0;
        for (let i = 0; i < newArr.length; i++) {
            sum += parseInt(newArr[i]);
        }

        let finalSum = sum/10

        console.log(Number.isInteger(finalSum) && idNumber !== "")

        if(Number.isInteger(finalSum) && idNumber !== ""){
            $("#"+idField).removeClass("fieldError")
            $("#"+errorField).addClass('hidden')
        }else{
            $("#"+idField).addClass("fieldError")
            $("#"+errorField).removeClass('hidden')
        }
    }

    $("#pIdNumber").on('keyup blur', function(){
        let idNumber = $("#pIdNumber").val();

        if(idNumber.length == 13){
            ValidID(idNumber, "pIdNumber", "pIdNumber_error")
        }else{
            $("#pIdNumber").addClass("fieldError")
            $("#pIdNumber_error").removeClass('hidden')
        }

        cecheckIDEmail();

    })

    $("#pEmail").on('keyup blur', function(){
        if(isEmail($("#pEmail").val())){
            $("#pEmail").removeClass("fieldError")
            $("#pEmail_error").addClass('hidden')
        }else{
            $("#pEmail").addClass("fieldError")
            $("#pEmail_error").removeClass('hidden')
        }
        cecheckIDEmail();
    })

    $("#pNationality").on('change', function(){
        if($("#pNationality").val() == "South Africa"){
            $("#pPassportField").addClass('hidden')
            $("#pIdField").removeClass('hidden')

            $("#pPassportNo").removeClass("required")
            $("#pIdNumber").addClass("required")
            $("#pPassportNo").removeClass("fieldError")
        }else{
            $("#pIdField").addClass('hidden')
            $("#pPassportField").removeClass('hidden')

            $("#pPassportNo").addClass("required")
            $("#pIdNumber").removeClass("required")
            $("#pIdNumber").removeClass("fieldError")
        }
    })

    $("#app_address_p").on('change', function(){

        if(document.getElementById('app_address_p').checked == true){
            $(".pAddress").addClass("hidden");
            $("#pAddress1").addClass("hidden").removeClass("required").removeClass("fieldError");
            $("#pAddress2").addClass("hidden").removeClass("required").removeClass("fieldError");
            $("#pAddressCity").addClass("hidden").removeClass("required").removeClass("fieldError");
            $("#pAddressPostal").addClass("hidden").removeClass("required").removeClass("fieldError");
            $("#pAddress1_error").addClass("hidden").removeClass("required").removeClass("fieldError");
        }else{
            $(".pAddress").removeClass('hidden')
            $("#pAddress1").removeClass("hidden").addClass("required");
            $("#pAddress2").removeClass("hidden").addClass("required");
            $("#pAddressCity").removeClass("hidden").addClass("required");
            $("#pAddressPostal").removeClass("hidden").addClass("required");
        }
    })

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    function cecheckIDEmail(){
        $.ajax({
            type: "post",
            url: "/email-check-idNo",
            data: {
                email: $("#email").val(),
                idNumber: $("#idNumber").val(),
            },

            success: function (data) {
                console.log(data)
                
                if (data[0] == "done") {

                    if($("#email").val() !== ""){
                        if(data[1] == false){
                            $("#email_InUse").removeClass('hidden')
                            $("#email").addClass('fieldError')
                        }else{
                            $("#email_InUse").addClass('hidden')
                            $("#email").removeClass('fieldError')
                        }
                    }

                    if($("#idNumber").val() !== ""){
                        if(data[2] == false){
                            $("#idNumber_InUse").removeClass('hidden')
                            $("#idNumber").addClass('fieldError')
                        }else{
                            $("#idNumber_InUse").addClass('hidden')
                            $("#idNumber").removeClass('fieldError')
                        }
                    }


                }
            },
        });
    }

    $("#submitForm").on('click', function() {

        //Check if any hidden fields are required, remove required check
        $('.hidden .fieldError').removeClass('fieldError').val('');

        var req = 1;


        $('.required').each( function(){
            if($(this).val() === "" || $(this).val() === null){
                req = 0

                $(this).addClass("fieldError")
                id = $(this)[0]["id"]
                $("#"+id+"_error").removeClass("hidden")
            }
        })

        if ($('.fieldDuplicate').length > 0) {
            req = 0;
        }

        if ($('.fieldError').length > 0) {
            req = 0;
        }

        if($("#studentType").val() == "Bursary"){
            if($('#b_meeting').is(":checked") === false){
                $("#b_meeting_error").removeClass('hidden');
                req = 0;
            }
        }else if($("#studentType").val() == "Cash / Direct"){
            if($('#c_credit_risk').is(":checked") === false){
                $("#c_credit_risk_error").removeClass('hidden');
                req = 0;
            }

            if($('#c_credit_check').is(":checked") === false){
                $("#c_credit_check_error").removeClass('hidden');
                req = 0;
            }
        }

        if(req == 0){
            Swal.fire(
                'Blank Fields',
                'Please fill in all Required Fields!',
                'error'
            )

        }else{
            let total = 1
            $('.fieldError').each( function(){
                if($(this).val() === ""){
                    total = 0

                    $(this).addClass("fieldError")
                    id = $(this)[0]["id"]
                    $("#"+id+"_error").removeClass("hidden")
                }
            })

            if(total == 0){
                Swal.fire(
                    'Blank Fields',
                    'Please fill in all Required Fields!',
                    'error'
                )

            }else{

                $("#submitForm").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

                $.ajax({
                        type: "post",
                        url: "/change-new-student-details",
                        data: {
                            studentType: $("#studentType").val(),
                            pFirstName: $("#pFirstName").val(), 
                            pLastName: $("#pLastName").val(), 
                            pCell_no: $("#pCell_no").val(), 
                            sponCellCode: $("#sponCellCode").val(),
                            pEmail: $("#pEmail").val(), 
                            pNationality: $("#pNationality").val(), 
                            pIdNumber: $("#pIdNumber").val(), 
                            pPassportNo: $("#pPassportNo").val(), 
                            app_address_p: $("#app_address_p").val(),
                            pAddress1: $("#pAddress1").val(), 
                            pAddress2: $("#pAddress2").val(), 
                            pAddressCity: $("#pAddressCity").val(), 
                            pAddressPostal: $("#pAddressPostal").val(), 
                            bCompanyName: $("#bCompanyName").val(), 
                            bRegistrationNo: $("#bRegistrationNo").val(), 
                            bContactPerson: $("#bContactPerson").val(), 
                            bContactNumber: $("#bContactNumber").val(), 
                            burContactCode: $("#burContactCode").val(),
                            bEmail: $("#bEmail").val(), 
                            bAddress1: $("#bAddress1").val(), 
                            bAddress2: $("#bAddress2").val(), 
                            bAddressCity: $("#bAddressCity").val(),
                            bAddressPostal: $("#bAddressPostal").val(), 
                            bPayment: $("#bPayment").val(), 
                            bint: $("#bint").val(), 
                            bIntName: $("#bIntName").val(), 
                            b_meeting: $("#b_meeting").val(),
                            c_credit_risk: $("#c_credit_risk").val(),
                            c_credit_check: $("#c_credit_check").val(),
                        },

                        success: function (data) {
                            window.location.replace('/dashboard');                      
                        },
                    });
            }
        }
        

    });

</script>

    

@endsection