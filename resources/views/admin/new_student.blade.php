@extends('layouts.admin')

@section('content')

<div class="container form-card">

    <form action="/add-student" method="post" id="newUser">
    @csrf
        <br/>
        <br/>

        <div class="form-field">
            <label>First Name *</label>
            <input type="text" name="firstName" id="firstName" required>
        </div>

        <div class="form-field">
            <label>Last Name *</label>
            <input type="text" name="lastName" id="lastName" required>
        </div>

        <div class="form-field">
            <label>Title *</label>
            <select name="title" id='title' required>
                <option value="" disabled selected>Please select one</option>
                <option value="Mr" >Mr</option>
                <option value="Ms" >Ms</option>
                <option value="Mrs" >Mrs</option>
                <option value="Miss" >Miss</option>
            </select>
        </div>

        <div class="form-field">
            <label>Date of Birth *</label>
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
            <label>Nationality *</label>
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
            <label>Passport Number *</label>
            <input type="text" name="passportNo" id="passportNo" >
        </div>

        <div class="form-field">
            <label>Student Type *</label>
            <select name="studentType" id='studentType' required>
                <option value="" selected disabled>Select a Student Type</option>
                <option value="NSFAS">NSFAS</option>
                <option value="Bursary">Bursary</option>
                <option value="Cash / Direct">Cash</option>
            </select>
        </div>

        <div class="form-field">
            <label>Year *</label>
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
            <label>Institution of Study *</label>
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
            <label>Year of study *</label>
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
            <br/>
        </div>
        <div class="form-field">
            <br/>
        </div>

        <div class="form-field">
            <label>Student Number *</label>
            <input type="text" name="studNumber" id="studNumber" required >
        </div>



        <div class="form-field">
            <label>Faculty *</label>
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
            <label>Property *</label>
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
            <label>Room Type *</label>
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
                    <label>First Name *</label>
                    <input type="text" name="pFirstName" id="pFirstName" >
                </div>

                <div class="form-field">
                    <label>Last Name *</label>
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
                    <label>I.D Number * <span id="pIdNumberError" class="hidden" style="color:red"><small>Not a valid ID number</small></span></label>
                    <input type="text" name="pIdNumber" id="pIdNumber" >
                </div>

                <div class="form-field">
                    <br/>
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
                    <label>Company Name *</label>
                    <input type="text" name="bCompanyName" id='bCompanyName' >
                </div>

                <div class="form-field">
                    <label>Registration Number *</label>
                    <input type="text" name="bRegistrationNo" id="bRegistrationNo" >
                </div>

                <div class="form-field">
                    <label>Contact Person *</label>
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
            <label>First Name *</label>
            <input type="text" name="kFirstName" id="kFirstName" required>
        </div>
        <div class="form-field">
            <label>Last Name *</label>
            <input type="text" name="kLastName" id="kLastName" required>
        </div>

        <div class="form-field">
            <label>Title *</label>
            <select name="kTitle" id='kTitle' required>
            <option value="" disabled selected>Please select one</option>
                <option value="Mr" >Mr</option>
                <option value="Ms" >Ms</option>
                <option value="Mrs" >Mrs</option>
                <option value="Miss" >Miss</option>
            </select>
        </div>

        <div class="form-field">
            <label>I.D Number * <span id="kIdNumberError" class="hidden" style="color:red"><small>Not a valid ID number</small></span></label>
            <input type="text" name="kIdNumber" id="kIdNumber" required>
        </div>

        <div class="form-field-big">
            <label>Physical Address*</label>
            <input type="text" placeholder="Street No" name="kAddress1" id="kAddress1" style="width:98% !important" required>
        </div>
        <div class="form-field-big">
            <input type="text" placeholder="Suburb" name="kAddress2" id="kAddress2" style="width:98% !important" required>
        </div>
        <div class="form-field">
            <input type="text" placeholder="City" name="kAddressCity" id='kAddressCity' required >
        </div>
        <div class="form-field">
            <input type="text" placeholder="postal Code" name="kAddressPostal" id="kAddressPostal" required>
        </div>

        <div class="form-field">
            <label>Relationship *</label>
            <input type="text" name="kRelationship" id="kRelationship" required >
        </div>

        <div class="form-field">
            <label>Telephone Number <span id="kTeleNoError" class="hidden" style="color:red"><small>Not a valid telephone number</small></span></label>
            <input type="text" name="kTeleNo" id='kTeleNo'>
        </div>

        <div class="form-field">
            <label>Cellphone Number * <span id="kCellNoError" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
            <input type="text" name="kCellNo" id='kCellNo' required>
        </div>

        <div class="form-field">
            <label>Work Number <span id="kWorkNoError" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
            <input type="text" name="kWorkNo" id="kWorkNo" >
        </div>

        <div class="form-field">
            <label>Email Address * <span id="kEmailError" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
            <input type="email" name="kEmail" id="kEmail" required>
        </div>

        <div class="form-field">
            <br/>
        </div>

        <input type="submit" value="Save" class="profileSubmit" style="opacity:1;"/>
    </form>
