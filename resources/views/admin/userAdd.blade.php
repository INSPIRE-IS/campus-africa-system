@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/add-admin" method="post" autocomplete="off">
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

        <!-- <div class="form-field">
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
        </div> -->

        <!-- <div class="form-field">
            <label>Password</label><small>Leave blank to auto generate</small>
            <input type="password" name="password" id="password"  >
        </div> -->

        <div class="form-field">
            <label>Email Address *</label>
            <input type="email" name="email" id="email" required  >
        </div>

        <div class="form-field">
            <label>User Role Name</label>
            <input type="text" name="user_role_name" id="user_role_name"  >
        </div>

        <div class="form-field">
            <label>Permissions Template</label>
            <select name="template" id='template'>
                <option value="0" selected>None</option>
                <option value="1" >Sales Team</option>
                <option value="2" >Building Manager</option>
                <option value="3" >HO Approver</option>
            </select>
        </div>

        <div class="form-field">
            <br/><br/>
        </div>

        <!-- <div class="form-field">
            <br/>
            <ul style="list-style-type:none">
                <li style="display:inline-flex">
                    <input type="checkbox" id="email_details" name="email_details" value="1" style="width:50px !important" checked>
                    <label for="email_details" style="width:500px">Email login details to user.</label>
                </li>
            </ul>
        </div> -->

        <!-- <div class="form-field">
            <label>User Role</label>
            <select name="userRole" id='userRole'>
                <option value="1" >Super Admin</option>
                <option value="2" >Admin</option>
                <option value="3" >Consultant</option>
            </select>
        </div> -->

        <div>
            <label>Application Permissions</label>
            <br/>
            <ul style="list-style-type:none;">
                <li style="display:inline-flex">
                    <input type="checkbox" id="create_app" name="create_app" value="1">
                    <label for="create_app" style="width:500px">Create Applications</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="edit_app" name="edit_app" value="1">
                    <label for="edit_app" style="width:500px">Edit Applications</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="upload_docs" name="upload_docs" value="1">
                    <label for="upload_docs" style="width:500px">Upload Documents</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="delete_booking" name="delete_booking" value="1">
                    <label for="delete_booking" style="width:500px">Delete Booking</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="delete_uploaded" name="delete_uploaded" value="1">
                    <label for="delete_uploaded" style="width:500px">Delete Approved Documents</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_change_date" name="role_change_date" value="1">
                    <label for="role_change_date" style="width:500px">Change Lease Dates</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_change_bed" name="role_change_bed" value="1">
                    <label for="role_change_bed" style="width:500px">Change Bed Allocation</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_cancel_booking" name="role_cancel_booking" value="1">
                    <label for="role_cancel_booking" style="width:500px">Cancel & Restore Bookings</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_comment" name="role_comment" value="1">
                    <label for="role_comment" style="width:500px">Make Comments</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_lease_regen" name="role_lease_regen" value="1">
                    <label for="role_lease_regen" style="width:500px">Regen Lease</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_modify_approved" name="role_modify_approved" value="1">
                    <label for="role_modify_approved" style="width:500px">Modify Approved Lease</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_non_funding" name="role_non_funding" value="1">
                    <label for="role_non_funding" style="width:500px">Approve Non-Funding Documents</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_funding" name="role_funding" value="1">
                    <label for="role_funding" style="width:500px">Approve Funding Documents</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_app_lease" name="role_app_lease" value="1">
                    <label for="role_app_lease" style="width:500px">Approve Applications & Leases</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_nsfas_approval_upload" name="role_nsfas_approval_upload" value="1">
                    <label for="role_nsfas_approval_upload" style="width:500px">NSFAS File Upload</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_change_student_type" name="role_change_student_type" value="1">
                    <label for="role_change_student_type" style="width:500px">Change Student Type</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_change_building" name="role_change_building" value="1">
                    <label for="role_change_building" style="width:500px">Change Building</label>
                </li>

                
                
            </ul>
        </div>

        <div>
            <label>Residence Permissions</label>
            <br/>
            <ul style="list-style-type:none;">
                @foreach($propertyList as $property)
                    <li style="display:inline-flex">
                        <input type="checkbox" id="{{$property->id}}" name="{{$property->id}}" value="1" onChange="propertyInfoChange('{{$property->id}}')">
                        <label for="{{$property->id}}" style="width:500px">{{$property->residence}}</label>
                    </li>
                @endforeach
            </ul>
            <!-- <ul style="list-style-type:none;">
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_braamlofts" name="role_braamlofts" value="1">
                    <label for="role_braamlofts" style="width:500px">Braamlofts</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_80_jorissen" name="role_80_jorissen" value="1">
                    <label for="role_80_jorissen" style="width:500px">80 Jorissen</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_49_jorissen" name="role_49_jorissen" value="1">
                    <label for="role_49_jorissen" style="width:500px">49 Jorissen</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_dunvista_mansions" name="role_dunvista_mansions" value="1">
                    <label for="role_dunvista_mansions" style="width:500px">Dunvista Mansions</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_wynton_joy" name="role_wynton_joy" value="1">
                    <label for="role_wynton_joy" style="width:500px">Wynton Joy</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_126_siemert" name="role_126_siemert" value="1">
                    <label for="role_126_siemert" style="width:500px">126 Siemert</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_ymca" name="role_ymca" value="1">
                    <label for="role_ymca" style="width:500px">YMCA</label>
                </li>
            </ul> -->
        </div>

        <div>
            <label>Configuration Permissions</label>
            <br/>
            <ul style="list-style-type:none;">
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_manage_users" name="role_manage_users" value="1">
                    <label for="role_manage_users" style="width:500px">Manage Users</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_lookup" name="role_lookup" value="1">
                    <label for="role_lookup" style="width:500px">Manage Lookups</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_view_booking_summary" name="role_view_booking_summary" value="1">
                    <label for="role_view_booking_summary" style="width:500px">View Booking Summary</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_toggle_online_booking" name="role_toggle_online_booking" value="1">
                    <label for="role_toggle_online_booking" style="width:500px">Toggle Online Booking</label>
                </li>
                
                
            </ul>
        </div>

        <!-- <div>
            <label>Access Roles</label>
            <br/><br/><br/><br/>
            <ul style="list-style-type:none"> -->
                <!-- <li style="display:inline-flex">
                    <input type="checkbox" id="approve_docs" name="approve_docs" value="1">
                    <label for="approve_docs" style="width:500px">Approve Documents</label>
                </li> -->
                <!-- <li style="display:inline-flex">
                    <input type="checkbox" id="role_cancel_lease" name="role_cancel_lease" value="1">
                    <label for="role_cancel_lease" style="width:500px">Change Bed on Approved Lease</label>
                </li> -->
                <!-- <li style="display:inline-flex">
                    <input type="checkbox" id="role_update_booking_fee" name="role_update_booking_fee" value="1">
                    <label for="role_update_booking_fee" style="width:500px">Update All Student Booking Fees</label>
                </li> -->
            <!-- </ul>
        </div> -->

        <!-- <div>
            <br/><br/>
            <label>Property Access</label>
            <br/>
            
        </div> -->

    <br/>

        <input type="hidden" name="propertyAccess" id="propertyAccess"/>

        <input type="submit" value="Update" class="profileSubmit" style="opacity:1"/>
    </form>
