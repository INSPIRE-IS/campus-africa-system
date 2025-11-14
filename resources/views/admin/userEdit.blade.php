@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/update-admin" method="post" autocomplete="off">
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

        <!-- <div class="form-field">
            <label>Title</label>
            <select name="title" id='title'>
                <option value="Mr" <?php //if($user->userTitle == "Mr"){echo "selected";} ?>>Mr</option>
                <option value="Ms" <?php //if($user->userTitle == "Ms"){echo "selected";} ?>>Ms</option>
                <option value="Mrs" <?php //if($user->userTitle == "Mrs"){echo "selected";} ?>>Mrs</option>
                <option value="Miss" <?php //if($user->userTitle == "Miss"){echo "selected";} ?>>Miss</option>
            </select>
        </div>

        <div class="form-field">
            <label>Date of Birth</label>
            <input type="date" name="dob" id="dob" value="{{$user->userDob}}" >
        </div>

        <div class="form-field">
            <label>Cellphone Number</label>
            <input type="text" name="cell_no" id="cell_no" value="{{$user->userCell}}" >
        </div> -->

        <div class="form-field">
            <label>Email Address *</label>
            <input type="email" name="email" id="email" required value="{{$user->email}}" >
        </div>

        <div class="form-field">
            <label>User Role Name</label>
            <input type="text" name="user_role_name" id="user_role_name" value="{{$user->user_role_name}}"  >
        </div>

        <!-- <div class="form-field">
            <br/>
            <ul style="list-style-type:none">
                <li style="display:inline-flex">
                    <input type="checkbox" id="email_details" name="email_details" value="1" style="width:50px !important" <?php //if($user->email_info == "1"){ echo "checked";}?>>
                    <label for="email_details" style="width:500px">Email login details to user.</label>
                </li>
            </ul>
        </div> -->
        <!-- <div class="form-field">
            <br/>
        </div> -->

        <!-- <div class="form-field">
             <label>User Role</label>
            <select name="userRole" id='userRole'>
                <option value="1" <?php if($user->userTitle == "1"){echo "selected";} ?>>Super Admin</option>
                <option value="2" <?php if($user->userTitle == "2"){echo "selected";} ?>>Admin</option>
                <option value="3" <?php if($user->userTitle == "3"){echo "selected";} ?>>Consultant</option>
            </select> 
        </div> -->

        <div class="form-field">
            <label>Permissions Template</label>
            <select name="template" id='template'>
                <option value="0" selected>None</option>
                <option value="1" <?php if($user->template == "1"){echo "selected";} ?>>Sales Team</option>
                <option value="2" <?php if($user->template == "2"){echo "selected";} ?>>Building Manager</option>
                <option value="3" <?php if($user->template == "3"){echo "selected";} ?>>HO Approver</option>
            </select>
        </div>

        <div class="form-field">
            <br/><br/>
        </div>

        <div>
            <label>Application Permissions</label>
            <br/>
            <ul style="list-style-type:none;">
                <li style="display:inline-flex">
                    <input type="checkbox" id="create_app" name="create_app" value="1" <?php if($user->role_create_app == "1"){echo "checked";} ?>>
                    <label for="create_app" style="width:500px">Create Applications</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="edit_app" name="edit_app" value="1" <?php if($user->role_edit_app == "1"){echo "checked";} ?>>
                    <label for="edit_app" style="width:500px">Edit Applications</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="upload_docs" name="upload_docs" value="1" <?php if($user->role_upload_docs == "1"){echo "checked";} ?>>
                    <label for="upload_docs" style="width:500px">Upload Documents</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="delete_booking" name="delete_booking" value="1"  <?php if($user->role_delete_booking == "1"){echo "checked";} ?>>
                    <label for="delete_booking" style="width:500px">Delete Booking</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="delete_uploaded" name="delete_uploaded" value="1" <?php if($user->role_delete_uploaded == "1"){echo "checked";} ?>>
                    <label for="delete_uploaded" style="width:500px">Delete Approved Documents</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_change_date" name="role_change_date" value="1" <?php if($user->role_change_date == "1"){echo "checked";} ?>>
                    <label for="role_change_date" style="width:500px">Change Lease Dates</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_change_bed" name="role_change_bed" value="1" <?php if($user->role_change_bed == "1"){echo "checked";} ?>>
                    <label for="role_change_bed" style="width:500px">Change Bed Allocation</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_cancel_booking" name="role_cancel_booking" value="1" <?php if($user->role_cancel_booking == "1"){echo "checked";} ?>>
                    <label for="role_cancel_booking" style="width:500px">Cancel & Restore Bookings</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_comment" name="role_comment" value="1" <?php if($user->role_comment == "1"){echo "checked";} ?>>
                    <label for="role_comment" style="width:500px">Make Comments</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_lease_regen" name="role_lease_regen" value="1" <?php if($user->role_lease_regen == "1"){echo "checked";} ?>>
                    <label for="role_lease_regen" style="width:500px">Regen Lease</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_modify_approved" name="role_modify_approved" value="1" <?php if($user->role_modify_approved == "1"){echo "checked";} ?>>
                    <label for="role_modify_approved" style="width:500px">Modify Approved Lease</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_non_funding" name="role_non_funding" value="1" <?php if($user->role_non_funding == "1"){echo "checked";} ?>>
                    <label for="role_non_funding" style="width:500px">Approve Non-Funding Documents</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_app_lease" name="role_app_lease" value="1" <?php if($user->role_app_lease == "1"){echo "checked";} ?>>
                    <label for="role_app_lease" style="width:500px">Approve Applications & Leases</label>
                </li>                
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_funding" name="role_funding" value="1" <?php if($user->role_funding == "1"){echo "checked";} ?>>
                    <label for="role_funding" style="width:500px">Approve Funding, Other & Fee Waiver</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_other_reject_doc" name="role_other_reject_doc" value="1" <?php if($user->role_other_reject_doc == "1"){echo "checked";} ?>>
                    <label for="role_other_reject_doc" style="width:500px">Reject Other Documents</label>
                </li>                
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_nsfas_approval_upload" name="role_nsfas_approval_upload" value="1" <?php if($user->role_nsfas_approval_upload == "1"){echo "checked";} ?>>
                    <label for="role_nsfas_approval_upload" style="width:500px">NSFAS File Upload</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_change_student_type" name="role_change_student_type" value="1" <?php if($user->role_change_student_type == "1"){echo "checked";} ?>>
                    <label for="role_change_student_type" style="width:500px">Change Student Type</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_waive_fee" name="role_waive_fee" value="1" <?php if($user->role_waive_fee == "1"){echo "checked";} ?>>
                    <label for="role_waive_fee" style="width:500px">Request Fee Waiver</label>
                </li>
                <!-- <li style="display:inline-flex">
                    <input type="checkbox" id="role_approve_waive" name="role_approve_waive" value="1" <?php if($user->role_approve_waive == "1"){echo "checked";} ?>>
                    <label for="role_approve_waive" style="width:500px">Approve Fee Waiver</label>
                </li> -->

                <li style="display:inline-flex">
                    <input type="checkbox" id="role_bm_report" name="role_bm_report" value="1" <?php if($user->role_bm_report == "1"){echo "checked";} ?>>
                    <label for="role_bm_report" style="width:500px">Building Manager Report</label>
                </li>

                <li style="display:inline-flex">
                    <input type="checkbox" id="role_mda_report" name="role_mda_report" value="1" <?php if($user->role_mda_report == "1"){echo "checked";} ?>>
                    <label for="role_mda_report" style="width:500px">MDA Report</label>
                </li>
                
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_change_building" name="role_change_building" value="1" <?php if($user->role_change_building == "1"){echo "checked";} ?>>
                    <label for="role_change_building" style="width:500px">Change Building</label>
                </li>

                <li style="display:inline-flex">
                    <input type="checkbox" id="role_all_apps_report" name="role_all_apps_report" value="1" <?php if($user->role_all_apps_report == "1"){echo "checked";} ?>>
                    <label for="role_all_apps_report" style="width:500px">All Application Report</label>
                </li>

                
                
            </ul>
        </div>

        <br/><br/>

        <div>
            <label>Residence Permissions</label>
            <br/>

            <ul style="list-style-type:none;">
                @foreach($propertyList as $property)
                    <li style="display:inline-flex">
                        <input type="checkbox" id="{{$property->id}}" name="{{$property->id}}" value="1" onChange="propertyInfoChange('{{$property->id}}')" <?php foreach($userAccess as $access){if($access->property == $property->id){ echo "checked";}} ?>>
                        <label for="{{$property->id}}" style="width:500px">{{$property->residence}}</label>
                    </li>
                @endforeach
            </ul>
            
            <!-- <ul style="list-style-type:none;">
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_braamlofts" name="role_braamlofts" value="1" <?php if($user->role_braamlofts == "1"){echo "checked";} ?>>
                    <label for="role_braamlofts" style="width:500px">Braamlofts</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_80_jorissen" name="role_80_jorissen" value="1" <?php if($user->role_80_jorissen == "1"){echo "checked";} ?>>
                    <label for="role_80_jorissen" style="width:500px">80 Jorissen</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_49_jorissen" name="role_49_jorissen" value="1" <?php if($user->role_49_jorissen == "1"){echo "checked";} ?>>
                    <label for="role_49_jorissen" style="width:500px">49 Jorissen</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_dunvista_mansions" name="role_dunvista_mansions" value="1" <?php if($user->role_dunvista_mansions == "1"){echo "checked";} ?>>
                    <label for="role_dunvista_mansions" style="width:500px">Dunvista Mansions</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_wynton_joy" name="role_wynton_joy" value="1" <?php if($user->role_wynton_joy == "1"){echo "checked";} ?>>
                    <label for="role_wynton_joy" style="width:500px">Wynton Joy</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_126_siemert" name="role_126_siemert" value="1" <?php if($user->role_126_siemert == "1"){echo "checked";} ?>>
                    <label for="role_126_siemert" style="width:500px">126 Siemert</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_ymca" name="role_ymca" value="1" <?php if($user->role_ymca == "1"){echo "checked";} ?>>
                    <label for="role_ymca" style="width:500px">YMCA</label>
                </li>
            </ul> -->
        </div>

        <br/><br/>

        <div>
            <label>Configuration Permissions</label>
            <br/>
            <ul style="list-style-type:none;">
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_manage_users" name="role_manage_users" value="1" <?php if($user->role_manage_users == "1"){echo "checked";} ?>>
                    <label for="role_manage_users" style="width:500px">Manage Users</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_lookup" name="role_lookup" value="1" <?php if($user->role_lookup == "1"){echo "checked";} ?>>
                    <label for="role_lookup" style="width:500px">Manage Lookups & Emails</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_view_booking_summary" name="role_view_booking_summary" value="1" <?php if($user->role_view_booking_summary == "1"){echo "checked";} ?>>
                    <label for="role_view_booking_summary" style="width:500px">View Booking Summary</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="role_toggle_online_booking" name="role_toggle_online_booking" value="1" <?php if($user->role_toggle_online_booking == "1"){echo "checked";} ?>>
                    <label for="role_toggle_online_booking" style="width:500px">Toggle Online Booking</label>
                </li>

            </ul>
        </div>

        <!-- <div>
            <label>Access Roles</label>
            <br/><br/><br/><br/>
            <ul style="list-style-type:none;"> -->
                <!-- <li style="display:inline-flex">
                    <input type="checkbox" id="approve_docs" name="approve_docs" value="1" <?php if($user->role_approve_docs == "1"){echo "checked";} ?>>
                    <label for="approve_docs" style="width:500px">Approve Documents</label>
                </li> -->
                <!-- <li style="display:inline-flex">
                    <input type="checkbox" id="role_cancel_lease" name="role_cancel_lease" value="1" <?php if($user->role_cancel_lease == "1"){echo "checked";} ?>>
                    <label for="role_cancel_lease" style="width:500px">Change Bed on Approved Lease</label>
                </li> -->
                <!-- <li style="display:inline-flex">
                    <input type="checkbox" id="role_update_booking_fee" name="role_update_booking_fee" value="1" <?php if($user->role_update_booking_fee == "1"){echo "checked";} ?>>
                    <label for="role_update_booking_fee" style="width:500px">Update All Student Booking Fees</label>
                </li> -->
            <!-- </ul>
        </div> -->

        <!-- <div>
            <br/><br/>
            <label>Property Access</label>
            <br/>
            
        </div> -->
        <input type="hidden" name="propertyAccess" id="propertyAccess" value="{{$accessString}}"/>

        <input type="submit" value="Update" class="profileSubmit" style="opacity:1"/>
    </form>

    <!-- <form action="/delete-admin" method="post" autocomplete="off"> -->
        <!-- @csrf -->

        <input type="submit" value="Delete User" class="profileSubmit" id="deleteUser" style="opacity:1"/>
        <!-- <input type="hidden" name="userUid" id="userUid" value="{{$user->id}}" required > -->
    <!-- </form> -->
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
    
    $("#deleteUser").on('click', function(){
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete this user",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "/delete-admin",
                    data: {
                        userUid: "{{$user->id}}",
                    },
        
                    success: function (data) {
                        
                        window.location.replace('/users');
        
                    },
                });
            }
        });
    })

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
            document.getElementById('role_change_building').checked = true;
            document.getElementById('role_toggle_online_booking').checked = true;
            document.getElementById('role_waive_fee').checked = true;
            document.getElementById('role_bm_report').checked = true;
            
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
            document.getElementById('role_view_booking_summary').checked = true;
            document.getElementById('role_mda_report').checked = true;
            // document.getElementById('role_approve_waive').checked = true;
        }
    })
</script>

@endsection