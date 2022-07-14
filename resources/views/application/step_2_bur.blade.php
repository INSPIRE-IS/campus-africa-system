@extends('layouts.app')

@section('content')

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="yellowGradientSmall">
    <div class="container">

        <nav class="navBar">
            <div class="leftMenu">
                <a href="/login"><img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91"></a>
            </div>

            <div class="rightMenu">
                
                <a href="https://campusafrica.co.za/contact-us/" target="_blank"><button type="button" class="contactButton">contact us</button></a>
                <!-- <button type="button" class="menuButton">Menu <i class="fa fa-bars"></i></button> -->
            </div>
        </nav>

        <div class="loginHeader">
            <h1 class="booking">Online Application</h1>
        </div>
    
    </div>
</div>

<div class="container form-card">
    <div class="onlineTabs">
    <div class="onlineTabs">
        <div class="tab complete">1: Applicant Details</div>
        <div class="tab active">2: Burser Information</div>
        <div class="tab">3: Next of Kin</div>
        <div class="tab">4: Upload Documents</div>
        <div class="tab">5: Pay Booking Fee</div>
    </div>
    </div>
    
    <form id="newStudentBur" class="form onlineForm" autocomplete="off">
        <br/>
        <h1 class="formHeading">Bursar Information</h1>
        <br/>

        <div class="form-field">
                    <label>Company Name * <span id="bCompanyName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="bCompanyName" id='bCompanyName' class="required" value="<?php if(isset($user)){ echo $user->burCompanyName;} ?>">
                </div>

                <div class="form-field">
                    <label>Registration Number * <span id="bRegistrationNo_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="bRegistrationNo" id="bRegistrationNo" class="required" value="<?php if(isset($user)){ echo $user->burRegNo;} ?>">
                </div>

                <div class="form-field">
                    <label>Contact Person * <span id="bContactperson_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="bContactPerson" id="bContactPerson" class="required"  value="<?php if(isset($user)){ echo $user->burContactPerson;} ?>">
                </div>

                <div class="form-field">
                    <label>Contact Number * <span id="bContactNumber_error" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>

                    <input type="text" name="burContactCode" id="burContactCode" style="width:18% !important;" value="<?php if(isset($user) && $user->burContactCode){ echo $user->kinWorkCode;}else{echo "+27";} ?>">
                    <input type="number" name="bContactNumber" id='bContactNumber' style="width:77% !important" value="<?php if(isset($user)){ echo $user->burContactNo;} ?>">
                </div>

                <div class="form-field">
                    <label>Email Address * <span id="bEmail_error" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
                    <input type="email" name="bEmail" id="bEmail" class="required"  value="<?php if(isset($user)){ echo $user->burEmail;} ?>">
                </div>

                <div class="form-field mobHidden">
                    <br/>
                </div>

                <div class="form-field-big">
                    <label>Physical Address * <span id="bAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" placeholder="Street No" name="bAddress1" id="bAddress1" style="width:98% !important" class="required" value="<?php if(isset($user)){ echo $user->bAddress1;} ?>">
                </div>
                <div class="form-field-big" style="height:60px !important">
                <span id="bAddress2_error" class="hidden" style="color:red"><small>This field is required</small></span>
                    <input type="text" placeholder="Suburb" name="bAddress2" id="bAddress2" style="width:98% !important" class="required" value="<?php if(isset($user)){ echo $user->bAddress2;} ?>">
                </div>
                <div class="form-field" style="height:60px !important">
                <span id="bAddressCity_error" class="hidden" style="color:red"><small>This field is required</small></span>
                    <input type="text" placeholder="City" name="bAddressCity" id='bAddressCity' class="required" value="<?php if(isset($user)){ echo $user->bAddressCity;} ?>">
                </div>
                <div class="form-field" style="height:60px !important">
                <span id="bAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal code</small></span>
                    <input type="number" placeholder="Postal Code" name="bAddressPostal" id="bAddressPostal" class="required" value="<?php if(isset($user)){ echo $user->bAddressPostal;} ?>">
                </div>

                

                <div class="form-field">
                    <label>How Is the Bursary Paid * <span id="bPayment_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <select name="bPayment" id='bPayment' class="required">
                    <option value="" disabled selected>Please select one</option>
                        <option value="Directly to the RESIDENCE" <?php if(isset($user) && $user->bPayment == "Directly to the RESIDENCE"){ echo "selected";} ?>>Directly to the RESIDENCE</option>
                        <option value="Directly to the STUDENT" <?php if(isset($user) && $user->bPayment == "Directly to the STUDENT"){ echo "selected";} ?>>Directly to the STUDENT</option>
                        <option value="Via an INTERMEDIARY" <?php if(isset($user) && $user->bPayment == "Via an INTERMEDIARY"){ echo "selected";} ?>>Via an INTERMEDIARY</option>
                    </select>
                </div>

                <div class="form-field mobHidden" id="placeholder">
                    <br/>
                </div>

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

                    <div class="form-field mobHidden">
                        <br/>
                    </div>
                </div>

                <div class="form-field-big" id="permission_c_2">
                    <li style="display:inline-flex">
                        <input type="checkbox" id="b_meeting" name="b_meeting" value="1" style="width:50px !important;" <?php if(isset($user) && $user->b_meeting == '1'){echo 'checked';}?>>
                        <label for="b_meeting" id="" style="width:100%">I understand that approval of my application is subject to my Bursary Provider meeting Campus Africa's internal Bursary Provider requirements and/or some mutually agreed payment arrangement <span id="b_meeting_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    </li>
                </div>

        <button type="button" class="detailsChange" id="submitForm" style="text-transform:uppercase; background-color:#e74f26">Proceed to next step</button>


    </form>

</div>


<script>

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
            console.log("1")
            $("#"+$(this)[0]["id"]).addClass('fieldError')
            $("#"+$(this)[0]["id"]+"_error").removeClass('hidden')
        }else{
            console.log("2")
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
            if(/^(?:[+\d].*\d|\d)$/.test(code)){
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
        // let cellPrefix = cell.slice(0, 3)
        // if(cellPrefix === "+27"){
        //     console.log(/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell))
        //     if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
        //         $("#"+fieldId).removeClass("fieldError")
        //         $("#"+fieldError).addClass('hidden')
        //     }else{
        //         $("#"+fieldId).addClass("fieldError")
        //         $("#"+fieldError).removeClass('hidden')
        //     }
        // }else if (/^((\+)[0-9]{1,3})[1-9](\d{2}){4}$/.test(cell)) {
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

    $("#bEmail").on('keyup blur', function(){
        if(isEmail($("#bEmail").val())){
            $("#bEmail").removeClass("fieldError")
            $("#bEmail_error").addClass('hidden')
        }else{
            $("#bEmail").addClass("fieldError")
            $("#bEmail_error").removeClass('hidden')
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

        if($('#b_meeting').is(":checked") === false){
            $("#b_meeting_error").removeClass('hidden');
            req = 0;
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
                        url: "/step-2-bur",
                        data: {
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
                        },

                        success: function (data) {
                            window.location.replace(data)                            
                        },
                    });
            }
        }

    });

</script>


@endsection
