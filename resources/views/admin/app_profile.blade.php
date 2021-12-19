@extends('layouts.admin')

@section('content')

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="greyAppSection">
    <div class="container">
        <div class="col-3" id="col1" style="margin-top:16px;">
        <div class="flex"><span id="approvedAddr" class="hidden">&#128994;</span><span id="rejectedAddr">&#128308;</span><p class="listHeader">Physical Address</p></div>
            <p class="list">
                {{$user[0]['userInfo']['userAddress1']}}<br/>
                {{$user[0]['userInfo']['userAddress2']}}<br/>
                {{$user[0]['userInfo']['userAddressCity']}}<br/>
                {{$user[0]['userInfo']['userAddressPostal']}}<br/>
            </p>
        </div>
        <div class="col-3" id="col2" style="margin-top:16px;">
            
        <div class="flex"><span id="approvedKin" class="hidden">&#128994;</span><span id="rejectedKin">&#128308;</span><p class="listHeader">Next of Kin</p></div>
            <p class="list">
                {{$user[0]['userInfo']['kinTitle']}} {{$user[0]['userInfo']['kinFirstName']}} {{$user[0]['userInfo']['kinLastName']}}<br/>
                {{$user[0]['userInfo']['kinRelationship']}}<br/>
                {{$user[0]['userInfo']['kinNationality']}}<br/>
                @if($user[0]['userInfo']['kinId']){{$user[0]['userInfo']['kinId']}}<br/>@endif
                @if($user[0]['userInfo']['kinPassportNo']){{$user[0]['userInfo']['kinPassportNo']}}<br/>@endif
                {{$user[0]['userInfo']['kinCellNo']}}<br/>
                {{$user[0]['userInfo']['kinEmail']}}<br/>
                {{$user[0]['userInfo']['kinAddressStr']}}, {{$user[0]['userInfo']['kinAddressSuburb']}}, <br/>
                {{$user[0]['userInfo']['kinAddressCity']}}, {{$user[0]['userInfo']['kinAddressPostal']}}<br/>
            </p>

        </div>
        <div class="col-3" id="col3" style="margin-top:16px;">
            @if($user[0]['userInfo']['studentType'] == "Cash / Direct")
            <div class="flex"><span id="approvedSpon" class="hidden">&#128994;</span><span id="rejectedSpon">&#128308;</span><p class="listHeader">Sponsor/Parent </p></div>
                <p class="list">
                    {{$user[0]['userInfo']['sponFirstName']}} {{$user[0]['userInfo']['sponLastName']}}<br/>
                    {{$user[0]['userInfo']['sponCellNo']}}<br/>
                    {{$user[0]['userInfo']['sponEmail']}}<br/>
                    {{$user[0]['userInfo']['sponId']}}<br/>
                    {{$user[0]['userInfo']['pNationality']}}<br/>
                    @if($user[0]['userInfo']['sponId']){{$user[0]['userInfo']['sponId']}}<br/>@endif
                    @if($user[0]['userInfo']['pPassportNo']){{$user[0]['userInfo']['pPassportNo']}}<br/>@endif
                    {{$user[0]['userInfo']['pAddress1']}}, {{$user[0]['userInfo']['pAddress2']}}, <br/>
                    {{$user[0]['userInfo']['pAddressCity']}}, {{$user[0]['userInfo']['pAddressPostal']}}<br/>
                </p>
            @endif

            @if($user[0]['userInfo']['studentType'] == "Bursary")
            <div class="flex"><span id="approvedBur" class="hidden">&#128994;</span><span id="rejectedBur">&#128308;</span><p class="listHeader">Bursar</p></div>
                <p class="list">
                    {{$user[0]['userInfo']['burCompanyName']}}<br/>
                    {{$user[0]['userInfo']['burRegNo']}}<br/>
                    {{$user[0]['userInfo']['burContactPerson']}} - {{$user[0]['userInfo']['burContactNo']}}<br/>
                    {{$user[0]['userInfo']['burEmail']}}<br/>
                    {{$user[0]['userInfo']['bAddress1']}}, {{$user[0]['userInfo']['bAddress2']}}, <br/>
                    {{$user[0]['userInfo']['bAddressCity']}}, {{$user[0]['userInfo']['bAddressPostal']}}<br/>
                </p>
            @endif
            <br/>
            
            <!-- <a href="/student/upload/{{$user[0]['userInfo']['id']}}"><button type="button" class="inactiveBtn">Upload Documents</button></a> -->
        </div>
        
    </div>
</div>