</div>

<script>

    $("#idNumber").on('blur', function(){
        let idNumber = $("#idNumber").val();

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

        if(Number.isInteger(finalSum) && idNumber !== ""){
            $("#idNumber").removeClass("fieldError")
            $("#idNumberError").addClass('hidden')
        }else{
            $("#idNumber").addClass("fieldError")
            $("#idNumberError").removeClass('hidden')
        }

    })

    $("#pIdNumber").on('blur', function(){
        let idNumber = $("#pIdNumber").val();

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


        if(Number.isInteger(finalSum) && idNumber !== ""){
            $("#pIdNumber").removeClass("fieldError")
            $("#pIdNumberError").addClass('hidden')
        }else{
            $("#pIdNumber").addClass("fieldError")
            $("#pIdNumberError").removeClass('hidden')
        }

    })

    $("#kIdNumber").on('blur', function(){
        let idNumber = $("#kIdNumber").val();

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


        if(Number.isInteger(finalSum) && idNumber !== ""){
            $("#kIdNumber").removeClass("fieldError")
            $("#kIdNumberError").addClass('hidden')
        }else{
            $("#kIdNumber").addClass("fieldError")
            $("#kIdNumberError").removeClass('hidden')
        }

    })
    

    $("#cell_no").on('blur', function(){

        cell = $("#cell_no").val()

        if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
            $("#cell_no").removeClass("fieldError")
            $("#cellError").addClass('hidden')
        }else{
            $("#cell_no").addClass("fieldError")
            $("#cellError").removeClass('hidden')
        }

    })

    $("#pCell_no").on('blur', function(){

        cell = $("#pCell_no").val()

        if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
            $("#pCell_no").removeClass("fieldError")
            $("#pCellError").addClass('hidden')
        }else{
            $("#pCell_no").addClass("fieldError")
            $("#pCellError").removeClass('hidden')
        }

    })

    $("#bContactNumber").on('blur', function(){

        cell = $("#bContactNumber").val()

        if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
            $("#bContactNumber").removeClass("fieldError")
            $("#bCellError").addClass('hidden')
        }else{
            $("#bContactNumber").addClass("fieldError")
            $("#bCellError").removeClass('hidden')
        }

    })

    $("#kTeleNo").on('blur', function(){

        cell = $("#kTeleNo").val()

        if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
            $("#kTeleNo").removeClass("fieldError")
            $("#kTeleNoError").addClass('hidden')
        }else{
            $("#kTeleNo").addClass("fieldError")
            $("#kTeleNoError").removeClass('hidden')
        }

    })

    $("#kCellNo").on('blur', function(){

        cell = $("#kCellNo").val()

        if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
            $("#kCellNo").removeClass("fieldError")
            $("#kCellNoError").addClass('hidden')
        }else{
            $("#kCellNo").addClass("fieldError")
            $("#kCellNoError").removeClass('hidden')
        }

    })

    $("#kWorkNo").on('blur', function(){

        cell = $("#kWorkNo").val()

        if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
            $("#kWorkNo").removeClass("fieldError")
            $("#kWorkNoError").addClass('hidden')
        }else{
            $("#kWorkNo").addClass("fieldError")
            $("#kWorkNoError").removeClass('hidden')
        }

    })

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    $("#email").on('blur', function(){
        if(isEmail($("#email").val())){
            $("#email").removeClass("fieldError")
            $("#emailError").addClass('hidden')
        }else{
            $("#email").addClass("fieldError")
            $("#emailError").removeClass('hidden')
        }
    })

    $("#pEmail").on('blur', function(){
        if(isEmail($("#pEmail").val())){
            $("#pEmail").removeClass("fieldError")
            $("#pEmailError").addClass('hidden')
        }else{
            $("#pEmail").addClass("fieldError")
            $("#pEmailError").removeClass('hidden')
        }
    })

    $("#bEmail").on('blur', function(){
        if(isEmail($("#bEmail").val())){
            $("#bEmail").removeClass("fieldError")
            $("#bEmailError").addClass('hidden')
        }else{
            $("#bEmail").addClass("fieldError")
            $("#bEmailError").removeClass('hidden')
        }
    })

    $("#kEmail").on('blur', function(){
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
        }else{
            $("#idField").addClass('hidden')
            $("#passportField").removeClass('hidden')
        }
    })

    function parentDetails(value){
        document.getElementById("pFirstName").required = value;
        document.getElementById("pLastName").required = value;
        document.getElementById("pCell_no").required = value;
        document.getElementById("pEmail").required = value;
        document.getElementById("pIdNumber").required = value;
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
        }
    });

    $("select").blur(function(){
        let fieldId = $(this)[0]["id"]

        var required = document.getElementById(fieldId).required

        if($("#"+fieldId).val() == "" && required == true){
            $("#"+$(this)[0]["id"]).addClass('fieldError')
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