</div>

<script>
    function propertyInfoChange(propertyName){
        let checked = document.getElementById(propertyName).checked

        if(checked == true){
            $("#propertyAccess").val($("#propertyAccess").val()+""+propertyName+",")
        }else{
            let data = $("#propertyAccess").val().split(",")

            let newString = "";
            data.forEach(function(item){
                if(item !== propertyName && item !== ""){
                    newString += item+",";
                }
            })

            $("#propertyAccess").val(newString);
        }
    }
    $("#template").on('change', function(){
        let templateName = $("#template").val();
        console.log(templateName)

        var checkboxes = document.querySelectorAll('input[type=checkbox]')

        for (var i = 0; i < checkboxes.length; i++) {
            document.getElementById(checkboxes[i].id).checked = false;
        }

        if(templateName == "1"){
            document.getElementById('create_app').checked = true;
            document.getElementById('edit_app').checked = true;
            document.getElementById('upload_docs').checked = true;
            document.getElementById('role_comment').checked = true;
        }

        if(templateName == "2"){
            document.getElementById('create_app').checked = true;
            document.getElementById('edit_app').checked = true;
            document.getElementById('upload_docs').checked = true;
            document.getElementById('role_comment').checked = true;
            document.getElementById('role_non_funding').checked = true;
            document.getElementById('role_change_bed').checked = true;
            document.getElementById('role_cancel_booking').checked = true;
            document.getElementById('role_lease_regen').checked = true;
            document.getElementById('role_view_booking_summary').checked = true;
            document.getElementById('role_change_student_type').checked = true;
        }

        if(templateName == "3"){
            document.getElementById('create_app').checked = true;
            document.getElementById('edit_app').checked = true;
            document.getElementById('upload_docs').checked = true;
            document.getElementById('role_comment').checked = true;
            document.getElementById('role_lease_regen').checked = true;
            document.getElementById('role_change_date').checked = true;
            document.getElementById('role_non_funding').checked = true;
            document.getElementById('role_funding').checked = true;
            document.getElementById('role_app_lease').checked = true;
        }
    })
</script>

@endsection