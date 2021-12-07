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
            <label>Cellphone Number</label>
            <input type="text" name="cell_no" id="cell_no" >
        </div>

        <div class="form-field">
            <label>Email Address *</label>
            <input type="email" name="email" id="email" required >
        </div>

        <div class="form-field">
            <label>I.D Number</label>
            <input type="text" name="idNumber" id="idNumber" >
        </div>

        <div class="form-field">
            <label>Nationality</label>
            <select name="nationality" id='nationality'>
                <option value="South African" >South African</option>
            </select>
        </div>

        <div class="form-field">
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
            <label>Year</label>
            <select name="year" id="year">
                <option value="2022" >2022</option>
                <option value="2021" >2021</option>
            </select>
        </div>

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
            <label>Cellphone Number</label>
            <input type="text" name="pCell_no" id="pCell_no" >
        </div>

        <div class="form-field">
            <label>Email Address</label>
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
            <label>Contact Number</label>
            <input type="text" name="bContactNumber" id="bContactNumber">
        </div>

        <div class="form-field">
            <label>Email Address</label>
            <input type="email" name="bEmail" id="bEmail" >
        </div>

        <div class="form-field">
            <br/>
        </div>

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
            <label>Telephone Number</label>
            <input type="text" name="kTeleNo" id='kTeleNo' >
        </div>

        <div class="form-field">
            <label>Cellphone Number</label>
            <input type="text" name="kCellNo" id='kCellNo' >
        </div>

        <div class="form-field">
            <label>Work Number</label>
            <input type="text" name="kWorkNo" id="kWorkNo" >
        </div>

        <div class="form-field">
            <label>Email Address</label>
            <input type="email" name="kEmail" id="kEmail" >
        </div>

        <div class="form-field">
            <br/>
        </div>

        <input type="submit" value="Update" class="profileSubmit" style="opacity:1;"/>
    </form>
</div>

@endsection