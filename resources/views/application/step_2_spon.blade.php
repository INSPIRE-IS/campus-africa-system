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
        <div class="tab active">2: Sponsor Information</div>
        <div class="tab">3: Next of Kin</div>
        <div class="tab">4: Upload Documents</div>
        <div class="tab">5: Pay Booking Fee</div>
    </div>
    </div>
    
    <form id="newStudentSpon" class="form onlineForm" autocomplete="off">
        <br/>
        <h1 class="formHeading">Sponsor Information</h1>
        <h2 style="text-transform:uppercase; font-weight:400">Parent / Gaurdians Details</h2>
        <br/>

                <div class="form-field">
                    <label>First Name * <span id="pFirstName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="pFirstName" id="pFirstName" class="required" value="<?php if(isset($user)){ echo $user->sponFirstName;} ?>">
                </div>

                <div class="form-field">
                    <label>Last Name * <span id="pLastName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="pLastName" id="pLastName" class="required" value="<?php if(isset($user)){ echo $user->sponLastName;} ?>">
                </div>

                <div class="form-field">
                    <label>Cellphone Number * <span id="pCell_no_error" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>

                    <input type="text" name="sponCellCode" id="sponCellCode" style="width:18% !important;" value="<?php if(isset($user) && $user->sponCellCode){ echo $user->kinWorkCode;}else{echo "+27";} ?>">
                    <input type="text" name="pCell_no" id='pCell_no' style="width:77% !important" value="<?php if(isset($user)){ echo $user->sponCell;} ?>">
                </div>

                <div class="form-field">
                    <label>Email Address * <span id="pEmail_error" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
                    <input type="email" name="pEmail" id='pEmail' class="required" value="<?php if(isset($user)){ echo $user->sponEmail;} ?>">
                </div>

                <div class="form-field">
                    <label>Nationality * <span id="pNationality_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <select name="pNationality" id='pNationality'class="required">
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
                    <input type="text" name="pIdNumber" id="pIdNumber" class="required" value="<?php if(isset($user)){ echo $user->sponId;} ?>">
                </div>

                <div class="form-field hidden" id="pPassportField">
                    <label>Passport Number * <span id="pPassportNo_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="pPassportNo" id="pPassportNo" class="required" value="<?php if(isset($user)){ echo $user->pPassportNo;} ?>">
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
                        <input type="text" placeholder="Street No" name="pAddress1" id="pAddress1" style="width:98% !important" class="required" value="<?php if(isset($user)){ echo $user->pAddress1;} ?>">
                    </div>
                    <div class="form-field-big" style="height:60px !important">
                    <span id="pAddress2_error" class="hidden" style="color:red"><small>This field is required</small></span>
                        <input type="text" placeholder="Suburb" name="pAddress2" id="pAddress2" style="width:98% !important" class="required" value="<?php if(isset($user)){ echo $user->pAddress2;} ?>">
                    </div>
                    <div class="form-field" style="height:60px !important">
                    <span id="pAddressCity_error" class="hidden" style="color:red"><small>This field is required</small></span>
                        <input type="text" placeholder="City" name="pAddressCity" id='pAddressCity'  class="required" value="<?php if(isset($user)){ echo $user->pAddressCity;} ?>">
                    </div>
                    <div class="form-field" style="height:60px !important">
                    <span id="pAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal code</small></span>
                        <input type="number" placeholder="Postal Code" name="pAddressPostal" id="pAddressPostal" class="required" value="<?php if(isset($user)){ echo $user->pAddressPostal;} ?>">
                    </div>
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

    function ValidCell(cell, fieldId, fieldError, code){
        if(code === "+27"){
            if (/^\d{9}$/.test(cell)) {
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
                        url: "/step-2-spon",
                        data: {
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
