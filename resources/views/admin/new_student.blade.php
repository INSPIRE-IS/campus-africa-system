@extends('layouts.admin')

@section('content')

<div class="container form-card">

    <form action="/add-student" method="post">
    @csrf
        <br/>
        <br/>

        <div class="form-field">
            <label>First Name *</label>
            <input type="text" name="firstName" id="firstName" required>
        </div>

        <div class="form-field">
            <label>Last Name</label>
            <input type="text" name="lastName" id="lastName" >
        </div>

        <div class="form-field">
            <label>Student Type</label>
            <select name="studentType" id='studentType'>
                <option value="Single Room" selected disabled>Select a Student Type</option>
                <option value="NSFAS">NSFAS</option>
                <option value="Bursary">Bursary</option>
                <option value="Cash / Direct">Cash / Direct</option>
            </select>
        </div>

        <div class="form-field">
            <label>Title</label>
            <select name="title" id='title'>
                <option value="Mr" >Mr</option>
                <option value="Ms" >Ms</option>
                <option value="Mrs" >Mrs</option>
                <option value="Miss" >Miss</option>
            </select>
        </div>

        <div class="form-field">
            <label>Date of Birth</label>
            <input type="date" name="dob" id="dob" >
        </div>

        <div class="form-field">
            <label>Cellphone Number <span id="cellError" class="hidden" style="color:red"><small>Not a valid number</small></span></label>
            <input type="text" name="cell_no" id="cell_no" >
        </div>

        <div class="form-field">
            <label>Email Address * <span id="emailError" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
            <input type="email" name="email" id="email" required >
        </div>

        <div class="form-field" id="idField">
            <label>I.D Number</label>
            <input type="text" name="idNumber" id="idNumber" >
        </div>

        <div class="form-field">
            <label>Nationality</label>
            <select name="nationality" id='nationality'>
                <option value="" selected disabled>Please select one</option>
                <option value="South African" >South African</option>
                <option value="Other" >Other</option>
            </select>
        </div>

        <div class="form-field" id="passportField">
            <label>Passport Number</label>
            <input type="text" name="passportNo" id="passportNo" >
        </div>

        <div class="form-field">
            <label>Institution of Study</label>
            <select name="institStudy" id="institStudy">
                <option value="Choose one" selected disabled>Choose one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "1")
                        <option value="{{$lookup->id}}" >{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label>Student Number</label>
            <input type="text" name="studNumber" id="studNumber" >
        </div>

        <div class="form-field">
            <label>Insitution Campus</label>
            <select name="institCampus" id="institCampus">
                <option value="Choose one" selected diabled>Choose one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "2")
                        <option value="{{$lookup->id}}" >{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label>Faculty</label>
            <select name="faculty" id="faculty">
                <option value="Choose one" disabled selected>Choose one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "3")
                        <option value="{{$lookup->id}}" >{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label>Year of study</label>
            <select name="yearOfStudy" id="yearOfStudy">
                <option value="" selected disabled>Please select one</option>
                <option value="1st Year" >1st Year</option>
                <option value="2nd Year" >2nd Year</option>
                <option value="3rd Year" >3rd Year</option>
                <option value="4th Year" >4th Year</option>
            </select>
        </div>

        <div class="form-field">
            <label>Year</label>
            <select name="year" id="year">
            <option value="" selected disabled>Please select one</option>
                <option value="2022" >2022</option>
                <option value="2021" >2021</option>
            </select>
        </div>

        

        <div class="form-field">
            <label>Property</label>
            <select name="property" id='property'>
                <option value="Property" selected disabled>Select a Proprty</option>
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
            <label>Room Type</label>
            <select name="roomType" id='roomType'>
                <option value="Single Room" selected disabled>Select a Room Type</option>

                
            </select>
        </div>

        <div id="sponsorDetails" class="hidden">

                <h1 class="fs-title">Sponsor / Burser Information</h2>
                <h2 class="fs-subtitle">Parent / guardians / sponsor details</h4>
                <br/>

                <div class="form-field">
                    <label>First Name</label>
                    <input type="text" name="pFirstName" id="pFirstName" >
                </div>

                <div class="form-field">
                    <label>Last Name</label>
                    <input type="text" name="pLastName" id="pLastName" >
                </div>

                <div class="form-field">
                    <label>Cellphone Number <span id="pCellError" class="hidden" style="color:red"><small>Not a valid number</small></span></label>
                    <input type="text" name="pCell_no" id="pCell_no" >
                </div>

                <div class="form-field">
                    <label>Email Address <span id="pEmailError" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
                    <input type="email" name="pEmail" id='pEmail' >
                </div>

                <div class="form-field">
                    <label>I.D Number</label>
                    <input type="text" name="pIdNumber" id="pIdNumber" >
                </div>

                <div class="form-field">
                    <br/>
                </div>

                <h4 class="fs-subtitle">Bursar Details</h4>
                <br/>

                <div class="form-field">
                    <label>Company Name</label>
                    <input type="text" name="bCompanyName" id='bCompanyName' >
                </div>

                <div class="form-field">
                    <label>Registration Number</label>
                    <input type="text" name="bRegistrationNo" id="bRegistrationNo" >
                </div>

                <div class="form-field">
                    <label>Contact Person</label>
                    <input type="text" name="bContactPerson" id="bContactPerson" >
                </div>

                <div class="form-field">
                    <label>Contact Number <span id="bCellError" class="hidden" style="color:red"><small>Not a valid number</small></span></label>
                    <input type="text" name="bContactNumber" id="bContactNumber">
                </div>

                <div class="form-field">
                    <label>Email Address <span id="bEmailError" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
                    <input type="email" name="bEmail" id="bEmail" >
                </div>

                <div class="form-field">
                    <br/>
                </div>

        </div>



            <h1 class="fs-title">Next of Kin</h2>


        <div class="form-field">
            <label>First Name</label>
            <input type="text" name="kFirstName" id="kFirstName" >
        </div>
        <div class="form-field">
            <label>Last Name</label>
            <input type="text" name="kLastName" id="kLastName" >
        </div>

        <div class="form-field">
            <label>Title</label>
            <select name="kTitle" id='kTitle'>
                <option value="Mr" >Mr</option>
                <option value="Ms" >Ms</option>
                <option value="Mrs" >Mrs</option>
                <option value="Miss" >Miss</option>
            </select>
        </div>

        <div class="form-field">
            <label>I.D Number</label>
            <input type="text" name="kIdNumber" id="kIdNumber">
        </div>

        <div class="form-field-big">
            <label>Physical Address</label>
            <input type="text" placeholder="Street No" name="kAddress1" id="kAddress1" style="width:98% !important" >
        </div>
        <div class="form-field-big">
            <input type="text" placeholder="Suburb" name="kAddress2" id="kAddress2" style="width:98% !important" >
        </div>
        <div class="form-field">
            <input type="text" placeholder="City" name="kAddressCity" id='kAddressCity' >
        </div>
        <div class="form-field">
            <input type="text" placeholder="postal Code" name="kAddressPostal" id="kAddressPostal" >
        </div>

        <div class="form-field">
            <label>Relationship</label>
            <input type="text" name="kRelationship" id="kRelationship" >
        </div>

        <div class="form-field">
            <label>Telephone Number <span id="kTeleNoError" class="hidden" style="color:red"><small>Not a valid number</small></span></label>
            <input type="text" name="kTeleNo" id='kTeleNo' >
        </div>

        <div class="form-field">
            <label>Cellphone Number <span id="kCellNoError" class="hidden" style="color:red"><small>Not a valid number</small></span></label>
            <input type="text" name="kCellNo" id='kCellNo' >
        </div>

        <div class="form-field">
            <label>Work Number <span id="kWorkNoError" class="hidden" style="color:red"><small>Not a valid number</small></span></label>
            <input type="text" name="kWorkNo" id="kWorkNo" >
        </div>

        <div class="form-field">
            <label>Email Address <span id="kEmailError" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
            <input type="email" name="kEmail" id="kEmail" >
        </div>

        <div class="form-field">
            <br/>
        </div>

        <input type="submit" value="Update" class="profileSubmit" style="opacity:1;"/>
    </form>
</div>

<script>
    $("#cell_no").on('blur', function(){

        if(($("#cell_no").val()).substring(0,3) == "+27"){
            cell = "0"+($("#cell_no").val()).substring(3)
        }else{
            cell = $("#cell_no").val()
        }
        
        if( /^\d{10}$/.test(cell) ){
            $("#cell_no").removeClass("fieldError")
            $("#cellError").addClass('hidden')
        } else {
            $("#cell_no").addClass("fieldError")
            $("#cellError").removeClass('hidden')
        }

    })

    $("#pCell_no").on('blur', function(){

        if(($("#pCell_no").val()).substring(0,3) == "+27"){
            cell = "0"+($("#pCell_no").val()).substring(3)
        }else{
            cell = $("#pCell_no").val()
        }

        if( /^\d{10}$/.test(cell) ){
            $("#pCell_no").removeClass("fieldError")
            $("#pCellError").addClass('hidden')
        } else {
            $("#pCell_no").addClass("fieldError")
            $("#pCellError").removeClass('hidden')
        }

    })

    $("#bContactNumber").on('blur', function(){

        if(($("#bContactNumber").val()).substring(0,3) == "+27"){
            cell = "0"+($("#bContactNumber").val()).substring(3)
        }else{
            cell = $("#bContactNumber").val()
        }

        if( /^\d{10}$/.test(cell) ){
            $("#bContactNumber").removeClass("fieldError")
            $("#bCellError").addClass('hidden')
        } else {
            $("#bContactNumber").addClass("fieldError")
            $("#bCellError").removeClass('hidden')
        }

    })

    $("#kTeleNo").on('blur', function(){

        if(($("#kTeleNo").val()).substring(0,3) == "+27"){
            cell = "0"+($("#kTeleNo").val()).substring(3)
        }else{
            cell = $("#kTeleNo").val()
        }

        if( /^\d{10}$/.test(cell) ){
            $("#kTeleNo").removeClass("fieldError")
            $("#kTeleNoError").addClass('hidden')
        } else {
            $("#kTeleNo").addClass("fieldError")
            $("#kTeleNoError").removeClass('hidden')
        }

    })

    $("#kCellNo").on('blur', function(){

        if(($("#kCellNo").val()).substring(0,3) == "+27"){
            cell = "0"+($("#kCellNo").val()).substring(3)
        }else{
            cell = $("#kCellNo").val()
        }

        if( /^\d{10}$/.test(cell) ){
            $("#kCellNo").removeClass("fieldError")
            $("#kCellNoError").addClass('hidden')
        } else {
            $("#kCellNo").addClass("fieldError")
            $("#kCellNoError").removeClass('hidden')
        }

    })

    $("#kWorkNo").on('blur', function(){

        if(($("#kWorkNo").val()).substring(0,3) == "+27"){
            cell = "0"+($("#kWorkNo").val()).substring(3)
        }else{
            cell = $("#kWorkNo").val()
        }

        if( /^\d{10}$/.test(cell) ){
            $("#kWorkNo").removeClass("fieldError")
            $("#kWorkNoError").addClass('hidden')
        } else {
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
            $("#idField").addClass('hidden')
            $("#passportField").removeClass('hidden')
        }else{
            $("#passportField").addClass('hidden')
            $("#idField").removeClass('hidden')
        }
    })

    $("#studentType").on('change', function(){
        if($("#studentType").val() == "Cash / Cash / Direct" || $("#studentType").val() == "Bursary"){
            $("#sponsorDetails").removeClass('hidden')
        }else{
            $("#sponsorDetails").addClass('hidden')
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


    
</script>

@endsection