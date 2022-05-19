@extends('layouts.app')

@section('content')

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
    
    <form action="/online-application-step-1" method="post" id="newUser" class="form onlineForm" autocomplete="off">
        <br/>
        <h1 class="formHeading">Applicant Details</h1>
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
            <label>Cellphone Number * <span id="cell_no_error" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span><span id="cell_no_duplicate" class="hidden" style="color:red"><small>. Duplicate detected!</small></span></label>
            <input type="text" name="cell_no" id="cell_no" class="required">
        </div>

        <div class="form-field">
            <label>Email Address * <span id="email_error" class="hidden" style="color:red"><small>Not a valid email</small></span>
            <span id="email_InUse" class="hidden" style="color:red"><small>Email Address already in use</small></span></label>
            <input type="email" name="email" id="email" class="required" >
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
            <label>I.D Number * <span id="idNumber_error" class="hidden" style="color:red"><small>Not a valid ID number</small></span>
            <span id="idNumber_InUse" class="hidden" style="color:red"><small>ID Number already in use</small></span></label>
            <input type="text" name="idNumber" id="idNumber" class="required">
        </div>

        <div class="form-field hidden" id="passportField">
            <label>Passport Number * <span id="passport_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="passportNo" id="passportNo" >
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


    </form>

</div>


@endsection
