@extends('layouts.admin')

@section('content')

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="greyAppSection">
    <div class="container">
        <div class="col-3" id="col1">
        <div class="flex">
            <span id="approvedAddr" class="hidden">&#128994;</span><span id="rejectedAddr">&#128308;</span><p class="listHeader">Physical Address</p></div>
            <div class="form" id="inline_edit_address">
                {{$user[0]['userInfo']['userAddress1']}}<br/>
                {{$user[0]['userInfo']['userAddress2']}}<br/>
                {{$user[0]['userInfo']['userAddressCity']}}<br/>
                {{$user[0]['userInfo']['userAddressPostal']}}<br/>

                <div class="address_btn"></div>

            </div>
        </div>
        <div class="col-3" id="col2">
            
        <div class="flex">
            <span id="approvedKin" class="hidden">&#128994;</span><span id="rejectedKin">&#128308;</span><p class="listHeader">Next of Kin</p></div>
            <div class="form" id="inline_edit_kin">
                {{$user[0]['userInfo']['kinTitle']}} {{$user[0]['userInfo']['kinFirstName']}} {{$user[0]['userInfo']['kinLastName']}}<br/>
                {{$user[0]['userInfo']['kinRelationship']}}<br/>
                {{-- {{$user[0]['userInfo']['kinNationality']}}<br/>
                @if($user[0]['userInfo']['kinId']){{$user[0]['userInfo']['kinId']}}<br/>@endif
                @if($user[0]['userInfo']['kinPassportNo']){{$user[0]['userInfo']['kinPassportNo']}}<br/>@endif --}}
                {{$user[0]['userInfo']['kinCellNo']}}<br/>
                {{$user[0]['userInfo']['kinEmail']}}<br/>
                {{$user[0]['userInfo']['kinAddressStr']}}, {{$user[0]['userInfo']['kinAddressSuburb']}}, <br/>
                {{$user[0]['userInfo']['kinAddressCity']}}, {{$user[0]['userInfo']['kinAddressPost']}}<br/>

                <div class="kin_btn"></div>

            </div>

        </div>
        <div class="col-3" id="col3">
            @if($user[0]['userInfo']['studentType'] == "Cash / Direct")
                <div class="flex"><span id="approvedSpon" class="hidden">&#128994;</span><span id="rejectedSpon">&#128308;</span><p class="listHeader">Sponsor/Parent </p></div>
                <div class="form">
                    {{$user[0]['userInfo']['sponFirstName']}} {{$user[0]['userInfo']['sponLastName']}}<br/>
                    {{$user[0]['userInfo']['sponCellNo']}}<br/>
                    {{$user[0]['userInfo']['sponEmail']}}<br/>
                    {{$user[0]['userInfo']['sponId']}}<br/>
                    {{$user[0]['userInfo']['pNationality']}}<br/>
                    @if($user[0]['userInfo']['sponId']){{$user[0]['userInfo']['sponId']}}<br/>@endif
                    @if($user[0]['userInfo']['pPassportNo']){{$user[0]['userInfo']['pPassportNo']}}<br/>@endif
                    {{$user[0]['userInfo']['pAddress1']}}, {{$user[0]['userInfo']['pAddress2']}}, <br/>
                    {{$user[0]['userInfo']['pAddressCity']}}, {{$user[0]['userInfo']['pAddressPostal']}}
                </div>
            @endif

            @if($user[0]['userInfo']['studentType'] == "Bursary")
                <div class="flex"><span id="approvedBur" class="hidden">&#128994;</span><span id="rejectedBur">&#128308;</span><p class="listHeader">Bursar</p></div>
                <div class="form">
                    {{$user[0]['userInfo']['burCompanyName']}}<br/>
                    {{$user[0]['userInfo']['burRegNo']}}<br/>
                    {{$user[0]['userInfo']['burContactPerson']}} - {{$user[0]['userInfo']['burContactNo']}}<br/>
                    {{$user[0]['userInfo']['burEmail']}}<br/>
                    {{$user[0]['userInfo']['bAddress1']}}, {{$user[0]['userInfo']['bAddress2']}}, <br/>
                    {{$user[0]['userInfo']['bAddressCity']}}, {{$user[0]['userInfo']['bAddressPostal']}}<br/>
                    <br />NSFAS bursar? <strong><?php if($user[0]['userInfo']['nsfas_bursar'] == "1"){ echo "Yes";}else{echo "No";} ?></strong>
                </div>
            @endif
        </div>
        
    </div>
</div>

