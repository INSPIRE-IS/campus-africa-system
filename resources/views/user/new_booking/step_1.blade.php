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
            <h1 class="booking">New Booking</h1>
        </div>
    
    </div>
</div>

<div class="container form-card">
    <div class="onlineTabs">
        <div class="tab active">1: Applicant Details</div>
        <div class="tab">2: Sponsor/Burser Information</div>
        <div class="tab">3: Next of Kin</div>
        <div class="tab">4: Upload Documents</div>
        <div class="tab">5: Pay Booking Fee</div>
    </div>
    
    <form id="newStudent" class="form onlineForm" autocomplete="off">
        <br/>
        <h1 class="formHeading">Applicant Details</h1>
        <br/>

        <div class="form-field">
            <label>First Name * <span id="firstName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="firstName" id="firstName" class="required" value="<?php if($cancelled == 0){echo $user->userFirstName;}?>">
        </div>

        <div class="form-field">
            <label>Last Name * <span id="lastName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="lastName" id="lastName" class="required" value="<?php if($cancelled == 0){echo $user->userLastname;}?>">
        </div>

        <div class="form-field">
            <label>Title * <span id="title_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="title" id='title' class="required">
                <option value="" disabled selected>Please select one</option>
                <option value="Mr" <?php if($cancelled == 0 && $user->userTitle == "Mr"){ echo "selected";} ?>>Mr</option>
                <option value="Ms" <?php if($cancelled == 0 && $user->userTitle == "Ms"){ echo "selected";} ?>>Ms</option>
                <option value="Mrs" <?php if($cancelled == 0 && $user->userTitle == "Mrs"){ echo "selected";} ?>>Mrs</option>
                <option value="Miss" <?php if($cancelled == 0 && $user->userTitle == "Miss"){ echo "selected";} ?>>Miss</option>
            </select>
        </div>

        <div class="form-field">
            <label>Date of Birth * <span id="dob_error" class="hidden" style="color:red"><small>Not a valid date of birth</small></span></label>
            <input type="date" name="dob" id="dob" class="required" max="<?php echo date('Y-m-d'); ?>" value="<?php if($cancelled == 0){echo $user->userDob;}?>" disabled>
        </div>

        <div class="form-field">
            <label>Cellphone Number * <span id="cell_no_error" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span><span id="cell_no_duplicate" class="hidden" style="color:red"><small>. Duplicate detected!</small></span></label>
            <input type="text" name="userCellCode" id="userCellCode" style="width:18% !important;" value="+27" value="<?php if($cancelled == 0){echo $user->userCellCode;}?>">
            <input type="number" name="cell_no" id='cell_no' style="width:77% !important" class="required" value="<?php if($cancelled == 0){echo $user->userCell;}?>">
        </div>

        <div class="form-field">
            <label>Email Address * <span id="email_error" class="hidden" style="color:red"><small>Not a valid email</small></span>
            <span id="email_InUse" class="hidden" style="color:red"><small>Email Address already in use</small></span></label>
            <input type="email" name="email" id="email" value="<?php if($cancelled == 0){echo $user->userEmail;}?>" disabled>
        </div>

        <div class="form-field">
            <label>Nationality * <span id="nationality_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="nationality" id='nationality' disabled>
                <option value="" selected disabled>Please select one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "5")
                        <option value="{{$lookup->lookup_name}}"  <?php if($user->userNationality == $lookup->lookup_name){ echo "selected";} ?>>{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field <?php if($user->userNationality == "International"){ echo "hidden";}?>" id="idField" style="height:143px !important">
            <label>I.D Number * <span id="idNumber_error" class="hidden" style="color:red"><small>Not a valid ID number</small></span>
            <span id="idNumber_InUse" class="hidden" style="color:red"><small>ID Number already in use - please <a href="<?php echo "https://$_SERVER[HTTP_HOST]/login"; ?>">login to your account</a> or email us at <a href="mailto:info@campusafrica.co.za">info@campusafrica.co.za</a></small></span></label>
            <input type="text" name="idNumber" id="idNumber" value="<?php echo $user->userId;?>" disabled>
        </div>

        <div class="form-field <?php if($user->userNationality !== "International"){ echo "hidden";}?>" id="passportField">
            <label>Passport Number * <span id="passport_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="passportNo" id="passportNo" value="<?php echo $user->userPassport;?>" disabled>
        </div>

        <div class="form-field">
            <label>Gender * <span id="gender_error" class="hidden" style="color:red"><small>This field is
                        required</small></span></label>
            <select name="gender" id='gender' class="required" <?php if($user->userNationality !== "International"){ echo "disabled";}?>>
                <option value="" disabled selected>Please select one</option>
                <option value="1" <?php if($user->gender == "1"){ echo "selected";} ?>>Male</option>
                <option value="2" <?php if($user->gender == "2"){ echo "selected";} ?>>Female</option>
            </select>
        </div>

        <div class="form-field">
            <br/>
        </div>

        <div class="form-field-big">
            <label>Physical Address * <span id="userAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" placeholder="Street No" name="userAddress1" id="userAddress1" style="width:98% " class="required" value="<?php if($cancelled == 0){echo $user->userAddress1;}?>">
        </div>
        <div class="form-field-big" style="height:77px !important">
        <span id="userAddress2_error" class="hidden" style="color:red"><small>This field is required</small></span>
            <input type="text" placeholder="Suburb" name="userAddress2" id="userAddress2" style=" width:98%" class="required" value="<?php if($cancelled == 0){echo $user->userAddress2;}?>">
        </div>
        <div class="form-field" style="margin-top:-15px; height:60px !important">
        <span id="userAddressCity_error" class="hidden" style="color:red"><small>This field is required</small></span>
            <input type="text" placeholder="City" name="userAddressCity" id='userAddressCity' class="required" value="<?php if($cancelled == 0){echo $user->userAddressCity;}?>">
        </div>
        <div class="form-field" style="margin-top:-5px; height:60px !important">
        <span id="userAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal code</small></span>
            <input type="number" placeholder="Postal Code" name="userAddressPostal" id="userAddressPostal" class="required" value="<?php if($cancelled == 0){echo $user->userAddressPostal;}?>">
        </div>

        <div class="form-field">
            <label>Student Type * <span id="studentType_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="studentType" id='studentType' class="required">
                <option value="" selected disabled>Select a Student Type</option>
                <option value="NSFAS" <?php if($cancelled == 0 && $user->studentType == "NSFAS"){ echo "selected";} ?>>NSFAS</option>
                <option value="Bursary" <?php if($cancelled == 0 && $user->studentType == "Bursary"){ echo "selected";} ?>>Bursary</option>
                <option value="Cash / Direct" <?php if($cancelled == 0 && $user->studentType == "Cash / Direct"){ echo "selected";} ?>>Cash</option>
            </select>
        </div>

        <div class="form-field">
            <label>Year * <span id="year_error" class="hidden" style="color:red"><small>This field is required</small></span></label>                
            <select name="year" id="year" class="required">
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "4")
                        <option value="{{$lookup->lookup_name}}" selected>{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label>Institution of Study * <span id="institStudy_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="institStudy" id="institStudy" class="required">
                <option value="" selected disabled>Choose one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "1")
                        <option value="{{$lookup->id}}" <?php if($cancelled == 0 && $user->institutionStudy == $lookup->id){ echo "selected";} ?>>{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label>Student Number * <span id="studNumber_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="studNumber" id="studNumber" class="required" value="<?php if($cancelled == 0){echo $user->studentNo;}?>">
        </div>

        <div class="form-field">
            <label>Year of study * <span id="yearOfStudy_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="yearOfStudy" id="yearOfStudy" class="required">
                <option value="" selected disabled>Please select one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "6")
                        <option value="{{$lookup->lookup_name}}" <?php if($cancelled == 0 && $user->yearOfStudy == $lookup->lookup_name){ echo "selected";} ?>>{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label>Faculty * <span id="faculty_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="faculty" id="faculty" class="required">
                <option value="" disabled selected>Choose one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "3")
                        <option value="{{$lookup->id}}" <?php if($cancelled == 0 && $user->faculty == $lookup->id){ echo "selected";} ?>>{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label>Residence * <span id="property_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="property" id='property' class="required">
                <option value="" selected disabled>Select a Residence</option>
                    @foreach($residences as $property)

                            @if($user->studentType == "Cash / Direct" && in_array($property->id, $inSelectedCash))
                                @if($user->gender == 1 && $property->male === null && $property->female == 1)
                                    <option value="{{$property->id}}" disabled>{{$property->residence}} (Female Only)</option>
                                @elseif($user->gender == 2 && $property->male == 1 && $property->female === null)
                                    <option value="{{$property->id}}" disabled>{{$property->residence}} (Male Only)</option>
                                @elseif($property->male === null && $property->female === null)
                                    <option value="{{$property->id}}" disabled>{{$property->residence}} (Not Allowed)</option>
                                @else
                                    <option value="{{$property->id}}">{{$property->residence}}</option>
                                @endif
                            @elseif($user->studentType == "Bursary" && in_array($property->id, $inSelectedBursary))
                                @if($user->gender == 1 && $property->male === null && $property->female == 1)
                                    <option value="{{$property->id}}" disabled>{{$property->residence}} (Female Only)</option>
                                @elseif($user->gender == 2 && $property->male == 1 && $property->female === null)
                                    <option value="{{$property->id}}" disabled>{{$property->residence}} (Male Only)</option>
                                @elseif($property->male === null && $property->female === null)
                                    <option value="{{$property->id}}" disabled>{{$property->residence}} (Not Allowed)</option>
                                @else
                                    <option value="{{$property->id}}">{{$property->residence}}</option>
                                @endif
                            @elseif($user->studentType == "NSFAS" && in_array($property->id, $inSelectedNsfas))
                                @if($user->gender == 1 && $property->male === null && $property->female == 1)
                                    <option value="{{$property->id}}" disabled>{{$property->residence}} (Female Only)</option>
                                @elseif($user->gender == 2 && $property->male == 1 && $property->female === null)
                                    <option value="{{$property->id}}" disabled>{{$property->residence}} (Male Only)</option>
                                @elseif($property->male === null && $property->female === null)
                                    <option value="{{$property->id}}" disabled>{{$property->residence}} (Not Allowed)</option>
                                @else
                                    <option value="{{$property->id}}">{{$property->residence}}</option>
                                @endif
                            @else
                                <option value="{{$property->id}}" disabled>{{$property->residence}} (Not Allowed)</option>
                            @endif

                    @endforeach
            </select>
        </div>

        <div class="form-field">
            <label>Room Type * <span id="roomType_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="roomType" id='roomType' class="required">
                <option value="" selected disabled>Select a Room Type</option>
            </select>
        </div>

        <button type="button" class="detailsChange" id="submitForm" style="text-transform:uppercase; background-color:#e74f26">Proceed to next step</button>


    </form>

</div>


<br/><br/><br/>


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

    $("#property").on('change', function(){
        $("#roomType").empty().append('<option value="" selected disabled>Loading</option>')

            $.ajax({
                type: "post",
                url: "/get-room-ids",
                data: {residence: $("#property").val()},

                success: function (data) {          
                    $("#roomType").empty().append('<option value="" selected disabled>Select a Room Type</option>')
                    data.forEach(function(index){
                        var selected = ""
                        var disabled = ""
                        var disabledRoom = ""

                        if(index.online == '0'){
                            var disabled ="disabled"
                            var disabledRoom = "(Fully Booked)"
                            var selected = ""
                        }

                                                
                        $("#roomType").append('<option value="'+index.id+'" '+selected+' '+disabled+'>'+index.name+' '+disabledRoom+'</option>')
                    })

                },
            });
    })

    $("input").blur(function(){
        let fieldId = $(this)[0]["id"]

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
        ValidCell(cell, "cell_no", "cell_no_error", $("#userCellCode").val());
    })
    $("#userCellCode").on('keyup blur', function(){
        cell = $("#cell_no").val()
        ValidCell(cell, "cell_no", "cell_no_error", $("#userCellCode").val());
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
    }

    $("#nationality").on('change', function(){
        if($("#nationality").val() == "South Africa"){
            $("#passportField").addClass('hidden')
            $("#idField").removeClass('hidden')

            $("#passportNo").removeClass("required")
            $("#idNumber").addClass("required")
            $("#passportNo").removeClass("fieldError")
            document.getElementById('gender').disabled = true;
        }else{
            $("#idField").addClass('hidden')
            $("#passportField").removeClass('hidden')

            $("#passportNo").addClass("required")
            $("#idNumber").removeClass("required")
            $("#idNumber").removeClass("fieldError")
            document.getElementById('gender').disabled = false;
        }
    })

    $("#studentType").on('change', function() {
        // if ($("#studentType").val() == "NSFAS") {
        //     nsfasProp()
        // } else {
        //     allowAllProp()
        // }
        allowAllProp();
    })

    $("#institStudy").on('change', function(){
        // if ($("#studentType").val() == "NSFAS") {
        //     nsfasProp()
        // }
        allowAllProp();
    })

    $("#gender").on('change', function(){
        // if($("#studentType").val() !== ""){
        //     if ($("#studentType").val() == "NSFAS") {
        //         nsfasProp()
        //     } else {
        //         allowAllProp()
        //     }
        // }else{
        //     allowAllProp()
        // }
        allowAllProp()
    })

    function allowAllProp(){
        $("#property").empty().append('<option value="" selected disabled>Loading</option>')

        $.ajax({
            type: "post",
            url: "/get-nsfas-properties-online",
            data: {institStudy: $("#institStudy").val()},

            success: function(data) {

                allprops = data[0];
                
                inSelectedCash = data[1];
                inSelectedBursary = data[2];
                inSelectedNsfas = data[3];

                $("#property").empty().append('<option value="" selected disabled>Select a Residence</option>')

                let text = "";
                let allow = 1;

                if($("#gender").val() == ""){
                    text = "(No gender selected)"
                    allow = 0
                }

                if($("#institStudy").val() == "" || $("#institStudy").val() == null){
                    allprops.forEach(function(index) {
                        $("#property").append('<option value="' + index.id + '" disabled>' + index.residence +
                            ' (No Institution Selected)</option>')
                    })
                }else{
                    allprops.forEach(function(index) {

                        if($("#gender").val() == 1 && index.male === null && index.female === 1){
                            text = "(Female Only)"
                            allow = 0
                        }else if($("#gender").val() == 2 && index.male === 1 && index.female === null){
                            text = "(Male Only)"
                            allow = 0
                        }else if(index.male === null && index.female === null){
                            text = "(Not Allowed)"
                            allow = 0
                        }else{
                            allow = 1
                            text = "";
                        }

                        if($("#studentType").val() == "Cash / Direct" && inSelectedCash.includes(index.id)){
                            if(allow == 0){
                                $("#property").append('<option value="' + index.id + '" disabled>' + index.residence + ' '+text+'</option>')
                            }else{
                                $("#property").append('<option value="' + index.id + '">' + index.residence + '</option>')
                            }
                        }else if($("#studentType").val() == "Bursary" && inSelectedBursary.includes(index.id)){
                            if(allow == 0){
                                $("#property").append('<option value="' + index.id + '" disabled>' + index.residence + ' '+text+'</option>')
                            }else{
                                $("#property").append('<option value="' + index.id + '">' + index.residence + '</option>')
                            }
                        }else if($("#studentType").val() == "NSFAS" && inSelectedNsfas.includes(index.id)){
                            if(allow == 0){
                                $("#property").append('<option value="' + index.id + '" disabled>' + index.residence + ' '+text+'</option>')
                            }else{
                                $("#property").append('<option value="' + index.id + '">' + index.residence + '</option>')
                            }
                        }else{
                            $("#property").append('<option value="' + index.id + '" disabled>' + index.residence +' (Not Allowed)</option>')
                        }

                    })
                }
            },
        });

        // $.ajax({
        //     type: "get",
        //     url: "/get-all-properties-online",

        //     success: function(data) {
        //         $("#property").empty().append('<option value="" selected disabled>Select a Residence</option>')

        //         let text = "";
        //         let allow = 1;

        //         if($("#gender").val() == ""){
        //             text = "(No gender selected)"
        //             allow = 0
        //         }

        //         data.forEach(function(index) {
        //             if($("#gender").val() == 1 && index.male === null && index.female === 1){
        //                 text = "(Female Only)"
        //                 allow = 0
        //             }else if($("#gender").val() == 2 && index.male === 1 && index.female === null){
        //                 text = "(Male Only)"
        //                 allow = 0
        //             }else if(index.male === null && index.female === null){
        //                 text = "(Not Allowed)"
        //                 allow = 0
        //             }else{
        //                 allow = 1
        //                 text = "";
        //             }

        //             if(allow == 0){
        //                 $("#property").append('<option value="' + index.id + '" disabled>' + index.residence + ' '+text+'</option>')
        //             }else{
        //                 $("#property").append('<option value="' + index.id + '">' + index.residence + '</option>')
        //             }
        //         })
        //     },
        // });
    }

    // function nsfasProp(){
    //     $("#property").empty().append('<option value="" selected disabled>Loading</option>')

    //     $.ajax({
    //         type: "post",
    //         url: "/get-nsfas-properties-online",
    //         data: {institStudy: $("#institStudy").val()},

    //         success: function(data) {

    //             allprops = data[0];
    //             allowed = data[1];
    //             $("#property").empty().append('<option value="" selected disabled>Select a Residence</option>')

    //             let text = "";
    //             let allow = 1;

    //             if($("#gender").val() == ""){
    //                 text = "(No gender selected)"
    //                 allow = 0
    //             }

    //             if($("#institStudy").val() == "" || $("#institStudy").val() == null){
    //                 allprops.forEach(function(index) {
    //                     $("#property").append('<option value="' + index.id + '" disabled>' + index.residence +
    //                         ' (No Institution Selected)</option>')
    //                 })
    //             }else{
    //                 allprops.forEach(function(index) {
    //                     if($("#gender").val() == 1 && index.male === null && index.female === 1){
    //                         text = "(Female Only)"
    //                         allow = 0
    //                     }else if($("#gender").val() == 2 && index.male === 1 && index.female === null){
    //                         text = "(Male Only)"
    //                         allow = 0
    //                     }else if(index.male === null && index.female === null){
    //                         text = "(Not Allowed)"
    //                         allow = 0
    //                     }else{
    //                         allow = 1
    //                         text = "";
    //                     }

    //                     if(allowed.includes(index.id)){
    //                         if(allow == 0){
    //                             $("#property").append('<option value="' + index.id + '" disabled>' + index.residence + ' '+text+'</option>')
    //                         }else{
    //                             $("#property").append('<option value="' + index.id + '">' + index.residence + '</option>')
    //                         }
    //                     }else{
    //                         $("#property").append('<option value="' + index.id + '" disabled>' + index.residence +' (Not Allowed)</option>')
    //                     }
    //                 })
    //             }
    //         },
    //     });
    // }


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

                console.log(id)
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

                Swal.fire({
                    title: 'Please accept the POPI Act to continue',
                    text: "Consolidated Urban Management Corporation (Pty) Ltd and it's associated companies, which include the Campus Africa & 49 Jorissen brands, is compliant with the Protection of Personal Information Act (POPIA), also known as the POPI Act, which is mandatory for all organisations in South Africa. POPI makes it illegal for us to collect, use or store the personal information of customers and businesses unless it is done in accordance with the laws and regulations prescribed in the POPI Act.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Accept'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#submitForm").attr('disabled', 'disabled');
                        $.ajax({
                            type: "post",
                            url: "/new-booking-step-1-save",
                            data: {
                                firstName: $("#firstName").val(), 
                                lastName: $("#lastName").val(), 
                                title: $("#title").val(), 
                                cell_no: $("#cell_no").val(), 
                                userCellCode: $("#userCellCode").val(),
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
                                property: $("#property").val(),
                                roomType: $("#roomType").val(),
                                gender: $("#gender").val(),
                            },
                            success: function (data) {
                                window.location.replace('/new-booking-step-2')   
                            },
                        });
                    }else{
                        $("#submitForm").empty().append('Proceed to next step');
                    }
                });

                
            }
        }

    });

</script>


@endsection
