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
        1.Applicant Details<br/>
        2.Sponsor/Burser Information<br/>
        3.Next of Kin<br/>
        4.Upload Documents<br/>
        5.Pay Booking Fee<br/>
    </div>
    
    <form id="newStudent" class="form onlineForm" autocomplete="off">
        <br/>
        <h1 class="formHeading">Applicant Details</h1>
        <br/>

        <div class="form-field-big hidden" style="height:60px !important" id="parentSameInfo">
                <li style="display:inline-flex">
                    <input type="checkbox" id="app_parent_kin" name="app_parent_kin" value="1" style="width:50px !important;">
                    <label for="app_parent_kin" style="width:400px">Same as Parent / Sponsor</label>
                </li>
            </div>

            <div class="nameFields">

                <div class="form-field">
                    <label>First Name * <span id="kFirstName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="kFirstName" id="kFirstName" class="required">
                </div>
                <div class="form-field">
                    <label>Last Name * <span id="kLastName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="kLastName" id="kLastName" class="required">
                </div>
            </div>

            <div class="form-field">
                <label>Title * <span id="kTitle_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                <select name="kTitle" id='kTitle' class="required">
                <option value="" disabled selected>Please select one</option>
                    <option value="Mr" >Mr</option>
                    <option value="Ms" >Ms</option>
                    <option value="Mrs" >Mrs</option>
                    <option value="Miss" >Miss</option>
                </select>
            </div>

            <div class="form-field">
                <label>Relationship * <span id="kRelationship_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                <input type="text" name="kRelationship" id="kRelationship" class="required" >
            </div>

            <div class="generalInfo">

                {{-- <div class="form-field">
                    <label>Nationality * <span id="kNationality" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <select name="kinNationality" id='kinNationality' class="required">
                        <option value="" selected disabled>Please select one</option>
                        @foreach($lookups as $lookup)
                            @if($lookup->group_id == "5")
                                <option value="{{$lookup->lookup_name}}" >{{$lookup->lookup_name}}</option>
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
                    <input type="text" name="kCellNo" id='kCellNo' class="required">
                </div>

                <div class="form-field">
                    <label>Email Address</label>
                    <input type="email" name="kEmail" id="kEmail">
                </div>
            </div>

        <div class="form-field">
            <label>Work Number <span id="kWorkNo_error" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
            <input type="text" name="kWorkNo" id="kWorkNo" >
        </div>

        <div class="form-field">
            <label>Telephone Number <span id="kTeleNo_error" class="hidden" style="color:red"><small>Not a valid telephone number</small></span></label>
            <input type="text" name="kTeleNo" id='kTeleNo'>
        </div>

        <div class="addressSection">

            <div class="form-field-big" style="height:50px !important;">
                <label>Physical Address * <span id="kAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            </div>
            <div class="form-field-big" style="height:60px !important">
                <li style="display:inline-flex">
                    <input type="checkbox" id="app_address" name="app_address" value="1" style="width:50px !important;">
                    <label for="app_address" style="width:400px">Same as Applicant's Home Address</label>
                </li>
            </div>
            <div class="kinAddress">
                <div class="form-field-big" style="height:60px !important">
                    <input type="text" placeholder="Street No" name="kAddress1" id="kAddress1" style="width:98% !important" class="required">
                </div>
                <div class="form-field-big" style="height:60px !important">
                <span id="kAddress2_error" class="hidden" style="color:red"><small>This field is required</small></span>
                    <input type="text" placeholder="Suburb" name="kAddress2" id="kAddress2" style="width:98% !important" class="required">
                </div>
                <div class="form-field" style="height:60px !important">
                <span id="kAddressCity_error" class="hidden" style="color:red"><small>This field is required</small></span>
                    <input type="text" placeholder="City" name="kAddressCity" id='kAddressCity' class="required" >
                </div>
                <div class="form-field" style="height:60px !important">
                <span id="kAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal code</small></span>
                    <input type="text" placeholder="Postal Code" name="kAddressPostal" id="kAddressPostal" class="required">
                </div>
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

    var d = new Date()
    month = '' + (d.getMonth() + 1),
    day = '' + d.getDate(),
    yearBack =(d.getFullYear() - 100);

    document.getElementById("dob").setAttribute("min", yearBack+"-"+month+"-"+day);

    $("#property").val("{{$property}}").trigger("change")

    $("#property").on('change', function(){
        let propValue = $("#property").val();

        if(propValue == "Braamlofts"){
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option><option value="3 Sharing">3 Sharing</option>')
        }else if(propValue == "80 Jorissen"){
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "49 Jorissen"){
            $("#roomType").empty().append('<option value="">Select One</option><option value="2 Sharing">2 Sharing</option><option value="2 Sharing 2 Bedroom">2 Sharing | 2 Bedroom</option><option value="Single">Single</option><option value="Single 2 bedroom">Single | 2 Bedroom</option><option value="Single 3 bedroom">Single | 3 Bedroom</option><option value="Single 4 bedroom">Single | 4 Bedroom</option>')
        }else if(propValue == "Dunvista Mansions"){
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "Wynton Joy"){
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "126 Siemert"){
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "YMCA"){
            $("#roomType").empty().append('<option value="">Select One</option><option value="2 Sharing">2 Sharing</option><option value="2 Sharing 2 Bedroom">2 Sharing | 2 Bedroom</option><option value="2 Sharing 3 Bedroom">2 Sharing | 3 Bedroom</option><option value="3 Sharing">3 Sharing</option><option value="Single Small">Single | Small</option><option value="Single Medium">Single | Medium</option><option value="Single Large">Single | Large</option><option value="Single 3 Bedroom">Single | 3 Bedroom</option>')
        }
    })

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

    $("#cell_no").on('keyup blur', function(){
        cell = $("#cell_no").val()
        ValidCell(cell, "cell_no", "cell_no_error");
    })

    $("#userAddressPostal").on('keyup blur', function(){
        postal = $("#userAddressPostal").val()

        if(/\b\d{4}\b/g.test(postal)){
            $("#userAddressPostal").removeClass("fieldError")
            $("#userAddressPostal_error").addClass('hidden')
        }else{
            $("#userAddressPostal").addClass("fieldError")
            $("#userAddressPostal_error").removeClass('hidden')
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

    $("#idNumber").on('keyup blur', function(){
        let idNumber = $("#idNumber").val();
        if(idNumber.length == 13){
            ValidID(idNumber, "idNumber", "idNumber_error")
        }else{
            $("#idNumber").addClass("fieldError")
            $("#idNumber_error").removeClass('hidden')
        }  

        cecheckIDEmail();
    })

    $("#dob").on('keyup blur', function(){
        let dob = $("#dob").val();

        var d = new Date()
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();
        yearBack =(d.getFullYear() - 100);

        if(dob < yearBack+"-01-01" || dob > year+"-"+month+"-"+day){
            $("#dob").addClass("fieldError")
            $("#dob_error").removeClass('hidden')
        }else{
            $("#dob").removeClass("fieldError")
            $("#dob_error").addClass('hidden')
        }
    })

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    $("#email").on('keyup blur', function(){
        if(isEmail($("#email").val())){
            $("#email").removeClass("fieldError")
            $("#email_error").addClass('hidden')
        }else{
            $("#email").addClass("fieldError")
            $("#email_error").removeClass('hidden')
        }

        cecheckIDEmail();
    })

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

    $("#nationality").on('change', function(){
        if($("#nationality").val() == "South Africa"){
            $("#passportField").addClass('hidden')
            $("#idField").removeClass('hidden')

            $("#passportNo").removeClass("required")
            $("#idNumber").addClass("required")
            $("#passportNo").removeClass("fieldError")
        }else{
            $("#idField").addClass('hidden')
            $("#passportField").removeClass('hidden')

            $("#passportNo").addClass("required")
            $("#idNumber").removeClass("required")
            $("#idNumber").removeClass("fieldError")
        }
    })


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

                $("#submitForm").empty().append("LOading")

                $.ajax({
                        type: "post",
                        url: "/registration_step_one",
                        data: {
                            firstName: $("#firstName").val(), 
                            lastName: $("#lastName").val(), 
                            title: $("#title").val(), 
                            dob: $("#dob").val(), 
                            cell_no: $("#cell_no").val(), 
                            email: $("#email").val(), 
                            idNumber: $("#idNumber").val(), 
                            nationality: $("#nationality").val(),
                            passportNo: $("#passportNo").val(), 
                            institStudy: $("#institStudy").val(), 
                            studNumber: $("#studNumber").val(), 
                            faculty: $("#faculty").val(), 
                            year: $("#year").val(),
                            userAddress1: $("#userAddress1").val(),
                            userAddress2: $("#userAddress2").val(),
                            userAddressCity: $("#userAddressCity").val(),
                            userAddressPostal: $("#userAddressPostal").val(),
                            studentType: $("#studentType").val(),
                            yearOfStudy: $("#yearOfStudy").val(),
                        },

                        success: function (data) {
                            window.location.replace('/online-application-verify/'+data)                            
                        },
                    });
            }
        }

    });

</script>


@endsection
