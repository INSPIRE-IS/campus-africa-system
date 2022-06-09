@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="container">

<br/>

    @if(Auth::user()->role_create_app == 1)
        <a href="/new/student"><button type="button" class="inactiveBtn">Add New Student</button></a>
    @endif

    @if(Auth::user()->role_nsfas_approval_upload == 1)
        <a href="/nsfas-approval-upload"><button type="button" class="inactiveBtn">NSFAS Approval Upload</button></a>
    @endif

    
    <div class="appSearchForm" style="margin-top:-35px">
        <div class="searchField">
        <label></label><br/>
            <select name="property" id="property" onChange="changeResidence()">
                <option value="All" selected>All Residences</option>
                @foreach($allowedResidences as $residence)
                    <option value="{{$residence->id}}" <?php if(Session::get('property') == $residence->id){echo "selected";}?>>{{$residence->residence}}</option>
                @endforeach
            </select>
        </div>
        <div class="searchField">
        <label>Room Type</label><br/>
            <select name="room_type" id="room_type">
                <option value="">Select One</option>
            </select>
        </div>
        <div class="searchField">
            <label>Applicant</label><br/>
            <input type="text" name="applicant" id="applicant" value="<?php echo Session::get('applicant');?>"/>
        </div>

        <div class="searchField">
            <label>Student Number</label><br/>
            <input type="text" name="studentNo" id="studentNo" value="<?php echo Session::get('studentNo');?>"/>
        </div>
        <div class="searchField">
            <label>ID Number / Passport</label><br/>
            <input type="text" name="idPassport" id="idPassport" value="<?php echo Session::get('idPassport');?>"/>
        </div>
        <div class="searchField">
            <label>Booking Reference</label><br/>
            <input type="text" name="bookingRef" id="bookingRef" value="<?php echo Session::get('bookingRef');?>"/>
        </div>

        <div class="searchField">
        <label>Student Type</label><br/>
            <select name="student_type" id='student_type'>
                <option value="All" selected <?php if( Session::get('student_type') == "All"){ echo "selected";}?>>All Student Types</option>
                <option value="NSFAS" <?php if( Session::get('student_type') == "NSFAS"){ echo "selected";}?>>NSFAS</option>
                <option value="Bursary" <?php if( Session::get('student_type') == "Bursary"){ echo "selected";}?>>Bursary</option>
                <option value="Cash / Direct" <?php if( Session::get('student_type') == "Cash / Direct"){ echo "selected";}?>>Cash</option>
            </select>
        </div>

        <div class="searchField">
        <label>Documents</label><br/>
            <select name="fieldStatus" id='fieldStatus'>
                <option value="All" selected <?php if( Session::get('fieldStatus') == "All"){ echo "selected";}?>>- Select One - </option>
                <option value="rob_10_fee" <?php if( Session::get('fieldStatus') == "rob_10_fee"){ echo "selected";}?>>Fee</option>
                <option value="rob_15_id" <?php if( Session::get('fieldStatus') == "rob_15_id"){ echo "selected";}?>>ID</option>
                <option value="rob_20_card" <?php if( Session::get('fieldStatus') == "rob_20_card"){ echo "selected";}?>>Card</option>
                <option value="rob_25_reg" <?php if( Session::get('fieldStatus') == "rob_25_reg"){ echo "selected";}?>>Reg</option>
                <option value="rob_30_funds" <?php if( Session::get('fieldStatus') == "rob_30_funds"){ echo "selected";}?>>Funds</option>
                <option value="rob_35_info" <?php if( Session::get('fieldStatus') == "rob_35_info"){ echo "selected";}?>>Info</option>
                <option value="rob_40_bed" <?php if( Session::get('fieldStatus') == "rob_40_bed"){ echo "selected";}?>>Bed</option>
                <option value="rob_45_other" <?php if( Session::get('fieldStatus') == "rob_45_other"){ echo "selected";}?>>Other</option>
                <option value="rob_50_approve" <?php if( Session::get('fieldStatus') == "rob_50_approve"){ echo "selected";}?>>Approve</option>
                <option value="rob_55_lease" <?php if( Session::get('fieldStatus') == "rob_55_lease"){ echo "selected";}?>>Lease</option>
            </select>
        </div>

        <div class="searchField" style="margin-top:26px;">
            <label>Status</label><br/>
            <select class="js-example-basic-multiple" multiple="multiple" name="status" id="status" style="width:90%;">
                <option value="2" <?php if(Session::get('status') !== null && in_array("2", Session::get('status'))){ echo "selected";} ?>>&#128994; Valid</option>
                <option value="1" <?php if(Session::get('status') !== null && in_array("1", Session::get('status'))){ echo "selected";} ?>>&#128993; Pending</option>
                <option value="0" <?php if(Session::get('status') !== null && in_array("0", Session::get('status'))){ echo "selected";} ?>>&#128308; Incomplete</option>
                <option value="3" <?php if(Session::get('status') !== null && in_array("3", Session::get('status'))){ echo "selected";} ?>>&xcirc; Not Applicable</option>
            </select>
        </div>

        <div class="searchField">
            <label>Application Date</label><br/>
            <input type="date" name="date" id="date" value="<?php echo Session::get('date');?>"/>
            
        </div>
        <div class="searchField" style="height:80px;">
            <label>Beds</label><br/>
            <select name="beds" id='beds'>
                <option value="All" selected <?php if( Session::get('beds') == "All"){ echo "selected";}?>>All</option>
                <option value="Allocated" <?php if( Session::get('beds') == "Allocated"){ echo "selected";}?>>Allocated</option>
                <option value="Unallocated" <?php if( Session::get('beds') == "Unallocated"){ echo "selected";}?>>Unallocated</option>
            </select>
        </div>
        <div class="searchField" style="height:80px;">
            <br/>
            <ul style="list-style-type:none;">
                <li style="display:inline-flex">
                    <input type="checkbox" id="show_cancelled" name="show_cancelled" value="1" style="width:50px;
                    height:25px; margin-top:3px;" <?php if(Session::get('show_cancelled') == 'true'){ echo "checked";} ?>>
                    <label for="show_cancelled" style="width:500px">Show Cancelled</label>
                </li>
            </ul>
            <!-- <br/><br/><br/> -->
            <!-- <label>Processing Status</label><br/>
            <select name="processingStatus" id='processingStatus'>
                <option value="All" selected >All</option>
                <option value="Approved" <?php if( Session::get('processingStatus') == "Approved"){ echo "selected";}?>>&#128994; Valid</option>
                <option value="Pending" <?php if( Session::get('processingStatus') == "Pending"){ echo "selected";}?>>&#128993; Pending</option>
                <option value="Incomplete" <?php if( Session::get('processingStatus') == "Incomplete"){ echo "selected";}?>>&#128308; Incomplete</option>
                <option value="NA" <?php if( Session::get('processingStatus') == "NA"){ echo "selected";}?>>⚪ NA</option>
            </select> -->
        </div>
        

        <div class="searchTab" >
            <button type="button" id="searchButton">Search</button><br/>
            <button type="button" id="resetFilter" style="margin-top:5px;">Reset Filters</button>
        </div>
        <div class="indexTab flex" id="applicationIndex" >
            <label>Legend</label>
            <div class="keys">
                <div id="indexOne"><div class="button approved"></div><span class="text">Approved</span></div>
                <div id="indexOne"><div class="button action"></div><span class="text">Action Required</span></div>
                <div id="indexOne"><div class="button rejected"></div><span class="text">Rejected / Not Uploaded</span></div>
                <div id="indexOne"><div class="button na"></div><span class="text">Not Applicable</span></div>
            </div>
        </div>
        
    </div>

    <div class="clearSpaceLogin"></div>

    <!-- <div class="container"> -->
        <div class="ApplicationResults">



            <table class="applicationTable" id="application_table">
                <thead>
                    <tr>
                        <th class="first">BOOKING REFERENCE</th>
                        <th style="width:50%">APPLICANT | ROOM</th>
                        <th style="width:10%">DATE</th>
                        <th>STUDENT TYPE</th>

                        <th>FEE</th>
                        <th>ID</th>
                        <th>CARD</th>
                        <th>REG</th>
                        <th>FUNDS</th>
                        <th>INFO</th>
                        <th>BED</th>
                        <th>OTHER</th>
                        <th>APPROVE</th>
                        <th>LEASE</th>
                    </tr>
                </thead>
                <tbody id="applicantsSection">                
                    
                </tbody>
            </table>

        </div>

    
</div>

@if(!$agent->isMobile())
    <div class="clearSpaceLoginPage"></div>
@endif

<input type="hidden" id="firstLoad" value="1"/>

<Script>

function changeResidence(){
        if($("#property").val() !== "All"){
            $("#room_type").empty().append('<option value="" selected disabled>Loading</option>')

            $.ajax({
                type: "post",
                url: "/get-room-types-id",
                data: {residence: $("#property").val()},

                success: function (data) {          
                    $("#room_type").empty().append('<option value="" selected disabled>Select a Room Type</option>')
                    data.forEach(function(index){
                        let roomTypeSelected = '<?php echo Session::get("room_type"); ?>';
                        let roomPropertySelected = '<?php echo Session::get("property"); ?>';
                        let selected = "";

                        if($("#property").val() == roomPropertySelected && roomTypeSelected == index.name){
                            selected = "selected"
                        }
                        $("#room_type").append('<option value="'+index.id+'" '+selected+'>'+index.name+'</option>')
                    })
                },
            });
        }
    };


jQuery(document).ready( function($) {
    
    changeResidence();
    $('#searchButton').click();

    
});

$(function(){
    $(".js-example-basic-multiple").select2({
        placeholder: "- Select One -",
    });
});



       

</script>

@endsection