<div class="container">
    
    <div class="appRow">
        <div class="cards">

            <div class="customWhiteCard" >
                <div class="flex">                    
                    <span id="app_det_statYes" class="hidden" style="margin-top:10px;">&#128994;</span>                    
                    <span id="app_det_statNo" style="margin-top:10px;">&#128308;</span>                    
                    <h2 class="appHeader">Applicant Details</h2>
                </div>
                
                <p class="InfoList">
                    {{$user[0]['userInfo']['userFirstName']}} {{$user[0]['userInfo']['userLastname']}}<br/>
                    {{$user[0]['userInfo']['userDob']}}<br/>
                    {{$user[0]['userInfo']['userNationality']}}<br/>
                    @if($user[0]['userInfo']['userId']){{$user[0]['userInfo']['userId']}}<br/>@endif
                    @if($user[0]['userInfo']['userPassport']){{$user[0]['userInfo']['userPassport']}}<br/>@endif
                </p>

                <p class="InfoList">
                    @foreach($lookups as $lookup) @if($lookup->id == $user[0]['userInfo']['institutionStudy']) {{$lookup->lookup_name}} @endif @endforeach<br/>
                    {{$user[0]['userInfo']['studentNo']}}<br/>
                    @foreach($lookups as $lookup) @if($lookup->id == $user[0]['userInfo']['faculty']) {{$lookup->lookup_name}} @endif @endforeach<br/>
                    {{$user[0]['userInfo']['yearOfStudy']}}
                </p>

                <div class="form" id="inline_app_details" style="margin-top: 20px;font-size: 21px;font-weight: 300;line-height: 26px;">
                    {{$user[0]['userInfo']['userEmail']}}<br/>
                    {{$user[0]['userInfo']['userCell']}}<br/>

                    <div class="app_details_btn"></div>
                    
                </div>
            </div>

            <div class="customWhiteCard" id="applicationCard" style="margin-top:20px;">
                <div class="flex">
                    <div class="bed_code_status"></div>
                    
                    <h2 class="appHeader">Application Summary</h2>
                </div>

                <div class="app_summary_details"></div>
                
                <div class="buttons">
                    @if($leaseApproved == 'false' && $user[0]['userInfo']['rob_60_cancelled'] == 0)
                        @if(Auth::user()->role_change_bed == "1")
                            <button type="button" class="appBooking" onClick="changeBooking('{{$user[0]['userInfo']['id']}}', 'generate')">Change Bed</button>
                        @endif

                        @if(Auth::user()->role_change_date == "1")
                            <button type="button" class="appBooking" onClick="changeLease('{{$user[0]['userInfo']['id']}}')">Change Lease Dates</button>
                        @endif
                    @endif

                    @if($leaseApproved == 'true' && $user[0]['userInfo']['rob_60_cancelled'] == 0)
                        @if(Auth::user()->role_cancel_lease == "1")
                            <button type="button" class="appBooking" onClick="cancelLease('{{$user[0]['userInfo']['id']}}', 'generate')">Change Bed</button>
                        @endif
                    @endif
                    
                </div>
                <div class="buttons">
                    @if ($user[0]['userInfo']['rob_60_cancelled'] == 0)
                        @if(Auth::user()->role_delete_booking == 1)
                            <button type="button" class="appBooking" onClick="deleteBooking('{{$booking->id}}', '{{$user[0]['userInfo']['id']}}')">Delete this booking</button>
                        @endif

                        @if(Auth::user()->role_cancel_booking == 1)
                            <button type="button" class="appBooking" onClick="cancelBooking('{{$booking->id}}', '{{$user[0]['userInfo']['id']}}')">Cancel booking</button>
                        @endif
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
                                <p>{{$action->users->userFirstName}} {{$action->users->userLastname}}</p>
                                @if($action->actionUserRole !== "0" )
                                    @if($action->users->user_role_name == "" || $action->users->user_role_name == null)
                                        <button type="button" class="admin">Admin</button>
                                    @else
                                    <button type="button" class="admin">{{$action->users->user_role_name}}</button>
                                    @endif
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

                    @if($user[0]['userInfo']['bPayment'] == "Directly to the STUDENT") <br /> <span style="color:red;">Directly to the STUDENT</span>  @elseif($user[0]['userInfo']['bPayment'] !== "") <br/> <span style="color:red;">{{$user[0]['userInfo']['bPayment']}}</span>@endif
                        @if($user[0]['userInfo']['bPayment'] == "Via an INTERMEDIARY" && $user[0]['userInfo']['bint'] != "Other") <span style="color:red;"> | {{$user[0]['userInfo']['bint']}}</span> @endif
                        @if($user[0]['userInfo']['bPayment'] == "Via an INTERMEDIARY" && $user[0]['userInfo']['bint'] == "Other") <span style="color:red;"> | {{$user[0]['userInfo']['bIntName']}}</span> @endif
                </p>
                <br/>

                <h2 class="appSubHeader bold">BOOKING FEE</h2>
                <p class="appSubHeader">
                    <!-- @if($residence !== null && $user[0]['userInfo']['studentType'] == "NSFAS") R {{$residence->nsfas_booking_rate}} @endif
                    @if($residence !== null && $user[0]['userInfo']['studentType'] == "Bursary") R {{$residence->bursary_booking_rate}} @endif
                    @if($residence !== null && $user[0]['userInfo']['studentType'] == "Cash / Direct") R {{$residence->booking_rate}} @endif -->
                    R {{$booking->room_rate}}
                </p>
                <br/>

                <h2 class="appSubHeader bold">PAID</h2>
                <p class="appSubHeader" id="paidOption">
                <div class="waived_paid_wording"></div>
                    

                    
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
                    <!-- <a href="/download/docs/{{$user[0]['userInfo']['id']}}"><button type="button" class="appBooking" id="downloadAllDocs">Download All</button></a> -->
                    <!-- files_to_download -->
                    <button type="button" class="appBooking" id="downloadAllDocs" onClick="downloadZipDocs('{{$user[0]['userInfo']['id']}}')">Download All</button>
                    <div class="waitve_btn"></div>
                    <button type="button" class="appBooking" id="addOtherDocument" onClick="addOtherDoc('{{$user[0]['userInfo']['id']}}')">Add Document</button>
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
                                    @else
                                        <?php echo strtoupper(str_replace("_", " ", $file["field"])); ?>
                                    @endif
                                </div>
                                <div class="status" id="{{$file['field']}}Status">

                                    <!--  -->

                                    @if(explode("_", $file["field"])[0] == "other" && $file["field"] == $file["field"])
                                        @if($file["status"] == "XApproved")
                                            <div class="button approved"></div>
                                        @elseif($file["status"] == "AUploaded")
                                            <div class="button action"></div>
                                        @else
                                            <div class="button rejected"></div>
                                        @endif
                                    @endif

                                    

                                    @if($file["field"] === "proof_of_payment")
                                        @if ($user[0]['userInfo']['rob_10_fee'] == 0)
                                            <div class="button rejected"></div>
                                        @elseif ($user[0]['userInfo']['rob_10_fee'] == 1)
                                            <div class="button action"></div>
                                        @elseif ($user[0]['userInfo']['rob_10_fee'] == 2)
                                            <div class="button approved"></div>
                                        @else
                                            <div class="button na"></div>
                                        @endif
                                    @endif

                                    @if($file["field"] === "copyID")
                                        @if ($user[0]['userInfo']['rob_15_id'] == 0)
                                            <div class="button rejected"></div>
                                        @elseif ($user[0]['userInfo']['rob_15_id'] == 1)
                                            <div class="button action"></div>
                                        @elseif ($user[0]['userInfo']['rob_15_id'] == 2)
                                            <div class="button approved"></div>
                                        @else
                                            <div class="button na"></div>
                                        @endif
                                    @endif

                                    @if($file["field"] === "studentCard")
                                        @if ($user[0]['userInfo']['rob_20_card'] == 0)
                                            <div class="button rejected"></div>
                                        @elseif ($user[0]['userInfo']['rob_20_card'] == 1)
                                            <div class="button action"></div>
                                        @elseif ($user[0]['userInfo']['rob_20_card'] == 2)
                                            <div class="button approved"></div>
                                        @else
                                            <div class="button na"></div>
                                        @endif
                                    @endif

                                    @if($file["field"] === "proofRegistration")
                                        @if ($user[0]['userInfo']['rob_25_reg'] == 0)
                                            <div class="button rejected"></div>
                                        @elseif ($user[0]['userInfo']['rob_25_reg'] == 1)
                                            <div class="button action"></div>
                                        @elseif ($user[0]['userInfo']['rob_25_reg'] == 2)
                                            <div class="button approved"></div>
                                        @else
                                            <div class="button na"></div>
                                        @endif
                                    @endif

                                    @if($file["field"] === "bursaryApproval" || $file["field"] === "bankStatement")
                                        @if ($user[0]['userInfo']['rob_30_funds'] == 0)
                                            <div class="button rejected"></div>
                                        @elseif ($user[0]['userInfo']['rob_30_funds'] == 1)
                                            <div class="button action"></div>
                                        @elseif ($user[0]['userInfo']['rob_30_funds'] == 2)
                                            <div class="button approved"></div>
                                        @else
                                            <div class="button na"></div>
                                        @endif
                                    @endif

                                    @if($file["field"] === "moveIn")
                                        @if ($user[0]['userInfo']['rob_55_lease'] == 0)
                                            <div class="button rejected"></div>
                                        @elseif ($user[0]['userInfo']['rob_55_lease'] == 1)
                                            <div class="button action"></div>
                                        @elseif ($user[0]['userInfo']['rob_55_lease'] == 2)
                                            <div class="button approved"></div>
                                        @else
                                            <div class="button na"></div>
                                        @endif
                                    @endif
                                    
                                    {{-- @if($file["field"] === "moveIn")
                                        @if($file["status"] === "XApproved" || $file["status"] === "Approved")
                                            <div class="button approved"></div>
                                        @elseif($file["status"] === "AUploaded")
                                            <div class="button action"></div>
                                        @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                            <div class="button rejected"></div>
                                        @endif
                                    @else
                                        @if($file["status"] === "AUploaded" )
                                            <div class="button action"></div>
                                        @elseif($file["status"] === "XApproved")
                                            <div class="button approved"></div>
                                        @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                            <div class="button rejected"></div>
                                        @elseif($file["status"] === "na")
                                            <div class="button na"></div>
                                        @endif
                                    @endif --}}
                                    
                                </div>

                                <div class="banner" id="{{$file['field']}}Banner">
                                    @if($file["field"] === "moveIn")
                                        <a href="/storage/files/{{$user[0]['userInfo']['userId']}}/Lease.pdf" download="{{$user[0]['userInfo']['userFirstName']}}_{{$user[0]['userInfo']['userLastname']}}_{{$user[0]['userInfo']['userId']}}_Lease.pdf"><button type="button" id="myBtn_download">Download Lease</button></a>                                        
                                    @endif

                                    @if($file["field"] === "moveIn" && $file["status"] !== "XApproved")
                                        <a href="#"><button type="button" id="myBtn_regen_lease">Regen Lease</button></a>
                                    @endif

                                    @if($file["status"] === "AUploaded" || $file["status"] === "XApproved")
                                        <button
                                            type="button"
                                            onClick="btn_view_document_ajax({{$file['id']}})"
                                        >View Document</button>
                                    @else
                                        
                                        @if(Auth::user()->role_upload_docs == 1)
                                    
                                            <label for="{{$file['field']}}" id="{{$file['field']}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;">
                                                <span class="text">Upload</span>
                                                <i class="fas fa-file-upload" ></i>
                                            </label>
                                            <input type="file" id="{{$file['field']}}" onChange="UploadDocument('{{$file['field']}}')" hidden/>

                                            @if(explode("_", $file["field"])[0] == "other")
                                                <button type="button" style="margin-left:5px;" onClick="deleteFile('{{$file['field']}}')">Delete</button>
                                            @endif

                                        @endif
                                    @endif
                                </div>
                            </div>

                            <?php $i++; ?>


                    @endforeach

                </div>


            </div>

            <div class="customWhiteCard" id="Comments" style="margin-top:20px; margin-bottom:20px; height:660px;">
                <h2 class="appHeader">Comments</h2>
                @if(Auth::user()->role_comment == "1")
                    <textarea id="newComment" name="newComment" rows="6" cols="50" style="width:100%; border-radius:5px;"></textarea>
                    <br/>
                    <button type="button" class='detailsChange' id="addComment" style="background-color:#e74f26">Add Comment</button>
                @endif

                <!-- comments -->

                <div class="commentItemList" style="<?php if(Auth::user()->role_comment == "1"){echo "margin-top:73px;";} ?> max-height: 350px; overflow: auto; width:100%">

                <div class="comments_block"></div>
                    
                </div>
            </div>

        </div>
    </div>
</div>

<div id="myModal" class="modal" style="padding-top: 10px;"></div>

<div id="myModalEditApp" class="modal">

    <div class="modal-content " id="editModalHeight" style="height: 439px;">
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

            <div class="form-field hidden" id="roomSection">
                <label>Room</label>
                <select name="room" id='room'>
                    <!--  -->
                </select>
            </div>

            <div class="form-field">
                <label>Bed Code * <span id="bed_code_error" class="hidden"><small style="color:red">This field is required</small></span></label>
                <select name="bed_code" id='bed_code'>
                    <!--  -->
                </select>
            </div>
            
            <input type="hidden" name="room" id="room" value="" />
            <input type="hidden" name="bed" id="bed" value="" />

            <div class="form-field-big" id="ChangeBookingOnly">
                <button type="button" id="changeBookingType" class="profileSubmit" style="width:98%; border:none; padding:12px; border-radius:10px;">Change booking</button>
            </div>
        </div>


    </div>

</div>

