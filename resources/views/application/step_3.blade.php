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
        @if(Auth::user()->studentType !== "NSFAS") 
            <div class="tab complete">2: @if(Auth::user()->studentType == "Bursary") Burser @else Sponsor @endif Information</div>
            <div class="tab active">3: Next of Kin</div>
            <div class="tab">4: Upload Documents</div>
            <div class="tab">5: Pay Booking Fee</div>
        @else
            <div class="tab active">2: Next of Kin</div>
            <div class="tab">3: Upload Documents</div>
            <div class="tab">4: Pay Booking Fee</div>
        @endif
    </div>
    </div>
    
    <form id="newStudent" class="form onlineForm" autocomplete="off">
        <br/>
        <h1 class="formHeading">Next of Kin</h1>
        <br/>

            @if($user->studentType == "Cash / Direct")
                <div class="form-field-big" style="height:60px !important" id="parentSameInfo">
                    <li style="display:inline-flex">
                        <input type="checkbox" id="app_parent_kin" name="app_parent_kin" value="1" style="width:50px !important;" <?php if(isset($user) && $user->app_parent_kin == '1'){echo 'checked';} ?>>
                        <label for="app_parent_kin" style="@if(!$agent->isMobile()) width:400px @endif">Same as Parent / Sponsor</label>
                    </li>
                </div>
            @endif

            <div class="nameFields <?php if(isset($user) && $user->app_parent_kin == '1'){echo 'hidden';} ?>">

                <div class="form-field">
                    <label>First Name * <span id="kFirstName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="kFirstName" id="kFirstName" class="required" value="<?php if(isset($user)){ echo $user->kinFirstName;} ?>">
                </div>
                <div class="form-field">
                    <label>Last Name * <span id="kLastName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="kLastName" id="kLastName" class="required" value="<?php if(isset($user)){ echo $user->kinLastName;} ?>">
                </div>
            </div>

            <div class="form-field">
                <label>Title * <span id="kTitle_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                <select name="kTitle" id='kTitle' class="required">
                <option value="" disabled selected>Please select one</option>
                    <option value="Mr" <?php if(isset($user) && $user->kinTitle == "Mr"){ echo "selected";} ?>>Mr</option>
                    <option value="Ms" <?php if(isset($user) && $user->kinTitle == "Ms"){ echo "selected";} ?>>Ms</option>
                    <option value="Mrs" <?php if(isset($user) && $user->kinTitle == "Mrs"){ echo "selected";} ?>>Mrs</option>
                    <option value="Miss" <?php if(isset($user) && $user->kinTitle == "Miss"){ echo "selected";} ?>>Miss</option>
                </select>
            </div>

            <div class="form-field">
                <label>Relationship * <span id="kRelationship_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                <input type="text" name="kRelationship" id="kRelationship" class="required" value="<?php if(isset($user)){ echo $user->kinRelationship;} ?>">
            </div>

            <div class="generalInfo <?php if(isset($user) && $user->app_parent_kin == '1'){echo 'hidden';} ?>">

                {{-- <div class="form-field">
                    <label>Nationality * <span id="kNationality" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <select name="kinNationality" id='kinNationality' class="required">
                        <option value="" selected disabled>Please select one</option>
                        @foreach($lookups as $lookup)
                            @if($lookup->group_id == "5")
                                <option value="{{$lookup->lookup_name}}">{{$lookup->lookup_name}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-field" id="kinIdField">
                    <label>I.D Number * <span id="kIdNumber_error" class="hidden" style="color:red"><small>Not a valid ID number</small></span></label>
                    <input type="text" name="kIdNumber" id="kIdNumber" class="required">
                </div> 

                <div class="form-field hidden" id="kinPassportField">
                    <label>Passport Number</label>
                    <input type="text" name="kinPassportNo" id="kinPassportNo" >
                </div>--}}

                <div class="form-field">
                    <label>Cellphone Number * <span id="kCellNo_error" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span><span id="kCellNo_duplicate" class="hidden" style="color:red"><small>. Duplicate detected!</small></span></label>
                    <input type="text" name="kCellNo" id='kCellNo' class="required" value="<?php if(isset($user)){ echo $user->kinCellNo;} ?>">
                </div>

                <div class="form-field">
                    <label>Email Address</label>
                    <input type="email" name="kEmail" id="kEmail" value="<?php if(isset($user)){ echo $user->kinEmail;} ?>">
                </div>
            </div>

        <div class="form-field">
            <label>Work Number <span id="kWorkNo_error" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
            <input type="text" name="kWorkNo" id="kWorkNo" value="<?php if(isset($user)){ echo $user->kinWorkNo;} ?>">
        </div>

        <div class="form-field">
            <label>Telephone Number <span id="kTeleNo_error" class="hidden" style="color:red"><small>Not a valid telephone number</small></span></label>
            <input type="text" name="kTeleNo" id='kTeleNo' value="<?php if(isset($user)){ echo $user->kinTelNo;} ?>">
        </div>

        <div class="addressSection <?php if(isset($user) && $user->app_parent_kin == '1'){echo 'hidden';} ?>">

            <div class="form-field-big" style="height:50px !important;">
                <label>Physical Address * <span id="kAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            </div>
            <div class="form-field-big" style="height:60px !important">
                <li style="display:inline-flex">
                    <input type="checkbox" id="app_address" name="app_address" value="1" style="width:50px !important;" <?php if(isset($user) && $user->app_address == '1'){echo 'checked';} ?>>
                    <label for="app_address" style="@if(!$agent->isMobile()) width:400px @endif">Same as Applicant's @if($agent->isMobile()) <br/> @endif Home Address</label>
                </li>
            </div>
            <div class="kinAddress <?php if(isset($user) && $user->app_address == '1'){echo 'hidden';} ?>">
                <div class="form-field-big" style="height:60px !important">
                    <input type="text" placeholder="Street No" name="kAddress1" id="kAddress1" style="width:98% !important" class="required" value="<?php if(isset($user)){ echo $user->kinAddressStr;} ?>">
                </div>
                <div class="form-field-big" style="height:60px !important">
                <span id="kAddress2_error" class="hidden" style="color:red"><small>This field is required</small></span>
                    <input type="text" placeholder="Suburb" name="kAddress2" id="kAddress2" style="width:98% !important" class="required" value="<?php if(isset($user)){ echo $user->kinAddressSuburb;} ?>">
                </div>
                <div class="form-field" style="height:60px !important">
                <span id="kAddressCity_error" class="hidden" style="color:red"><small>This field is required</small></span>
                    <input type="text" placeholder="City" name="kAddressCity" id='kAddressCity' class="required" value="<?php if(isset($user)){ echo $user->kinAddressCity;} ?>">
                </div>
                <div class="form-field" style="height:60px !important">
                <span id="kAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal code</small></span>
                    <input type="text" placeholder="Postal Code" name="kAddressPostal" id="kAddressPostal" class="required" value="<?php if(isset($user)){ echo $user->kinAddressPost;} ?>">
                </div>
            </div>
        </div>

        <button type="button" class="detailsChange" id="submitForm" style="text-transform:uppercase; background-color:#e74f26">Proceed to next step</button>


    </form>

</div>

<div class="footerClearfix"></div>
<div class="footerClearfix"></div>

@if($agent->isMobile())
<div class="footerClearfix"></div>
<div class="footerClearfix"></div>
@endif

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

    $("#kTeleNo").on('keyup blur', function(){
        cell = $("#kTeleNo").val()
        if(cell !== ""){
            ValidCell(cell, "kTeleNo", "kTeleNo_error")
        }else{
            $("#kTeleNo_error").addClass('hidden')
            $("#kTeleNo").removeClass('fieldError')
        }
    })

    $("#kCellNo").on('keyup blur', function(){
        cell = $("#kCellNo").val();
        
        ValidCell(cell, "kCellNo", "kCellNo_error");
    })

    $("#kWorkNo").on('keyup blur', function(){
        cell = $("#kWorkNo").val()
        if(cell !== ""){
            ValidCell(cell, "kWorkNo", "kWorkNo_error")
        }else{
            $("#kWorkNo_error").addClass('hidden')
            $("#kWorkNo").removeClass('fieldError')
        }
    })


    $("#kAddressPostal").on('keyup blur', function(){
        postal = $("#kAddressPostal").val()

        if(/\b\d{4}\b/g.test(postal)){
            $("#kAddressPostal").removeClass("fieldError")
            $("#kAddressPostal_error").addClass('hidden')
        }else{
            $("#kAddressPostal").addClass("fieldError")
            $("#kAddressPostal_error").removeClass('hidden')
        } 
    })

    function ValidCell(cell, fieldId, fieldError){
        if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
            $("#"+fieldId).removeClass("fieldError")
            $("#"+fieldError).addClass('hidden')
        }else{
            $("#"+fieldId).addClass("fieldError")
            $("#"+fieldError).removeClass('hidden')
        }
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

    $("#kEmail").on('keyup blur', function(){
        if(isEmail($("#kEmail").val())){
            $("#kEmail").removeClass("fieldError")
            $("#kEmail_error").addClass('hidden')
        }else{
            if ($("#kEmail").val() !== "") {
                $("#kEmail").addClass("fieldError")
                $("#kEmail_error").removeClass('hidden')
            } else {
                $("#kEmail").removeClass("fieldError")
                $("#kEmail_error").addClass('hidden')
            }
        }
    })

    $("#app_parent_kin").on('change', function(){

        if(document.getElementById('app_parent_kin').checked == true){

            $(".nameFields").addClass('hidden')
            $(".generalInfo").addClass('hidden')
            $(".addressSection").addClass('hidden')

            $("#kFirstName").addClass("hidden").removeClass("required").removeClass("fieldError");
            $("#kLastName").addClass("hidden").removeClass("required").removeClass("fieldError");
            $("#kCellNo").addClass("hidden").removeClass("required").removeClass("fieldError");
            $("#kEmail").addClass("hidden").removeClass("required").removeClass("fieldError");
            $("#kAddress1").addClass("hidden").removeClass("required").removeClass("fieldError");
            $("#kAddress2").addClass("hidden").removeClass("required").removeClass("fieldError");
            $("#kAddressCity").addClass("hidden").removeClass("required").removeClass("fieldError");
            $("#kAddressPostal").addClass("hidden").removeClass("required").removeClass("fieldError");
            
        }else{

            $(".nameFields").removeClass('hidden')
            $(".generalInfo").removeClass('hidden')
            $(".addressSection").removeClass('hidden')
            
            $("#kFirstName").removeClass("hidden").addClass("required");
            $("#kLastName").removeClass("hidden").addClass("required");
            $("#kCellNo").removeClass("hidden").addClass("required");
            $("#kEmail").removeClass("hidden").addClass("required");
            $("#kAddress1").removeClass("hidden").addClass("required");
            $("#kAddress2").removeClass("hidden").addClass("required");
            $("#kAddressCity").removeClass("hidden").addClass("required");
            $("#kAddressPostal").removeClass("hidden").addClass("required");
            
        }

    })

    $("#app_address").on('change', function(){

        if(document.getElementById('app_address').checked == true){
            $(".kinAddress").addClass("hidden");
            $("#kAddress1").addClass("hidden").removeClass("required").removeClass("fieldError");
            $("#kAddress2").addClass("hidden").removeClass("required").removeClass("fieldError");
            $("#kAddressCity").addClass("hidden").removeClass("required").removeClass("fieldError");
            $("#kAddressPostal").addClass("hidden").removeClass("required").removeClass("fieldError");
            
        }else{
            $(".kinAddress").removeClass('hidden')
            $("#kAddress1").removeClass("hidden").addClass("required");
            $("#kAddress2").removeClass("hidden").addClass("required");
            $("#kAddressCity").removeClass("hidden").addClass("required");
            $("#kAddressPostal").removeClass("hidden").addClass("required");
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
                        url: "/step-3-kin",
                        data: {
                            app_parent_kin: $("#app_parent_kin").val(),
                            kFirstName: $("#kFirstName").val(), 
                            kLastName: $("#kLastName").val(), 
                            kTitle: $("#kTitle").val(), 
                            kRelationship: $("#kRelationship").val(), 
                            kCellNo: $("#kCellNo").val(), 
                            kEmail: $("#kEmail").val(), 
                            kWorkNo: $("#kWorkNo").val(), 
                            kTeleNo: $("#kTeleNo").val(),
                            app_address: $("#app_address").val(),
                            kAddress1: $("#kAddress1").val(), 
                            kAddress2: $("#kAddress2").val(), 
                            kAddressCity: $("#kAddressCity").val(), 
                            kAddressPostal: $("#kAddressPostal").val(), 
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
