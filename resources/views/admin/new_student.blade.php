@extends('layouts.admin')

@section('content')

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container form-card">

    <form action="/add-student" method="post" id="newUser" class="form" autocomplete="off">
    @csrf
        <br/>
        <br/>

        <div class="form-field">
            <label>First Name * <span id="firstName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="firstName" id="firstName" class="required">
        </div>

        <div class="form-field">
            <label>Last Name * <span id="lastName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="lastName" id="lastName" class="required">
        </div>

        <div class="form-field">
            <label>Title * <span id="title_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="title" id='title' class="required">
                <option value="" disabled selected>Please select one</option>
                <option value="Mr" >Mr</option>
                <option value="Ms" >Ms</option>
                <option value="Mrs" >Mrs</option>
                <option value="Miss" >Miss</option>
            </select>
        </div>

        <div class="form-field">
            <label>Date of Birth * <span id="dob_error" class="hidden" style="color:red"><small>Not a valid date of birth</small></span></label>
            <input type="date" name="dob" id="dob" class="required" max="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="form-field">
            <label>Nationality * <span id="nationality_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="nationality" id='nationality' class="required">
                <option value="" selected disabled>Please select one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "5")
                        <option value="{{$lookup->lookup_name}}" >{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field" id="idField">
            <label>I.D Number * 
                <span id="idNumber_error" class="hidden" style="color:red"><small>Not a valid ID number</small></span>
                <span id="idNumber_InUse_cancelled" class="hidden" style="color:red"><small>a CANCELLED booking with this ID number exists - click <a id="toViewCancelled" href="" target="_blank" style="cursor:pointer">here</a> to view</small></span>
                <span id="idNumber_InUse_active" class="hidden" style="color:red"><small>an ACTIVE booking with this ID number exists - Please contact the building manager at <span id="activeBuildingName"></span> to move this booking OR advise the student that they can login and change the building</small></span>
            </label>
            <input type="text" name="idNumber" id="idNumber" class="required">
        </div>

        <div class="form-field hidden" id="passportField">
            <label>Passport Number * <span id="passport_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="passportNo" id="passportNo" >
        </div>

        <div class="form-field">
            <label>Cellphone Number * <span id="cell_no_error" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span><span id="cell_no_duplicate" class="hidden" style="color:red"><small>. Duplicate detected!</small></span></label>
            <input type="text" name="cell_no" id="cell_no" class="required" value="+27">
        </div>

        <div class="form-field">
            <label>Email Address * <span id="email_error" class="hidden" style="color:red"><small>Not a valid email</small></span>
            <span id="email_InUse" class="hidden" style="color:red"><small>Email Address already in use</small></span></label>
            <input type="email" name="email" id="email" class="required" >
        </div>

        <div class="form-field-big">
            <label>Physical Address * <span id="userAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" placeholder="Street No" name="userAddress1" id="userAddress1" style="width:98% !important" class="required">
        </div>
        <div class="form-field-big" style="height:77px !important">
        <span id="userAddress2_error" class="hidden" style="color:red"><small>This field is required</small></span>
            <input type="text" placeholder="Suburb" name="userAddress2" id="userAddress2" style="width:98% !important" class="required">
        </div>
        <div class="form-field" style="margin-top:-15px; height:60px !important">
        <span id="userAddressCity_error" class="hidden" style="color:red"><small>This field is required</small></span>
            <input type="text" placeholder="City" name="userAddressCity" id='userAddressCity' class="required" >
        </div>
        <div class="form-field" style="margin-top:-15px; height:60px !important">
        <span id="userAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal code</small></span>
            <input type="text" placeholder="Postal Code" name="userAddressPostal" id="userAddressPostal" class="required">
        </div>

        <div class="form-field">
            <label>Student Type * <span id="studentType_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="studentType" id='studentType' class="required">
                <option value="" selected disabled>Select a Student Type</option>
                <option value="NSFAS">NSFAS</option>
                <option value="Bursary">Bursary</option>
                <option value="Cash / Direct">Cash</option>
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
                        <option value="{{$lookup->id}}" >{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label>Student Number * <span id="studNumber_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="studNumber" id="studNumber" class="required" >
        </div>

        <div class="form-field">
            <label>Year of study * <span id="yearOfStudy_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="yearOfStudy" id="yearOfStudy" class="required">
                <option value="" selected disabled>Please select one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "6")
                        <option value="{{$lookup->lookup_name}}" >{{$lookup->lookup_name}}</option>
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
                        <option value="{{$lookup->id}}" >{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>


        <div class="form-field">
            <label>Residence * <span id="property_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="property" id='property' class="required">
                <option value="" selected disabled>Select a Residence</option>
                @foreach($PropertyList as $property)
                    <option value="{{$property->id}}">{{$property->residence}}</option>
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label>Room Type * <span id="roomType_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="roomType" id='roomType' class="required">
                <option value="" selected disabled>Select a Room Type</option>
            </select>
        </div>

        <div id="sponsorDetails" class="hidden">

                <div class="form-field">
                    <br/>
                </div>
                <div class="form-field">
                    <br/>
                </div>

                <h1 class="fs-title">Sponsor Information</h2>
                <h2 class="fs-subtitle">Parent / guardians details</h4>
                <br/>

                <div class="form-field">
                    <label>First Name * <span id="pFirstName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="pFirstName" id="pFirstName" >
                </div>

                <div class="form-field">
                    <label>Last Name * <span id="pLastName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="pLastName" id="pLastName" >
                </div>

                <div class="form-field">
                    <label>Cellphone Number * <span id="pCell_no_error" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
                    <input type="text" name="pCell_no" id="pCell_no" value="+27">
                </div>

                <div class="form-field">
                    <label>Email Address * <span id="pEmail_error" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
                    <input type="email" name="pEmail" id='pEmail' >
                </div>

                <div class="form-field">
                    <label>Nationality * <span id="pNationality_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <select name="pNationality" id='pNationality'>
                        <option value="" selected disabled>Please select one</option>
                        @foreach($lookups as $lookup)
                            @if($lookup->group_id == "5")
                                <option value="{{$lookup->lookup_name}}" >{{$lookup->lookup_name}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-field" id="pIdField">
                    <label>I.D Number * <span id="pIdNumber_error" class="hidden" style="color:red"><small>Not a valid ID number</small></span></label>
                    <input type="text" name="pIdNumber" id="pIdNumber" >
                </div>

                <div class="form-field hidden" id="pPassportField">
                    <label>Passport Number * <span id="pPassportNo_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="pPassportNo" id="pPassportNo" >
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
                        <input type="checkbox" id="app_address_p" name="app_address_p" value="1" style="width:50px !important;">
                        <label for="app_address_p" style="width:400px">Same as Applicant's Home Address</label>
                    </li>
                </div>

                <div class="pAddress">
                    <div class="form-field-big" style="height:60px !important">
                        <input type="text" placeholder="Street No" name="pAddress1" id="pAddress1" style="width:98% !important" class="required">
                    </div>
                    <div class="form-field-big" style="height:60px !important">
                    <span id="pAddress2_error" class="hidden" style="color:red"><small>This field is required</small></span>
                        <input type="text" placeholder="Suburb" name="pAddress2" id="pAddress2" style="width:98% !important" >
                    </div>
                    <div class="form-field" style="height:60px !important">
                    <span id="pAddressCity_error" class="hidden" style="color:red"><small>This field is required</small></span>
                        <input type="text" placeholder="City" name="pAddressCity" id='pAddressCity'  >
                    </div>
                    <div class="form-field" style="height:60px !important">
                    <span id="pAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal code</small></span>
                        <input type="text" placeholder="Postal Code" name="pAddressPostal" id="pAddressPostal" >
                    </div>
                </div>

            </div>
            <div id="bursarDetails" class="hidden">

            <div class="form-field">
                    <br/>
                </div>
                <div class="form-field">
                    <br/>
                </div>

                <h1 class="fs-title">Bursar Information</h2>
                <br/>

                <div class="form-field">
                    <label>Company Name * <span id="bCompanyName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="bCompanyName" id='bCompanyName' >
                </div>

                <div class="form-field">
                    <label>Registration Number * <span id="bRegistrationNo_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="bRegistrationNo" id="bRegistrationNo" >
                </div>

                <div class="form-field">
                    <label>Contact Person * <span id="bContactperson_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="bContactPerson" id="bContactPerson" >
                </div>

                <div class="form-field">
                    <label>Contact Number * <span id="bContactNumber_error" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
                    <input type="text" name="bContactNumber" id="bContactNumber" value="+27">
                </div>

                <div class="form-field">
                    <label>Email Address * <span id="bEmail_error" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
                    <input type="email" name="bEmail" id="bEmail" >
                </div>

                <div class="form-field">
                    <!-- <ul style="list-style-type:none">
                        <li style="display:inline-flex; margin-top:30px;">
                            <input type="checkbox" id="nsfas_bursar" name="nsfas_bursar" value="1" style="width:50px !important">
                            <label for="nsfas_bursar" style="width:500px">NSFAS bursar?</label>
                        </li>
                    </ul> -->
                    <br/>
                </div>

                <div class="form-field-big">
                    <label>Physical Address * <span id="bAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" placeholder="Street No" name="bAddress1" id="bAddress1" style="width:98% !important">
                </div>
                <div class="form-field-big" style="height:60px !important">
                <span id="bAddress2_error" class="hidden" style="color:red"><small>This field is required</small></span>
                    <input type="text" placeholder="Suburb" name="bAddress2" id="bAddress2" style="width:98% !important" >
                </div>
                <div class="form-field" style="height:60px !important">
                <span id="bAddressCity_error" class="hidden" style="color:red"><small>This field is required</small></span>
                    <input type="text" placeholder="City" name="bAddressCity" id='bAddressCity'  >
                </div>
                <div class="form-field" style="height:60px !important">
                <span id="bAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal code</small></span>
                    <input type="text" placeholder="Postal Code" name="bAddressPostal" id="bAddressPostal" >
                </div>

                

                <div class="form-field">
                    <label>How Is the Bursary Paid * <span id="bPayment_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <select name="bPayment" id='bPayment'>
                    <option value="" disabled selected>Please select one</option>
                        <option value="Directly to the RESIDENCE" >Directly to the RESIDENCE</option>
                        <option value="Directly to the STUDENT" >Directly to the STUDENT</option>
                        <option value="Via an INTERMEDIARY" >Via an INTERMEDIARY</option>
                    </select>
                </div>

                <div class="form-field" id="placeholder">
                    <br/>
                </div>

                <div id="viaInt" class="hidden">

                    <div class="form-field">
                        <label>Intermediary * <span id="bint_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                        <select name="bint" id='bint'>
                        <option value="" disabled selected>Please select one</option>
                            <option value="Fundi" >Fundi</option>
                            <option value="Intellicard" >Intellicard</option>
                            <option value="Other" >Other</option>
                        </select>
                    </div>
                </div>

                <div id="spesificInt" class="hidden">

                    <div class="form-field">
                        <label>Specify Intermediary Name * <span id="bIntName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                        <input type="text" name="bIntName" id="bIntName">
                    </div>

                    <div class="form-field">
                        <br/>
                    </div>
                </div>

                

        </div>

            <div class="form-field">
                <br/>
            </div>
            <div class="form-field">
                <br/>
            </div>

            <h1 class="fs-title">Next of Kin</h2>

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
                    <input type="text" name="kCellNo" id='kCellNo' class="required" value="+27">
                </div>

                <div class="form-field">
                    <label>Email Address</label>
                    <input type="email" name="kEmail" id="kEmail">
                </div>
            </div>

        <div class="form-field">
            <label>Work Number <span id="kWorkNo_error" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
            <input type="text" name="kWorkNo" id="kWorkNo" value="+27">
        </div>

        <div class="form-field">
            <label>Telephone Number <span id="kTeleNo_error" class="hidden" style="color:red"><small>Not a valid telephone number</small></span></label>
            <input type="text" name="kTeleNo" id='kTeleNo' value="+27">
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

        <div class="form-field" style="height:60px !important">
            <br/>
        </div>

        <input type="submit" value="Save" class="profileSubmit" style="opacity:1;"/>
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

        // var required = document.getElementById(fieldId).required
        var required = $("#"+fieldId).hasClass('required')

        if($("#"+fieldId).val() == null && required == true){
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
            $("#idNumber_InUse_cancelled").addClass('hidden')
            $("#idNumber_InUse_active").addClass('hidden')
            $("#idField").removeClass('idHeight');
        }  
    })

    $("#pIdNumber").on('keyup blur', function(){
        let idNumber = $("#pIdNumber").val();

        if(idNumber.length == 13){
            ValidID(idNumber, "pIdNumber", "pIdNumber_error")
        }else{
            $("#pIdNumber").addClass("fieldError")
            $("#pIdNumber_error").removeClass('hidden')
        }

    })

    /*
    $("#kIdNumber").on('keyup blur', function(){
        let idNumber = $("#kIdNumber").val();

        if(idNumber.length == 13){
            ValidID(idNumber, "kIdNumber", "kIdNumber_error")
        }else{
            $("#kIdNumber").addClass("fieldError")
            $("#kIdNumber_error").removeClass('hidden')
        }

    })
    */

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

    function ValidCell(cell, fieldId, fieldError){
        let cellPrefix = cell.slice(0, 3)
        if(cellPrefix === "+27"){
            console.log(/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell))
            if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
                $("#"+fieldId).removeClass("fieldError")
                $("#"+fieldError).addClass('hidden')
            }else{
                $("#"+fieldId).addClass("fieldError")
                $("#"+fieldError).removeClass('hidden')
            }
        }else if (/^((\+)[0-9]{1,3})[1-9](\d{2}){4}$/.test(cell)) {
            $("#"+fieldId).removeClass("fieldError")
            $("#"+fieldError).addClass('hidden')
        }else{
            $("#"+fieldId).addClass("fieldError")
            $("#"+fieldError).removeClass('hidden')
        }
    }

    function duplicateCell(){
        $('#kCellNo_duplicate').addClass('hidden');
        $('#cell_no_duplicate').addClass('hidden');
        $('#cell_no, #kCellNo').removeClass('fieldDuplicate');

        if ($('#cell_no').val() != "" && $('#cell_no').val() == $('#kCellNo').val()) {
            $('#kCellNo_duplicate').removeClass('hidden');
            $('#cell_no_duplicate').removeClass('hidden');

            $('#cell_no_error').removeClass('hidden');
            $('#kCellNo_error').removeClass('hidden');

            $('#cell_no').addClass('fieldError');
            $('#kCellNo').addClass('fieldError');

            $('#cell_no').addClass('fieldDuplicate');
            $('#kCellNo').addClass('fieldDuplicate');
        } else {
            cell = $("#cell_no").val()
            ValidCell(cell, "cell_no", "cell_no_error");
            
            cell = $("#kCellNo").val();            
            ValidCell(cell, "kCellNo", "kCellNo_error");
        }
    }
    

    $("#cell_no").on('keyup blur', function(){
        cell = $("#cell_no").val()
        ValidCell(cell, "cell_no", "cell_no_error");
        duplicateCell();
    })

    $("#pCell_no").on('keyup blur', function(){
        cell = $("#pCell_no").val()
        ValidCell(cell, "pCell_no", "pCell_no_error")
    })

    $("#bContactNumber").on('keyup blur', function(){
        cell = $("#bContactNumber").val()
        ValidCell(cell, "bContactNumber", "bContactNumber_error")
    })

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
        duplicateCell();
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
    })

    $("#email, #idNumber").on('blur', function(){
        $.ajax({
            type: "post",
            url: "/email-check",
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
                            if(data[3]['rob_60_cancelled'] == '1'){
                                $("#idNumber_InUse_cancelled").removeClass('hidden')
                                $("#idNumber").addClass('fieldError')
                                document.getElementById('toViewCancelled').href = "/application-profile/"+data[3]['id'];
                            }else{

                                $("#idNumber_InUse_active").removeClass('hidden')
                                $("#idNumber").addClass('fieldError')
                                $("#activeBuildingName").empty().append(data[4]);
                                $("#idField").addClass('idHeight');
                            }
                        }else{
                            $("#idNumber_InUse").addClass('hidden')
                            $("#idNumber").removeClass('fieldError')
                            $("#idField").removeClass('idHeight');
                        }
                    }


                }
            },
        });
    })

    $("#pEmail").on('keyup blur', function(){
        if(isEmail($("#pEmail").val())){
            $("#pEmail").removeClass("fieldError")
            $("#pEmail_error").addClass('hidden')
        }else{
            $("#pEmail").addClass("fieldError")
            $("#pEmail_error").removeClass('hidden')
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

    /*
    $("#kinNationality").on('change', function(){
        if($("#kinNationality").val() == "South Africa"){
            $("#kinPassportField").addClass('hidden')
            $("#kinIdField").removeClass('hidden')

            //$("#kinPassportNo").removeClass("required")
            //$("#kIdNumber").addClass("required")
            //$("#kinPassportNo").removeClass("fieldError")
        }else{
            $("#kinIdField").addClass('hidden')
            $("#kinPassportField").removeClass('hidden')

            //$("#kinPassportNo").addClass("required")
            //$("#kIdNumber").removeClass("required")
            //$("#kIdNumber").removeClass("fieldError")
        }
    })
    */

    $("#pNationality").on('change', function(){
        console.log($("#pNationality").val())
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

    function parentDetails(value){

        if(value == true){
            $("#pFirstName").addClass('required')
            $("#pLastName").addClass('required')
            $("#pCell_no").addClass('required')
            $("#pEmail").addClass('required')
            $("#pNationality").addClass('required')
            $("#pAddress1").addClass('required')
            $("#pAddress2").addClass('required')
            $("#pAddressCity").addClass('required')
            $("#pAddressPostal").addClass('required')
        }else{
            $("#pFirstName").removeClass('required')
            $("#pLastName").removeClass('required')
            $("#pCell_no").removeClass('required')
            $("#pEmail").removeClass('required')
            $("#pNationality").removeClass('required')
            $("#pAddress1").removeClass('required')
            $("#pAddress2").removeClass('required')
            $("#pAddressCity").removeClass('required')
            $("#pAddressPostal").removeClass('required')
        }

    }

    function sponsorDetails(value){

        if(value == true){
            $("#bCompanyName").addClass('required')
            $("#bRegistrationNo").addClass('required')
            $("#bContactPerson").addClass('required')
            $("#bContactNumber").addClass('required')
            $("#bEmail").addClass('required')

            $("#bAddress1").addClass('required')
            $("#bAddress2").addClass('required')
            $("#bAddressCity").addClass('required')
            $("#bAddressPostal").addClass('required')
            $("#bPayment").addClass('required')

        }else{
            $("#bCompanyName").removeClass('required')
            $("#bRegistrationNo").removeClass('required')
            $("#bContactPerson").removeClass('required')
            $("#bContactNumber").removeClass('required')
            $("#bEmail").removeClass('required')

            $("#bAddress1").removeClass('required')
            $("#bAddress2").removeClass('required')
            $("#bAddressCity").removeClass('required')
            $("#bAddressPostal").removeClass('required')
            $("#bPayment").removeClass('required')
        }
    }

    $("#studentType").on('change', function(){
        if($("#studentType").val() == "Cash / Direct"){
            $("#sponsorDetails").removeClass('hidden')
            $("#bursarDetails").addClass('hidden')
            $("#parentSameInfo").removeClass('hidden')

            parentDetails(true)
            sponsorDetails(false)

        }else if($("#studentType").val() == "Bursary"){
            $("#bursarDetails").removeClass('hidden')
            $("#sponsorDetails").addClass('hidden')
            $("#parentSameInfo").addClass('hidden')

            parentDetails(false)
            sponsorDetails(true)
        
        }else{
            $("#sponsorDetails").addClass('hidden')
            $("#bursarDetails").addClass('hidden')
            $("#parentSameInfo").addClass('hidden')

            parentDetails(false)
            sponsorDetails(false)
        }
    })

    $("#property").on('change', function(){
        $("#roomType").empty().append('<option value="" selected disabled>Loading</option>')

            $.ajax({
                type: "post",
                url: "/get-room-types-id",
                data: {residence: $("#property").val()},

                success: function (data) {          
                    $("#roomType").empty().append('<option value="" selected disabled>Select a Room Type</option>')
                    data.forEach(function(index){
                        $("#roomType").append('<option value="'+index.id+'">'+index.name+'</option>')
                    })
                },
            });
    })

    $("#app_address").on('change', function(){

        if(document.getElementById('app_address').checked == true){
            $(".kinAddress").addClass("hidden");
        }else{
            $(".kinAddress").removeClass('hidden')
        }
    })

    $("#app_address_p").on('change', function(){

        if(document.getElementById('app_address_p').checked == true){
            $(".pAddress").addClass("hidden");
        }else{
            $(".pAddress").removeClass('hidden')
        }
    })

    $("#app_parent_kin").on('change', function(){

        if(document.getElementById('app_parent_kin').checked == true){

            $(".nameFields").addClass('hidden')
            $(".generalInfo").addClass('hidden')
            $(".addressSection").addClass('hidden')
            
        }else{

            $(".nameFields").removeClass('hidden')
            $(".generalInfo").removeClass('hidden')
            $(".addressSection").removeClass('hidden')
            
        }
    })


    $('#newUser').submit(function() {

        if(document.getElementById('app_parent_kin').checked == true){
            $("#kFirstName").removeClass('fieldError').empty().val($("#pFirstName").val())
            $("#kLastName").removeClass('fieldError').empty().val($("#pLastName").val())
            $("#kCellNo").removeClass('fieldError').empty().val($("#pCell_no").val())
            $("#kEmail").removeClass('fieldError').empty().val($("#pEmail").val())
            let element = document.getElementById("pNationality");
            element.value = $("#pNationality").val();


            if($("#pNationality").val() == "South Africa"){
                $("#passportField").addClass('hidden')
                $("#idField").removeClass('hidden')

                //$("#kinPassportNo").removeClass("required")
                //$("#kIdNumber").addClass("required")
            }else{
                $("#idField").addClass('hidden')
                $("#passportField").removeClass('hidden')

                //$("#kinPassportNo").addClass("required")
                //$("#kIdNumber").removeClass("required")
            }

            //$("#kIdNumber").removeClass('fieldError').empty().val($("#pIdNumber").val())
            //$("#kinPassportNo").removeClass('fieldError').empty().val($("#pPassportNo").val())

            if(document.getElementById('app_address_p').checked == true){
                document.getElementById('app_address').checked = true;
                $(".kinAddress").addClass("hidden");
                $("#kAddress1").removeClass('fieldError').empty().val($("#userAddress1").val())
                $("#kAddress2").removeClass('fieldError').empty().val($("#userAddress2").val())
                $("#kAddressCity").removeClass('fieldError').empty().val($("#userAddressCity").val())
                $("#kAddressPostal").removeClass('fieldError').empty().val($("#userAddressPostal").val())
            }else{
                $("#kAddress1").empty().val($("#pAddress1").val())
                $("#kAddress2").empty().val($("#pAddress2").val())
                $("#kAddressCity").empty().val($("#pAddressCity").val())
                $("#kAddressPostal").empty().val($("#pAddressPostal").val())
            }
        }

        if(document.getElementById('app_address').checked == true){
            $("#kAddress1").removeClass('fieldError').empty().val($("#userAddress1").val())
            $("#kAddress2").removeClass('fieldError').empty().val($("#userAddress2").val())
            $("#kAddressCity").removeClass('fieldError').empty().val($("#userAddressCity").val())
            $("#kAddressPostal").removeClass('fieldError').empty().val($("#userAddressPostal").val())
        }

        if(document.getElementById('app_address_p').checked == true){
            $("#pAddress1").removeClass('fieldError').empty().val($("#userAddress1").val())
            $("#pAddress2").removeClass('fieldError').empty().val($("#userAddress2").val())
            $("#pAddressCity").removeClass('fieldError').empty().val($("#userAddressCity").val())
            $("#pAddressPostal").removeClass('fieldError').empty().val($("#userAddressPostal").val())
        }

        

         //Check if any hidden fields are required, remove required check
         $('.hidden .fieldError').removeClass('fieldError').val('');


        var req = 1;

        $('.required').each( function(){
            if($(this).val() === "" || $(this).val() === null){
                req = 0
                console.log($(this)[0]["id"])

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

            return false;
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

                return false;
            }else{
                return true;
            }
        }

        

        // if($("#cell_no").hasClass('fieldError') == true || $("#email").hasClass('fieldError') == true || $("#idNumber").hasClass('fieldError') == true || $("#kIdNumber").hasClass('fieldError') == true || $("#kCellNo").hasClass('fieldError') == true || $("#kEmail").hasClass('fieldError') == true){

        //     if($("#cell_no").hasClass('fieldError') == true){
        //         $("#cell_no").focus();
        //     }else if($("#email").hasClass('fieldError') == true){
        //         $("#email").focus();
        //     }else if($("#idNumber").hasClass('fieldError') == true){
        //         $("#idNumber").focus();
        //     }else if($("#kIdNumber").hasClass('fieldError') == true){
        //         $("#kIdNumber").focus();
        //     }else if($("#kCellNo").hasClass('fieldError') == true){
        //         $("#kCellNo").focus();
        //     }else if($("#kEmail").hasClass('fieldError') == true){
        //         $("#kEmail").focus();
        //     }

        //     return false; 
        // }else{

        //     if($("#studentType").val() == "Bursary"){
        //         if($("#bContactNumber").hasClass('fieldError') == true || $("#bEmail").hasClass('fieldError') == true){

        //             if($("#bContactNumber").hasClass('fieldError') == true){
        //                 $("#bContactNumber").focus();
        //             }else if($("#bEmail").hasClass('fieldError') == true){
        //                 $("#bEmail").focus();
        //             }

        //             return false; 
        //         }else{
        //             return true; 
        //         }
        //     }else if($("#studentType").val() == "Cash / Direct"){
        //         if($("#pCell_no").hasClass('fieldError') == true || $("#pEmail").hasClass('fieldError') == true || $("#pIdNumber").hasClass('fieldError') == true){

        //             if($("#pCell_no").hasClass('fieldError') == true){
        //                 $("#pCell_no").focus();
        //             }else if($("#pEmail").hasClass('fieldError') == true){
        //                 $("#pEmail").focus();
        //             }else if($("#pIdNumber").hasClass('fieldError') == true){
        //                 $("#pIdNumber").focus();
        //             }

        //             return false; 
        //         }else{
        //             return true; 
        //         }
        //     }else{
        //         return true; 
        //     }
        // }

    });


    
</script>

@endsection