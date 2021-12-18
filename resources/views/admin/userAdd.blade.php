@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/add-admin" method="post">
    @csrf
        <br/>
        <br/>

        <div class="form-field">
            <label>First Name </label>
            <input type="text" name="firstName" id="firstName" required>
            <input type="hidden" name="userUid" id="userUid" required >
        </div>

        <div class="form-field">
            <label>Last Name</label>
            <input type="text" name="lastName" id="lastName"  >
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
            <input type="date" name="dob" id="dob"  >
        </div>

        <div class="form-field">
            <label>Cellphone Number</label>
            <input type="text" name="cell_no" id="cell_no"  >
        </div>

        <div class="form-field">
            <label>Password</label>
            <input type="password" name="password" id="password"  >
        </div>

        <div class="form-field">
            <label>Email Address *</label>
            <input type="email" name="email" id="email" required  >
        </div>

        <div class="form-field">
            <br/>
        </div>

        <!-- <div class="form-field">
            <label>User Role</label>
            <select name="userRole" id='userRole'>
                <option value="1" >Super Admin</option>
                <option value="2" >Admin</option>
                <option value="3" >Consultant</option>
            </select>
        </div> -->

        <div class="form-field" style="margin-bottom:850px;">
            <label>Access Roles</label>
            <br/><br/>
            <ul style="list-style-type:none">
                <li style="display:inline-flex">
                    <input type="checkbox" id="create_app" name="create_app" value="1">
                    <label for="create_app" style="width:400px">Create Applications</label>
                </li>
                <br/><br/>
                <li style="display:inline-flex">
                    <input type="checkbox" id="edit_app" name="edit_app" value="1">
                    <label for="edit_app" style="width:400px">Edit Applications</label>
                </li>
                <br/><br/>
                <li style="display:inline-flex">
                    <input type="checkbox" id="upload_docs" name="upload_docs" value="1">
                    <label for="upload_docs" style="width:400px">Upload Documents</label>
                </li>
                <br/><br/>
                <li style="display:inline-flex">
                    <input type="checkbox" id="approve_docs" name="approve_docs" value="1">
                    <label for="approve_docs" style="width:400px">Approve Documents</label>
                </li>
                <br/><br/>
                <li style="display:inline-flex">
                    <input type="checkbox" id="delete_booking" name="delete_booking" value="1">
                    <label for="delete_booking" style="width:400px">Delete Booking</label>
                </li>
                <br/><br/>
                <li style="display:inline-flex">
                    <input type="checkbox" id="delete_uploaded" name="delete_uploaded" value="1">
                    <label for="delete_uploaded" style="width:400px">Delete Approved Documents</label>
                </li>
                <br/><br/>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_braamlofts" name="role_braamlofts" value="1">
                    <label for="role_braamlofts" style="width:400px">Braamlofts</label>
                </li>
                <br/><br/>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_80_jorissen" name="role_80_jorissen" value="1">
                    <label for="role_80_jorissen" style="width:400px">80 Jorissen</label>
                </li>
                <br/><br/>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_49_jorissen" name="role_49_jorissen" value="1">
                    <label for="role_49_jorissen" style="width:400px">49 Jorissen</label>
                </li>
                <br/><br/>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_dunvista_mansions" name="role_dunvista_mansions" value="1">
                    <label for="role_dunvista_mansions" style="width:400px">Dunvista Mansions</label>
                </li>
                <br/><br/>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_wynton_joy" name="role_wynton_joy" value="1">
                    <label for="role_wynton_joy" style="width:400px">Wynton Joy</label>
                </li>
                <br/><br/>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_126_siemert" name="role_126_siemert" value="1">
                    <label for="role_126_siemert" style="width:400px">126 Siemert</label>
                </li>
                <br/><br/>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_ymca" name="role_ymca" value="1">
                    <label for="role_ymca" style="width:400px">YMCA</label>
                </li>
            </ul>
        </div>

    <br/>

        <input type="submit" value="Update" class="profileSubmit" style="opacity:1"/>
    </form>
</div>

@endsection