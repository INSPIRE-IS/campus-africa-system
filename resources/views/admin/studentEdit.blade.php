@extends('layouts.admin')

@section('content')

<div class="container form-card">

    <form action="/update-student" method="post">
    @csrf
        <br/>
        <h1 class="header fs-title">Student: {{$user->userFirstName}} {{$user->userLastname}}</h2>
        <br/>

        <div class="form-field">
            <label>First Name *</label>
            <input type="text" name="firstName" id="firstName" value="{{$user->userFirstName}}" required>
            <input type="hidden" name="userUid" id="userUid" value="{{$user->id}}" required>
        </div>

        <div class="form-field">
            <label>Last Name</label>
            <input type="text" name="lastName" id="lastName" value="{{$user->userLastname}}">
        </div>

        <div class="form-field">
            <label>Title</label>
            <select name="title" id='title'>
                <option value="Mr" <?php if($user->userTitle == "Mr"){echo "selected";} ?>>Mr</option>
                <option value="Ms" <?php if($user->userTitle == "Ms"){echo "selected";} ?>>Ms</option>
                <option value="Mrs" <?php if($user->userTitle == "Mrs"){echo "selected";} ?>>Mrs</option>
                <option value="Miss" <?php if($user->userTitle == "Miss"){echo "selected";} ?>>Miss</option>
            </select>
        </div>

        <div class="form-field">
            <label>Date of Birth</label>
            <input type="date" name="dob" id="dob" value="{{$user->userDob}}">
        </div>

        <div class="form-field">
            <label>Cellphone Number</label>
            <input type="text" name="cell_no" id="cell_no" value="{{$user->userCell}}">
        </div>

        <div class="form-field">
            <label>Email Address *</label>
            <input type="email" name="email" id="email" required value="{{$user->userEmail}}">
        </div>

        <div class="form-field">
            <label>I.D Number</label>
            <input type="text" name="idNumber" id="idNumber" value="{{$user->userId}}">
        </div>

        <div class="form-field">
            <label>Nationality</label>
            <select name="nationality" id='nationality'>
                <option value="South African" <?php if($user->userNationality == "South African"){echo "selected";}?>>South African</option>
            </select>
        </div>

        <div class="form-field">
            <label>Passport Number</label>
            <input type="text" name="passportNo" id="passportNo" value="{{$user->userPassport}}">
        </div>

        <div class="form-field">
            <label>Institution of Study</label>
            <select name="institStudy" id="institStudy">
                <option value="Choose one" selected disabled>Choose one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "1")
                        <option value="{{$lookup->id}}" <?php if($user->institutionStudy == $lookup->id){echo "selected";} ?>>{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label>Student Number</label>
            <input type="text" name="studNumber" id="studNumber" value="{{$user->studentNo}}">
        </div>



        <div class="form-field">
            <label>Faculty</label>
            <select name="faculty" id="faculty">
                <option value="Choose one" disabled selected>Choose one</option>
                @foreach($lookups as $lookup)
                    @if($lookup->group_id == "3")
                        <option value="{{$lookup->id}}" <?php if($user->faculty == $lookup->id){echo "selected";} ?>>{{$lookup->lookup_name}}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-field">
            <label>Year</label>
            <select name="year" id="year">
                <option value="2022" <?php if($user->year == "2022"){echo "selected";}?>>2022</option>
                <option value="2021" <?php if($user->year == "2021"){echo "selected";}?>>2021</option>
            </select>
        </div>

        <h1 class="fs-title">Sponsor / Burser Information</h2>
        <h2 class="fs-subtitle">Parent / guardians / sponsor details</h4>
        <br/>

        <div class="form-field">
            <label>First Name</label>
            <input type="text" name="pFirstName" id="pFirstName" value="{{$user->sponFirstName}}">
        </div>

        <div class="form-field">
            <label>Last Name</label>
            <input type="text" name="pLastName" id="pLastName" value="{{$user->sponLastName}}">
        </div>

        <div class="form-field">
            <label>Cellphone Number</label>
            <input type="text" name="pCell_no" id="pCell_no" value="{{$user->sponCell}}">
        </div>

        <div class="form-field">
            <label>Email Address</label>
            <input type="email" name="pEmail" id='pEmail' value="{{$user->sponEmail}}">
        </div>

        <div class="form-field">
            <label>I.D Number</label>
            <input type="text" name="pIdNumber" id="pIdNumber" value="{{$user->sponId}}">
        </div>

        <div class="form-field">
            <br/>
        </div>

        <h4 class="fs-subtitle">Bursar Details</h4>
        <br/>

        <div class="form-field">
            <label>Company Name</label>
            <input type="text" name="bCompanyName" id='bCompanyName' value="{{$user->burCompanyName}}">
        </div>

        <div class="form-field">
            <label>Registration Number</label>
            <input type="text" name="bRegistrationNo" id="bRegistrationNo" value="{{$user->burRegNo}}">
        </div>

        <div class="form-field">
            <label>Contact Person</label>
            <input type="text" name="bContactPerson" id="bContactPerson" value="{{$user->burContactPerson}}">
        </div>

        <div class="form-field">
            <label>Contact Number</label>
            <input type="text" name="bContactNumber" id="bContactNumber" value="{{$user->burContactNo}}">
        </div>

        <div class="form-field">
            <label>Email Address</label>
            <input type="email" name="bEmail" id="bEmail" value="{{$user->burEmail}}">
        </div>

        <div class="form-field">
            <br/>
        </div>

        <div class="form-field">
            <label>First Name</label>
            <input type="text" name="kFirstName" id="kFirstName" value="{{$user->kinFirstName}}">
        </div>
        <div class="form-field">
            <label>Last Name</label>
            <input type="text" name="kLastName" id="kLastName" value="{{$user->kinLastName}}">
        </div>

        <div class="form-field">
            <label>Title</label>
            <select name="kTitle" id='kTitle'>
                <option value="Mr" <?php if($user->kinTitle == "Mr"){ echo "selected";} ?>>Mr</option>
                <option value="Ms" <?php if($user->kinTitle == "Ms"){ echo "selected";} ?>>Ms</option>
                <option value="Mrs" <?php if($user->kinTitle == "Mrs"){ echo "selected";} ?>>Mrs</option>
                <option value="Miss" <?php if($user->kinTitle == "Miss"){ echo "selected";} ?>>Miss</option>
            </select>
        </div>

        <div class="form-field">
            <label>I.D Number</label>
            <input type="text" name="kIdNumber" id="kIdNumber" value="{{$user->kinId}}">
        </div>

        <div class="form-field-big">
            <label>Physical Address</label>
            <input type="text" placeholder="Street No" name="kAddress1" id="kAddress1" style="width:98% !important" value="{{$user->kinAddressStr}}">
        </div>
        <div class="form-field-big">
            <input type="text" placeholder="Suburb" name="kAddress2" id="kAddress2" style="width:98% !important" value="{{$user->kinAddressSuburb}}">
        </div>
        <div class="form-field">
            <input type="text" placeholder="City" name="kAddressCity" id='kAddressCity' value="{{$user->kinAddressCity}}">
        </div>
        <div class="form-field">
            <input type="text" placeholder="postal Code" name="kAddressPostal" id="kAddressPostal" value="{{$user->kinAddressPost}}">
        </div>

        <div class="form-field">
            <label>Relationship</label>
            <input type="text" name="kRelationship" id="kRelationship" value="{{$user->kinRelationship}}">
        </div>

        <div class="form-field">
            <label>Telephone Number</label>
            <input type="text" name="kTeleNo" id='kTeleNo' value="{{$user->kinTelNo}}">
        </div>

        <div class="form-field">
            <label>Cellphone Number</label>
            <input type="text" name="kCellNo" id='kCellNo' value="{{$user->kinCellNo}}">
        </div>

        <div class="form-field">
            <label>Work Number</label>
            <input type="text" name="kWorkNo" id="kWorkNo" value="{{$user->kinWorkNo}}">
        </div>

        <div class="form-field">
            <label>Email Address</label>
            <input type="email" name="kEmail" id="kEmail" value="{{$user->kinEmail}}">
        </div>

        <div class="form-field">
            <br/>
        </div>

        <input type="submit" value="Update" class="profileSubmit" style="opacity:1;"/>
    </form>
</div>

@endsection