<div class="container">
    
    <div class="appRow">
        <div class="cards">

            <div class="customWhiteCard" >
                <h2 class="appHeader">Applicant Details</h2>
                <p class="InfoList">
                    {{$user[0]['userInfo']['userFirstName']}} {{$user[0]['userInfo']['userLastname']}}<br/>
                    {{$user[0]['userInfo']['userDob']}}<br/>
                    {{$user[0]['userInfo']['userNationality']}}<br/>
                    @if($user[0]['userInfo']['userId']){{$user[0]['userInfo']['userId']}}<br/>@endif
                    @if($user[0]['userInfo']['userPassport']){{$user[0]['userInfo']['userPassport']}}<br/>@endif
                    <br/>
                    {{$user[0]['userInfo']['userEmail']}}<br/>
                    {{$user[0]['userInfo']['userCell']}}<br/>
                    <br/>
                    @foreach($lookups as $lookup) @if($lookup->id == $user[0]['userInfo']['institutionStudy']) {{$lookup->lookup_name}} @endif @endforeach<br/>
                    {{$user[0]['userInfo']['studentNo']}}<br/>
                    @foreach($lookups as $lookup) @if($lookup->id == $user[0]['userInfo']['faculty']) {{$lookup->lookup_name}} @endif @endforeach<br/>
                    {{$user[0]['userInfo']['yearOfStudy']}}
                </p>
            </div>

            <div class="customWhiteCard" id="applicationCard" style="margin-top:20px;">
            <div class="flex">
                @if($booking->bed_code)
                    <span id="bedAllocatedYes" style="margin-top:10px;">&#128994;</span>
                @else
                    <span id="bedAllocatedNo" style="margin-top:10px;">&#128308;</span>
                @endif
                <h2 class="appHeader">Application Summary</h2>
            </div>
                <h4 class="cardSubHeaderGrey">{{$booking->property}}</h4>
                <h4 class="cardHeader" >{{$booking->roomType}}</h4>
                <h4 class="cardHeader" >@if($booking->floor){{$booking->floor}} Floor,@endif @if($booking->Unit)Unit {{$booking->unit}},@endif @if($booking->room)Room {{$booking->room}},@endif @if($booking->bed)Bed {{$booking->bed}}@endif</h4>
                <h4 class="cardHeader" >@if($booking->bed_code)Bed Code: {{$booking->bed_code}}@endif</h4>
                <h4 class="cardHeader">
                    @if($booking->lease_from_date) Lease From: {{$booking->lease_from_date}} at {{$booking->lease_from_time}} <br/>@endif
                    @if($booking->lease_to_date) Lease To: {{$booking->lease_to_date}} at {{$booking->lease_to_time}} <br/>@endif

                    @if($booking->lease_from_date && $booking->lease_to_date) Lease Duration: <?php $date1 = new DateTime($booking->lease_from_date); $date2 = new DateTime($booking->lease_to_date); $interval = $date1->diff($date2); if($interval->y > 1 || $interval->y < 1){ $years = "years";}else{$years = "year";} echo $interval->y . " ".$years.", " . $interval->m." months, ".$interval->d." days "; ?> @endif
                </h4>
                <br/>
                @if($residence)
                    <h2 class="cardSubHeaderGrey">BOOKING FEE PAID</h2>
                    <h4 class="cardHeader" >
                        R{{$residence->booking_rate}}
                    </h4>
                    <br/>
                @endif
                <div class="buttons">
                    <button type="button" class="appBooking" onClick="changeBooking('{{$user[0]['userInfo']['id']}}', 'generate')">Change this booking</button>
                    @if(Auth::user()->role_delete_booking == 1)
                        <button type="button" class="appBooking" onClick="deleteBooking('{{$booking->id}}', '{{$user[0]['userInfo']['id']}}')">Delete this booking</button>
                    @endif
                </div>
            </div>

            <div class="customWhiteCard" id="actionListCard" style="margin-top:20px;">
                <div class="">
                    <h2 class="appHeader">Action List
                        <select id="actionListSort">
                            <option selected value="all">All Users</option>
                            <option value="admin">Admin Users</option>
                            <option value="applicant">Applicant</option>
                        </select>
                    </h2>
                </div>

                <div class="actionItemList">

                    @foreach($actions as $action)

                        <div class="infoBlock">
                            <div class="responsible">
                                <p>{{$action->actionUserDisplayName}}</p>
                                @if($action->actionUserRole !== "0" )
                                    <button type="button" class="admin">Admin</button>
                                @else
                                    <button type="button" class="applicant">Applicant</button>
                                @endif
                                <p class="datetime">  {{substr($action->created_at, 8, 2)}} | {{substr($action->created_at, 5, 2)}} | {{substr($action->created_at, 0, 4)}} | {{substr($action->created_at, 11, 5)}} </p>
                            </div>
                            <div class="information">
                                <p>{{$action->action}}</p>
                            </div>
                        </div>

                    @endforeach
                </div>
            </div>

        </div>
    </div>
    <div class="appRow">
        <div class="cards">

            <div class="customWhiteCard">
                <!-- <button type="button" class="reminder">Send reminder</button> -->
                <h2 class="appSubHeader bold">PAYMENT TYPE </h2>
                <p class="appSubHeader">
                    @if($user[0]['userInfo']['studentType'] == "Cash / Direct") Cash @else {{$user[0]['userInfo']['studentType']}} @endif
                </p>
                <br/>

                <h2 class="appSubHeader bold">BOOKING FEE</h2>
                <p class="appSubHeader">
                    @if($residence !== null) {{$residence->booking_rate}} @endif
                </p>
                <br/>

                <h2 class="appSubHeader bold">PAID</h2>
                <p class="appSubHeader">
                    @if($user[0]['userInfo']['bPayment'] == "Directly to the STUDENT") <span style="color:red;">Directly to the STUDENT</span>  @elseif($user[0]['userInfo']['bPayment'] !== "") {{$user[0]['userInfo']['bPayment']}}@endif
                </p>
            </div>

            <div class="customWhiteCard" id="documentsCard" style="margin-top:20px;">
                <div class="bigHeader appSearchForm">
                    <h2 class="appHeader">Documents
                        <select id="documentSortList">
                            <option selected value="all">All Documents</option>
                            <option value="review">Uploaded For Review</option>
                            <option value="approved">Approved</option>
                            <option value="noDoc">No Document</option>
                        </select>
                    </h2>
                    <div class="indexTab flex " style="width:100%">
                        <div class="keys">
                            <div id="indexOne"><div class="button approved"></div><span class="text">APPROVED</span></div>
                            <div id="indexOne"><div class="button action"></div><span class="text">ACTION REQUIRED</span></div>
                            <div id="indexOne"><div class="button rejected"></div><span class="text">NO DOCUMENT</span></div>
                        </div>
                    </div>
                </div>

                <br/><br/><br/><br/>

                <div id="documentsSectionDisplay">

                    <?php $i = 0; ?>

                    @foreach($user[0]["files"] as $file)


                            <div class="section">
                                <div class="label">
                                    
                                    @if($file["field"] === "studentCard")
                                        STUDENT CARD / PROOF OF STUDENT NUMBER
                                    @elseif($file["field"] === "proof_of_payment")
                                        BOOKING FEE PROOF OF PAYMENT
                                    @elseif($file["field"] === "copyID")
                                        COPY OF ID DOCUMENT
                                    @elseif($file["field"] === "proofRegistration")
                                        PROOF OF REGISTRATION
                                    @elseif($file["field"] === "bursaryApproval")
                                        NSFAS / BURSARY APPROVAL
                                    @elseif($file["field"] === "bankStatement")
                                        BANK STATEMENT
                                    @elseif($file["field"] === "moveIn")
                                        LEASE
                                    @endif
                                </div>
                                <div class="status" id="{{$file['field']}}Status">
                                    @if($file["field"] === "moveIn")
                                        @if($file["status"] === "XApproved" || $file["status"] === "Approved")
                                            <div class="button approved"></div>
                                        @elseif($file["status"] === "Uploaded")
                                            <div class="button action"></div>
                                        @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                            <div class="button rejected"></div>
                                        @endif
                                    @else
                                        @if($file["status"] === "Uploaded" )
                                            <div class="button action"></div>
                                        @elseif($file["status"] === "XApproved")
                                            <div class="button approved"></div>
                                        @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                            <div class="button rejected"></div>
                                        @elseif($file["status"] === "na")
                                            <div class="button na"></div>
                                        @endif
                                    @endif
                                    
                                </div>

                                <div class="banner" id="{{$file['field']}}Banner">
                                    @if($file["field"] === "moveIn")
                                        <a href="/storage/files/{{$user[0]['userInfo']['userId']}}/Lease.pdf" download="{{$user[0]['userInfo']['userFirstName']}}_{{$user[0]['userInfo']['userLastname']}}_{{$user[0]['userInfo']['userId']}}_Lease.pdf"><button type="button" id="myBtn_download">Download Lease</button></a>
                                        <a href="#"><button type="button" id="myBtn_regen_lease">Regen Lease</button></a>
                                    @endif

                                    @if($file["status"] === "Uploaded" || $file["status"] === "XApproved")
                                        <button type="button" onClick="
                                            openModal(
                                                '{{$file['field']}}', 
                                                '{{$file['url']}}',
                                                '{{$file['uploaded_by']}}',
                                                '{{$file['created_at']}}',
                                                '{{$file['id']}}',
                                                '{{$file['status']}}'
                                            )" 
                                            id="myBtn_<?php echo $i; ?>">View Document
                                        </button>
                                    @else
                                        
                                            <!-- <button type="button" id="{{$file['field']}}Error" class="error">Send reminder</button> -->

                                            @if(Auth::user()->role_upload_docs == 1)
                                        
                                                <label for="{{$file['field']}}" id="{{$file['field']}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;">
                                                    <span class="text">Upload</span>
                                                    <i class="fas fa-file-upload" ></i>
                                                </label>
                                                <input type="file" id="{{$file['field']}}" hidden/>

                                            @endif
                                    @endif
                                </div>
                            </div>

                            <?php $i++; ?>


                    @endforeach

                </div>


            </div>

        </div>
    </div>
</div>

