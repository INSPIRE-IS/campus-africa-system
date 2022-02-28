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
            <select name="property" id="property">
                <option value="All" selected>All Residences</option>
                @if(Auth::user()->role_braamlofts == 1)
                    <option value="Braamlofts">Braamlofts</option>
                @endif
                @if(Auth::user()->role_80_jorissen == 1)
                    <option value="80 Jorissen">80 Jorissen</option>
                @endif
                @if(Auth::user()->role_49_jorissen == 1)
                    <option value="49 Jorissen">49 Jorissen</option>
                @endif
                @if(Auth::user()->role_dunvista_mansions == 1)
                    <option value="Dunvista Mansions">Dunvista Mansions</option>
                @endif
                @if(Auth::user()->role_wynton_joy == 1)
                    <option value="Wynton Joy">Wynton Joy</option>
                @endif
                @if(Auth::user()->role_126_siemert == 1)
                    <option value="126 Siemert">126 Siemert</option>
                @endif
                @if(Auth::user()->role_ymca == 1)
                    <option value="YMCA">YMCA</option>
                @endif
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
                <option value="bookingStatus" <?php if( Session::get('fieldStatus') == "bookingStatus"){ echo "selected";}?>>Booking Status</option>
                <option value="proof_of_payment" <?php if( Session::get('fieldStatus') == "proof_of_payment"){ echo "selected";}?>>Proof of Payment</option>
                <option value="copyID" <?php if( Session::get('fieldStatus') == "All"){ echo "copyID";}?>>ID Copy</option>
                <option value="applicationStatus" <?php if( Session::get('fieldStatus') == "applicationStatus"){ echo "selected";}?>>Application Status</option>
                <option value="studentCard" <?php if( Session::get('fieldStatus') == "studentCard"){ echo "selected";}?>>Student Card Copy</option>
                <option value="proofRegistration" <?php if( Session::get('fieldStatus') == "proofRegistration"){ echo "selected";}?>>Proof of Registration</option>
                <option value="bursaryApproval" <?php if( Session::get('fieldStatus') == "bursaryApproval"){ echo "selected";}?>>NSFAS | Bursary Approval</option>
                <option value="bankStatement" <?php if( Session::get('fieldStatus') == "bankStatement"){ echo "selected";}?>>Bank Statements</option>
                <option value="leaseStatus" <?php if( Session::get('fieldStatus') == "leaseStatus"){ echo "selected";}?>>Lease Status</option>
            </select>
        </div>

        <div class="searchField" style="margin-top:26px;">
            <label>Status</label><br/>
            <select class="js-example-basic-multiple" multiple="multiple" name="status" id="status" style="width:90%;">
                <option value="Valid" <?php if(Session::get('status') !== null && in_array("Valid", Session::get('status'))){ echo "selected";} ?>>&#128994; Valid</option>
                <option value="Pending" <?php if(Session::get('status') !== null && in_array("Pending", Session::get('status'))){ echo "selected";} ?>>&#128993; Pending</option>
                <option value="Incomplete" <?php if(Session::get('status') !== null && in_array("Incomplete", Session::get('status'))){ echo "selected";} ?>>&#128308; Incomplete</option>
                <option value="NA" <?php if(Session::get('status') !== null && in_array("NA", Session::get('status'))){ echo "selected";} ?>>&xcirc; Not Applicable</option>
            </select>
        </div>

        <div class="searchField">
            <label>Application Date</label><br/>
            <input type="date" name="date" id="date" value="<?php echo Session::get('date');?>"/>
            
        </div>
        <div class="searchField">
            <br/><br/><br/><br/><br/><br/>
        </div>
        <div class="searchField">
            <br/><br/><br/><br/><br/><br/>
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
                        <th style="width:10%">BED CODE</th>
                        <th style="width:10%">DATE</th>
                        <th>STUDENT TYPE</th>
                        <th>PROCESSING STATUS</th>
                        <th style="background-color:#ccc; color:#000">BOOKING STATUS</th>
                        <th>PROOF OF PAYMENT</th>
                        <th>ID COPY</th>
                        <th style="background-color:#ccc; color:#000">APPLICATION STATUS</th>
                        <th>STUDENT CARD COPY</th>
                        <th>PROOF OF REGISTRATION</th>
                        <th>NSFAS | BURSARY APPROVAL</th>
                        <th>BANK STATEMENTS</th>
                        <th style="background-color:#ccc; color:#000">LEASE STATUS</th>
                    </tr>
                </thead>
                <tbody id="applicantsSection">                
                    
                </tbody>
            </table>

        </div>

    
</div>

<div class="clearSpaceLoginPage"></div>

<Script>


