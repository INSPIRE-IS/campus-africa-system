@extends('layouts.admin')

@section('content')

<div class="container form-card">

    <form action="/add-student" method="post" id="newUser">
    @csrf
        <br/>
        <br/>

        <div class="form-field">
            <label>First Name * <span id="firstName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="firstName" id="firstName" required>
        </div>

        <div class="form-field">
            <label>Last Name * <span id="lastName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="lastName" id="lastName" required>
        </div>

        <div class="form-field">
            <label>Title * <span id="title_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="title" id='title' required>
                <option value="" disabled selected>Please select one</option>
                <option value="Mr" >Mr</option>
                <option value="Ms" >Ms</option>
                <option value="Mrs" >Mrs</option>
                <option value="Miss" >Miss</option>
            </select>
        </div>

        <div class="form-field">
            <label>Date of Birth * <span id="dob_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="date" name="dob" id="dob" required>
        </div>

        <div class="form-field">
            <label>Cellphone Number * <span id="cellError" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
            <input type="text" name="cell_no" id="cell_no" required>
        </div>

        <div class="form-field">
            <label>Email Address * <span id="emailError" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
            <input type="email" name="email" id="email" required >
        </div>

        <div class="form-field">
            <label>Nationality * <span id="nationality_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="nationality" id='nationality' required>
                <option value="" selected disabled>Please select one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "5")
                        <option value="{{$lookup->lookup_name}}" >{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field" id="idField">
            <label>I.D Number * <span id="idNumberError" class="hidden" style="color:red"><small>Not a valid ID number</small></span></label>
            <input type="text" name="idNumber" id="idNumber" >
        </div>

        <div class="form-field hidden" id="passportField">
            <label>Passport Number * <span id="passport_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="passportNo" id="passportNo" >
        </div>

        <div class="form-field">
            <label>Student Type * <span id="studentType_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="studentType" id='studentType' required>
                <option value="" selected disabled>Select a Student Type</option>
                <option value="NSFAS">NSFAS</option>
                <option value="Bursary">Bursary</option>
                <option value="Cash / Direct">Cash</option>
            </select>
        </div>

        <div class="form-field">
            <label>Year * <span id="year_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="year" id="year" required>
            <option value="" selected disabled>Please select one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "4")
                        <option value="{{$lookup->lookup_name}}" >{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label>Institution of Study * <span id="institStudy_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="institStudy" id="institStudy" required>
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
            <input type="text" name="studNumber" id="studNumber" required >
        </div>

        


        <div class="form-field">
            <br/>
        </div>
        <div class="form-field">
            <br/>
        </div>

        <div class="form-field">
            <label>Year of study * <span id="yearOfStudy_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="yearOfStudy" id="yearOfStudy" required>
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
            <select name="faculty" id="faculty" required>
                <option value="" disabled selected>Choose one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "3")
                        <option value="{{$lookup->id}}" >{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>


        <div class="form-field">
            <label>Property * <span id="property_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="property" id='property' required>
                <option value="" selected disabled>Select a Proprty</option>
                <option value="Braamlofts">Braamlofts</option>
                <option value="80 Jorissen">80 Jorissen</option>
                <option value="49 Jorissen">49 Jorissen</option>
                <option value="Dunvista Mansions">Dunvista Mansions</option>
                <option value="Wynton Joy">Wynton Joy</option>
                <option value="126 Siemert">126 Siemert</option>
                <option value="YMCA">YMCA</option>
            </select>
        </div>

        <div class="form-field">
            <label>Room Type * <span id="roomType_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="roomType" id='roomType' required>
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
                    <label>Cellphone Number * <span id="pCellError" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
                    <input type="text" name="pCell_no" id="pCell_no" >
                </div>

                <div class="form-field">
                    <label>Email Address * <span id="pEmailError" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
                    <input type="email" name="pEmail" id='pEmail' >
                </div>

                <div class="form-field">
                    <label>Nationality * <span id="pNationality_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <select name="pNationality" id='pNationality' required>
                        <option value="" selected disabled>Please select one</option>
                        @foreach($lookups as $lookup)
                            @if($lookup->group_id == "5")
                                <option value="{{$lookup->lookup_name}}" >{{$lookup->lookup_name}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-field" id="pIdField">
                    <label>I.D Number * <span id="pIdNumberError" class="hidden" style="color:red"><small>Not a valid ID number</small></span></label>
                    <input type="text" name="pIdNumber" id="pIdNumber" >
                </div>

                <div class="form-field hidden" id="pPassportField">
                    <label>Passport Number * <span id="pPassportNo_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="pPassportNo" id="pPassportNo" >
                </div>

                

                <div class="form-field-big">
                    <label>Physical Address* <span id="pAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" placeholder="Street No" name="pAddress1" id="pAddress1" style="width:98% !important" required>
                </div>
                <div class="form-field-big">
                <span id="pAddress2_error" class="hidden" style="color:red"><small>This field is required</small></span>
                    <input type="text" placeholder="Suburb" name="pAddress2" id="pAddress2" style="width:98% !important" required>
                </div>
                <div class="form-field">
                <span id="pAddressCity_error" class="hidden" style="color:red"><small>This field is required</small></span>
                    <input type="text" placeholder="City" name="pAddressCity" id='pAddressCity' required >
                </div>
                <div class="form-field">
                <span id="pAddressPostal_error" class="hidden" style="color:red"><small>This field is required</small></span>
                    <input type="text" placeholder="postal Code" name="pAddressPostal" id="pAddressPostal" required>
                </div>

            </div>
            <div id="bursarDetails" class="hidden">

            <div class="form-field">
                    <br/>
                </div>
                <div class="form-field">
                    <br/>
                </div>

                <h1 class="fs-title">Burser Information</h2>
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
                    <label>Contact Number * <span id="bCellError" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
                    <input type="text" name="bContactNumber" id="bContactNumber">
                </div>

                <div class="form-field">
                    <label>Email Address * <span id="bEmailError" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
                    <input type="email" name="bEmail" id="bEmail" >
                </div>

                <div class="form-field">
                    <br/>
                </div>

        </div>

            <div class="form-field">
                <br/>
            </div>
            <div class="form-field">
                <br/>
            </div>

            <h1 class="fs-title">Next of Kin</h2>


        <div class="form-field">
            <label>First Name * <span id="kFirstName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="kFirstName" id="kFirstName" required>
        </div>
        <div class="form-field">
            <label>Last Name * <span id="kLastName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="kLastName" id="kLastName" required>
        </div>

        <div class="form-field">
            <label>Title * <span id="kTitle_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="kTitle" id='kTitle' required>
            <option value="" disabled selected>Please select one</option>
                <option value="Mr" >Mr</option>
                <option value="Ms" >Ms</option>
                <option value="Mrs" >Mrs</option>
                <option value="Miss" >Miss</option>
            </select>
        </div>

        <div class="form-field">
            <label>Relationship * <span id="kRelationship_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="kRelationship" id="kRelationship" required >
        </div>

        <div class="form-field">
            <label>Nationality * <span id="kNationality" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <select name="kinNationality" id='kinNationality' required>
                <option value="" selected disabled>Please select one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "5")
                        <option value="{{$lookup->lookup_name}}" >{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field" id="kinIdField">
            <label>I.D Number * <span id="kIdNumberError" class="hidden" style="color:red"><small>Not a valid ID number</small></span></label>
            <input type="text" name="kIdNumber" id="kIdNumber" required>
        </div>

        <div class="form-field hidden" id="kinPassportField">
            <label>Passport Number * <span id="kinPassport_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="kinPassportNo" id="kinPassportNo" >
        </div>

        <div class="form-field">
            <label>Cellphone Number * <span id="kCellNoError" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
            <input type="text" name="kCellNo" id='kCellNo' required>
        </div>

        <div class="form-field">
            <label>Email Address * <span id="kEmailError" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
            <input type="email" name="kEmail" id="kEmail" required>
        </div>

        <div class="form-field">
            <label>Work Number <span id="kWorkNoError" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
            <input type="text" name="kWorkNo" id="kWorkNo" >
        </div>

        <div class="form-field">
            <label>Telephone Number <span id="kTeleNoError" class="hidden" style="color:red"><small>Not a valid telephone number</small></span></label>
            <input type="text" name="kTeleNo" id='kTeleNo'>
        </div>

        <div class="form-field-big">
            <label>Physical Address * <span id="kAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" placeholder="Street No" name="kAddress1" id="kAddress1" style="width:98% !important" required>
        </div>
        <div class="form-field-big">
        <span id="kAddress2_error" class="hidden" style="color:red"><small>This field is required</small></span>
            <input type="text" placeholder="Suburb" name="kAddress2" id="kAddress2" style="width:98% !important" required>
        </div>
        <div class="form-field">
        <span id="kAddressCity_error" class="hidden" style="color:red"><small>This field is required</small></span>
            <input type="text" placeholder="City" name="kAddressCity" id='kAddressCity' required >
        </div>
        <div class="form-field">
        <span id="kAddressPostal_error" class="hidden" style="color:red"><small>This field is required</small></span>
            <input type="text" placeholder="postal Code" name="kAddressPostal" id="kAddressPostal" required>
        </div>

        <div class="form-field">
            <br/>
        </div>

        <input type="submit" value="Save" class="profileSubmit" style="opacity:1;"/>
    </form>
</div>

<script>

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
            $("#"+idField).removeClass("errorBorder")
            $("#"+errorField).addClass('hidden')
        }else{
            $("#"+idField).addClass("fieldError")
            $("#"+errorField).removeClass('hidden')
        }
    }

    $("#idNumber").on('keyup blur', function(){
        let idNumber = $("#idNumber").val();
        if(idNumber.length == 13){
            console.log("1")
            ValidID(idNumber, "idNumber", "idNumberError")
        }else{
            console.log("2")
            $("#idNumber").addClass('errorBorder')
            $("#idNumber").addClass("fieldError")
            $("#idNumberError").removeClass('hidden')
        }  
    })

    $("#pIdNumber").on('keyup blur', function(){
        let idNumber = $("#pIdNumber").val();

        if(idNumber.length == 13){
            ValidID(idNumber, "pIdNumber", "pIdNumberError")
        }else{
            $("#pIdNumber").addClass("fieldError")
            $("#pIdNumberError").removeClass('hidden')
        }

    })

    $("#kIdNumber").on('keyup blur', function(){
        let idNumber = $("#kIdNumber").val();

        if(idNumber.length == 13){
            ValidID(idNumber, "kIdNumber", "kIdNumberError")
        }else{
            $("#kIdNumber").addClass("fieldError")
            $("#kIdNumberError").removeClass('hidden')
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
    

    $("#cell_no").on('keyup blur', function(){
        cell = $("#cell_no").val()
        ValidCell(cell, "cell_no", "cellError")
    })

    $("#pCell_no").on('keyup blur', function(){
        cell = $("#pCell_no").val()
        ValidCell(cell, "pCell_no", "pCellError")
    })

    $("#bContactNumber").on('keyup blur', function(){
        cell = $("#bContactNumber").val()
        ValidCell(cell, "bContactNumber", "bCellError")
    })

    $("#kTeleNo").on('keyup blur', function(){
        cell = $("#kTeleNo").val()
        ValidCell(cell, "kTeleNo", "kTeleNoError")
    })

    $("#kCellNo").on('keyup blur', function(){
        cell = $("#kCellNo").val()
        ValidCell(cell, "kCellNo", "kCellNoError")
    })

    $("#kWorkNo").on('keyup blur', function(){
        cell = $("#kWorkNo").val()
        ValidCell(cell, "kWorkNo", "kWorkNoError")
    })

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    $("#email").on('keyup', function(){
        if(isEmail($("#email").val())){
            $("#email").removeClass("fieldError")
            $("#emailError").addClass('hidden')
        }else{
            $("#email").addClass("fieldError")
            $("#emailError").removeClass('hidden')
        }
    })

    $("#pEmail").on('keyup', function(){
        if(isEmail($("#pEmail").val())){
            $("#pEmail").removeClass("fieldError")
            $("#pEmailError").addClass('hidden')
        }else{
            $("#pEmail").addClass("fieldError")
            $("#pEmailError").removeClass('hidden')
        }
    })

    $("#bEmail").on('keyup', function(){
        if(isEmail($("#bEmail").val())){
            $("#bEmail").removeClass("fieldError")
            $("#bEmailError").addClass('hidden')
        }else{
            $("#bEmail").addClass("fieldError")
            $("#bEmailError").removeClass('hidden')
        }
    })
 
    $("#kEmail").on('keyup', function(){
        if(isEmail($("#kEmail").val())){
            $("#kEmail").removeClass("fieldError")
            $("#kEmailError").addClass('hidden')
        }else{
            $("#kEmail").addClass("fieldError")
            $("#kEmailError").removeClass('hidden')
        }
    })

    $("#nationality").on('change', function(){
        if($("#nationality").val() == "South African"){
            $("#passportField").addClass('hidden')
            $("#idField").removeClass('hidden')
            document.getElementById("passportNo").required = false;
            document.getElementById("idNumber").required = true;
        }else{
            $("#idField").addClass('hidden')
            $("#passportField").removeClass('hidden')
            document.getElementById("passportNo").required = true;
            document.getElementById("idNumber").required = false;
        }
    })

    $("#kinNationality").on('change', function(){
        if($("#kinNationality").val() == "South African"){
            $("#kinPassportField").addClass('hidden')
            $("#kinIdField").removeClass('hidden')
            document.getElementById("passportNo").required = false;
            document.getElementById("idNumber").required = true;
        }else{
            $("#kinIdField").addClass('hidden')
            $("#kinPassportField").removeClass('hidden')
            document.getElementById("passportNo").required = true;
            document.getElementById("idNumber").required = false;
        }
    })

    $("#pNationality").on('change', function(){
        if($("#pNationality").val() == "South African"){
            $("#pPassportField").addClass('hidden')
            $("#pIdField").removeClass('hidden')
            document.getElementById("passportNo").required = false;
            document.getElementById("idNumber").required = true;
        }else{
            $("#pIdField").addClass('hidden')
            $("#pPassportField").removeClass('hidden')
            document.getElementById("passportNo").required = true;
            document.getElementById("idNumber").required = false;
        }
    })

    function parentDetails(value){
        document.getElementById("pFirstName").required = value;
        document.getElementById("pLastName").required = value;
        document.getElementById("pCell_no").required = value;
        document.getElementById("pEmail").required = value;
        document.getElementById("pNationality").required = value;
        document.getElementById("pAddress1").required = value;
        document.getElementById("pAddress2").required = value;
        document.getElementById("pAddressCity").required = value;
        document.getElementById("pAddressPostal").required = value;
    }

    function sponsorDetails(value){
        document.getElementById("bCompanyName").required = value;
        document.getElementById("bRegistrationNo").required = value;
        document.getElementById("bContactPerson").required = value;
        document.getElementById("bContactNumber").required = value;
        document.getElementById("bEmail").required = value;
    }

    $("#studentType").on('change', function(){
        if($("#studentType").val() == "Cash / Direct"){
            $("#sponsorDetails").removeClass('hidden')
            $("#bursarDetails").addClass('hidden')

            parentDetails(true)
            sponsorDetails(false)

        }else if($("#studentType").val() == "Bursary"){
            $("#bursarDetails").removeClass('hidden')
            $("#sponsorDetails").addClass('hidden')

            parentDetails(false)
            sponsorDetails(true)
        
        }else{
            $("#sponsorDetails").addClass('hidden')
            $("#bursarDetails").addClass('hidden')

            parentDetails(false)
            sponsorDetails(false)
        }
    })

    $("#property").on('change', function(){
        let propValue = $("#property").val();

        if(propValue == "Braamlofts"){
            $("#roomType").empty().append('<option value="Single Room">Single Room</option><option value="2 Sharing">2 Sharing</option><option value="3 Sharing">3 sharing</option>')
        }else if(propValue == "80 Jorissen"){
            $("#roomType").empty().append('<option value="Single Room">Single Room</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "49 Jorissen"){
            $("#roomType").empty().append('<option value="2 Sharing">2 Sharing</option><option value="single room 2 bedroom">Single Room | 2 Bedroom</option><option value="single room 3 bedroom">Single Room | 3 Bedroom</option>')
        }else if(propValue == "Dunvista Mansions"){
            $("#roomType").empty().append('<option value="Single Room">Single Room</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "Wynton Joy"){
            $("#roomType").empty().append('<option value="Single Room">Single Room</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "126 Siemert"){
            $("#roomType").empty().append('<option value="Single Room">Single Room</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "YMCA"){
            $("#roomType").empty().append('<option value="2 Sharing">2 Sharing</option><option value="3 Sharing">3 sharing</option><option value="single room small">Single Room | small</option>')
        }
    })

    $("input").blur(function(){
        let fieldId = $(this)[0]["id"]

        var required = document.getElementById(fieldId).required

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

        var required = document.getElementById(fieldId).required

        if($("#"+fieldId).val() == "" && required == true){
            $("#"+$(this)[0]["id"]).addClass('fieldError')
            $("#"+$(this)[0]["id"]+"_error").removeClass('hidden')
        }else{
            $("#"+$(this)[0]["id"]).removeClass('fieldError')
            $("#"+$(this)[0]["id"]+"_error").addClass('hidden')
        }
    });

    $('#newUser').submit(function() {

        if($("#cell_no").hasClass('fieldError') == true || $("#email").hasClass('fieldError') == true || $("#idNumber").hasClass('fieldError') == true || $("#kIdNumber").hasClass('fieldError') == true || $("#kCellNo").hasClass('fieldError') == true || $("#kEmail").hasClass('fieldError') == true){

            if($("#cell_no").hasClass('fieldError') == true){
                $("#cell_no").focus();
            }else if($("#email").hasClass('fieldError') == true){
                $("#email").focus();
            }else if($("#idNumber").hasClass('fieldError') == true){
                $("#idNumber").focus();
            }else if($("#kIdNumber").hasClass('fieldError') == true){
                $("#kIdNumber").focus();
            }else if($("#kCellNo").hasClass('fieldError') == true){
                $("#kCellNo").focus();
            }else if($("#kEmail").hasClass('fieldError') == true){
                $("#kEmail").focus();
            }

            return false; 
        }else{

            if($("#studentType").val() == "Bursary"){
                if($("#bContactNumber").hasClass('fieldError') == true || $("#bEmail").hasClass('fieldError') == true){

                    if($("#bContactNumber").hasClass('fieldError') == true){
                        $("#bContactNumber").focus();
                    }else if($("#bEmail").hasClass('fieldError') == true){
                        $("#bEmail").focus();
                    }

                    return false; 
                }else{
                    return true; 
                }
            }else if($("#studentType").val() == "Cash / Direct"){
                if($("#pCell_no").hasClass('fieldError') == true || $("#pEmail").hasClass('fieldError') == true || $("#pIdNumber").hasClass('fieldError') == true){

                    if($("#pCell_no").hasClass('fieldError') == true){
                        $("#pCell_no").focus();
                    }else if($("#pEmail").hasClass('fieldError') == true){
                        $("#pEmail").focus();
                    }else if($("#pIdNumber").hasClass('fieldError') == true){
                        $("#pIdNumber").focus();
                    }

                    return false; 
                }else{
                    return true; 
                }
            }else{
                return true; 
            }
        }

    });


    
</script>

@endsection