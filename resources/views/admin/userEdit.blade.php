@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/update-admin" method="post">
    @csrf
        <br/>
        <h1 class="header fs-title">User: {{$user->userFirstName}} {{$user->userLastname}}</h2>
        <br/>

        <div class="form-field">
            <label>First Name </label>
            <input type="text" name="firstName" id="firstName" value="{{$user->userFirstName}}" required>
            <input type="hidden" name="userUid" id="userUid" value="{{$user->id}}" required >
        </div>

        <div class="form-field">
            <label>Last Name</label>
            <input type="text" name="lastName" id="lastName" value="{{$user->userLastname}}" >
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
            <input type="date" name="dob" id="dob" value="{{$user->userDob}}" >
        </div>

        <div class="form-field">
            <label>Cellphone Number</label>
            <input type="text" name="cell_no" id="cell_no" value="{{$user->userCell}}" >
        </div>

        <div class="form-field">
            <label>Email Address *</label>
            <input type="email" name="email" id="email" required value="{{$user->email}}" >
        </div>

        <div class="form-field">
            <label>User Role</label>
            <select name="userRole" id='userRole'>
                <option value="1" <?php if($user->userTitle == "1"){echo "selected";} ?>>Super Admin</option>
                <option value="2" <?php if($user->userTitle == "2"){echo "selected";} ?>>Admin</option>
                <option value="3" <?php if($user->userTitle == "3"){echo "selected";} ?>>Consultant</option>
            </select>
        </div>

        <input type="submit" value="Update" class="profileSubmit" style="opacity:1"/>
    </form>
</div>

@endsection