jQuery(document).ready( function($) {

    if(performance.navigation.type == 2){
        window.location.reload()
     }
    

    $("#property").on('change', function(){
        let propValue = $("#property").val();

        if(propValue == "Braamlofts"){
            $("#room_type").empty().append(
                '<option value="" <?php if(Session::get('room_type') == null){ echo "selected";}?>>Select One</option>'+
                '<option value="Single" <?php if(Session::get('room_type') == "Single"){ echo "selected";}?>>Single</option>'+
                '<option value="2 Sharing" <?php if(Session::get('room_type') == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>'+
                '<option value="3 Sharing" <?php if(Session::get('room_type') == "3 Sharing"){ echo "selected";}?>>3 Sharing</option>'
            )
        }else if(propValue == "80 Jorissen"){
            $("#room_type").empty().append(
                '<option value="" <?php if(Session::get('room_type') == null){ echo "selected";}?>>Select One</option>'+
                '<option value="Single" <?php if(Session::get('room_type') == "Single"){ echo "selected";}?>>Single</option>'+
                '<option value="2 Sharing" <?php if(Session::get('room_type') == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>'
            )
        }else if(propValue == "49 Jorissen"){
            $("#room_type").empty().append(
                '<option value="" <?php if(Session::get('room_type') == null){ echo "selected";}?>>Select One</option>'+
                '<option value="2 Sharing" <?php if(Session::get('room_type') == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>'+
                '<option value="2 Sharing 2 Bedroom" <?php if(Session::get('room_type') == "2 Sharing 2 Bedroom"){ echo "selected";}?>>2 Sharing | 2 Bedroom</option>'+
                '<option value="Single" <?php if(Session::get('room_type') == "Single"){ echo "selected";}?>>Single</option>'+
                '<option value="Single 2 bedroom" <?php if(Session::get('room_type') == "Single 2 bedroom"){ echo "selected";}?>>Single | 2 Bedroom</option>'+
                '<option value="Single 3 bedroom" <?php if(Session::get('room_type') == "Single 3 bedroom"){ echo "selected";}?>>Single | 3 Bedroom</option>'+
                '<option value="Single 4 bedroom" <?php if(Session::get('room_type') == "Single 4 bedroom"){ echo "selected";}?>>Single | 4 Bedroom</option>'
            )
        }else if(propValue == "Dunvista Mansions"){
            $("#room_type").empty().append(
                '<option value="" <?php if(Session::get('room_type') == null){ echo "selected";}?>>Select One</option>'+
                '<option value="Single" <?php if(Session::get('room_type') == "Single"){ echo "selected";}?>>Single</option>'+
                '<option value="2 Sharing" <?php if(Session::get('room_type') == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>'
            )
        }else if(propValue == "Wynton Joy"){
            $("#room_type").empty().append(
                '<option value="" <?php if(Session::get('room_type') == null){ echo "selected";}?>>Select One</option>'+
                '<option value="Single" <?php if(Session::get('room_type') == "Single"){ echo "selected";}?>>Single</option>'+
                '<option value="2 Sharing" <?php if(Session::get('room_type') == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>'
            )
        }else if(propValue == "126 Siemert"){
            $("#room_type").empty().append(
                '<option value="" <?php if(Session::get('room_type') == null){ echo "selected";}?>>Select One</option>'+
                '<option value="Single" <?php if(Session::get('room_type') == "Single"){ echo "selected";}?>>Single</option>'+
                '<option value="2 Sharing" <?php if(Session::get('room_type') == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>'
            )
        }else if(propValue == "YMCA"){
            $("#room_type").empty().append(
                '<option value="" <?php if(Session::get('room_type') == null){ echo "selected";}?>>Select One</option>'+
                '<option value="2 Sharing" <?php if(Session::get('room_type') == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>'+
                '<option value="2 Sharing 2 Bedroom" <?php if(Session::get('room_type') == "2 Sharing 2 Bedroom"){ echo "selected";}?>>2 Sharing | 2 Bedroom</option>'+
                '<option value="2 Sharing 3 Bedroom" <?php if(Session::get('room_type') == "2 Sharing 3 Bedroom"){ echo "selected";}?>>2 Sharing | 3 Bedroom</option>'+
                '<option value="3 Sharing" <?php if(Session::get('room_type') == "3 Sharing"){ echo "selected";}?>>3 Sharing</option>'+
                '<option value="Single Small" <?php if(Session::get('room_type') == "Single Small"){ echo "selected";}?>>Single | Small</option>'+
                '<option value="Single Medium" <?php if(Session::get('room_type') == "Single Medium"){ echo "selected";}?>>Single | Medium</option>'+
                '<option value="Single Large" <?php if(Session::get('room_type') == "Single Large"){ echo "selected";}?>>Single | Large</option>'+
                '<option value="Single 3 Bedroom" <?php if(Session::get('room_type') == "Single 3 Bedroom"){ echo "selected";}?>>Single | 3 Bedroom</option>'
            )
        } else {
            $("#room_type").empty().append(
                '<option value="" <?php if(Session::get('room_type') == null){ echo "selected";}?>>Select One</option>'
            );
        }
    });

    $("#property").val("<?php echo Session::get('property');?>").trigger('change')
    
    $('#searchButton').click();

    
});

$(function(){
    $(".js-example-basic-multiple").select2({
        placeholder: "- Select One -",
    });
});



       

</script>

@endsection