<div id="myModalEditApp" class="modal">

    <div class="modal-content " id="editModalHeight" style="<?php if(Auth::user()->role_change_date == "1"){echo "height:700px;";}else{echo "height:530px;";} ?>">
    <span class="closeBooking" style="float:right; font-size:25px; cursor:pointer">&times;</span>
    <div class="form-card">
        <h2>Change this booking</h2>

        <input type="hidden" id="bookingId"/>

        <br/><br/>
        <div class="form-field">
            <label>Residence * <span id="property_error" class="hidden"><small style="color:red">This field is required</small></span></label>

            <select name="property" id='property' class="required">
                <option value="" selected disabled>Select a Residence</option>
                @if(Auth::user()->role_braamlofts == 1)
                    <option value="Braamlofts" <?php if($booking->property == "Braamlofts"){echo "selected";}?>>Braamlofts</option>
                @endif
                @if(Auth::user()->role_80_jorissen == 1)
                    <option value="80 Jorissen" <?php if($booking->property == "80 Jorissen"){echo "selected";}?>>80 Jorissen</option>
                @endif
                @if(Auth::user()->role_49_jorissen == 1)
                    <option value="49 Jorissen" <?php if($booking->property == "49 Jorissen"){echo "selected";}?>>49 Jorissen</option>
                @endif
                @if(Auth::user()->role_dunvista_mansions == 1)
                    <option value="Dunvista Mansions" <?php if($booking->property == "Dunvista Mansions"){echo "selected";}?>>Dunvista Mansions</option>
                @endif
                @if(Auth::user()->role_wynton_joy == 1)
                    <option value="Wynton Joy" <?php if($booking->property == "Wynton Joy"){echo "selected";}?>>Wynton Joy</option>
                @endif
                @if(Auth::user()->role_126_siemert == 1)
                    <option value="126 Siemert" <?php if($booking->property == "126 Siemert"){echo "selected";}?>>126 Siemert</option>
                @endif
                @if(Auth::user()->role_ymca == 1)
                    <option value="YMCA" <?php if($booking->property == "YMCA"){echo "selected";}?>>YMCA</option>
                @endif
            </select>
        </div>

        <div class="form-field">
            <label>Room Type * <span id="roomType_error" class="hidden"><small style="color:red">This field is required</small></span></label>
            <select name="roomType" id='roomType' class="required">
            <option value="" selected disabled>Select a Room Type</option>
                <?php if($booking->property == "Braamlofts"){?>
                    <option value="Single" <?php if($booking->roomType == "Single"){ echo "selected";}?>>Single</option>
                    <option value="2 Sharing" <?php if($booking->roomType == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>
                    <option value="3 Sharing" <?php if($booking->roomType == "3 Sharing"){ echo "selected";}?>>3 Sharing</option>
                <?php } ?>

                <?php if($booking->property == "80 Jorissen" || $booking->property == "Dunvista Mansions" || $booking->property == "Wynton Joy" || $booking->property == "126 Siemert"){?>
                    <option value="Single" <?php if($booking->roomType == "Single"){ echo "selected";}?>>Single</option>
                    <option value="2 Sharing" <?php if($booking->roomType == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>
                <?php } ?>

                <?php if($booking->property == "49 Jorissen"){?>
                    <option value="2 Sharing" <?php if($booking->roomType == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>
                    <option value="Single 2 bedroom" <?php if($booking->roomType == "Single 2 bedroom"){ echo "selected";}?>>Single | 2 Bedroom</option>
                    <option value="Single 3 bedroom" <?php if($booking->roomType == "Single 3 bedroom"){ echo "selected";}?>>Single | 3 Bedroom</option>
                <?php } ?>

                <?php if($booking->property == "YMCA"){?>
                    <option value="2 Sharing" <?php if($booking->roomType == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>
                    <option value="3 Sharing" <?php if($booking->roomType == "3 Sharing"){ echo "selected";}?>>3 Sharing</option>
                    <option value="Single small" <?php if($booking->roomType == "Single small"){ echo "selected";}?>>Single | small</option>
                    <option value="Single medium" <?php if($booking->roomType == "Single medium"){ echo "selected";}?>>Single | Medium</option>
                    <option value="Single large" <?php if($booking->roomType == "Single large"){ echo "selected";}?>>Single | Large</option>
                <?php } ?>
            </select>
        </div>

        <div class="form-field">
            <label>Floor</label>
            <select name="floor" id='floor'>
                <!--  -->
            </select>
        </div>

        <div class="form-field">
            <label>Unit</label>
            <select name="unit" id='unit'>
                <!--  -->
            </select>
        </div>

        <div class="form-field">
            <label>Room</label>
            <select name="room" id='room'>
                <!--  -->
            </select>
        </div>

        <div class="form-field">
            <label>Bed</label>
            <select name="bed" id='bed'>
                <!--  -->
            </select>
        </div>

        <div class="form-field">
            <label>Bed Code * <span id="bed_code_error" class="hidden"><small style="color:red">This field is required</small></span></label>
            <select name="bed_code" id='bed_code'>
                <!--  -->
            </select>
        </div>
        
        

        {{-- <div class="form-field-big" id="ChangeBookingOnly">
            <button type="button" id="changeBookingType" class="profileSubmit" style="width:98%; border:none; padding:12px; border-radius:10px;">Change booking</button>
        </div> --}}

        <div id="LeaseInfo">
            <div class="form-field">
                <br/>
            </div>
            @if(Auth::user()->role_change_date == "1")
                <div class="form-field">
                    <label>Lease From Date * <span id="lease_from_date_error" class="hidden"><small style="color:red">This field is required</small></span></label>
                    <input type="date" name="lease_from_date" id="lease_from_date" <?php foreach($lookups as $lookup){if($lookup->group_id == "4"){echo "value='".$lookup->lease_from_date."'";}}?>>
                </div>
                <div class="form-field">
                    <label>Lease From Time * <span id="lease_from_time_error" class="hidden"><small style="color:red">This field is required</small></span></label>
                    <input type="time" id="lease_from_time" name="lease_from_time" <?php foreach($lookups as $lookup){if($lookup->group_id == "4"){echo "value='".$lookup->lease_from_time."'";}}?>>
                </div>
                <div class="form-field">
                    <label>Lease To Date * <span id="lease_to_date_error" class="hidden"><small style="color:red">This field is required</small></span></label>
                    <input type="date" name="lease_to_date" id="lease_to_date" <?php foreach($lookups as $lookup){if($lookup->group_id == "4"){echo "value='".$lookup->lease_to_date."'";}}?>>
                </div>
                <div class="form-field">
                    <label>Lease To Time * <span id="lease_to_time_error" class="hidden"><small style="color:red">This field is required</small></span></label>
                    <input type="time" id="lease_to_time" name="lease_to_time" <?php foreach($lookups as $lookup){if($lookup->group_id == "4"){echo "value='".$lookup->lease_to_time."'";}}?>>
                </div>
            @endif

            <div class="form-field-big" >
                <button type="button" id="generateLease" class="profileSubmit" style="width:98%; border:none; padding:12px; border-radius:10px;">Generate Lease</button>
            </div>
        </div>

        <div class="form-field-big" id="ChangeBookingOnly">
            <button type="button" id="changeBookingType" class="profileSubmit" style="width:98%; border:none; padding:12px; border-radius:10px;">Change booking</button>
        </div>
    </div>


    </div>

</div>

<div id="myModal" class="modal">

    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalHeader"></h2>
        <small>Uploaded by <span id="uploadedBy"></span> at <span id="uploadedTime"></span> on the <span id="uploadedDate"></span></small>
        <br/>
        
            <input type="hidden" id="FileIdForApproval">
            <input type="hidden" id="FileNameForApproval">
            
            <span id="actionUserName_error" class="hidden" ><small style="color:red">This field is required</small></span>
            <br/>
            <input type="text" id="actionUserName" placeholder="Name" value="{{Auth::user()->userFirstName}}">
            
            <br/>
            
            <div class="flex" style="margin-top:35px;" id="buttonSection">
                    <div id="reasonBox" style="height:38px; margin-top:-21px; width:65%">
                        <span id="rejectReason_error" class="hidden" ><small style="color:red">This field is required</small></span>
                        <br/>
                        <input type="text" id="rejectReason" placeholder="Type field for rejection reason" style="width:100%">
                    </div>
                    <button type="button" class="rejected button" id="rejected" onClick="actionFile('Rejected', '')">Reject with reason</button>
                    <button type="button" class="amend button" id="amend"  onClick="actionFile('Approved', 'Amend')">Amend</button>
                    <button type="button" class="approve button" id="approved" onClick="actionFile('Approved', '')">Approve</button>
            </div>

            <div id="infoChecker">
                <br/><hr/><br/>
                <div class="flex">
                    <p id="infoToCheck"></p>
                    <div id="infoButtons" class="hidden">
                        <button type="button" class="approve button" style="height:40px; margin-left:100px;" id="approveData" onClick="approveData()">Approve</button>
                        <button type="button" class="rejected button" style="height:40px; margin-left:20px;" id="RejectDAta" onClick="rejectData()">Reject</button>
                    </div>
                </div>
                <br/><br/><hr/>
            </div>

            <div id="approvedDocs" class="hidden">
                @if(Auth::user()->role_delete_uploaded == 1)
                    <button type="button" class="rejected button" id="delete" onClick="removeFile()">Delete</button>
                @endif
            </div>

        <div class="pdfSection"></div>
    </div>

</div>

<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>

<input type="hidden" id="id_data_approved" value="{{$user[0]['userInfo']['id_data_approved']}}"/>
<input type="hidden" id="student_card_approved" value="{{$user[0]['userInfo']['student_card_approved']}}"/>
<input type="hidden" id="registration_approved" value="{{$user[0]['userInfo']['registration_approved']}}"/>
<input type="hidden" id="bursary_approved" value="{{$user[0]['userInfo']['bursary_approved']}}"/>
<input type="hidden" id="bank_approved" value="{{$user[0]['userInfo']['bank_approved']}}"/>
<input type="hidden" id="address_approved" value="{{$user[0]['userInfo']['address_approved']}}"/>
<input type="hidden" id="kin_approved" value="{{$user[0]['userInfo']['kin_approved']}}"/>
<input type="hidden" id="parent_approved" value="{{$user[0]['userInfo']['parent_approved']}}"/>
<input type="hidden" id="burser_approved" value="{{$user[0]['userInfo']['burser_approved']}}"/>
<input type="hidden" id="bed_allocated" value="{{$booking->bed_code}}"/>


<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    checkUserStatus();

    // MainAppSection
    function checkUserStatus(){

        if($("#parent_approved").val() == "1" || $("#parent_approved").val() == "2"){
            $("#rejectedSpon").addClass('hidden')
            $("#approvedSpon").removeClass('hidden')
        }

        if($("#burser_approved").val() == "1" || $("#burser_approved").val() == "2"){
            $("#rejectedBur").addClass('hidden')
            $("#approvedBur").removeClass('hidden')
        }

        if($("#kin_approved").val() == "1" || $("#kin_approved").val() == "2"){
            $("#rejectedKin").addClass('hidden')
            $("#approvedKin").removeClass('hidden')
        }

        if($("#address_approved").val() == "1" || $("#address_approved").val() == "2"){
            $("#rejectedAddr").addClass('hidden')
            $("#approvedAddr").removeClass('hidden')
        }

        if("{{$user[0]['userInfo']['studentType']}}" == "Cash / Direct"){
            
            if($("#id_data_approved").val() == "2" && $("#student_card_approved").val() == "2" && $("#registration_approved").val() == "2" && $("#bank_approved").val() == "2" && $("#parent_approved").val() == "2" && "{{$user[0]['userInfo']['applicationStatus']}}" !== "Approved Confirmed" && $("#bed_allocated").val() !== ""){
                $("#MainAppSection").removeClass('hidden')
                document.getElementById("col1").style.marginTop = "-35px";
                document.getElementById("col2").style.marginTop = "-35px";
                document.getElementById("col3").style.marginTop = "-35px";
            }

        }else if("{{$user[0]['userInfo']['studentType']}}" == "NSFAS" || "{{$user[0]['userInfo']['studentType']}}" == "Bursary"){
            
            if($("#id_data_approved").val() == "2" && $("#student_card_approved").val() == "2" && $("#registration_approved").val() == "2" && $("#bursary_approved").val() == "2" && $("#burser_approved").val() == "2" && "{{$user[0]['userInfo']['applicationStatus']}}" !== "Approved Confirmed" && $("#bed_allocated").val() !== ""){
                $("#MainAppSection").removeClass('hidden')
                document.getElementById("col1").style.marginTop = "-35px";
                document.getElementById("col2").style.marginTop = "-35px";
                document.getElementById("col3").style.marginTop = "-35px";
            }
        }
    }


    function approveData(){
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to approve this data",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.isConfirmed) {
                var FileNameForApproval = $("#FileNameForApproval").val();
                $("#approveData").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                var rejBtn = document.getElementById("RejectDAta");
                rejBtn.setAttribute("disabled", "disabled");

                $.ajax({
                    type: "post",
                    url: "/action-data",
                    data: {
                        FileNameForApproval: FileNameForApproval, 
                        type: "1", 
                        userId: "{{$user[0]['userInfo']['id']}}",
                    },

                    success: function (data) {
                        
                        var preview1 = document.getElementById("rejected");
                        preview1.removeAttribute("disabled", "disabled");
                        var preview2 = document.getElementById("approved");
                        preview2.removeAttribute("disabled", "disabled");
                        var preview3 = document.getElementById("amend");
                        preview3.removeAttribute("disabled", "disabled");
                        var preview4 = document.getElementById("rejectReason");
                        preview4.removeAttribute("readonly", "readonly");
                        var preview5 = document.getElementById("actionUserName");
                        preview5.removeAttribute("readonly", "readonly");
                        var rejBtn = document.getElementById("RejectDAta");
                        rejBtn.removeAttribute("disabled", "disabled");

                        $("#infoButtons").addClass('hidden');
                        $("#approveData").empty().append('Approve')

                        /*$("#infoChecker").addClass('hidden')
                        $("#infoToCheck").empty();
                        */

                        if(FileNameForApproval == "copyID"){
                            $("#id_data_approved").val('1')
                        }else if(FileNameForApproval == "studentCard"){
                            $("#student_card_approved").val('1')
                        }else if(FileNameForApproval == "bankStatement"){
                            $("#bank_approved").val('1')
                            $("#parent_approved").val('1')
                        }else if(FileNameForApproval == "proofRegistration"){
                            $("#registration_approved").val('1')
                        }else if(FileNameForApproval == "bursaryApproval"){
                            $("#bursary_approved").val('1')
                            $("#burser_approved").val('1')
                        }

                        checkUserStatus();
                    }
                })

            }
        });
    }

    function rejectData(){
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to reject this data",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.isConfirmed) {
                var FileNameForApproval = $("#FileNameForApproval").val();
                $("#RejectDAta").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                var approveData = document.getElementById("approveData");
                approveData.setAttribute("disabled", "disabled");

                $.ajax({
                    type: "post",
                    url: "/action-data",
                    data: {
                        FileNameForApproval: $("#FileNameForApproval").val(), 
                        type: "0", 
                        userId: "{{$user[0]['userInfo']['id']}}",
                    },

                    success: function (data) {
                        
                        var approveData = document.getElementById("approveData");
                        approveData.removeAttribute("disabled", "disabled");

                        $("#RejectDAta").empty().append('Reject')

                        var modal = document.getElementById("myModal");
                        modal.style.display = "none";

                    },
                });
            }
        })
    }

    function changeBooking(bookingId, type){

        if(type == "generate"){
            //$("#ChangeBookingOnly").addClass('hidden')
            //$("#LeaseInfo").removeClass('hidden')
            // document.getElementById("editModalHeight").style.height = "700px";
            $("#lease_from_date").addClass("required")
            $("#lease_from_time").addClass("required")
            $("#lease_to_date").addClass("required")
            $("#lease_to_time").addClass("required")
        }else{
            //$("#ChangeBookingOnly").removeClass('hidden')
            //$("#LeaseInfo").addClass('hidden')
            // document.getElementById("editModalHeight").style.height = "530px";
            $("#lease_from_date").removeClass("required")
            $("#lease_from_time").removeClass("required")
            $("#lease_to_date").removeClass("required")
            $("#lease_to_time").removeClass("required")
        }

        $("#bookingId").val(bookingId)
        var modal = document.getElementById("myModalEditApp");
        modal.style.display = "block";

        callRoomDetails('floor','', '', '', '', '', 'open')
        
    }

    $("#generateLease").on('click', function(){
        
        if($("#property").val() == "" || $("#roomType").val() == "" || $("#bed_code").val() == null || $("#lease_from_date").val() == "" || $("#lease_from_time").val() == "" || $("#lease_to_date").val() == "" || $("#lease_to_time").val() == ""){
            if($("#property").val() == ""){
                $("#property").addClass("fieldError")
                $("#property_error").removeClass("hidden")
            }

            if($("#roomType").val() == ""){
                $("#roomType").addClass("fieldError")
                $("#roomType_error").removeClass("hidden")
            }

            if($("#bed_code").val() == null){
                $("#bed_code").addClass('fieldError')
                $("#bed_code_error").removeClass('hidden')
            }

            if($("#lease_from_date").val() == ""){
                $("#lease_from_date").addClass("fieldError")
                $("#lease_from_date_error").removeClass("hidden")
            }
            if($("#lease_from_time").val() == ""){
                $("#lease_from_time").addClass("fieldError")
                $("#lease_from_time_error").removeClass("hidden")
            }
            if($("#lease_to_date").val() == ""){
                $("#lease_to_date").addClass("fieldError")
                $("#lease_to_date_error").removeClass("hidden")
            }
            if($("#lease_to_time").val() == ""){
                $("#lease_to_time").addClass("fieldError")
                $("#lease_to_time_error").removeClass("hidden")
            }
        }else{

            $("#property").removeClass("fieldError")
            $("#property_error").addClass("hidden")
            $("#roomType").removeClass("fieldError")
            $("#roomType_error").addClass("hidden")
            $("#bed_code").removeClass('fieldError')
            $("#bed_code_error").addClass('hidden')
            $("#lease_from_date").removeClass("fieldError")
            $("#lease_from_date_error").addClass("hidden")
            $("#lease_from_time").removeClass("fieldError")
            $("#lease_from_time_error").addClass("hidden")
            $("#lease_to_date").removeClass("fieldError")
            $("#lease_to_date_error").addClass("hidden")
            $("#lease_to_time").removeClass("fieldError")
            $("#lease_to_time_error").addClass("hidden")

            $("#generateLease").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');
            
            
            $.ajax({
                type: "post",
                url: "/createLease/"+$("#bookingId").val(),
                data: {
                    bookingId: $("#bookingId").val(), 
                    property: $("#property").val(),
                    roomType: $("#roomType").val(), 
                    floor: $("#floor").val(),
                    unit: $("#unit").val(),
                    room: $("#room").val(),
                    bed: $("#bed").val(),
                    bed_code: $("#bed_code").val(),
                    lease_from_date: $("#lease_from_date").val(),
                    lease_from_time: $("#lease_from_time").val(),
                    lease_to_date: $("#lease_to_date").val(),
                    lease_to_time: $("#lease_to_time").val(),
                },

                success: function (data) {
                    
                    window.location.reload();
                },
            });

        }
    })

    function callRoomDetails(callId, floor, unit, room, bed, bed_code, action){
        $.ajax({
            type: "post",
            url: "/get-booking-info",
            data: {
                bookingId: $("#bookingId").val(), 
                property: $("#property").val(),
                roomType: $("#roomType").val(), 
                floor:floor,
                unit:unit,
                room:room,
                bed:bed,
                bed_code:bed_code,
            },

            success: function (data) {
                
                //No Room data been selected, just Property & room Type
                roomData = data[2]; 
                type = []

                console.log(callId, floor, unit, room, bed, bed_code, action)

                roomData.forEach(function (arrayItem) {
                    type.push(arrayItem[''+callId+''])
                })

                var unique = [...new Set(type)];
                $("#"+callId).empty();

                if(unit == ""){
                    $("#unit").empty();
                }
                if(room == ""){
                    $("#room").empty();
                }
                if(bed == ""){
                    $("#bed").empty();
                }
                if(bed_code == ""){
                    $("#bed_code").empty();
                }

                console.log(unique)
                $("#"+callId).append('<option value="">Select One</option>');

                unique.forEach(function (arrayItem) {
                    if(action == "open" && data[1][callId] == arrayItem){
                        value = '<option value="'+arrayItem+'" selected>'+arrayItem+'</option>';
                    }else{
                        value = '<option value="'+arrayItem+'">'+arrayItem+'</option>';
                    }
                    
                    $("#"+callId).append(value);
                })

                //If room selected & open

                if(action == "open"){
                    units = [];
                    rooms = [];
                    beds = [];
                    bed_codes = [];

                    roomData.forEach(function (arrayItem) {
                        if(arrayItem['floor'] == data[1]['floor']){
                            units.push(arrayItem['unit'])
                        }
                        if(arrayItem['floor'] == data[1]['floor'] && arrayItem['unit'] == data[1]['unit']){
                            rooms.push(arrayItem['room'])
                        }
                        if(arrayItem['floor'] == data[1]['floor'] && arrayItem['unit'] == data[1]['unit'] && arrayItem['room'] == data[1]['room']){
                            beds.push(arrayItem['bed'])
                        }
                        if(arrayItem['floor'] == data[1]['floor'] && arrayItem['unit'] == data[1]['unit'] && arrayItem['room'] == data[1]['room'] && arrayItem['bed'] == data[1]['bed']){
                            bed_codes.push(arrayItem['bed_code'])
                        }
                    })

                    var uniqueUnits = [...new Set(units)];
                    var uniqueRooms = [...new Set(rooms)];
                    var uniqueBeds = [...new Set(beds)];
                    var uniqueBedCodes = [...new Set(bed_codes)];

                    uniqueUnits.forEach(function (arrayItem) {
                        if(data[1]['unit'] == arrayItem){
                            value = '<option value="'+arrayItem+'" selected>'+arrayItem+'</option>';
                        }else{
                            value = '<option value="'+arrayItem+'">'+arrayItem+'</option>';
                        }
                        $("#unit").append(value);
                    })
                    uniqueRooms.forEach(function (arrayItem) {
                        if(data[1]['room'] == arrayItem){
                            value = '<option value="'+arrayItem+'" selected>'+arrayItem+'</option>';
                        }else{
                            value = '<option value="'+arrayItem+'">'+arrayItem+'</option>';
                        }
                        $("#room").append(value);
                    })
                    uniqueBeds.forEach(function (arrayItem) {
                        if(data[1]['bed'] == arrayItem){
                            value = '<option value="'+arrayItem+'" selected>'+arrayItem+'</option>';
                        }else{
                            value = '<option value="'+arrayItem+'">'+arrayItem+'</option>';
                        }
                        $("#bed").append(value);
                    })
                    uniqueBedCodes.forEach(function (arrayItem) {
                        if(data[1]['bed_code'] == arrayItem){
                            value = '<option value="'+arrayItem+'" selected>'+arrayItem+'</option>';
                        }else{
                            value = '<option value="'+arrayItem+'">'+arrayItem+'</option>';
                        }
                        $("#bed_code").append(value);
                    })
                }
            },
        });
    }

    $("#floor").on('change', function(){
        callRoomDetails('unit', $("#floor").val(), '', '', '', '', '')
    })

    $("#unit").on('change', function(){
        callRoomDetails('room', $("#floor").val(), $("#unit").val(), '', '', '', '')
    })

    $("#room").on('change', function(){
        callRoomDetails('bed', $("#floor").val(), $("#unit").val(), $("#room").val(), '', '', '')
    })

    $("#bed").on('change', function(){
        callRoomDetails('bed_code', $("#floor").val(), $("#unit").val(), $("#room").val(), $("#bed").val(), '', '')
    })

    

    

    var modalBooking = document.getElementById("myModalEditApp");
    var spanBooking = document.getElementsByClassName("closeBooking")[0];

    spanBooking.onclick = function() {
        modalBooking.style.display = "none";
    }

    $("#changeBookingType").on('click', function(){


        if($("#property").val() == "" || $("#roomType").val() == "" || $("#bed_code").val() == null){
            if($("#property").val() == ""){
                $("#property").addClass("fieldError")
                $("#property_error").removeClass("hidden")
            }

            if($("#roomType").val() == ""){
                $("#roomType").addClass("fieldError")
                $("#roomType_error").removeClass("hidden")
            }

            if($("#bed_code").val() == null){
                $("#bed_code").addClass('fieldError')
                $("#bed_code_error").removeClass('hidden')
            }
        }else{
            $("#changeBookingType").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
            $.ajax({
                    type: "post",
                    url: "/change-booking",
                    data: {
                        bookingId: $("#bookingId").val(), 
                        property: $("#property").val(),
                        roomType: $("#roomType").val(), 
                        floor: $("#floor").val(),
                        unit: $("#unit").val(),
                        room: $("#room").val(),
                        bed: $("#bed").val(),
                        bed_code: $("#bed_code").val(),
                    },

                    success: function (data) {
                        
                        window.location.reload();
                    },
                });
        }
    })

    $("#property").on('blur', function(){
        if($("#property").val() == ""){
            $("#property").addClass("fieldError")
            $("#property_error").removeClass("hidden")
        }else{
            $("#property").removeClass("fieldError")
            $("#property_error").addClass("hidden")
        }
    })

    $("#roomType").on('blur', function(){
        if($("#roomType").val() == ""){
            $("#roomType").addClass("fieldError")
            $("#roomType_error").removeClass("hidden")
        }else{
            $("#roomType").removeClass("fieldError")
            $("#roomType_error").addClass("hidden")
        }
    })

    $("#roomType").on('change', function(){
        callRoomDetails('floor','', '', '', '', '', '')
    })

    $("#bed_code").on('blur, change', function(){
        if($("#bed_code").val() == null){
            $("#bed_code").addClass("fieldError")
            $("#bed_code_error").removeClass("hidden")
        }else{
            $("#bed_code").removeClass("fieldError")
            $("#bed_code_error").addClass("hidden")
        }
    })


    $("#property").on('change', function(){
        let propValue = $("#property").val();

        if(propValue == "Braamlofts"){
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option><option value="3 Sharing">3 Sharing</option>')
        }else if(propValue == "80 Jorissen"){
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "49 Jorissen"){
            $("#roomType").empty().append('<option value="">Select One</option><option value="2 Sharing">2 Sharing</option><option value="Single 2 bedroom">Single | 2 Bedroom</option><option value="Single 3 bedroom">Single | 3 Bedroom</option>')
        }else if(propValue == "Dunvista Mansions"){
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "Wynton Joy"){
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "126 Siemert"){
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "YMCA"){
            $("#roomType").empty().append('<option value="">Select One</option><option value="2 Sharing">2 Sharing</option><option value="3 Sharing">3 Sharing</option><option value="Single small">Single | small</option><option value="Single medium">Single | Medium</option><option value="Single large">Single | Large</option>')
        }

        $("#floor").empty();
        $("#unit").empty();
        $("#room").empty();
        $("#bed").empty();
        $("#bed_code").empty();
    })

    function openModal(field, url, uploaded_by, created_at, fileId, status){

        if("{{Auth::user()->role_approve_docs}}" !== "1"){
            document.getElementById("approveData").setAttribute("disabled", "disabled");
            document.getElementById("RejectDAta").setAttribute("disabled", "disabled");
            document.getElementById("rejected").setAttribute("disabled", "disabled");
            document.getElementById("approved").setAttribute("disabled", "disabled");

            document.getElementById("amend").removeAttribute("disabled", "disabled");
        }

        if(field == "studentCard"){
            var title = "Student card / Proof of student number";
            if($("#student_card_approved").val() != "1" && $("#student_card_approved").val() != "2"){
                var approved = 0;
            }else{
                var approved = 1;
            }
            var Data = "<span style='font-weight:bold'>Student Number:</span> {{$user[0]['userInfo']['studentNo']}}";
        }else if(field == "proof_of_payment"){
            var title = "Proof of payment";
            var approved = 1;
        }else if(field == "copyID"){
            var title = "Copy Of ID document";
            if($("#id_data_approved").val() != "1" && $("#id_data_approved").val() != "2"){
                var approved = 0;
            }else{
                var approved = 1;
            }
            if("{{$user[0]['userInfo']['userTitle']}}" == "Mr"){
                var gender = "<br/><span style='font-weight:bold'>Gender:</span> Male"
            }else{
                var gender = "<br/><span style='font-weight:bold'>Gender:</span> Female"
            }
            if("{{$user[0]['userInfo']['userNationality']}}" == "South Africa"){
                var IdData = "<br/><span style='font-weight:bold'>ID Number:</span>{{$user[0]['userInfo']['userId']}}";
            }else{
                var IdData = "<br/><span style='font-weight:bold'>Passport Number:</span>{{$user[0]['userInfo']['userPassport']}}";
            }
            var Data = "<span style='font-weight:bold'>Name:</span> {{$user[0]['userInfo']['userFirstName']}} {{$user[0]['userInfo']['userLastname']}}"+
                "<br/><span style='font-weight:bold'>Nationality:</span>{{$user[0]['userInfo']['userNationality']}}"+
                IdData+
                gender;
        }else if(field == "proofRegistration"){
            var title = "Proof of registration";
            if($("#registration_approved").val() != "1" && $("#registration_approved").val() != "2"){
                var approved = 0;
            }else{
                var approved = 1;
            }

            var Institution = "<?php foreach($lookups as $lookup){ if($lookup->id == $user[0]['userInfo']['institutionStudy']){ echo $lookup->lookup_name;}} ?>";
            var Faculty = "<?php foreach($lookups as $lookup){ if($lookup->id == $user[0]['userInfo']['faculty']){ echo $lookup->lookup_name;}} ?>";

            var Data = "<span style='font-weight:bold'>Institution:</span> "+Institution+
                "<br/><span style='font-weight:bold'>Faculty:</span> "+Faculty;
        }else if(field == "bursaryApproval"){
            var title = "NSFAS / Bursary Approval";
            if($("#bursary_approved").val() != "1" && $("#bursary_approved").val() != "2"){
                var approved = 0;
            }else{
                var approved = 1;
            }

            if("{{$user[0]['userInfo']['bint']}}"){
                bInt = "<span style='font-weight:bold'>Intermediary:</span> {{$user[0]['userInfo']['bint']}}<br/>"
            }else{
                bInt = ""
            }
            if("{{$user[0]['userInfo']['bIntName']}}"){
                bIntName = "<span style='font-weight:bold'>Specify Intermediary Name:</span> {{$user[0]['userInfo']['bIntName']}}<br/>"
            }else{
                bIntName = ""
            }
            if("{{$user[0]['userInfo']['studentType']}}" !== "NSFAS"){

                var Data = "<span style='font-weight:bold'>Company Name:</span> {{$user[0]['userInfo']['burCompanyName']}}<br/>"+
                    "<span style='font-weight:bold'>Registration Number:</span> {{$user[0]['userInfo']['burRegNo']}}<br/>"+
                    "<span style='font-weight:bold'>Contact Person:</span> {{$user[0]['userInfo']['burContactPerson']}}<br/>"+
                    "<span style='font-weight:bold'>Contact Number:</span> {{$user[0]['userInfo']['burContactNo']}}<br/>"+
                    "<span style='font-weight:bold'>Email Address:</span> {{$user[0]['userInfo']['burEmail']}}<br/>"+
                    "<span style='font-weight:bold'>Physical Address:</span> {{$user[0]['userInfo']['bAddress1']}}, {{$user[0]['userInfo']['bAddress2']}}, {{$user[0]['userInfo']['bAddressCity']}}, {{$user[0]['userInfo']['bAddressPostal']}}<br/>"+
                    "<span style='font-weight:bold'>How is the Bursary Paid:</span> {{$user[0]['userInfo']['bPayment']}}<br/>"+bInt+bIntName;
            }else{
                var Data = "";
            }

        }else if(field == "bankStatement"){
            var title = "Bank statement";
            if($("#bank_approved").val() != "1" && $("#bank_approved").val() != "2"){
                var approved = 0;
            }else{
                var approved = 1;
            }

            if("{{$user[0]['userInfo']['pNationality']}}" == "South Africa"){
                var IdData = "<span style='font-weight:bold'>ID Number:</span>{{$user[0]['userInfo']['userId']}}<br/>";
            }else{
                var IdData = "<span style='font-weight:bold'>Passport Number:</span>{{$user[0]['userInfo']['userPassport']}}<br/>";
            }

            var Data = "<span style='font-weight:bold'>Name:</span> {{$user[0]['userInfo']['sponFirstName']}} {{$user[0]['userInfo']['sponLastName']}}<br/>"+
                "<span style='font-weight:bold'>Cellphone Number:</span> {{$user[0]['userInfo']['sponCell']}}<br/>"+
                "<span style='font-weight:bold'>Email Address:</span> {{$user[0]['userInfo']['sponEmail']}}<br/>"+
                "<span style='font-weight:bold'>Nationality:</span> {{$user[0]['userInfo']['pNationality']}}<br/>"+
                IdData+
                "<span style='font-weight:bold'>Physical Address:</span> {{$user[0]['userInfo']['pAddress1']}}, {{$user[0]['userInfo']['pAddress2']}}, {{$user[0]['userInfo']['pAddressCity']}}, {{$user[0]['userInfo']['pAddressPostal']}}<br/>";

        }else if(field == "moveIn"){
            var title = "Lease";
            var approved = 1;
        }
        
        $("#modalHeader").empty().append(title);
        $("#uploadedBy").empty().append(uploaded_by)
        $("#uploadedTime").empty().append(created_at.slice(11, 16))
        $("#uploadedDate").empty().append(created_at.slice(8, 10)+"|"+created_at.slice(5, 7)+"|"+created_at.slice(0, 4))
        $("#FileIdForApproval").val(fileId)
        $("#FileNameForApproval").val(field)

        $("#infoToCheck").empty().append(Data);

        if(approved !== 1){
            /*var preview1 = document.getElementById("rejected");
            preview1.setAttribute("disabled", "disabled");*/
            var preview2 = document.getElementById("approved");
            preview2.setAttribute("disabled", "disabled");
            /*var preview3 = document.getElementById("amend");
            preview3.setAttribute("disabled", "disabled");
            var preview4 = document.getElementById("rejectReason");
            preview4.setAttribute("readonly", "readonly");
            var preview5 = document.getElementById("actionUserName");
            preview5.setAttribute("readonly", "readonly");*/

            $("#infoButtons").removeClass('hidden');

            /*$("#infoChecker").removeClass('hidden')
            $("#infoToCheck").empty().append(Data);*/
        }else{
            var preview1 = document.getElementById("rejected");
            preview1.removeAttribute("disabled", "disabled");
            var preview2 = document.getElementById("approved");
            preview2.removeAttribute("disabled", "disabled");
            var preview3 = document.getElementById("amend");
            preview3.removeAttribute("disabled", "disabled");
            var preview4 = document.getElementById("rejectReason");
            preview4.removeAttribute("readonly", "readonly");
            var preview5 = document.getElementById("actionUserName");
            preview5.removeAttribute("readonly", "readonly");

            $("#infoButtons").addClass('hidden');

            /*$("#infoChecker").addClass('hidden')
            $("#infoToCheck").empty();*/
        }

        if(status == "Approved" || status == "XApproved"){
            $("#approvedDocs").removeClass("hidden")
            $("#actionUserName").addClass("hidden")
        }else{
            $("#approvedDocs").addClass("hidden")
            $("#actionUserName").removeClass("hidden")
        }

        if(status == "Approved" || status == "Rejected" || status == "XApproved")
            $("#buttonSection").addClass('hidden')
        else
            $("#buttonSection").removeClass('hidden')


        $(".pdfSection").empty().append('<embed id="pdfView" src="/storage/'+url+'"  width="100%" height="600px" />')
        
        var modal = document.getElementById("myModal");
        modal.style.display = "block";
    }

    var modal = document.getElementById("myModal");
    var span = document.getElementsByClassName("close")[0];

    span.onclick = function() {
        modal.style.display = "none";
        $("#actionUserName").removeClass("errorBorder")
        $("#rejectReason").removeClass("errorBorder")
        $("#rejectReason_error").addClass("hidden")
        $("#actionUserName_error").addClass("hidden")
        
        $("#rejectReason").val("")
        $("#approvedDocs").addClass("hidden")
        document.getElementById("reasonBox").style.marginTop = "-21px";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            
            modal.style.display = "none";
            $("#actionUserName").removeClass("errorBorder")
            $("#rejectReason").removeClass("errorBorder")
            $("#rejectReason_error").addClass("hidden")
            $("#actionUserName_error").addClass("hidden")
            $("#rejectReason").val("")
            $("#approvedDocs").addClass("hidden")
            document.getElementById("reasonBox").style.marginTop = "-21px";
        }

        if (event.target == modalBooking) {
            //
            modalBooking.style.display = "none";
        }

    }

    function removeFile(){
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete this file",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#delete").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')

                $.ajax({
                    type: "post",
                    url: "/action/file",
                    data: {
                        file: $("#FileIdForApproval").val(), 
                        type: "Rejected",
                        reason: "", 
                        userName: "",
                    },

                    success: function (data) {
                        
                        refreshActions();

                            $("#"+$("#FileNameForApproval").val()+"Status").empty().append('<div class="button rejected"></div>')
                            $("#"+$("#FileNameForApproval").val()+"Banner").empty().append('<label for="'+$("#FileNameForApproval").val()+'" id="'+$("#FileNameForApproval").val()+'Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload"></i></label><input type="file" id="'+$("#FileNameForApproval").val()+'" hidden>')


                        var modal = document.getElementById("myModal");
                        modal.style.display = "none";

                        $("#delete").empty().append('Delete')
                    

                        $("#actionUserName").removeClass("errorBorder")
                        $("#rejectReason").removeClass("errorBorder")
                        $("#rejectReason_error").addClass("hidden")
                        $("#actionUserName_error").addClass("hidden")
                        
                        $("#rejectReason").val("")
                        $("#approvedDocs").addClass("hidden")
                        document.getElementById("reasonBox").style.marginTop = "-21px";
                    },
                });

            }
        })
    }

    function actionFile(type, action){

        if(type === "Rejected"){
            actionName = "Reject"
        }else{
            if(action == "Amend"){
                actionName = "Amend"
            }else{
                actionName = "Approve"
            }
        }

        if(type === "Rejected"){
            if($("#rejectReason").val() !== "" && $("#actionUserName").val() !== ""){
                actionSwal(actionName, type, action)
            }else{
                if($("#actionUserName").val() == ""){
                    $("#actionUserName").addClass("errorBorder")
                    $("#actionUserName_error").removeClass("hidden")
                }

                if($("#rejectReason").val() == ""){
                    $("#rejectReason").addClass("errorBorder")
                    $("#rejectReason_error").removeClass("hidden")
                }
                
                document.getElementById("reasonBox").style.marginTop = "-27px";
            }
            
        }else{
            if(action == "Amend"){
                if($("#actionUserName").val() !== ""){

                    start();

                async function start() {
                    const { value: file } = await Swal.fire({
                        title: 'Select file',
                        input: 'file',
                        inputAttributes: {
                            'aria-label': 'Upload your file'
                        }
                    })

                    if (file) {
                        fileForApproval = $("#FileNameForApproval").val();

                        $("#amend").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                        var preview1 = document.getElementById("rejected");
                        preview1.setAttribute("disabled", "disabled");
                        var preview2 = document.getElementById("approved");
                        preview2.setAttribute("disabled", "disabled");

                        // $("#copyIDSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');
                        let userId = <?php echo $user[0]['userInfo']['id'];?>

                        var form_data = new FormData();
                        form_data.append("file", file);
                        form_data.append("userUid", userId);
                        form_data.append("fileType", fileForApproval);
                        form_data.append("adminName", $("#actionUserName").val());

                        $.ajax({
                            type: "post",
                            url: "/move-in-form-admin",
                            data: form_data,
                            cache: false,
                            contentType: false,
                            processData: false,

                            success: function (data) {
                                if (data[0] == "done") {
                                    $("#"+fileForApproval+"Status").empty().append('<div class="button action"></div>');

                                    $("#"+fileForApproval+"Banner").empty().append('<button type="Button" onClick="openModal(\''+fileForApproval+'\', \''+data[1]+'\', \''+$("#actionUserName").val()+'\', \''+data[2]["created_at"]+'\', \''+data[2]["id"]+'\', \'Uploaded\')">View Document</button>');

                                    refreshActions();
                                    

                                    $("#amend").empty().append('AMEND')
                                    var preview1 = document.getElementById("rejected");
                                    preview1.removeAttribute("disabled", "disabled");
                                    var preview2 = document.getElementById("approved");
                                    preview2.removeAttribute("disabled", "disabled");

                                    var modal = document.getElementById("myModal");
                                    modal.style.display = "none";
                                }
                            },
                        });
                    }
                }
                    
                }else{
                    $("#actionUserName").addClass("errorBorder")
                    $("#actionUserName_error").removeClass("hidden")
                }
     
                
            }else{
                if($("#actionUserName").val() !== ""){
                    actionSwal(actionName, type, action)
                }else{
                    $("#actionUserName").addClass("errorBorder")
                    $("#actionUserName_error").removeClass("hidden")
                }
                
            }
        } 

    }

    function actionSwal(actionName, type, action){
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to "+actionName+" this file",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.isConfirmed) {
                if(type === "Rejected"){
                        $("#rejected").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                        var preview1 = document.getElementById("amend");
                        preview1.setAttribute("disabled", "disabled");
                        var preview2 = document.getElementById("approved");
                        preview2.setAttribute("disabled", "disabled");

                        sendAction(type, action);

                }else{
                    if(action == "Amend"){
                            $("#amend").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                            var preview1 = document.getElementById("rejected");
                            preview1.setAttribute("disabled", "disabled");
                            var preview2 = document.getElementById("approved");
                            preview2.setAttribute("disabled", "disabled");

                            sendAction(type, action);
                        
                    }else{
                            $("#approved").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                            var preview1 = document.getElementById("rejected");
                            preview1.setAttribute("disabled", "disabled");
                            var preview2 = document.getElementById("amend");
                            preview2.setAttribute("disabled", "disabled");

                            sendAction(type, action);
                        
                    }
                }
            }
        })
    }

    function sendAction(type, action){
        
        $.ajax({
            type: "post",
            url: "/action/file",
            data: {
                file: $("#FileIdForApproval").val(), 
                type: type,
                reason: $("#rejectReason").val(), 
                userName: $("#actionUserName").val(),
            },

            success: function (data) {
                
                refreshActions();

                if(type === "Rejected"){
                    $("#"+$("#FileNameForApproval").val()+"Status").empty().append('<div class="button rejected"></div>')
                    $("#"+$("#FileNameForApproval").val()+"Banner").empty().append('<label for="'+$("#FileNameForApproval").val()+'" id="'+$("#FileNameForApproval").val()+'Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload"></i></label><input type="file" id="'+$("#FileNameForApproval").val()+'" hidden onChange="bursaryApp()">')

                    $("#rejected").empty().append('REJECT WITH REASON')
                    var preview1 = document.getElementById("amend");
                    preview1.removeAttribute("disabled", "disabled");
                    var preview2 = document.getElementById("approved");
                    preview2.removeAttribute("disabled", "disabled");
                }else{
                    $("#"+$("#FileNameForApproval").val()+"Status").empty().append('<div class="button approved"></div>')
                    $("#"+$("#FileNameForApproval").val()+"Banner").empty().append('<button type="button" onclick="openModal(\''+$("#FileNameForApproval").val()+'\', \''+data["fileUrl"]+'\', \''+$("#actionUserName").val()+'\', \''+data["created_at"]+'\', \''+data["id"]+'\', \'XApproved\')">View Document</button>')

                    if(action == "Amend"){
                        $("#amend").empty().append('AMEND')
                        var preview1 = document.getElementById("rejected");
                        preview1.removeAttribute("disabled", "disabled");
                        var preview2 = document.getElementById("approved");
                        preview2.removeAttribute("disabled", "disabled");
                    }else{
                        $("#approved").empty().append('APPROVE')
                        var preview1 = document.getElementById("rejected");
                        preview1.removeAttribute("disabled", "disabled");
                        var preview2 = document.getElementById("amend");
                        preview2.removeAttribute("disabled", "disabled");

                        console.log($("#FileIdForApproval").val() )

                        if($("#FileNameForApproval").val() == "copyID"){
                            $("#id_data_approved").val('2')
                        }else if($("#FileNameForApproval").val() == "studentCard"){
                            $("#student_card_approved").val('2')
                        }else if($("#FileNameForApproval").val() == "bankStatement"){
                            console.log("bankStatement")
                            $("#bank_approved").val('2')
                            $("#parent_approved").val('2')
                        }else if($("#FileNameForApproval").val() == "proofRegistration"){
                            $("#registration_approved").val('2')
                        }else if($("#FileNameForApproval").val() == "bursaryApproval"){
                            $("#bursary_approved").val('2')
                            $("#burser_approved").val('2')
                        }
                    }

                }

                var modal = document.getElementById("myModal");
                modal.style.display = "none";
               

                $("#actionUserName").removeClass("errorBorder")
                $("#rejectReason").removeClass("errorBorder")
                $("#rejectReason_error").addClass("hidden")
                $("#actionUserName_error").addClass("hidden")
                
                $("#rejectReason").val("")
                $("#approvedDocs").addClass("hidden")
                document.getElementById("reasonBox").style.marginTop = "-21px";

                checkUserStatus();
            },
        });
    }

    function refreshActions(){

        $(".actionItemList").empty().append('<i class="fas fa-circle-notch fa-spin" style="font-size:41px; margin-left:41%;"></i>');

        $.ajax({
            type: "get",
            url: "/action-list/<?php echo $user[0]['userInfo']['id']; ?>/all",
            success: function (data) {
                $(".actionItemList").empty();
                
                data.forEach(myFunction);
                function myFunction(item, index) {
                    if(item['actionUserRole'] == "1"){
                        var role = '<button type="button" class="admin">Admin</button>';
                    }else{
                        var role = '<button type="button" class="applicant">Applicant</button>';
                    }

                    $(".actionItemList").append(
                        '<div class="infoBlock">'+
                            '<div class="responsible">'+
                                '<p>'+item['actionUserDisplayName']+'</p>'+
                                    role +
                                '<p class="datetime">  '+item["created_at"].slice(8, 10)+' | '+item["created_at"].slice(5, 7)+' | '+item["created_at"].slice(0, 4)+' | '+item["created_at"].slice(11, 16)+' </p>'+
                            '</div>'+
                            '<div class="information">'+
                                '<p>'+item['action']+'</p>'+
                           ' </div>'+
                        '</div>'
                    )
                }
            },
        });
    }

    $("#actionListSort").on("change", function(){
        $(".actionItemList").empty().append('<i class="fas fa-circle-notch fa-spin" style="font-size:41px; margin-left:41%;"></i>');

        $.ajax({
            type: "get",
            url: "/action-list/<?php echo $user[0]['userInfo']['id']; ?>/"+$("#actionListSort").val(),
            success: function (data) {
                $(".actionItemList").empty();
                
                data.forEach(myFunction);
                function myFunction(item, index) {
                    if(item['actionUserRole'] == "1"){
                        var role = '<button type="button" class="admin">Admin</button>';
                    }else{
                        var role = '<button type="button" class="applicant">Applicant</button>';
                    }

                    $(".actionItemList").append(
                        '<div class="infoBlock">'+
                            '<div class="responsible">'+
                                '<p>'+item['actionUserDisplayName']+'</p>'+
                                    role +
                                '<p class="datetime">  '+item["created_at"].slice(8, 10)+' | '+item["created_at"].slice(5, 7)+' | '+item["created_at"].slice(0, 4)+' | '+item["created_at"].slice(11, 16)+' </p>'+
                            '</div>'+
                            '<div class="information">'+
                                '<p>'+item['action']+'</p>'+
                           ' </div>'+
                        '</div>'
                    )
                }
            },
        });
    })

    $("#documentSortList").on('change', function(){
        
        $("#documentsSectionDisplay").empty().append('<i class="fas fa-circle-notch fa-spin" style="font-size:41px; margin-left:41%;"></i>');

        $.ajax({
            type: "get",
            url: "/document-list/<?php echo $user[0]['userInfo']['id']; ?>/"+$("#documentSortList").val(),
            success: function (data) {

                $("#documentsSectionDisplay").empty();
                
                data.forEach(myFunction);
                function myFunction(item, index) {
                    if(item['field'] == "studentCard"){
                        var name = 'STUDENT CARD / PROOF OF STUDENT NUMBER';
                    }else if(item['field'] == "proof_of_payment"){
                        var name = 'PROOF OF PAYMENT';
                    }else if(item['field'] == "copyID"){
                        var name = 'COPY OF ID DOCUMENT';
                    }else if(item['field'] == "proofRegistration"){
                        var name = 'PROOF OF REGISTRATION';
                    }else if(item['field'] == "bursaryApproval"){
                        var name = 'NSFAS APPROVAL';
                    }else if(item['field'] == "bankStatement"){
                        var name = 'BANK STATEMENT';
                    }

                    if(item['status'] == "Uploaded" || item['status'] == "Approved"){
                        var button = '<button type="button" onClick="openModal(\''+item['field']+'\', \''+item['url']+'\',\''+item['uploaded_by']+'\',\''+item['created_at']+'\',\''+item['id']+'\',\''+item['status']+'\')" id="myBtn_'+index+'">View Document</button>';
                    }else{
                        // var button = '<button type="button" class="error">Send reminder</button>';
                    }

                    if(item['status'] == "Uploaded"){
                        var status = '<div class="button action"></div>';
                    }else if(item['status'] == "Approved"){
                        var status = '<div class="button approved"></div>';
                    }else if(item['status'] == "Rejected" || item['status'] == ""){
                        var status = '<div class="button rejected"></div>';
                    }else if(item['status'] == "na"){
                        var status = '<div class="button na"></div>';
                    }

                    $("#documentsSectionDisplay").append(
                        '<div class="section">'+
                            '<div class="label">'+
                                name +
                            '</div>'+
                            '<div class="banner">'+
                                button +
                            '</div>'+
                            '<div class="status">'+
                                status + 
                            '</div>'+
                        '</div>'
                    )
                }
            },
        });
    })

    $("#documentsSectionDisplay").on('change', '#copyID', function(){
        $("#copyIDSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');
        let userId = <?php echo $user[0]['userInfo']['id'];?>

        var form_data = new FormData();
        form_data.append("file", document.getElementById('copyID').files[0]);
        form_data.append("userUid", userId);
        form_data.append("fileType", "copyID");
        form_data.append("adminName", $("#adminName").val());

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0] == "done") {
                    $("#copyIDStatus").empty().append('<div class="button action"></div>');

                    $("#copyIDBanner").empty().append('<button type="Button" onClick="openModal(\'copyID\', \''+data[1]+'\', \''+data[2]["uploaded_by"]+'\', \''+data[2]["created_at"]+'\', \''+data[2]["id"]+'\', \'Uploaded\')">View Document</button>');

                    refreshActions();
                }
            },
        });
    });

    $("#documentsSectionDisplay").on('change', "#studentCard", function(){
        $("#studentCardSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');
        let userId = <?php echo $user[0]['userInfo']['id'];?>

        var form_data = new FormData();
        form_data.append("file", document.getElementById('studentCard').files[0]);
        form_data.append("userUid", userId);
        form_data.append("fileType", "studentCard");
        form_data.append("adminName", $("#adminName").val());

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0] == "done") {
                    $("#studentCardStatus").empty().append('<div class="button action"></div>');

                    $("#studentCardBanner").empty().append('<button type="Button" onClick="openModal(\'studentCard\', \''+data[1]+'\', \''+data[2]["uploaded_by"]+'\', \''+data[2]["created_at"]+'\', \''+data[2]["id"]+'\', \'Uploaded\')">View Document</button>');

                    refreshActions();
                }
            },
        });  
    })

    $("#documentsSectionDisplay").on('change', "#bankStatement",  function(){
        $("#bankStatementSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');
        let userId = <?php echo $user[0]['userInfo']['id'];?>

        var form_data = new FormData();
        form_data.append("file", document.getElementById('bankStatement').files[0]);
        form_data.append("userUid", userId);
        form_data.append("fileType", "bankStatement");
        form_data.append("adminName", $("#adminName").val());

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0] == "done") {
                    
                    $("#bankStatementStatus").empty().append('<div class="button action"></div>');

                    $("#bankStatementBanner").empty().append('<button type="Button" onClick="openModal(\'bankStatement\', \''+data[1]+'\', \''+data[2]["uploaded_by"]+'\', \''+data[2]["created_at"]+'\', \''+data[2]["id"]+'\', \'Uploaded\')">View Document</button>');

                    refreshActions();
                }
            },
        });
    })

    $("#documentsSectionDisplay").on('change', "#proofRegistration", function(){

        $("#proofRegistrationSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');
        let userId = <?php echo $user[0]['userInfo']['id'];?>

        var form_data = new FormData();
        form_data.append("file", document.getElementById('proofRegistration').files[0]);
        form_data.append("userUid", userId);
        form_data.append("fileType", "proofRegistration");
        form_data.append("adminName", $("#adminName").val());

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0] == "done") {
                    $("#proofRegistrationStatus").empty().append('<div class="button action"></div>');

                    $("#proofRegistrationBanner").empty().append('<button type="Button" onClick="openModal(\'proofRegistration\', \''+data[1]+'\', \''+data[2]["uploaded_by"]+'\', \''+data[2]["created_at"]+'\', \''+data[2]["id"]+'\', \'Uploaded\')">View Document</button>');

                    refreshActions();
                }
            },
        });
    })


    $("#documentsSectionDisplay").on('change', "#proof_of_payment", function(){
        $("#proof_of_paymentSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');
        let userId = <?php echo $user[0]['userInfo']['id'];?>

        var form_data = new FormData();
        form_data.append("file", document.getElementById('proof_of_payment').files[0]);
        form_data.append("userUid", userId);
        form_data.append("fileType", "proof_of_payment");
        form_data.append("adminName", $("#adminName").val());

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0] == "done") {

                    $("#proof_of_paymentStatus").empty().append('<div class="button action"></div>');

                    $("#proof_of_paymentBanner").empty().append('<button type="Button" onClick="openModal(\'proof_of_payment\', \''+data[1]+'\', \''+data[2]["uploaded_by"]+'\', \''+data[2]["created_at"]+'\', \''+data[2]["id"]+'\', \'Uploaded\')">View Document</button>');

                    refreshActions();

                }
            },
        });
    })
    
    function bursaryApp(){
        $("#bursaryApprovalSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        let userId = <?php echo $user[0]['userInfo']['id'];?>

        var form_data = new FormData();
        form_data.append("file", document.getElementById('bursaryApproval').files[0]);
        form_data.append("userUid", userId);
        form_data.append("fileType", "bursaryApproval");
        form_data.append("adminName", $("#adminName").val());

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0] == "done") {


                    $("#bursaryApprovalStatus").empty().append('<div class="button action"></div>');

                    $("#bursaryApprovalBanner").empty().append('<button type="Button" onClick="openModal(\'bursaryApproval\', \''+data[1]+'\', \''+data[2]["uploaded_by"]+'\', \''+data[2]["created_at"]+'\', \''+data[2]["id"]+'\', \'Uploaded\')">View Document</button>');

                    refreshActions();

                }
            },
        });
    }

    $("#bursaryApproval").on('change', function(){
        $("#bursaryApprovalSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        let userId = <?php echo $user[0]['userInfo']['id'];?>

        var form_data = new FormData();
        form_data.append("file", document.getElementById('bursaryApproval').files[0]);
        form_data.append("userUid", userId);
        form_data.append("fileType", "bursaryApproval");
        form_data.append("adminName", $("#adminName").val());

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0] == "done") {


                    $("#bursaryApprovalStatus").empty().append('<div class="button action"></div>');

                    $("#bursaryApprovalBanner").empty().append('<button type="Button" onClick="openModal(\'bursaryApproval\', \''+data[1]+'\', \''+data[2]["uploaded_by"]+'\', \''+data[2]["created_at"]+'\', \''+data[2]["id"]+'\', \'Uploaded\')">View Document</button>');

                    refreshActions();

                }
            },
        });
    })

    $("#moveIn").on('change', function(){
        $("#moveInSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        let userId = <?php echo $user[0]['userInfo']['id'];?>

        var form_data = new FormData();
        form_data.append("file", document.getElementById('moveIn').files[0]);
        form_data.append("userUid", userId);
        form_data.append("fileType", "moveIn");
        form_data.append("adminName", $("#adminName").val());

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0] == "done") {
                    $("#moveInStatus").empty().append('<div class="button action"></div>');

                    $("#moveInBanner").empty().append('<button type="Button" onClick="openModal(\'moveIn\', \''+data[1]+'\', \''+data[2]["uploaded_by"]+'\', \''+data[2]["created_at"]+'\', \''+data[2]["id"]+'\', \'Uploaded\')">View Document</button>');

                    refreshActions();
                }
            },
        });
    });

    function deleteBooking(bookingId, userId){
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "/delete/booking",
                    data: {
                        bookingId: bookingId, 
                        userId: userId,
                    },

                    success: function (data) {
                        window.location = "/applications";
                    },
                });
            }
        })
    }

    $('#myBtn_regen_lease').on("click", function(e) {
        let btn = $(this);
        $(btn).html('<i class="fas fa-circle-notch fa-spin"></i>');

        $.ajax({
            type: "post",
            url: "/createLease/{{$user[0]['userInfo']['id']}}",
            data: {
                type: "auto",
            },

            success: function (data) {                
                $(btn).html('REGEN LEASE');
            },
        });

        e.preventDefault();
    });

    // function checkData(){
    //     $.ajax({
    //         type: "get",
    //         url: "/action-list/<?php echo $user[0]['userInfo']['id']; ?>/all",
    //         success: function (data) {
    //             $(".actionItemList").empty();
                
    //             data.forEach(myFunction);
    //             function myFunction(item, index) {
    //                 if(item['actionUserRole'] == "1"){
    //                     var role = '<button type="button" class="admin">Admin</button>';
    //                 }else{
    //                     var role = '<button type="button" class="applicant">Applicant</button>';
    //                 }

    //                 $(".actionItemList").append(
    //                     '<div class="infoBlock">'+
    //                         '<div class="responsible">'+
    //                             '<p>'+item['actionUserDisplayName']+'</p>'+
    //                                 role +
    //                             '<p class="datetime">  '+item["created_at"].slice(8, 10)+' | '+item["created_at"].slice(5, 7)+' | '+item["created_at"].slice(0, 4)+' | '+item["created_at"].slice(11, 16)+' </p>'+
    //                         '</div>'+
    //                         '<div class="information">'+
    //                             '<p>'+item['action']+'</p>'+
    //                        ' </div>'+
    //                     '</div>'
    //                 )
    //             }
    //         },
    //     });
    // }

    // setInterval(function(){
    //     checkData()
    // }, 30000)
</script>



@endsection