<div id="myModalEditLease" class="modal">

    <div class="modal-content " style="height: 304px;">
        <span class="closeLease" style="float:right; font-size:25px; cursor:pointer">&times;</span>
        <div class="form-card">
            <h2>Change this Lease</h2>

            <input type="hidden" id="bookingId2"/>

            <div id="LeaseInfo">
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
            </div>

            <div class="form-field-big" id="changeLeaseDatesOnly">
                <button type="button" id="changeLeaseDates" class="profileSubmit" style="width:98%; border:none; padding:12px; border-radius:10px;">Change Lease Dates</button>
            </div>
        </div>


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
<input type="hidden" id="files_to_download" value="{{$files_to_download}}"/>
<input type="hidden" id="selectedRoom" value=""/>
<input type="hidden" id="email_data_approved" value="{{$user[0]['userInfo']['email_data_approved']}}"/>

<input type="hidden" id="booking_fee_paid" value="{{$booking_paid}}" />
<input type="hidden" id="booking_fee_waived" value="{{$booking_waived}}" />




<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function changeNsfsBursar(userId, bursar){

        if(bursar == 1){
            newBursar = "No"
        }else{
            newBursar = "Yes"
        }
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to change the NSFAS bursar to "+newBursar,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "/change-nsfsbursar",
                    data: {
                        userId: "{{$user[0]['userInfo']['id']}}",
                    },
        
                    success: function (data) {
                        
                        window.location.reload();
        
                    },
                });
            }
        });
    }
    
    $("#updateProfileStatus").on('click', function(){
        $("#updateProfileStatus").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
        $.ajax({
            type: "get",
            url: "/update-user-status/{{$user[0]['userInfo']['id']}}",
            success: function (data) {
                
                window.location.reload();

            },
        });
    })

    $("#addComment").on('click', function(){
        $("#addComment").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
        $.ajax({
            type: "post",
            url: "/add-comment",
            data: {
                userId: "{{$user[0]['userInfo']['id']}}",
                comment: $("#newComment").val(),
            },

            success: function (data) {
                
                // window.location.reload();
                $("#newComment").val('')
                $("#addComment").empty().append('Add Comment')
                getAllComments();

            },
        });
    });

    getAllComments();

    function getAllComments(){
        $.ajax({
            type: "post",
            url: "/get-comments",
            data: {
                userId: "{{$user[0]['userInfo']['id']}}",
            },

            success: function (data) {
                
                // console.log(data)
                $(".comments_block").empty()

                if(data.length == 0){
                    $(".comments_block").append('<p style="text-align:center">There are no comments!</p>');
                }else{

                    Object.keys(data).forEach( function(key) {
                        $(".comments_block").append('<div class="infoBlock"><div class="responsible" style="width:100%">'+(data[key].created_at).slice(8, 10)+'/'+(data[key].created_at).slice(5, 7)+'/'+(data[key].created_at).slice(0, 4)+' '+(data[key].created_at).slice(11, 16)+' | '+data[key].users.email+' | '+data[key].users.user_role_name+'</p></div><div class="information"><p>'+data[key].comment+'</p></div></div>')
                    });
                }

            },
        });
    }   


    checkUserStatus();

    // MainAppSection
    function checkUserStatus(){

        $(".address_btn").empty()

        if("<?php echo $user[0]['userInfo']['rob_50_approve']; ?>" == 0 && "<?php echo $user[0]['userInfo']['rob_60_cancelled']; ?>" == 0){
            if($("#address_approved").val() != 1){
                if("<?php echo Auth::user()->role_approve_docs; ?>" == "1"){
                    $(".address_btn").append('<button type="button" class="approve button btn_approve_inline" formid="inline_edit_address" approvefield="address_approved" style="height:40px;">Approve</button>')
                }

                if("<?php echo Auth::user()->role_edit_app; ?>" == "1"){
                    $(".address_btn").append('<button type="button" class="amend button btn_edit_inline" formid="inline_edit_address" edittype="form">Edit</button>')
                }
            }else{
                if("<?php echo Auth::user()->role_delete_uploaded; ?>" == 1){
                    $(".address_btn").append('<button type="button" class="rejected button btn_approve_inline" formid="inline_edit_address" approvefield="address_approved" style="height:40px;">Reject</button>')
                }
            }

        }

        $(".kin_btn").empty()

        if("<?php echo $user[0]['userInfo']['rob_50_approve']; ?>" == 0 && "<?php echo $user[0]['userInfo']['rob_60_cancelled'] ;?>" == 0){
            if($("#kin_approved").val() != 1){
                if(<?php echo Auth::user()->role_approve_docs; ?> == "1"){
                    $(".kin_btn").append('<button type="button" class="approve button btn_approve_inline" formid="inline_edit_kin" approvefield="kin_approved" style="height:40px;">Approve</button>')
                }

                if("<?php echo Auth::user()->role_edit_app; ?>" == "1"){
                    $(".kin_btn").append('<button type="button" class="amend button btn_edit_inline" formid="inline_edit_kin" edittype="form">Edit</button>')
                }
            }else{
                if("<?php echo Auth::user()->role_delete_uploaded; ?>" == 1){
                    $(".kin_btn").append('<button type="button" class="rejected button btn_approve_inline" formid="inline_edit_kin" approvefield="kin_approved" style="height:40px;">Reject</button>')
                }
            }
        }

        $(".app_details_btn").empty()

        if("<?php echo $user[0]['userInfo']['rob_50_approve']; ?>" == 0 && "<?php echo $user[0]['userInfo']['rob_60_cancelled']; ?>" == 0){
            if($("#email_data_approved").val() != 1){
                if("<?php echo Auth::user()->role_approve_docs; ?>" == "1"){
                    $(".app_details_btn").append('<button type="button" class="approve button btn_approve_inline" formid="inline_app_details" approvefield="email_data_approved" style="height:40px;">Approve</button>')
                }

                if("<?php echo Auth::user()->role_edit_app; ?>" == "1"){
                    $(".app_details_btn").append('<button type="button" class="amend button btn_edit_inline" formid="inline_app_details" edittype="form">Edit</button>')
                }
            }else{
                if("<?php echo Auth::user()->role_delete_uploaded; ?>" == 1){
                    $(".app_details_btn").append('<button type="button" class="rejected button btn_approve_inline" formid="inline_app_details" approvefield="email_data_approved" style="height:40px;">Reject</button>')
                }
            }
        }

        $(".waitve_btn").empty();

        if($("#booking_fee_paid").val() == 'false'){
            if($("#booking_fee_waived").val() == 'false'){
                $(".waitve_btn").append('<button type="button" class="appBooking" id="waiveThisBookingFee" onClick="waiveBookingFee(\'{{$user[0]['userInfo']['id']}}\')">Waive Booking Fee</button>')
            }else{
                $(".waitve_btn").append('<button type="button" class="appBooking" id="unWaiveThisBookingFee" onClick="unWaiveBookingFee(\'{{$user[0]['userInfo']['id']}}\')">Undo Booking Fee Waiver </button>')
            }
        }

        $(".waived_paid_wording").empty();

        if($("#booking_fee_waived").val() == 'true'){
            $(".waived_paid_wording").append('Waived')
        }else if($("#booking_fee_waived").val() == '0'){
            $(".waived_paid_wording").append('Yes, no booking fee')
        }else{
            <?php foreach($user[0]["files"] as $file){ 
                if($file["field"] === "proof_of_payment"){
                    if($file["status"] === "XApproved" || $file["status"] === "Approved"){
                        ?>$(".waived_paid_wording").append('Yes')<?php
                    }else{
                        ?>$(".waived_paid_wording").append('No')<?php
                    }
                }
            }?>
        }
        

        if($("#parent_approved").val() == "1"){
            $("#rejectedSpon").addClass('hidden')
            $("#approvedSpon").removeClass('hidden')
        }

        if($("#burser_approved").val() == "1"){
            $("#rejectedBur").addClass('hidden')
            $("#approvedBur").removeClass('hidden')
        }

        if($("#kin_approved").val() == "1"){
            $("#rejectedKin").addClass('hidden')
            $("#approvedKin").removeClass('hidden')
        }else{
            $("#rejectedKin").removeClass('hidden')
            $("#approvedKin").addClass('hidden')
        }

        if($("#address_approved").val() == "1"){
            $("#rejectedAddr").addClass('hidden')
            $("#approvedAddr").removeClass('hidden')
        }else{
            $("#rejectedAddr").removeClass('hidden')
            $("#approvedAddr").addClass('hidden')
        }
        
        if($("#email_data_approved").val() == "1"){
            $("#app_det_statNo").addClass('hidden')
            $("#app_det_statYes").removeClass('hidden')
        }else{
            $("#app_det_statNo").removeClass('hidden')
            $("#app_det_statYes").addClass('hidden')
        }

        //Check if we show/hide the approve user section
        $.ajax({
            type: "get",
            url: "/check-user-status/{{$user[0]['userInfo']['id']}}",

            success: function (data) {

                // console.log(data)

                if(data['rob_40_bed'] == 2){
                    $(".bed_code_status").empty().append('<span id="bedAllocatedYes" style="margin-top:10px;">&#128994;</span>')
                }else{
                    $(".bed_code_status").empty().append('<span id="bedAllocatedNo" style="margin-top:10px;">&#128308;</span>')
                }

                $(".appSection").empty()

                if(data['rob_50_approve'] == 0 && data['rob_60_cancelled'] == 0){
                    $(".appSection").append('<div id="MainAppSection"><div class="main_btn"></div></div>')

                    if("<?php echo Auth::user()->role_approve_docs; ?>" == 1
                        && data['rob_10_fee'] == 2
                        && data['rob_15_id'] == 2
                        && data['rob_20_card'] == 2
                        && data['rob_25_reg'] == 2
                        && data['rob_30_funds'] == 2
                        && data['rob_35_info'] == 2
                        && data['rob_40_bed'] == 2)
                    {
                        $(".main_btn").empty().append('<button type="button" class="appBtn" id="approveProfile" onClick="approveApplication(\'<?php echo $user[0]['userInfo']['id']; ?>\')">APPROVE APPLICATION AND SEND CONFIRMATION</button><br/><br/><br/><button type="button" class="appBtn" id="rejectApplication" onClick="rejectApplication(\'<?php echo $user[0]['userInfo']['id']; ?>\')">REJECT APPLICATION AND SEND NOTIFICATION</button><br/><br/><br/>')
                    }
                }

                if(data['rob_60_cancelled'] == 1){
                    if(data['rob_50_approve'] == 3){
                        $(".appStatusText").empty().append(": Rejected")
                    }else{
                        $(".appStatusText").empty().append(": Cancelled")
                    }
                }else{
                    if(data['rob_50_approve'] == 2){
                        $(".appStatusText").empty().append(": Approved")                        
                    }else if(data['rob_50_approve'] == 3){
                        $(".appStatusText").empty().append(": Rejected")
                    }
                }

                // appStatusText
            }
        });

        //         if("{{$user[0]['userInfo']['studentType']}}" == "Cash / Direct"){
            
        //             if($("#id_data_approved").val() == "2" && $("#student_card_approved").val() == "2" && $("#registration_approved").val() == "2" && $("#bank_approved").val() == "2" && $("#parent_approved").val() == "2" && data.bookingStatus === "Approved" && data.applicationStatus === "Approved" && data.overall_status === "Pending" && $("#bed_allocated").val() !== ""){
        //                 $("#MainAppSection").removeClass('hidden')
        //                 // document.getElementById("col1").style.marginTop = "-35px";
        //                 // document.getElementById("col2").style.marginTop = "-35px";
        //                 // document.getElementById("col3").style.marginTop = "-35px";
        //             } else {
        //                 $("#MainAppSection").addClass('hidden')
        //             }

        //         }else if("{{$user[0]['userInfo']['studentType']}}" == "NSFAS"){
                    
        //             if($("#id_data_approved").val() == "2" && $("#student_card_approved").val() == "2" && $("#registration_approved").val() == "2" && data.bookingStatus === "Approved" && data.applicationStatus === "Approved" && data.overall_status === "Pending" && $("#bed_allocated").val() !== ""){
        //                 $("#MainAppSection").removeClass('hidden')
        //                 // document.getElementById("col1").style.marginTop = "-35px";
        //                 // document.getElementById("col2").style.marginTop = "-35px";
        //                 // document.getElementById("col3").style.marginTop = "-35px";
        //             } else {
        //                 $("#MainAppSection").addClass('hidden')
        //             }
        //         }else if("{{$user[0]['userInfo']['studentType']}}" == "Bursary"){
                    
        //             if($("#id_data_approved").val() == "2" && $("#student_card_approved").val() == "2" && $("#registration_approved").val() == "2" && $("#bursary_approved").val() == "2" && $("#burser_approved").val() == "2" && data.bookingStatus === "Approved" && data.applicationStatus === "Approved" && data.overall_status === "Pending" && $("#bed_allocated").val() !== ""){
        //                 $("#MainAppSection").removeClass('hidden')
        //                 // document.getElementById("col1").style.marginTop = "-35px";
        //                 // document.getElementById("col2").style.marginTop = "-35px";
        //                 // document.getElementById("col3").style.marginTop = "-35px";
        //             } else {
        //                 $("#MainAppSection").addClass('hidden')
        //             }
        //         }
        //     }
        // })
    }

    function changeBooking(bookingId, type){

        $("#bookingId").val(bookingId)
        var modal = document.getElementById("myModalEditApp");
        modal.style.display = "block";

        if($("#property").val() == "Dunvista Mansions" || $("#property").val() == "Braamlofts" || $("#property").val() == "Braamlofts" || $("#property").val() == "Wynton Joy" || $("#property").val() == "126 Siemert"){
            $("#roomSection").removeClass('hidden');
        }else{
            $("#roomSection").addClass('hidden');
        }

        $("#property").change();
        $("#roomType").val('<?php echo $booking->roomType;?>');

        callRoomDetails('floor','', '', '', '', '', 'open')
        
    }

    function changeLease(bookingId, type){

        $("#bookingId2").val(bookingId)
        var modal = document.getElementById("myModalEditLease");
        modal.style.display = "block";
        
    }

    function cancelLease(bookingId, type){
        
        Swal.fire({
            title: 'Are you sure?',
            text: "The current Approved Lease will be rejected.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    type: "post",
                    url: "/cancel-lease",
                    data: {
                        userId: "{{$user[0]['userInfo']['id']}}",
                    },
                });

                changeBooking(bookingId, type);

            }
        });
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

    function callRoomDetails(callId){

        

        if (callId == "floor") {
            if ($("#property").val() !== "" && $("#roomType").val() !== "") {
                $('#floor').html('<option>Loading</option>');
                $('#unit').html('<option>Choose a Unit</option>');
                $('#room').html('<option>All Rooms</option>');
                $('#bed_code').html('<option>Choose a Bed</option>');

                $('#room').val('');
                $('#bed').val('');

                $.ajax({
                    type: "post",
                    url: "/get-booking-info",
                    data: {
                        bookingId: $("#bookingId").val(), 
                        property: $("#property").val(),
                        roomType: $("#roomType").val(), 
                        getType: callId
                    },

                    success: function (data) {
                        let roomData = data[2];

                        $('#floor').html('<option>Choose a Floor</option>');

                        Object.keys(roomData).forEach( function(key) {

                            let floor_name = "";
                            let spacer = "";

                            if (roomData[key].meta_info_1 !== "" && roomData[key].meta_info_1 !== null) {
                                floor_name = roomData[key].meta_info_1;
                                spacer = " | ";
                            }

                            /*if (roomData[key].meta_info_2 !== "" && roomData[key].meta_info_2 !== null) {
                                floor_name += spacer + roomData[key].meta_info_2;
                                spacer = " - ";
                            }*/

                            floor_name += spacer + roomData[key].floor;

                            selected = "";
                            /*if (data[1].floor == roomData[key].floor) {
                                selected = "selected='selected'";
                            }*/

                            let meta = roomData[key].meta_info_1 !== null ? roomData[key].meta_info_1 : "";

                            $('#floor').append("<option "+selected+" value='"+roomData[key].floor+"' meta='"+meta+"'>"+floor_name+"</option>")
                        });                  
                    }
                });
            }

        } else if (callId == "unit") {
            $('#unit').html('<option>Loading</option>');
            $('#bed_code').html('<option>Choose a Bed</option>');

            $('#room').val('');
            $('#bed').val('');

            $.ajax({
                type: "post",
                url: "/get-booking-info",
                data: {
                    bookingId: $("#bookingId").val(), 
                    property: $("#property").val(),
                    roomType: $("#roomType").val(), 
                    floor: $('#floor').val(),
                    meta: $('#floor option:selected').attr('meta'),
                    getType: callId
                },

                success: function (data) {
                    let roomData = data[2];

                    $('#unit').html('<option>Choose a Unit</option>');

                    Object.keys(roomData).forEach( function(key) {
                        $('#unit').append("<option value='"+roomData[key].unit+"'>"+roomData[key].unit+"</option>")
                    });                    
                }
            });

        } else if (callId == "bed_code") {
            $('#bed_code').html('<option>Loading</option>');

            $('#room').val('');
            $('#bed').val('');

            $.ajax({
                type: "post",
                url: "/get-booking-info",
                data: {
                    bookingId: $("#bookingId").val(), 
                    property: $("#property").val(),
                    roomType: $("#roomType").val(), 
                    floor: $('#floor').val(),
                    unit: $('#unit').val(),
                    getType: callId
                },

                success: function (data) {
                    let roomData = data[2];
                    let bookedRoomData = data[3];

                    let RoomSelected = $("#selectedRoom").val()

                    $('#bed_code').html('<option>Choose a Bed</option>');
                    $('#room').empty()
                    $('#room').html('<option value="all">All Rooms</option>');

                    let roomCodes = [];

                   

                    Object.keys(roomData).forEach( function(key) {

                        if(!roomCodes.includes(roomData[key].room)){
                            roomCodes.push(roomData[key].room)
                        }

                        if(roomData[key].room == RoomSelected){
                            $('#bed_code').append("<option value='"+roomData[key].bed_code+"' room='"+roomData[key].room+"' bed='"+roomData[key].bed+"'>"+roomData[key].bed_code+"</option>")
                        }else if(RoomSelected == "all" || RoomSelected == "" || RoomSelected == "All Rooms"){
                            $('#bed_code').append("<option value='"+roomData[key].bed_code+"' room='"+roomData[key].room+"' bed='"+roomData[key].bed+"'>"+roomData[key].bed_code+"</option>")
                        }
                    }); 

                    Object.keys(roomCodes).forEach( function(key) {
                        console.log(roomCodes[key])
                        if(RoomSelected == roomCodes[key]){
                            $('#room').append("<option value='"+roomCodes[key]+"' selected>"+roomCodes[key]+"</option>")
                        }else{
                            $('#room').append("<option value='"+roomCodes[key]+"'>"+roomCodes[key]+"</option>")
                        }
                    })

                    

                    Object.keys(bookedRoomData).forEach( function(key) {
                        $('#bed_code').append("<option disabled value='"+bookedRoomData[key].bed_code+"' room='"+bookedRoomData[key].room+"' bed='"+bookedRoomData[key].bed+"'>"+bookedRoomData[key].bed_code+" (Booked)</option>")
                    });                    
                }
            });
        }

        /*
        if (floor === "") {
            $("#floor").html('<option>Loading...</option>');
            $("#unit").html('<option>Loading...</option>');
            $("#room").html('<option>Loading...</option>');
            $("#bed").html('<option>Loading...</option>');
            $("#bed_code").html('<option>Loading...</option>');
        } else if (unit === "") {
            $("#unit").html('<option>Loading...</option>');
            $("#room").html('<option>Loading...</option>');
            $("#bed").html('<option>Loading...</option>');
            $("#bed_code").html('<option>Loading...</option>');
        } else if (room === "") {
            $("#room").html('<option>Loading...</option>');
            $("#bed").html('<option>Loading...</option>');
            $("#bed_code").html('<option>Loading...</option>');
        } else if (bed === "") {
            $("#bed").html('<option>Loading...</option>');
            $("#bed_code").html('<option>Loading...</option>');
        } else if (bed_code === "") {
            $("#bed_code").html('<option>Loading...</option>');
        }

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

                console.log(callId, floor, unit, room, bed, bed_code, action);

                console.log(roomData);
                
                roomData.forEach(function (arrayItem) {
                    let new_key = arrayItem[''+callId+''];
                    let meta1 = "";
                    let meta2 = "";
                    
                    if (arrayItem['meta_info_1']) {
                        meta1 = arrayItem['meta_info_1'] + " - ";
                    }
                    
                    if (arrayItem['meta_info_2']) {
                        meta2 = arrayItem['meta_info_2'] + " - ";
                    }

                    //type.push(meta1 + new_key);
                    type.push(new_key);
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
        */
    }

    $("#floor").on('change', function(){
        callRoomDetails('unit');
    });

    $("#unit").on('change', function(){
        callRoomDetails('bed_code');
        $("#selectedRoom").val('all')
    });

    $("#room").on('change', function(){
        $("#selectedRoom").val($("#room").val())
        callRoomDetails('bed_code');
    });
    
    $('#bed_code').on('change', function() {
        if ($('#bed_code option:selected').attr('room') !== "null") {
            $('#room').val($('#bed_code option:selected').attr('room'));
        }

        if ($('#bed_code option:selected').attr('bed') !== "null") {
            $('#bed').val($('#bed_code option:selected').attr('bed'));
        }               
    });

    /*
    $("#room").on('change', function(){
        callRoomDetails('bed', $("#floor").val(), $("#unit").val(), $("#room").val(), '', '', '')
    })

    $("#bed").on('change', function(){
        callRoomDetails('bed_code', $("#floor").val(), $("#unit").val(), $("#room").val(), $("#bed").val(), '', '')
    })
    */

    

    

    var modalBooking = document.getElementById("myModalEditApp");
    var spanBooking = document.getElementsByClassName("closeBooking")[0];

    spanBooking.onclick = function() {
        modalBooking.style.display = "none";
    }

    var modalLease = document.getElementById("myModalEditLease");
    var spanLease = document.getElementsByClassName("closeLease")[0];
    
    spanLease.onclick = function() {
        modalLease.style.display = "none";
    }

    bookingTypeDetails();

    function bookingTypeDetails(){
        $.ajax({
            type: "post",
            url: "/get-booking-details",
            data: {
                userId: "{{$user[0]['userInfo']['id']}}",
            },

            success: function (data) {

                var metaInfo1 = "";
                var floor = "";
                var unit = "";
                if(data[1]?.meta_info_1 && data[1].meta_info_1 !== "" && data[1].meta_info_1 !== null){
                    metaInfo1 = data[1].meta_info_1+", ";
                }
                if(data[0]?.floor !== "" && data[0].floor !== null){
                    floor = data[0].floor + " Floor";
                }
                if(data[0]?.unit !== "" && data[0].unit !== null) {
                    unit = ", Unit " + data[0].unit;
                }

                var bed_code = "";
                var unallocated_bed = "";
                if(data[0]?.bed_code !== "" && data[0].bed_code !== null){
                    bed_code = "Bed Code: "+data[0].bed_code;

                    if("<?php echo Auth::user()->role_change_bed; ?>" == "1"){
                        if(data[2] == 'false'){
                            var unallocated_bed = '<button class="unallocateBed" type="button" id="btn_clear_bed">Unallocate Bed</button>';
                        }
                    }
                }

                var lease_from = "";
                if(data[0].lease_from_date !== ""){
                    var lease_from = 'Lease From: '+data[0].lease_from_date+' at '+data[0].lease_from_time+'</br/>';
                }
                var lease_to = "";
                if(data[0].lease_to_date !== ""){
                    var lease_to = 'Lease To: '+data[0].lease_to_date+' at '+data[0].lease_to_time+'</br/>';
                }

                let date1 = new Date(data[0].lease_from_date);
                let date2 = new Date(data[0].lease_to_date);
                var diff = new Date(date2.getTime() - date1.getTime());
                let years = diff.getUTCFullYear() - 1970;
                let months = diff.getUTCMonth();
                let days = diff.getUTCDate() - 3;
                var lease_duration = "";
                if(data[0].lease_from_date !== "" && data[0].lease_to_date !== ""){
                    var lease_duration = "Lease Duration: "+years+" years, "+months+" months, "+days+" days ";
                }

                var booking_fee = "";
                if(data[3] !== 'false'){
                    var booking_fee = '<h2 class="cardSubHeaderGrey">BOOKING FEE PAID</h2><h4 class="cardHeader" > R '+data[0].room_rate+'</h4><br/></div>';
                }
                
                let app_summary_details = '<h4 class="cardSubHeaderGrey">'+data[0].property+'</h4>'+
                    '<h4 class="cardHeader" >'+data[0].roomType+'</h4>'+
                    '<h4 class="cardHeader" id="prop_room_row1" >'+metaInfo1+floor+unit+'</h4>'+
                    '<h4 class="cardHeader" id="prop_room_row2" >'+
                    bed_code+
                    unallocated_bed+
                    '</h4>'+
                    '<h4 class="cardHeader">'+
                    lease_from+
                    lease_to+
                    lease_duration+
                    '</h4><br/>'+
                    booking_fee;

                $(".app_summary_details").html(app_summary_details);

                checkUserStatus();

            },
        });
    }

    $("#changeBookingType").on('click', function(){


        if($("#floor").val() !== "Choose a Floor" && $("#bed_code").val() == "Choose a Bed"){
            // if($("#property").val() == ""){
            //     $("#property").addClass("fieldError")
            //     $("#property_error").removeClass("hidden")
            // }

            // if($("#roomType").val() == ""){
            //     $("#roomType").addClass("fieldError")
            //     $("#roomType_error").removeClass("hidden")
            // }

            // if($("#bed_code").val() == null){
                $("#bed_code").addClass('fieldError')
                $("#bed_code_error").removeClass('hidden')
            // }
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

                        // console.log(data)
                        
                        // window.location.reload();
                        bookingTypeDetails();
                        refreshActions();

                        $(".waived_paid_wording").empty();

                        if(data[4] == 'true'){
                            $(".waived_paid_wording").append('Waived')
                        }else if(data[4] == '0'){
                            $(".waived_paid_wording").append('Yes, no booking fee')
                        }else{
                            if(data[3].status == "Approved"){
                                $(".waived_paid_wording").append('Yes')
                            }else{
                                $(".waived_paid_wording").append('No')
                            }
                        }

                        $("#myModalEditApp").html("");
                        $("#myModalEditApp").css("display", "none");
                    },
                });
        }
    })

    $("#changeLeaseDates").on('click', function(){
        
        $("#changeLeaseDates").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        $.ajax({
                type: "post",
                url: "/change-lease",
                data: {
                    bookingId: $("#bookingId2").val(), 
                    lease_from_date: $("#lease_from_date").val(), 
                    lease_from_time: $("#lease_from_time").val(),
                    lease_to_date: $("#lease_to_date").val(), 
                    lease_to_time: $("#lease_to_time").val(),
                },

                success: function (data) {
                    
                    // window.location.reload();

                    bookingTypeDetails();
                    refreshActions();
                    $("#myModalEditLease").html("");
                    $("#myModalEditLease").css("display", "none");
                },
            });
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
    });    

    $("#property").on('change', function(){
        let propValue = $("#property").val();

        if(propValue == "Braamlofts"){
            $("#roomSection").removeClass('hidden');
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option><option value="3 Sharing">3 Sharing</option>')
        }else if(propValue == "80 Jorissen"){
            $("#roomSection").addClass('hidden');
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "49 Jorissen"){
            $("#roomSection").addClass('hidden');
            $("#roomType").empty().append('<option value="">Select One</option><option value="2 Sharing">2 Sharing</option><option value="2 Sharing 2 Bedroom">2 Sharing | 2 Bedroom</option><option value="Single">Single</option><option value="Single 2 bedroom">Single | 2 Bedroom</option><option value="Single 3 bedroom">Single | 3 Bedroom</option><option value="Single 4 bedroom">Single | 4 Bedroom</option>')
        }else if(propValue == "Dunvista Mansions"){
            $("#roomSection").removeClass('hidden');
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "Wynton Joy"){
            $("#roomSection").removeClass('hidden');
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "126 Siemert"){
            $("#roomSection").removeClass('hidden');
            $("#roomType").empty().append('<option value="">Select One</option><option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "YMCA"){
            $("#roomSection").addClass('hidden');
            $("#roomType").empty().append('<option value="">Select One</option><option value="2 Sharing">2 Sharing</option><option value="2 Sharing 2 Bedroom">2 Sharing | 2 Bedroom</option><option value="2 Sharing 3 Bedroom">2 Sharing | 3 Bedroom</option><option value="3 Sharing">3 Sharing</option><option value="Single Small">Single | Small</option><option value="Single Medium">Single | Medium</option><option value="Single Large">Single | Large</option><option value="Single 3 Bedroom">Single | 3 Bedroom</option>')
        }

        $("#floor").empty();
        $("#unit").empty();
        $("#room").empty();
        $("#bed").empty();
        $("#bed_code").empty();
    })

    // function openModal(field, url, uploaded_by, created_at, fileId, status){
    //     url = url.replace("%27", "'");

    //     if(field == "studentCard"){
    //         var title = "Student card / Proof of student number";
    //         if($("#student_card_approved").val() != "1" && $("#student_card_approved").val() != "2"){
    //             var approved = 0;
    //         }else{
    //             var approved = 1;
    //         }
    //         var Data = "<span style='font-weight:bold'>Student Number:</span> {{$user[0]['userInfo']['studentNo']}}";
    //     }else if(field == "proof_of_payment"){
    //         var title = "Proof of payment";
    //         var approved = 1;
    //     }else if(field == "copyID"){
    //         var title = "Copy Of ID document";
    //         if($("#id_data_approved").val() != "1" && $("#id_data_approved").val() != "2"){
    //             var approved = 0;
    //         }else{
    //             var approved = 1;
    //         }
    //         if("{{$user[0]['userInfo']['userTitle']}}" == "Mr"){
    //             var gender = "<br/><span style='font-weight:bold'>Gender:</span> Male"
    //         }else{
    //             var gender = "<br/><span style='font-weight:bold'>Gender:</span> Female"
    //         }
    //         if("{{$user[0]['userInfo']['userNationality']}}" == "South Africa"){
    //             var IdData = "<br/><span style='font-weight:bold'>ID Number:</span>{{$user[0]['userInfo']['userId']}}";
    //         }else{
    //             var IdData = "<br/><span style='font-weight:bold'>Passport Number:</span>{{$user[0]['userInfo']['userPassport']}}";
    //         }
    //         var Data = "<span style='font-weight:bold'>Name:</span> {{$user[0]['userInfo']['userFirstName']}} {{$user[0]['userInfo']['userLastname']}}"+
    //             "<br/><span style='font-weight:bold'>Nationality:</span>{{$user[0]['userInfo']['userNationality']}}"+
    //             IdData+
    //             gender;
    //     }else if(field == "proofRegistration"){
    //         var title = "Proof of registration";
    //         if($("#registration_approved").val() != "1" && $("#registration_approved").val() != "2"){
    //             var approved = 0;
    //         }else{
    //             var approved = 1;
    //         }

    //         var Institution = "<?php foreach($lookups as $lookup){ if($lookup->id == $user[0]['userInfo']['institutionStudy']){ echo $lookup->lookup_name;}} ?>";
    //         var Faculty = "<?php foreach($lookups as $lookup){ if($lookup->id == $user[0]['userInfo']['faculty']){ echo $lookup->lookup_name;}} ?>";

    //         var Data = "<span style='font-weight:bold'>Institution:</span> "+Institution+
    //             "<br/><span style='font-weight:bold'>Faculty:</span> "+Faculty;
    //     }else if(field == "bursaryApproval"){
    //         var title = "NSFAS / Bursary Approval";
    //         if($("#bursary_approved").val() != "1" && $("#bursary_approved").val() != "2"){
    //             var approved = 0;
    //         }else{
    //             var approved = 1;
    //         }

    //         if("{{$user[0]['userInfo']['bint']}}"){
    //             bInt = "<span style='font-weight:bold'>Intermediary:</span> {{$user[0]['userInfo']['bint']}}<br/>"
    //         }else{
    //             bInt = ""
    //         }
    //         if("{{$user[0]['userInfo']['bIntName']}}"){
    //             bIntName = "<span style='font-weight:bold'>Specify Intermediary Name:</span> {{$user[0]['userInfo']['bIntName']}}<br/>"
    //         }else{
    //             bIntName = ""
    //         }
    //         if("{{$user[0]['userInfo']['studentType']}}" !== "NSFAS"){

    //             var Data = "<span style='font-weight:bold'>Company Name:</span> {{$user[0]['userInfo']['burCompanyName']}}<br/>"+
    //                 "<span style='font-weight:bold'>Registration Number:</span> {{$user[0]['userInfo']['burRegNo']}}<br/>"+
    //                 "<span style='font-weight:bold'>Contact Person:</span> {{$user[0]['userInfo']['burContactPerson']}}<br/>"+
    //                 "<span style='font-weight:bold'>Contact Number:</span> {{$user[0]['userInfo']['burContactNo']}}<br/>"+
    //                 "<span style='font-weight:bold'>Email Address:</span> {{$user[0]['userInfo']['burEmail']}}<br/>"+
    //                 "<span style='font-weight:bold'>Physical Address:</span> {{$user[0]['userInfo']['bAddress1']}}, {{$user[0]['userInfo']['bAddress2']}}, {{$user[0]['userInfo']['bAddressCity']}}, {{$user[0]['userInfo']['bAddressPostal']}}<br/>"+
    //                 "<span style='font-weight:bold'>How is the Bursary Paid:</span> {{$user[0]['userInfo']['bPayment']}}<br/>"+bInt+bIntName;
    //         }else{
    //             var Data = "";
    //         }

    //     }else if(field == "bankStatement"){
    //         var title = "Bank statement";
    //         if($("#bank_approved").val() != "1" && $("#bank_approved").val() != "2"){
    //             var approved = 0;
    //         }else{
    //             var approved = 1;
    //         }

            

    //         if("{{$user[0]['userInfo']['pNationality']}}" == "South Africa"){
    //             var IdData = "<span style='font-weight:bold'>ID Number:</span>{{$user[0]['userInfo']['sponId']}}<br/>";
    //         }else{
    //             var IdData = "<span style='font-weight:bold'>Passport Number:</span>{{$user[0]['userInfo']['pPassportNo']}}<br/>";
    //         }

    //         var Data = "<span style='font-weight:bold'>Name:</span> {{$user[0]['userInfo']['sponFirstName']}} {{$user[0]['userInfo']['sponLastName']}}<br/>"+
    //             "<span style='font-weight:bold'>Cellphone Number:</span> {{$user[0]['userInfo']['sponCell']}}<br/>"+
    //             "<span style='font-weight:bold'>Email Address:</span> {{$user[0]['userInfo']['sponEmail']}}<br/>"+
    //             "<span style='font-weight:bold'>Nationality:</span> {{$user[0]['userInfo']['pNationality']}}<br/>"+
    //             IdData+
    //             "<span style='font-weight:bold'>Physical Address:</span> {{$user[0]['userInfo']['pAddress1']}}, {{$user[0]['userInfo']['pAddress2']}}, {{$user[0]['userInfo']['pAddressCity']}}, {{$user[0]['userInfo']['pAddressPostal']}}<br/>";

    //     }else if(field == "moveIn"){
    //         var title = "Lease";
    //         var approved = 1;
    //     }
        
    //     $("#modalHeader").empty().append(title);
    //     $("#uploadedBy").empty().append(uploaded_by)
    //     $("#uploadedTime").empty().append(created_at.slice(11, 16))
    //     $("#uploadedDate").empty().append(created_at.slice(8, 10)+"|"+created_at.slice(5, 7)+"|"+created_at.slice(0, 4))
    //     $("#FileIdForApproval").val(fileId)
    //     $("#FileNameForApproval").val(field)

    //     if (Data !== "") {
    //         $("#infoToCheck").empty().append(Data);
    //     } else {
    //         approved = 1;
    //     }

    //     if(approved !== 1){
    //         /*var preview1 = document.getElementById("rejected");
    //         preview1.setAttribute("disabled", "disabled");*/
    //         @if(Auth::user()->role_approve_docs == 1)
    //             $("#approved").attr("disabled", "disabled");
    //         @endif
    //         /*var preview3 = document.getElementById("amend");
    //         preview3.setAttribute("disabled", "disabled");
    //         var preview4 = document.getElementById("rejectReason");
    //         preview4.setAttribute("readonly", "readonly");
    //         var preview5 = document.getElementById("actionUserName");
    //         preview5.setAttribute("readonly", "readonly");*/

    //         if (Data !== "") {
    //             $("#infoButtons").removeClass('hidden');
    //         }

    //         /*$("#infoChecker").removeClass('hidden')
    //         $("#infoToCheck").empty().append(Data);*/
    //     }else{
    //         @if(Auth::user()->role_approve_docs == 1)
    //             $("#rejected").removeAttr("disabled", "disabled");
    //             $("#approved").removeAttr("disabled", "disabled");
    //             $("#amend").removeAttr("disabled", "disabled");
    //             $("#rejectReason").removeAttr("readonly", "readonly");
    //             $("#actionUserName").removeAttr("readonly", "readonly");
    //         @endif

    //         $("#infoButtons").addClass('hidden');

    //         /*$("#infoChecker").addClass('hidden')
    //         $("#infoToCheck").empty();*/
    //     }

    //     if(status == "Approved" || status == "XApproved"){
    //         $("#approvedDocs").removeClass("hidden")
    //         $("#actionUserName").addClass("hidden")
    //     }else{
    //         $("#approvedDocs").addClass("hidden")
    //         $("#actionUserName").removeClass("hidden")
    //     }

    //     if(status == "Approved" || status == "Rejected" || status == "XApproved")
    //         $("#buttonSection").addClass('hidden')
    //     else
    //         $("#buttonSection").removeClass('hidden')


    //     if (url.search(".pdf") > 0) {
    //         $(".pdfSection").empty().append('<embed id="pdfView" src="/storage/'+url+'"  width="100%" height="600px" />')
    //     } else {
    //         $(".pdfSection").empty().append('<img id="pdfView" src="/storage/'+url+'" />')
    //     }  
        
    //     var modal = document.getElementById("myModal");
    //     modal.style.display = "block";
    // }

    // var modal = document.getElementById("myModal");
    
    
    // var span = document.getElementsByClassName("close")[0];

    // span.onclick = function() {
    //     modal.style.display = "none";
    //     $("#actionUserName").removeClass("errorBorder")
    //     $("#rejectReason").removeClass("errorBorder")
    //     $("#rejectReason_error").addClass("hidden")
    //     $("#actionUserName_error").addClass("hidden")
        
    //     $("#rejectReason").val("")
    //     $("#approvedDocs").addClass("hidden")
    //     document.getElementById("reasonBox").style.marginTop = "-21px";
    // }

    // window.onclick = function(event) {
    //     if (event.target == modal) {
            
    //         modal.style.display = "none";
    //         $("#actionUserName").removeClass("errorBorder")
    //         $("#rejectReason").removeClass("errorBorder")
    //         $("#rejectReason_error").addClass("hidden")
    //         $("#actionUserName_error").addClass("hidden")
    //         $("#rejectReason").val("")
    //         $("#approvedDocs").addClass("hidden")
    //         document.getElementById("reasonBox").style.marginTop = "-21px";
    //     }

    //     if (event.target == modalBooking) {
    //         //
    //         modalBooking.style.display = "none";
    //     }

    // }       

    function refreshActions(){

        $(".actionItemList").empty().append('<i class="fas fa-circle-notch fa-spin" style="font-size:41px; margin-left:41%;"></i>');

        $.ajax({
            type: "get",
            url: "/action-list/<?php echo $user[0]['userInfo']['id']; ?>/all",
            success: function (data) {
                $(".actionItemList").empty();
                
                data.forEach(myFunction);
                function myFunction(item, index) {
                    // console.log(item)
                    if(item['actionUserRole'] == "1"){
                        if(item['users']['user_role_name'] == ""){
                            var role = '<button type="button" class="admin">Admin</button>';
                        }else{
                            var role = '<button type="button" class="admin">'+item['users']['user_role_name']+'</button>';
                        }
                    }else{
                        var role = '<button type="button" class="applicant">Applicant</button>';
                    }

                    $(".actionItemList").append(
                        '<div class="infoBlock">'+
                            '<div class="responsible">'+
                                '<p>'+item['users']['userFirstName']+' '+item['users']['userLastname']+'</p>'+
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

        checkUserStatus();
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

                    if(item['status'] == "Uploaded" || item['status'] == "Approved") {
                        var button = '<button type="button" onClick="btn_view_document_ajax('+item['id']+')">View Document</button>';

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

    function UploadDocument(field){
        console.log("here")
        // let field = $(this).attr('id');
        // console.log(field);

        $("#" + field + "Section").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        let userId = <?php echo $user[0]['userInfo']['id'];?>

        var form_data = new FormData();
        form_data.append("file", document.getElementById(field).files[0]);
        form_data.append("userUid", userId);
        form_data.append("fileType", field);
        form_data.append("adminName", "{{Auth::user()->userFirstName}}");

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0]> 0) {
                    
                    $("#" + field + "Status").empty().append('<div class="button action"></div>');

                    var button = '<button type="button" onClick="btn_view_document_ajax('+data[2]['id']+')">View Document</button>';

                    if(field == "moveIn"){
                        var button = '<a href="/storage/'+data[2]['fileUrl']+'" download="'+data[1]['userFirstName']+'_'+data[1]['userLastname']+'_'+data[1]['userId']+'_Lease.pdf" style="margin-right:5px;"><button type="button" id="myBtn_download">Download Lease</button></a><button type="button" id="myBtn_regen_lease" style="margin-right:5px;">Regen Lease</button><button type="button" onClick="btn_view_document_ajax('+data[2]['id']+')">View Document</button>'
                    }

                    $("#" + field + "Banner").empty().append(button);

                    refreshActions();

                } else {
                    Swal.fire(
                        'Error',
                        data[0],
                        'error'
                    )
                    $("#" + field + "Section").empty().append('<span class="text">Upload</span>  <i class="fas fa-file-upload" ></i>');
                }
            },
        });
    }


    // //File Upload
    // $(document).on('change', '#bursaryApproval, #moveIn, #copyID, #studentCard, #bankStatement, #proofRegistration, #proof_of_payment', function(){
    //     let field = $(this).attr('id');
    //     // console.log(field);

    //     $("#" + field + "Section").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

    //     let userId = <?php echo $user[0]['userInfo']['id'];?>

    //     var form_data = new FormData();
    //     form_data.append("file", document.getElementById(field).files[0]);
    //     form_data.append("userUid", userId);
    //     form_data.append("fileType", field);
    //     form_data.append("adminName", "{{Auth::user()->userFirstName}}");

    //     $.ajax({
    //         type: "post",
    //         url: "/move-in-form-admin",
    //         data: form_data,
    //         cache: false,
    //         contentType: false,
    //         processData: false,

    //         success: function (data) {
    //             if (data[0]> 0) {
                    
    //                 $("#" + field + "Status").empty().append('<div class="button action"></div>');

    //                 var button = '<button type="button" onClick="btn_view_document_ajax('+data[2]['id']+')">View Document</button>';

    //                 if(field == "moveIn"){
    //                     var button = '<a href="/storage/'+data[2]['fileUrl']+'" download="'+data[1]['userFirstName']+'_'+data[1]['userLastname']+'_'+data[1]['userId']+'_Lease.pdf" style="margin-right:5px;"><button type="button" id="myBtn_download">Download Lease</button></a><button type="button" id="myBtn_regen_lease" style="margin-right:5px;">Regen Lease</button><button type="button" onClick="btn_view_document_ajax('+data[2]['id']+')">View Document</button>'
    //                 }

    //                 $("#" + field + "Banner").empty().append(button);

    //                 refreshActions();

    //             } else {
    //                 Swal.fire(
    //                     'Error',
    //                     data[0],
    //                     'error'
    //                 )
    //                 $("#" + field + "Section").empty().append('<span class="text">Upload</span>  <i class="fas fa-file-upload" ></i>');
    //             }
    //         },
    //     });
    // })    

    function unWaiveBookingFee(userId){
        $("#unWaiveThisBookingFee").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to undo this booking fee waiver",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, undo it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "/undo-waive-booking-fee",
                    data: {
                        userId: userId,
                    },

                    success: function (data) {
                        // window.location.reload();

                        $("#proof_of_paymentStatus").empty().append('<div class="button rejected"></div>');

                        <?php if(Auth::user()->role_upload_docs == 1){ ?>
                            let uploadbtn = '<label for="proof_of_payment" id="proof_of_paymentSection" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="proof_of_payment" onChange="UploadDocument(\'proof_of_payment\')" hidden/>';
                            $("#proof_of_paymentBanner").empty().append(uploadbtn);
                        <?php } ?>

                        $("#booking_fee_waived").val('false');
                        refreshActions();  
                        checkUserStatus();
                    },
                });
            }else{
                $("#unWaiveThisBookingFee").empty().append('Undo Booking Fee Waiver')
            }
        })
    }

    function waiveBookingFee(userId){
        $("#waiveThisBookingFee").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to waive this booking fee",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, waive it!'
        }).then((result) => {
            if (result.isConfirmed) {
                swal.fire({
                    title: 'Reason for waiving the booking fee',
                    input: 'text',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    showLoaderOnConfirm: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "post",
                            url: "/waive-booking-fee",
                            data: {
                                userId: userId,
                                reason: result.value
                            },

                            success: function (data) {
                                // window.location.reload();

                                $("#proof_of_paymentStatus").empty().append('<div class="button approved"></div>');

                                var button = '<button type="button" onClick="btn_view_document_ajax('+data+')">View Document</button>';
                                $("#proof_of_paymentBanner").empty().append(button);

                    
                                $("#booking_fee_waived").val('true');
                                refreshActions();  
                                checkUserStatus();
                            },
                        });
                    }else{
                        $("#waiveThisBookingFee").empty().append('waive booking fee')
                    }
                })
                
                
            }else{
                $("#waiveThisBookingFee").empty().append('waive booking fee')
            }
        })

        
    }

    function downloadZipDocs(userId){
        $("#downloadAllDocs").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')

        canDownload = $("#files_to_download").val()

        if(canDownload == 'true'){
            window.location.replace('/download/docs/'+userId);
            $("#downloadAllDocs").empty().append('Download All')
        }else{
            $("#downloadAllDocs").empty().append('Download All')
            Swal.fire(
                'Oops!',
                'Nothing to Download',
                'warning'
            )
        }
    }

    function cancelBooking(bookingId, userId){
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "/cancel/booking",
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

    $(document).off('click').on('click', '#myBtn_regen_lease', function(e) {
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

    jQuery(document).ready( function($) {
        $(document).on("click", '#btn_clear_bed', function(e) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to unallocate this bed, the bed will be available to book again",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes!',
                showLoaderOnConfirm: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "post",
                        url: "/unallocate-bed",
                        data: {
                            userId: "{{$user[0]['userInfo']['id']}}",
                        },

                        success: function (data) {
                            $("#prop_room_row1, #prop_room_row2").remove();
                            $("#bed_allocated").val('')

                            refreshActions();  
                            checkUserStatus();
                            

                        }
                    })

                }
            });

            e.preventDefault();
        });

        $(document).on('click', '.btn_edit_inline', function(e) {
            let formId = $(this).attr('formid');
            let edittype = $(this).attr('edittype');

            $('#' + formId).html('<i class="fas fa-circle-notch fa-spin"></i>');

            $.ajax({
                type: "get",
                url: "/edit-inline-data",
                data: {
                    userUid: "{{$user[0]['userInfo']['id']}}",
                    formId: formId,
                    type: edittype
                },
    
                success: function (data) {
                    
                    $('#' + formId).html(data);
                    checkUserStatus();
                },
            });

            e.preventDefault();
        });

        $(document).off('blur').on('blur', 'input', function(e) {
            let fieldId = e.target.id
            var required = $("#"+fieldId).hasClass('required')

            if($("#"+fieldId).val() == "" && required == true){
                $("#"+fieldId).addClass('fieldError')
                $("#"+fieldId+"_error").removeClass('hidden')
            }else{
                $("#"+fieldId).removeClass('fieldError')
                $("#"+fieldId+"_error").addClass('hidden')
            }
        });

        //Inline Form Validation
        $(document).on('keyup blur', "#userAddressPostal, #kinAddressPost", function(){
            let postal = $(this).val()
            let id = $(this).attr('id');

            if(/\b\d{4}\b/g.test(postal)){
                $("#" + id).removeClass("fieldError")
                $("#" + id + "_error").addClass('hidden')
            }else{
                $("#" + id).addClass("fieldError")
                $("#" + id + "_error").removeClass('hidden')
            }            
        });
        
        $(document).on('keyup blur', "#kinCellNo, #userCell", function(){
            let cell = $(this).val();
            let id = $(this).attr('id');

            ValidCell(cell, id);
            duplicateCell();          
        });
        
        $(document).on('keyup blur', "#userEmail", function(){
            let id = $(this).attr('id');

            if(isEmail($("#" + id).val())){
                $("#" + id).removeClass("fieldError")
                $("#" + id + "_error").addClass('hidden')
            }else{
                $("#" + id).addClass("fieldError")
                $("#" + id + "_error").removeClass('hidden')
            }    
        });
        
        $(document).on('keyup blur', "#kinEmail", function(){
            let id = $(this).attr('id');

            if ($("#" + id).val() !== "") {
                if(isEmail($("#" + id).val())){
                    $("#" + id).removeClass("fieldError")
                    $("#" + id + "_error").addClass('hidden')
                }else{
                    $("#" + id).addClass("fieldError")
                    $("#" + id + "_error").removeClass('hidden')
                }    
            }            
        });

        function ValidCell(cell, fieldId){
            if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
                $("#"+fieldId).removeClass("fieldError")
                $("#"+fieldId + "_error").addClass('hidden')
            }else{
                $("#"+fieldId).addClass("fieldError")
                $("#"+fieldId + "_error").removeClass('hidden')
            }
        }

        function duplicateCell(){
            $('#kinCellNo_duplicate').addClass('hidden');
            $('#cell_no_duplicate').addClass('hidden');
            $('#userCell, #kinCellNo').removeClass('fieldDuplicate');

            console.log($('#userCell').val(), $('#kinCellNo').val());

            if ($('#userCell').val() != "" && $('#userCell').val() == $('#kinCellNo').val()) {
                $('#kinCellNo_duplicate').removeClass('hidden');
                $('#cell_no_duplicate').removeClass('hidden');

                $('#cell_no_error').removeClass('hidden');
                $('#kinCellNo_error').removeClass('hidden');

                $('#userCell').addClass('fieldError');
                $('#kinCellNo').addClass('fieldError');

                $('#userCell').addClass('fieldDuplicate');
                $('#kinCellNo').addClass('fieldDuplicate');
            } else {
                cell = $("#userCell").val()
                ValidCell(cell, "userCell");
                
                cell = $("#kinCellNo").val();            
                ValidCell(cell, "kinCellNo");
            }
        }

        function isEmail(email) {
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return regex.test(email);
        }

        $(document).on('click', '.btn_save_inline', function(e) {

            let formid = $(this).attr('formid')

            //Check if any hidden fields are required, remove required check
            $('.edit_info .hidden .fieldError').removeClass('fieldError').val('');
            
            var req = 1;

            //Check empty fields that are required
            $('#'+formid+' .required').each( function(){
                if($(this).val() === "" || $(this).val() === null){
                    if (!$(this).parent().hasClass('hidden')) {
                        req = 0

                        console.log($(this)[0]["id"])

                        $(this).addClass("fieldError")
                        id = $(this)[0]["id"]
                        $("#"+id+"_error").removeClass("hidden")
                    }
                }
            });

            //Check any other fields with validation errors
            if ($('#'+formid+' .fieldError').length > 0) {
                req = 0;
            }

            if (req === 1) {
                let formId = $(this).attr('formid');
                let edittype = $(this).attr('edittype');

                let btn = $(this);

                $.ajax({
                    type: "post",
                    url: "/save-inline-data",
                    data: $('#' + formId + ' form').serialize(),
        
                    success: function (data) {

                        $(btn).next().click();
        
                    },
                });

                e.preventDefault();           

            } else {
                Swal.fire(
                    'Blank Fields',
                    'Please fill in all Required Fields!',
                    'error'
                );
            }
        });

        $(document).on('click', '.btn_approve_inline', function(e) {
            let formId = $(this).attr('formid');
            let approvefield = $(this).attr('approvefield');
            let task = "1";

            let message = "You are about to Approve this information";

            let btn = $(this);

            if ($(this).hasClass("rejected")) {
                message = "You are about to Reject this information";
                task = "0";
            }

            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes!',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {                    
                    $.ajax({
                        type: "post",
                        url: "/approve-inline-data",
                        data: {
                            userUid: "{{$user[0]['userInfo']['id']}}",
                            formId: formId,
                            approvefield: approvefield,
                            task: task
                        },
            
                        success: function (data) {

                            $("#"+approvefield).val(task)

                            refreshActions();  
                            checkUserStatus();

            
                        },
                    });
                },
            });

            e.preventDefault();
        });
    });

    function addOtherDoc(){
        swal.fire({
            title: 'Please name this document',
            input: 'text',
            inputAttributes: {
                autocapitalize: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'Submit',
            showLoaderOnConfirm: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "/add-other-document",
                    data: {
                        userId: "{{$user[0]['userInfo']['id']}}",
                        docName: result.value
                    },

                    success: function (data) {

                        $("#documentsSectionDisplay").append(
                            '<div class="section">'+
                                '<div class="label">'+
                                    (data.replaceAll("_", " ")).toUpperCase()+
                                '</div>'+
                                '<div class="status" id="'+data+'Status">'+
                                    '<div class="button rejected"></div>'+
                                '</div>'+
                                '<div class="banner" id="'+data+'Banner">'+
                                    '<label for="'+data+'" id="'+data+'Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;">'+
                                        '<span class="text">Upload</span>'+
                                        '<i class="fas fa-file-upload"></i>'+
                                    '</label>'+
                                    '<input type="file" id="'+data+'" onChange="UploadDocument(\''+data+'\')" hidden>'+
                                    '<button type="button" style="margin-left:5px;" onClick="deleteFile(\''+data+'\')">Delete</button>'+
                                '</div>'+
                            '</div>'
                        )

                        refreshActions();  
                        checkUserStatus()
                    },
                });
            }else{
                $("#waiveThisBookingFee").empty().append('waive booking fee')
            }
        })
    }

    function deleteFile(fileName){

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
                    url: "/remove-document-upload",
                    data: {
                        userId: "{{$user[0]['userInfo']['id']}}",
                        fileName: fileName
                    },

                    success: function (data) {

                        document.getElementById(fileName+"Status").parentElement.remove();

                        checkUserStatus()
                    },
                });
            }
        })
    }

</script>



@endsection
