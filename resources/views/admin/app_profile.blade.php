@extends('layouts.admin')

@section('content')

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="greyAppSection  @if($user[0]['userInfo']['studentType'] == 'Cash / Direct') greyAppCash @endif">
    <div class="container">
        <div class="col-3" id="col1">
        <div class="flex">
            <span id="approvedAddr" class="hidden">&#128994;</span><span id="rejectedAddr">&#128308;</span><p class="listHeader">Physical Address</p></div>
            <div class="form" id="inline_edit_address">
                {{$user[0]['userInfo']['userAddress1']}}<br/>
                {{$user[0]['userInfo']['userAddress2']}}<br/>
                {{$user[0]['userInfo']['userAddressCity']}}<br/>
                {{$user[0]['userInfo']['userAddressPostal']}}<br/>

                @if(in_array($booking->property, $allowedResidences))
                    <div class="address_btn"></div>
                @endif

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

                @if(in_array($booking->property, $allowedResidences))
                    <div class="kin_btn"></div>
                @endif

            </div>

        </div>
        
        @if($user[0]['userInfo']['studentType'] == "Cash / Direct")
            <div class="col-3" id="col3">
                <div class="flex"><span id="approvedSpon" class="hidden">&#128994;</span><span id="rejectedSpon">&#128308;</span><p class="listHeader">Sponsor/Parent </p></div>
                <div class="form">
                    <div class="sponInfoSection"></div>
                </div>
            </div>
        @endif

        @if($user[0]['userInfo']['studentType'] == "Bursary")
            <div class="col-3" id="col3">
                <div class="flex"><span id="approvedBur" class="<?php if($user[0]['userInfo']['burser_approved'] != '1'){ echo "hidden";} ?>">&#128994;</span><span id="rejectedBur" class="<?php if($user[0]['userInfo']['burser_approved'] == '1'){ echo "hidden";} ?>">&#128308;</span><p class="listHeader">Bursar</p></div>
                <div class="form">
                    <div class="burInfoSection"></div>
                </div>
            </div>
        @endif
        

       
            <div class="col-3" id="col3">
                <div class="form" id="inline_edit_student_type">
                    <p class="listHeader">Student Type</p>
                    <span id="studentUserType" class="{{$user[0]['userInfo']['studentType']}}"> @if($user[0]['userInfo']['studentType'] == "Cash / Direct") Cash @else {{$user[0]['userInfo']['studentType']}} @endif </span><br/>

                    @if($user[0]['userInfo']['rob_60_cancelled'] != "1" && $user[0]['userInfo']['rob_55_lease'] != "2" && Auth::user()->role_change_student_type == '1')
                        @if(in_array($booking->property, $allowedResidences))
                            <div class="studentType_btn"></div>
                        @endif
                    @endif
                </div>
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
                    <p class="InfoList">
                        {{$user[0]['userInfo']['userEmail']}}  @if($user[0]['userInfo']['email_verified'] == '1') <i class="fas fa-check" style="color:green"></i> @else &#10060; @endif<br/>
                        {{$user[0]['userInfo']['userCell']}}<br/>
                    </p>

                    @if(in_array($booking->property, $allowedResidences))
                        @if($mailVerificationEmail->status == '1')
                            <div class="app_details_btn"></div>
                        @endif
                    @endif
                    
                </div>
            </div>

            <div class="customWhiteCard" id="applicationCard" style="margin-top:20px;">
                <div class="flex">
                    <div class="bed_code_status"></div>
                    
                    <h2 class="appHeader">Application Summary</h2>
                </div>

                <div class="app_summary_details"></div>
                
                <div class="buttons flex" id="buttonTop">

                    @if(in_array($booking->property, $allowedResidences))
                        @if($leaseApproved == 'false' && $user[0]['userInfo']['rob_60_cancelled'] == 0)
                            @if(Auth::user()->role_change_bed == "1")
                                @if ($user[0]['userInfo']['rob_50_approve'] > 0)
                                    <button type="button" class="appBooking" id="changeLeaseCancel" onClick="cancelLease('{{$user[0]['userInfo']['id']}}', 'generate')">Change Bed</button>
                                @else
                                    <button type="button" class="appBooking" id="changeBedOnly" onClick="changeBooking('{{$user[0]['userInfo']['id']}}', 'generate')">Change Bed</button>
                                @endif                                
                            @endif

                            @if(Auth::user()->role_change_date == "1" && $user[0]['userInfo']['rob_50_approve'] != 2)
                                <button type="button" class="appBooking" id="changeDatesLease" onClick="changeLease('{{$user[0]['userInfo']['id']}}')">Change Lease Dates</button>
                            @endif
                        @endif

                        @if($leaseApproved == 'true' && $user[0]['userInfo']['rob_60_cancelled'] == 0)
                            @if(Auth::user()->role_modify_approved == 1)
                                <button type="button" class="appBooking" id="changeLeaseCancel" onClick="cancelLease('{{$user[0]['userInfo']['id']}}', 'generate')">Change Bed</button>
                            @endif
                        @endif
                    @endif


                        @if(Auth::user()->role_change_building == '1')
                            <button type="button" class="appBooking" onClick="changeBuilding('{{$booking->id}}', '{{$user[0]['userInfo']['id']}}')">Change Building</button>
                        @endif
                </div>
                <div class="buttons flex" id="buttonBottom">
                    @if(in_array($booking->property, $allowedResidences))
                        @if ($user[0]['userInfo']['rob_60_cancelled'] == 0)
                            @if(Auth::user()->role_delete_booking == 1)
                                <button type="button" class="appBooking" onClick="deleteBooking('{{$booking->id}}', '{{$user[0]['userInfo']['id']}}')">Delete this booking</button>
                            @endif

                            @if(Auth::user()->role_cancel_booking == 1)
                                @if($user[0]['userInfo']['rob_55_lease'] != 2 || (Auth::user()->role_modify_approved == 1 && $user[0]['userInfo']['rob_55_lease'] == 2))
                                    <button type="button" class="appBooking" onClick="cancelBooking('{{$booking->id}}', '{{$user[0]['userInfo']['id']}}')">Cancel booking</button>
                                @endif
                            @endif
                        @elseif($user[0]['userInfo']['rob_60_cancelled'] == 1)
                            @if(Auth::user()->role_cancel_booking == 1)
                                @if($user[0]['userInfo']['rob_55_lease'] != 2 || (Auth::user()->role_modify_approved == 1 && $user[0]['userInfo']['rob_55_lease'] == 2))
                                    @if(in_array($booking->property, $allowedResidences))
                                        <button type="button" class="appBooking" onClick="restoreBooking('{{$booking->id}}', '{{$user[0]['userInfo']['id']}}')">Restore booking</button>
                                    @endif
                                @endif
                            @endif
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
                                @if(!empty($action->users))
                                    <p>{{$action->users->userFirstName}} {{$action->users->userLastname}}</p>
                                @else
                                    <p>{{$action->actionUserDisplayName}}</p>
                                @endif
                                
                                @if($action->actionUserRole !== 0 )

                                    @if(!empty($action->users))
                                        @if($action->users->user_role_name == "" || $action->users->user_role_name == null)
                                            <button type="button" class="admin">Admin</button>
                                        @else
                                        <button type="button" class="admin">{{$action->users->user_role_name}}</button>
                                        @endif
                                    @else
                                        <button type="button" class="admin">Admin</button>
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
                    <div class="burPaymentDetails"></div>
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
                    @if(in_array($booking->property, $allowedResidences))
                        @if((Auth::user()->role_modify_approved == 1 && $user[0]['userInfo']['rob_50_approve'] == 2) || $user[0]['userInfo']['rob_50_approve'] == 0)
                            @if(Auth::user()->role_upload_docs == 1 && $user[0]['userInfo']['rob_60_cancelled'] == 0)
                                <button type="button" class="appBooking" id="addOtherDocument" onClick="addOtherDoc('{{$user[0]['userInfo']['id']}}')">Add Document</button>
                            @endif
                        @endif
                    @endif
                </div>

                <br/><br/><br/><br/>

                <div id="documentsSectionDisplay">

                    <?php $i = 0; ?>

                    @foreach($user[0]["files"] as $file)

                    @if(($file["field"] !== "moveIn") || ($file["field"] == "moveIn" && $user[0]['userInfo']['rob_50_approve'] == '2'))


                            <div class="section">
                                <div class="label">
                                    
                                    @if($file["field"] === "studentCard")
                                        STUDENT CARD
                                    @elseif($file["field"] === "proof_of_payment")
                                        BOOKING FEE PROOF OF PAYMENT
                                    @elseif($file["field"] === "copyID")
                                        COPY OF ID DOCUMENT
                                    @elseif($file["field"] === "proofRegistration")
                                        PROOF OF REGISTRATION
                                    @elseif($file["field"] === "bursaryApproval")
                                        @if($user[0]['userInfo']['studentType'] == "NSFAS")
                                            NSFAS APPROVAL
                                        @else
                                            BURSARY APPROVAL
                                        @endif
                                    @elseif($file["field"] === "bankStatement")
                                        BANK STATEMENT
                                    @elseif($file["field"] === "moveIn")
                                        LEASE
                                    @else
                                        <?php echo strtoupper(str_replace("_", " ", str_replace("other", "other |", $file["field"]))); ?>
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
                                        @if(Auth::user()->role_lease_regen == '1')
                                            <a href="#"><button type="button" id="myBtn_regen_lease">Regen Lease</button></a>
                                        @endif
                                    @endif

                                    @if($file["status"] === "AUploaded" || $file["status"] === "XApproved")
                                        <button
                                            type="button"
                                            onClick="btn_view_document_ajax({{$file['id']}})"
                                        >View Document</button>
                                    @else
                                        @if(in_array($booking->property, $allowedResidences))

                                            @if(Auth::user()->role_upload_docs == 1 && $user[0]['userInfo']['rob_60_cancelled'] == 0)
                                        

                                                <!-- booking_payment_ref -->
                                                @if($file["field"] === "proof_of_payment")
                                                    <label onClick="paymentRef()" id="{{$file['field']}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;">
                                                        <span class="text">Upload</span>
                                                        <i class="fas fa-file-upload" ></i>
                                                    </label>
                                                @else
                                                    <label for="{{$file['field']}}" id="{{$file['field']}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;">
                                                        <span class="text">Upload</span>
                                                        <i class="fas fa-file-upload" ></i>
                                                    </label>
                                                    <input type="file" id="{{$file['field']}}" onChange="UploadDocument('{{$file['field']}}')" hidden/>
                                                @endif
                                    

                                                @if(explode("_", $file["field"])[0] == "other")
                                                    <button type="button" style="margin-left:5px;" onClick="deleteFile('{{$file['field']}}')">Delete</button>
                                                @endif

                                            @endif
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <?php $i++; ?>


                        @endif
                    @endforeach

                </div>


            </div>

            <div class="customWhiteCard" id="Comments" style="margin-top:20px; margin-bottom:20px; height:660px;">
                <h2 class="appHeader">Comments</h2>
                @if(in_array($booking->property, $allowedResidences))
                    @if(Auth::user()->role_comment == "1" && $user[0]['userInfo']['rob_60_cancelled'] == 0)
                        <textarea id="newComment" name="newComment" rows="6" cols="50" style="width:100%; border-radius:5px;"></textarea>
                        <br/>
                        <button type="button" class='detailsChange' id="addComment" style="background-color:#e74f26">Add Comment</button>
                    @endif
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

<div id="changeBuildingModal" class="modal">
    <div class="modal-content " id="changeBuildingHeight" style="height: 275px;">
        <span class="closeBuilding" style="float:right; font-size:25px; cursor:pointer">&times;</span>
        <div class="form-card">
            <h2>Change this booking</h2>

            <input type="hidden" id="bookingId"/>
            <br/><br/>
            <div class="form-field">
                <label>Residence * <span id="propertyBuilding_error" class="hidden"><small style="color:red">This field is required</small></span></label>
                <select name="propertyBuilding" id='propertyBuilding' class="required">
                    <option value="" selected disabled>Select a Residence</option>
                </select>
            </div>

            <div class="form-field" id="buildingRooms">
                <label>Room Type * <span id="roomTypeBuilding_error" class="hidden"><small style="color:red">This field is required</small></span></label>
                <select name="roomTypeBuilding" id='roomTypeBuilding' class="required">
                    <option value="" selected disabled>Select a Room Type</option>
                </select>
            </div>
            <div class="form-field-big">
                <button type="button" id="changeBuildingType" class="profileSubmit" style="width:98%; border:none; padding:12px; border-radius:10px;">Change building</button>
            </div>
        </div>
    </div>
</div>

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

            <div class="form-field" id="roomSection">
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

    <div class="modal-content " id="leaseEditModal" style="height: 304px;">
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

<div id="myModalSposorInformation" class="modal">
    <div class="modal-content " style="height: 690px;">
        <!-- <span class="closeSponsorinfo" style="float:right; font-size:25px; cursor:pointer">&times;</span> -->
        <div class="form-card">
            <h2>Sponsor Information</h2>
            <div class="form-field">
                    <label>First Name * <span id="pFirstName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="pFirstName" id="pFirstName" class="required" value="{{$user[0]['userInfo']['sponFirstName']}}">
                </div>

                <div class="form-field">
                    <label>Last Name * <span id="pLastName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="pLastName" id="pLastName" class="required" value="{{$user[0]['userInfo']['sponLastName']}}">
                </div>

                <div class="form-field">
                    <label>Cellphone Number * <span id="pCell_no_error" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
                    <input type="text" name="pCell_no" id="pCell_no" class="required" value="{{$user[0]['userInfo']['sponCell']}}">
                </div>

                <div class="form-field">
                    <label>Email Address * <span id="pEmail_error" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
                    <input type="email" name="pEmail" id='pEmail' class="required" value="{{$user[0]['userInfo']['sponEmail']}}">
                </div>

                <div class="form-field">
                    <label>Nationality * <span id="pNationality_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <select name="pNationality" id='pNationality' class="required">
                        <option value="" selected disabled>Please select one</option>
                        @foreach($lookups as $lookup)
                            @if($lookup->group_id == "5")
                                <option value="{{$lookup->lookup_name}}" <?php if($user[0]['userInfo']['pNationality'] == $lookup->lookup_name){echo "selected";} ?>>{{$lookup->lookup_name}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-field" id="pIdField">
                    <label>I.D Number * <span id="pIdNumber_error" class="hidden" style="color:red"><small>Not a valid ID number</small></span></label>
                    <input type="text" name="pIdNumber" id="pIdNumber" value="{{$user[0]['userInfo']['sponId']}}">
                </div>

                <div class="form-field hidden" id="pPassportField">
                    <label>Passport Number * <span id="pPassportNo_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="pPassportNo" id="pPassportNo" value="{{$user[0]['userInfo']['pPassportNo']}}">
                </div>

                

                <!-- <div class="form-field-big">
                    <label>Physical Address* <span id="pAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" placeholder="Street No" name="pAddress1" id="pAddress1" style="width:98% !important">
                </div> -->
                <div class="form-field-big" style="height:50px !important">
                    <label>Physical Address * <span id="pAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                </div>
                <div class="form-field-big" style="height:60px !important">
                    <li style="display:inline-flex">
                        <input type="checkbox" id="app_address_p" name="app_address_p" value="1" style="width:50px !important;" <?php if($user[0]['userInfo']['app_address_p'] == '1'){echo "checked";}?>>
                        <label for="app_address_p" style="width:400px">Same as Applicant's Home Address</label>
                    </li>
                </div>

                <div class="pAddress <?php if($user[0]['userInfo']['app_address_p'] == '1'){echo "hidden";}?>">
                    <div class="form-field-big" style="height:60px !important">
                        <input type="text" placeholder="Street No" name="pAddress1" id="pAddress1" style="width:98% !important" class="required" value="{{$user[0]['userInfo']['pAddress1']}}">
                    </div>
                    <div class="form-field-big" style="height:60px !important">
                    <span id="pAddress2_error" class="hidden" style="color:red"><small>This field is required</small></span>
                        <input type="text" placeholder="Suburb" name="pAddress2" id="pAddress2" style="width:98% !important" value="{{$user[0]['userInfo']['pAddress2']}}">
                    </div>
                    <div class="form-field" style="height:60px !important">
                    <span id="pAddressCity_error" class="hidden" style="color:red"><small>This field is required</small></span>
                        <input type="text" placeholder="City" name="pAddressCity" id='pAddressCity'  value="{{$user[0]['userInfo']['pAddressCity']}}">
                    </div>
                    <div class="form-field" style="height:60px !important">
                    <span id="pAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal code</small></span>
                        <input type="text" placeholder="Postal Code" name="pAddressPostal" id="pAddressPostal" value="{{$user[0]['userInfo']['pAddressPostal']}}">
                    </div>
                </div>
            <div class="form-field-big" id="changeLeaseDatesOnly">
                <button type="button" id="sponsorSubmit" class="profileSubmit" style="width:98%; border:none; padding:12px; border-radius:10px;">Save Sponsor / Parent Information</button>
            </div>
        </div>
    </div>
</div>

<div id="myModalBursarInformation" class="modal">
    <div class="modal-content " style="height: 860px;">
        <!-- <span class="closeBursarinfo" style="float:right; font-size:25px; cursor:pointer">&times;</span> -->
        <div class="form-card">
            <h2>Bursar Information</h2>
            <div class="form-field">
                    <label>Company Name * <span id="bCompanyName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="bCompanyName" id='bCompanyName' class="required" value="{{$user[0]['userInfo']['burCompanyName']}}">
                </div>

                <div class="form-field">
                    <label>Registration Number * <span id="bRegistrationNo_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="bRegistrationNo" id="bRegistrationNo" class="required" value="{{$user[0]['userInfo']['burRegNo']}}">
                </div>

                <div class="form-field">
                    <label>Contact Person * <span id="bContactperson_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="bContactPerson" id="bContactPerson" class="required" value="{{$user[0]['userInfo']['burContactPerson']}}">
                </div>

                <div class="form-field">
                    <label>Contact Number * <span id="bContactNumber_error" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
                    <input type="text" name="bContactNumber" id="bContactNumber" class="required" value="{{$user[0]['userInfo']['burContactNo']}}">
                </div>

                <div class="form-field">
                    <label>Email Address * <span id="bEmail_error" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
                    <input type="email" name="bEmail" id="bEmail" class="required" value="{{$user[0]['userInfo']['burEmail']}}">
                </div>

                <div class="form-field">
                    <br/>
                </div>

                <div class="form-field-big">
                    <label>Physical Address * <span id="bAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <input type="text" placeholder="Street No" name="bAddress1" id="bAddress1" style="width:98% !important" class="required" value="{{$user[0]['userInfo']['bAddress1']}}">
                </div>
                <div class="form-field-big" style="height:60px !important">
                <span id="bAddress2_error" class="hidden" style="color:red"><small>This field is required</small></span>
                    <input type="text" placeholder="Suburb" name="bAddress2" id="bAddress2" style="width:98% !important" class="required" value="{{$user[0]['userInfo']['bAddress2']}}">
                </div>
                <div class="form-field" style="height:60px !important">
                <span id="bAddressCity_error" class="hidden" style="color:red"><small>This field is required</small></span>
                    <input type="text" placeholder="City" name="bAddressCity" id='bAddressCity'  class="required" value="{{$user[0]['userInfo']['bAddressCity']}}">
                </div>
                <div class="form-field" style="height:60px !important">
                <span id="bAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal code</small></span>
                    <input type="text" placeholder="Postal Code" name="bAddressPostal" id="bAddressPostal" class="required" value="{{$user[0]['userInfo']['bAddressPostal']}}">
                </div>

                <div class="form-field">
                    <label>How Is the Bursary Paid * <span id="bPayment_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                    <select name="bPayment" id='bPayment' class="required">
                    <option value="" disabled selected>Please select one</option>
                        <option value="Directly to the RESIDENCE" <?php if($user[0]['userInfo']['bPayment'] == "Directly to the RESIDENCE"){echo 'selected';} ?>>Directly to the RESIDENCE</option>
                        <option value="Directly to the STUDENT" <?php if($user[0]['userInfo']['bPayment'] == "Directly to the STUDENT"){echo 'selected';} ?>>Directly to the STUDENT</option>
                        <option value="Via an INTERMEDIARY" <?php if($user[0]['userInfo']['bPayment'] == "Via an INTERMEDIARY"){echo 'selected';} ?>>Via an INTERMEDIARY</option>
                    </select>
                </div>

                <div class="form-field" id="placeholder">
                    <br/>
                </div>

                <div id="viaInt" class="<?php if($user[0]['userInfo']['bPayment'] != "Via an INTERMEDIARY"){echo 'hidden';} ?>">

                    <div class="form-field">
                        <label>Intermediary * <span id="bint_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                        <select name="bint" id='bint'>
                        <option value="" disabled selected>Please select one</option>
                            <option value="Fundi" <?php if($user[0]['userInfo']['bint'] == "Fundi"){echo 'selected';} ?>>Fundi</option>
                            <option value="Intellicard" <?php if($user[0]['userInfo']['bint'] == "Intellicard"){echo 'selected';} ?>>Intellicard</option>
                            <option value="Other" <?php if($user[0]['userInfo']['bint'] == "Other"){echo 'selected';} ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div id="spesificInt" class="<?php if($user[0]['userInfo']['bint'] != "Other"){echo 'hidden';} ?>">

                    <div class="form-field">
                        <label>Specify Intermediary Name * <span id="bIntName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                        <input type="text" name="bIntName" id="bIntName" value="{{$user[0]['userInfo']['bIntName']}}">
                    </div>

                    <div class="form-field">
                        <br/>
                    </div>
                </div>
            <div class="form-field-big" id="changeLeaseDatesOnly">
                <button type="button" id="saveBursarDetails" class="profileSubmit" style="width:98%; border:none; padding:12px; border-radius:10px;">Save Bursar Information</button>
            </div>
        </div>
    </div>
</div>

<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>

<!-- <input type="hidden" id="id_data_approved" value="{{$user[0]['userInfo']['id_data_approved']}}"/> -->
<!-- <input type="hidden" id="student_card_approved" value="{{$user[0]['userInfo']['student_card_approved']}}"/> -->
<!-- <input type="hidden" id="registration_approved" value="{{$user[0]['userInfo']['registration_approved']}}"/> -->
<!-- <input type="hidden" id="bursary_approved" value="{{$user[0]['userInfo']['bursary_approved']}}"/> -->
<!-- <input type="hidden" id="bank_approved" value="{{$user[0]['userInfo']['bank_approved']}}"/> -->
<input type="hidden" id="address_approved" value="{{$user[0]['userInfo']['address_approved']}}"/>
<input type="hidden" id="kin_approved" value="{{$user[0]['userInfo']['kin_approved']}}"/>
<!-- <input type="hidden" id="parent_approved" value="{{$user[0]['userInfo']['parent_approved']}}"/> -->
<!-- <input type="hidden" id="burser_approved" value="{{$user[0]['userInfo']['burser_approved']}}"/> -->
<!-- <input type="hidden" id="bed_allocated" value="{{$booking->bed_code}}"/> -->
<input type="hidden" id="files_to_download" value="{{$files_to_download}}"/>
<input type="hidden" id="selectedRoom" value=""/>
<input type="hidden" id="email_data_approved" value="{{$user[0]['userInfo']['email_data_approved']}}"/>

<input type="hidden" id="booking_fee_paid" value="{{$booking_paid}}" />
<input type="hidden" id="booking_fee_waived" value="{{$booking_waived}}" />

<input type="hidden" id="change_bed_type" value="" />




<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function paymentRef(){
        swal.fire({
                title: 'Please enter the Payment Reference:',
                input: 'text',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Submit',
                showLoaderOnConfirm: true,
            }).then((result) => {
                if (result.isConfirmed && result.value !== "") {
                    $("#booking_payment").empty().append(result.value);

                    $.ajax({
                        type: "post",
                        url: "/UpdatePaymentRef",
                        data: {
                            bookingId: "{{$booking->id}}",
                            reason: result.value
                        },

                        success: function (data) {

                            $("#proof_of_paymentStatus").empty().append('<div class="button rejected"></div>');
                                let uploadbtn = '<label for="proof_of_payment" id="proof_of_paymentSection" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="proof_of_payment" onChange="UploadDocument(\'proof_of_payment\')" hidden/>';
                                $("#proof_of_paymentBanner").empty().append(uploadbtn);
                                $("#proof_of_paymentSection").click();

                            refreshActions();  
                            checkUserStatus();
                        },
                    });
                }
            })
    }

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
                        $(".comments_block").append('<div class="infoBlock"><div class="responsible" style="width:100%">'+(data[key].created_at).slice(8, 10)+'/'+(data[key].created_at).slice(5, 7)+'/'+(data[key].created_at).slice(0, 4)+' '+(data[key].created_at).slice(11, 16)+' | <span class="commentMail">'+data[key].users.email+'</span> <span class="commentName"> | '+data[key].users.user_role_name+'</span></p></div><div class="information"><p>'+data[key].comment+'</p></div></div>')
                    });
                }

            },
        });
    }   


    checkUserStatus();

    // MainAppSection
    function checkUserStatus(){

        $(".waitve_btn").empty();

        if($("#booking_fee_paid").val() == 'false'){
            if(("{{Auth::user()->role_modify_approved}}" == 1 && "{{$user[0]['userInfo']['rob_50_approve']}}" == 2 && "{{Auth::user()->role_waive_fee}}" == 1) || ("{{$user[0]['userInfo']['rob_60_cancelled']}}" == 0 &&  "{{$user[0]['userInfo']['rob_50_approve']}}" != 2 && "{{Auth::user()->role_waive_fee}}" == 1)){
                if($("#booking_fee_waived").val() == 'false'){
                    $(".waitve_btn").append('<button type="button" class="appBooking" id="waiveThisBookingFee" onClick="waiveBookingFee(\'{{$user[0]['userInfo']['id']}}\')">Waive Booking Fee</button>')
                }else{
                    $(".waitve_btn").append('<button type="button" class="appBooking" id="unWaiveThisBookingFee" onClick="unWaiveBookingFee(\'{{$user[0]['userInfo']['id']}}\')">Undo Booking Fee Waiver </button>')
                }
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
                        break;
                    }
                }
            }?>
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

        //
        $.ajax({
            type: "get",
            url: "/check-user-payment/{{$user[0]['userInfo']['id']}}",
            success: function (data) {
                if(data['nsfas_bursar'] == 1){
                    var nsfas_bursar = "Yes";
                }else{
                    var nsfas_bursar = "No"
                }

                $(".burInfoSection").empty().append(data['burCompanyName']+"<br/>"+data['burRegNo']+"<br/>"+data['burContactPerson']+" - "+data['burContactNo']+"<br/>"+data['burEmail']+"<br/>"+data['bAddress1']+", "+data['bAddress2']+", <br/>"+data['bAddressCity']+", "+data['bAddressPostal']+"<br/><br/> NSFAS bursar? <strong>"+nsfas_bursar+"</strong>")

                if(data['studentType'] == "Cash / Direct"){
                    var studentType = "Cash"
                }else{
                    var studentType = data['studentType']
                }

                if(data['bPayment'] !== null){

                    var bPayment = "<span style='color:red;'>"+data['bPayment']+"</span>";

                    if(data['bPayment'] == "Via an INTERMEDIARY" && data['bint'] !== "Other"){
                        var bint = "<span style='color:red;'> | "+data['bint'];
                    }else if(data['bPayment'] == "Via an INTERMEDIARY" && data['bint'] == "Other"){
                        var bint = "<span style='color:red;'> | "+data['bIntName'];
                    }else{
                        var bint = "";
                    }
                }else{
                    var bPayment = "";
                    var bint = "";
                }


                if(data['sponId'] !== null){
                    var IDspon = data['sponId']
                }else{
                    var IDspon = data['pPassportNo']
                }

                if(studentType == "Bursary"){
                    $(".burPaymentDetails").empty().append(studentType+"<br/>"+bPayment+" "+bint)
                }else{
                    $(".burPaymentDetails").empty().append(studentType)
                }

                

                $(".sponInfoSection").empty().append(data['sponFirstName']+" "+data['sponLastName']+"<br/>"+data['sponCell']+"<br/>"+data['sponEmail']+"<br/>"+data['pNationality']+"<br/>"+IDspon+"<br/>"+data['pAddress1']+", "+data['pAddress2']+"<br/>"+data['pAddressCity']+", "+data['pAddressPostal'])
            }
        });


        //Check if we show/hide the approve user section
        $.ajax({
            type: "get",
            url: "/check-user-status/{{$user[0]['userInfo']['id']}}",

            success: function (data) {

                $(".address_btn").empty()

                
                $(".studentType_btn").empty().append('<button type="button" class="amend button btn_edit_inline" formid="inline_edit_student_type" edittype="form">Edit</button>')
        

                if(data.rob_50_approve != 2 && "<?php echo $user[0]['userInfo']['rob_60_cancelled']; ?>" == 0){
                    if($("#address_approved").val() != 1){
                        if("<?php echo Auth::user()->role_non_funding; ?>" == "1"){
                            $(".address_btn").append('<button type="button" class="approve button btn_approve_inline" formid="inline_edit_address" approvefield="address_approved" style="height:40px;">Approve</button>')
                        }

                        if("<?php echo Auth::user()->role_edit_app; ?>" == "1"){
                            $(".address_btn").append('<button type="button" class="amend button btn_edit_inline" formid="inline_edit_address" edittype="form">Edit</button>')
                        }
                    }else{
                        if("<?php echo Auth::user()->role_non_funding; ?>" == 1){
                            $(".address_btn").append('<button type="button" class="rejected button btn_approve_inline" formid="inline_edit_address" approvefield="address_approved" style="height:40px;">Reject</button>')
                        }
                    }

                }else if(data.rob_50_approve == 2){

                    if($("#address_approved").val() != 1){
                        if("<?php echo Auth::user()->role_non_funding; ?>" == "1"){

                            if("<?php echo Auth::user()->role_non_funding; ?>" == "1"){
                                $(".address_btn").append('<button type="button" class="approve button btn_approve_inline" formid="inline_edit_address" approvefield="address_approved" style="height:40px;">Approve</button>')
                            }

                            if("<?php echo Auth::user()->role_edit_app; ?>" == "1"){
                                $(".address_btn").append('<button type="button" class="amend button btn_edit_inline" formid="inline_edit_address" edittype="form">Edit</button>')
                            }
                        }
                    }else{
                        if("<?php echo Auth::user()->role_delete_uploaded; ?>" == '1'){
                            $(".address_btn").append('<button type="button" class="rejected button btn_approve_inline" formid="inline_edit_address" approvefield="address_approved" style="height:40px;">Reject</button>')
                        }
                    }
                }

                $(".kin_btn").empty()

                if(data.rob_50_approve != 2 && "<?php echo $user[0]['userInfo']['rob_60_cancelled'] ;?>" == 0){
                    if($("#kin_approved").val() != 1){
                        if("<?php echo Auth::user()->role_non_funding; ?>" == "1"){
                            $(".kin_btn").append('<button type="button" class="approve button btn_approve_inline" formid="inline_edit_kin" approvefield="kin_approved" style="height:40px;">Approve</button>')
                        }

                        if("<?php echo Auth::user()->role_edit_app; ?>" == "1"){
                            $(".kin_btn").append('<button type="button" class="amend button btn_edit_inline" formid="inline_edit_kin" edittype="form">Edit</button>')
                        }
                    }else{
                        if("<?php echo Auth::user()->role_non_funding; ?>" == 1){
                            $(".kin_btn").append('<button type="button" class="rejected button btn_approve_inline" formid="inline_edit_kin" approvefield="kin_approved" style="height:40px;">Reject</button>')
                        }
                    }
                }else if(data.rob_50_approve == 2){
                    if($("#kin_approved").val() != 1){
                        if("<?php echo Auth::user()->role_non_funding; ?>" == "1"){
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

                if(data.rob_50_approve != 2 && "<?php echo $user[0]['userInfo']['rob_60_cancelled']; ?>" == 0){
                    if($("#email_data_approved").val() != 1){
                        if("<?php echo Auth::user()->role_non_funding; ?>" == "1"){
                            $(".app_details_btn").append('<button type="button" class="approve button btn_approve_inline" formid="inline_app_details" approvefield="email_data_approved" style="height:40px;">Approve</button>')
                        }

                        if("<?php echo Auth::user()->role_edit_app; ?>" == "1"){
                            $(".app_details_btn").append('<button type="button" class="amend button btn_edit_inline" formid="inline_app_details" edittype="form">Edit</button>')
                        }
                    }else{
                        if("<?php echo Auth::user()->role_non_funding; ?>" == 1){
                            $(".app_details_btn").append('<button type="button" class="rejected button btn_approve_inline" formid="inline_app_details" approvefield="email_data_approved" style="height:40px;">Reject</button>')
                        }
                    }
                }else if(data.rob_50_approve == 2){
                    if($("#email_data_approved").val() != 1){
                        if("<?php echo Auth::user()->role_non_funding; ?>" == "1"){
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

                if(data['parent_approved'] == 1){
                    $("#rejectedSpon").addClass('hidden')
                    $("#approvedSpon").removeClass('hidden')
                }else{
                    $("#rejectedSpon").removeClass('hidden')
                    $("#approvedSpon").addClass('hidden')
                }

                if(data['burser_approved'] == 1){
                    $("#rejectedBur").addClass('hidden')
                    $("#approvedBur").removeClass('hidden')
                }else{
                    $("#rejectedBur").removeClass('hidden')
                    $("#approvedBur").addClass('hidden')
                }

                if(data['rob_40_bed'] == 2){
                    $(".bed_code_status").empty().append('<span id="bedAllocatedYes" style="margin-top:10px;">&#128994;</span>')
                }else{
                    $(".bed_code_status").empty().append('<span id="bedAllocatedNo" style="margin-top:10px;">&#128308;</span>')
                }

                $(".appSection").empty()

                if((data['rob_50_approve'] == 0 || data['rob_50_approve'] == 1) && data['rob_60_cancelled'] == 0){
                    $(".appSection").append('<div id="MainAppSection"><div class="main_btn"></div></div>')

                    if("<?php echo Auth::user()->role_app_lease; ?>" == 1
                        && data['rob_10_fee'] == 2
                        && data['rob_15_id'] == 2
                        && data['rob_20_card'] == 2
                        && data['rob_25_reg'] == 2
                        && data['rob_30_funds'] == 2
                        && data['rob_35_info'] == 2
                        && data['rob_40_bed'] == 2
                        && (data['rob_45_other'] == 3 || data['rob_45_other'] == 2))
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

    function changeBuilding(bookingId, userid){
        var modal = document.getElementById("changeBuildingModal");
        modal.style.display = "block";

        $.ajax({
            type: "get",
            url: "/check-all-rooms/{{$user[0]['userInfo']['id']}}",
            success: function (data) {
                let properties = data[0];

                console.log(data)

                let selProp = data[2].property;

                properties.sort();
                $('#propertyBuilding').empty()
                $('#propertyBuilding').append('<option value="" selected disabled>Select one</option>')

                let propArr = <?php echo json_encode($allowedResidences); ?>;

                Object.keys(properties).forEach( function(key) {
                    if(parseFloat("{{$booking->property}}") !== properties[key].id){

                        if("{{$user[0]['userInfo']['rob_60_cancelled']}}" == '1'){
                            if(propArr.includes(properties[key].id)){
                                $('#propertyBuilding').append('<option value="'+properties[key].id+'">'+properties[key].residence+'</option>')
                            }
                        }else{
                            $('#propertyBuilding').append('<option value="'+properties[key].id+'">'+properties[key].residence+'</option>')
                        }

                        // $('#propertyBuilding').append('<option value="'+properties[key].id+'">'+properties[key].residence+'</option>')
                    }
                });

                $('#propertyBuilding').val(selProp);

                $("#propertyBuilding").change();
            }
        });

        // callRoomDetails('floor','', '', '', '', '', 'open')
    }

    function changeBooking(bookingId, type){

        console.log(bookingId)
        console.log(type)

        if (type === "generate" && $('#moveInStatus').length) {
            console.log('regen lease');
            $("#change_bed_type").val('cancel')
        }

        $("#bookingId").val(bookingId)
        var modal = document.getElementById("myModalEditApp");
        modal.style.display = "block";

        $.ajax({
            type: "get",
            url: "/check-rooms/{{$user[0]['userInfo']['id']}}",
            success: function (data) {
                let properties = data[0];

                console.log(data)

                let selProp = data[2].property;

                properties.sort();
                $('#property').empty()

                Object.keys(properties).forEach( function(key) {
                    $('#property').append('<option value="'+properties[key].id+'">'+properties[key].residence+'</option>')
                });

                $('#property').val(selProp);

                $("#property").change();
            }
        });

        callRoomDetails('floor','', '', '', '', '', 'open')
        
    }

    function changeLease(bookingId, type){

        $("#bookingId2").val(bookingId)
        var modal = document.getElementById("myModalEditLease");
        modal.style.display = "block";
        
    }

    function cancelLease(bookingId, type) {

        @if ($user[0]['userInfo']['rob_55_lease'] == 2)
            let msg = "The current Approved Lease will be rejected.";
        @else
            let msg = "The current Lease will be regenerated.";
        @endif
        
        Swal.fire({
            title: 'Are you sure?',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.isConfirmed) {

                // $.ajax({
                //     type: "post",
                //     url: "/cancel-lease",
                //     data: {
                //         userId: "{{$user[0]['userInfo']['id']}}",
                //     },
                // });

                $("#change_bed_type").val('cancel')
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
                            if(roomCodes[key] !== null){
                                $('#room').append("<option value='"+roomCodes[key]+"'>"+roomCodes[key]+"</option>")
                            }
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

    var modalBuilding = document.getElementById("changeBuildingModal");
    var spanBuilding = document.getElementsByClassName("closeBuilding")[0];

    spanBooking.onclick = function() {
        modalBooking.style.display = "none";
    }

    spanBuilding.onclick = function() {
        modalBuilding.style.display = "none";
    }

    var modalLease = document.getElementById("myModalEditLease");
    var modalBursarInfo = document.getElementById("myModalBursarInformation");
    var modalSponInfo = document.getElementById("myModalSposorInformation");
    var spanLease = document.getElementsByClassName("closeLease")[0];
    // var spanBurInfo = document.getElementsByClassName("closeBursarinfo")[0];
    // var spanSponInfo = document.getElementsByClassName("closeSponsorinfo")[0];

    

    

    
    
    spanLease.onclick = function() {
        modalLease.style.display = "none";
    }

    // spanBurInfo.onclick = function() {
    //     modalBursarInfo.style.display = "none";
    // }
    // spanSponInfo.onclick = function() {
    //     modalSponInfo.style.display = "none";
    // }

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
                if(data[0].bed_code !== "" && data[0].bed_code !== null){
                    bed_code = "Bed Code: "+data[0].bed_code;

                    if("<?php in_array($booking->property, $allowedResidences);?>"){
                        //
                        if("<?php echo Auth::user()->role_change_bed; ?>" == "1" && data[5].rob_55_lease !== 2){
                            // if(data[2] == 'false'){                            
                                var unallocated_bed = '<button data-lease="'+data[2]+'" class="unallocateBed" type="button" id="btn_clear_bed">Unallocate Bed</button>';
                            // }
    
                            // console.log(data);
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
                if(data[3] !== 'false' && data[0].room_rate !== null){
                    var booking_fee = '<h2 class="cardSubHeaderGrey">BOOKING FEE PAID</h2><h4 class="cardHeader" > R '+data[0].room_rate+'</h4><br/></div>';
                }

                var room_type = "";
                if( data[0].room_type !== null && data[0].room_type !== undefined){
                    var room_type = '<h4 class="cardHeader" >'+data[0].room_type.name+'</h4>';
                }
                
                let app_summary_details = '<h4 class="cardSubHeaderGrey">'+data[0].residence.residence+'</h4>'+
                    room_type+
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

    $("input").blur(function(){
        let fieldId = $(this)[0]["id"]

        // var required = document.getElementById(fieldId).required
        var required = $("#"+fieldId).hasClass('required')

        if($("#"+fieldId).val() == "" && required == true){
            $("#"+$(this)[0]["id"]).addClass('fieldError')
            $("#"+$(this)[0]["id"]+"_error").removeClass('hidden')
        }else{
            $("#"+$(this)[0]["id"]).removeClass('fieldError')
            $("#"+$(this)[0]["id"]+"_error").addClass('hidden')
        }

    });

    $("select").blur(function(){
        let fieldId = $(this)[0]["id"]

        // var required = document.getElementById(fieldId).required
        var required = $("#"+fieldId).hasClass('required')

        if($("#"+fieldId).val() == null && required == true){
            $("#"+$(this)[0]["id"]).addClass('fieldError')
            $("#"+$(this)[0]["id"]+"_error").removeClass('hidden')
        }else{
            $("#"+$(this)[0]["id"]).removeClass('fieldError')
            $("#"+$(this)[0]["id"]+"_error").addClass('hidden')
        }
    });

    $("#bPayment").on('change', function(){
        
        if($("#bPayment").val() == "Via an INTERMEDIARY"){
            $("#viaInt").removeClass("hidden")
            $("#bint").addClass("required")
            $("#placeholder").addClass("hidden")
            $("#spesificInt").addClass("hidden")
            $("#bIntName").removeClass("required")
        }else{
            $("#viaInt").addClass("hidden")
            $("#bint").removeClass("required")
            $("#bint").removeClass("fieldError")
            $("#placeholder").removeClass("hidden")
            $("#spesificInt").addClass("hidden")
            $("#bIntName").removeClass("required")
            $("#bIntName").removeClass("fieldError")
        }
    })

    $("#bint").on('change', function(){
        
        if($("#bint").val() == "Other"){
            $("#spesificInt").removeClass("hidden")
            $("#bIntName").addClass("required")
        }else{
            $("#spesificInt").addClass("hidden")
            $("#bIntName").removeClass("required")
        }
    })
    $("#pIdNumber").on('keyup blur', function(){
        let idNumber = $("#pIdNumber").val();

        if(idNumber.length == 13){
            ValidID(idNumber, "pIdNumber", "pIdNumber_error")
        }else{
            $("#pIdNumber").addClass("fieldError")
            $("#pIdNumber_error").removeClass('hidden')
        }

    })
    function ValidCell(cell, fieldId, fieldError){
        if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
            $("#"+fieldId).removeClass("fieldError")
            $("#"+fieldError).addClass('hidden')
        }else{
            $("#"+fieldId).addClass("fieldError")
            $("#"+fieldError).removeClass('hidden')
        }
    }
    $("#pCell_no").on('keyup blur', function(){
        cell = $("#pCell_no").val()
        ValidCell(cell, "pCell_no", "pCell_no_error")
    })

    $("#bContactNumber").on('keyup blur', function(){
        cell = $("#bContactNumber").val()
        ValidCell(cell, "bContactNumber", "bContactNumber_error")
    })
    $("#bAddressPostal").on('keyup blur', function(){
        postal = $("#bAddressPostal").val()

        if(/\b\d{4}\b/g.test(postal)){
            $("#bAddressPostal").removeClass("fieldError")
            $("#bAddressPostal_error").addClass('hidden')
        }else{
            $("#bAddressPostal").addClass("fieldError")
            $("#bAddressPostal_error").removeClass('hidden')
        }
        
    })

    $("#pAddressPostal").on('keyup blur', function(){
        postal = $("#pAddressPostal").val()

        if(/\b\d{4}\b/g.test(postal)){
            $("#pAddressPostal").removeClass("fieldError")
            $("#pAddressPostal_error").addClass('hidden')
        }else{
            $("#pAddressPostal").addClass("fieldError")
            $("#pAddressPostal_error").removeClass('hidden')
        }
        
    })
    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }
    $("#pEmail").on('keyup blur', function(){
        if(isEmail($("#pEmail").val())){
            $("#pEmail").removeClass("fieldError")
            $("#pEmail_error").addClass('hidden')
        }else{
            $("#pEmail").addClass("fieldError")
            $("#pEmail_error").removeClass('hidden')
        }
    })

    $("#bEmail").on('keyup blur', function(){
        if(isEmail($("#bEmail").val())){
            $("#bEmail").removeClass("fieldError")
            $("#bEmail_error").addClass('hidden')
        }else{
            $("#bEmail").addClass("fieldError")
            $("#bEmail_error").removeClass('hidden')
        }
    })
    $("#pNationality").on('change', function(){
        console.log($("#pNationality").val())
        if($("#pNationality").val() == "South Africa"){
            $("#pPassportField").addClass('hidden')
            $("#pIdField").removeClass('hidden')

            $("#pPassportNo").removeClass("required")
            $("#pIdNumber").addClass("required")
            $("#pPassportNo").removeClass("fieldError")
        }else{
            $("#pIdField").addClass('hidden')
            $("#pPassportField").removeClass('hidden')

            $("#pPassportNo").addClass("required")
            $("#pIdNumber").removeClass("required")
            $("#pIdNumber").removeClass("fieldError")
        }
    })

    $("#changeBuildingType").on('click', function(){
        if($("#propertyBuilding").val() == ""|| $("#roomTypeBuilding").val() == ""){
            if($("#propertyBuilding").val() == ""){
                $("#propertyBuilding").addClass('fieldError')
                $("#propertyBuilding_error").removeClass('hidden')
            }

            if($("#roomTypeBuilding").val() == ""){
                $("#roomTypeBuilding").addClass('fieldError')
                $("#roomTypeBuilding_error").removeClass('hidden')
            }
        }else{

            $("#changeBuildingType").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
            
            $.ajax({
                type: "post",
                url: "/change-booking",
                data: {
                    bookingId: "{{$user[0]['userInfo']['id']}}", 
                    property: $("#propertyBuilding").val(),
                    roomType: $("#roomTypeBuilding").val(), 
                    floor: "Choose a Floor",
                },

                success: function (data) {
                    window.location.replace('/applications');
                }
            });
        }
    })

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
                        change_bed_type: $("#change_bed_type").val(),
                    },

                    success: function (data) {

                        // console.log(data)
                        
                        // window.location.reload();

                        if($("#change_bed_type").val() == "cancel"){
                            $.ajax({
                                type: "post",
                                url: "/createLease/{{$user[0]['userInfo']['id']}}",
                                data: {
                                    type: "auto",
                                },

                                success: function (data) {          
                                    let uploadbtn = '<a href="/storage/'+data[0]['fileUrl']+'" download="'+data[1]['userFirstName']+'_'+data[1]['userLastname']+'_'+data[1]['userId']+'_Lease.pdf" style="margin-right:5px;"><button type="button" id="myBtn_download">Download Lease</button></a> '

                                    if("<?php echo Auth::user()->role_lease_regen;?>" == "1"){
                                        uploadbtn += '<button type="button" id="myBtn_regen_lease" style="margin-right:5px;">Regen Lease</button> '
                                    }
                                    
                                    uploadbtn += '<label for="moveIn" id="moveInSection" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="moveIn" onChange="UploadDocument(\'moveIn\')" hidden/>';
                                    $("#moveInBanner").empty().append(uploadbtn);
                                    $("#moveInStatus").empty().append('<div class="button rejected"></div>');

                                    $("#buttonTop").empty();

                                    @if(Auth::user()->role_change_bed == "1")
                                        $("#buttonTop").append('<button type="button" class="appBooking" id="changeBedOnly" onClick="changeBooking(\'{{$user[0]['userInfo']['id']}}\', \'generate\')">Change Bed</button>');
                                    @endif

                                    @if(Auth::user()->role_change_date == "1" && $user[0]['userInfo']['rob_50_approve'] != 2)
                                        $("#buttonTop").append('<button type="button" class="appBooking" id="changeDatesLease" onClick="changeLease(\'{{$user[0]['userInfo']['id']}}\')">Change Lease Dates</button>');
                                    @endif
                                },
                            });
                        }

                        if(data[1] !== null){
                            if(data[1].status == "Approved"){
                                $("#proof_of_paymentStatus").empty().append('<div class="button approved"></div>');
                                var button = '<button type="button" onClick="btn_view_document_ajax('+data[1]['id']+')">View Document</button>';
                                $("#proof_of_paymentBanner").empty().append(button);
    
                            }else if(data[1].status == "Uploaded"){
                                $("#proof_of_paymentStatus").empty().append('<div class="button action"></div>');
                                var button = '<button type="button" onClick="btn_view_document_ajax('+data[1]['id']+')">View Document</button>';
                                $("#proof_of_paymentBanner").empty().append(button);
                            }else{
                                $("#proof_of_paymentStatus").empty().append('<div class="button rejected"></div>');
                                let uploadbtn = '<label for="proof_of_payment" id="proof_of_paymentSection" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="proof_of_payment" onChange="UploadDocument(\'proof_of_payment\')" hidden/>';
                                $("#proof_of_paymentBanner").empty().append(uploadbtn);
                            }
                        }

                        $("#change_bed_type").val();
                        bookingTypeDetails();
                        refreshActions();

                        // $(".waived_paid_wording").empty();

                        // if(data[4] == 'true'){
                        //     $(".waived_paid_wording").append('Waived')
                        // }else if(data[4] == '0'){
                        //     $(".waived_paid_wording").append('Yes, no booking fee')
                        // }else{
                        //     if(data[3].status == "Approved"){
                        //         $(".waived_paid_wording").append('Yes')
                        //     }else{
                        //         $(".waived_paid_wording").append('No')
                        //     }
                        // }

                        $("#changeBookingType").empty().append('Change booking')

                        // $("#myModalEditApp").html("");
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
    
    $("#propertyBuilding").on('change', function(){
        let propValue = $("#propertyBuilding").val();

        $("#roomTypeBuilding").empty().append('<option value="">Loading</option>');

        $.ajax({
            type: "get",
            url: "/check-all-rooms/{{$user[0]['userInfo']['id']}}",
            success: function (data) {
                let roomData = data[3]

                $("#roomTypeBuilding").empty().append('<option value="">Select One</option>');
                Object.keys(roomData).forEach( function(key) {
                    // if("{{$booking->property}}" == propValue){
                    //     $("#buildingRooms").addClass('hidden')
                    // }else{
                    //     $("#buildingRooms").removeClass('hidden')
                    // }
                    if(propValue == roomData[key].residence){
                        var selected = "<?php echo $booking->roomType;?>" == roomData[key].room_type.id ? "selected" : "";
                        $("#roomTypeBuilding").append('<option value="'+roomData[key].room_type.id+'" '+selected+'>'+roomData[key].room_type.name+'</option>');
                    }
                });

            }
        })
    })

    $("#property").on('change', function(){
        let propValue = $("#property").val();

        //here
        $("#roomType").empty().append('<option value="">Loading</option>');

        $.ajax({
            type: "get",
            url: "/check-rooms/{{$user[0]['userInfo']['id']}}",
            success: function (data) {
                //

                console.log(data)
                let roomData = data[3]
                console.log(roomData)

                $("#roomType").empty().append('<option value="">Select One</option>');
                Object.keys(roomData).forEach( function(key) {
                    if(propValue == roomData[key].residence){
                        var selected = "<?php echo $booking->roomType;?>" == roomData[key].room_type.id ? "selected" : "";
                        $("#roomType").append('<option value="'+roomData[key].room_type.id+'" '+selected+'>'+roomData[key].room_type.name+'</option>');
                    }
                });

                $("#floor").empty();
                $("#unit").empty();
                $("#room").empty();
                $("#bed").empty();
                $("#bed_code").empty();
                $("#roomType").val('<?php echo $booking->roomType;?>');
                callRoomDetails('floor');
            }
        })

        

        
    })

    // function openModal(field, url, uploaded_by, created_at, fileId, status){
    //     url = url.replace("%27", "'");

    //     if(field == "studentCard"){
    //         var title = "Student card";
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
                        var name = 'STUDENT CARD';
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
                        var button = '<a href="/storage/'+data[2]['fileUrl']+'" download="'+data[1]['userFirstName']+'_'+data[1]['userLastname']+'_'+data[1]['userId']+'_Lease.pdf" style="margin-right:5px;"><button type="button" id="myBtn_download">Download Lease</button></a>'
                        
                        if("<?php echo Auth::user()->role_lease_regen;?>" == "1"){
                            button += '<button type="button" id="myBtn_regen_lease" style="margin-right:5px;">Regen Lease</button>'
                        }
                        
                        button += '<button type="button" onClick="btn_view_document_ajax('+data[2]['id']+')">View Document</button>'
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

    function restoreBooking(bookingId, userId){
        Swal.fire({
            title: 'Are you sure?',
            text: "This booking will become active again!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, activate it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "/restore/booking",
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

    function cancelBooking(bookingId, userId){
        Swal.fire({
            title: 'Are you sure?',
            text: "You will cancel this booking!",
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
                // if($("#change_bed_type").val() == "cancel"){
                    console.log(data)
                // }      
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
            var msg = "You are about to unallocate this bed, the bed will be available to book again";

            if ($(this).data("lease") === "true") {
                msg = "You are about to unallocate this bed, the bed will be available to book again. The Lease will also be rejected.";
            }

            Swal.fire({
                title: 'Are you sure?',
                text: msg,
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
                            // $("#bed_allocated").val('')

                            $('#moveInStatus').parent().remove();

                            //Update change bed to not show lease cancel message
                            $("#buttonTop").empty().append('<button type="button" class="appBooking" id="changeLeaseCancel" onClick="changeBooking({{$user[0]['userInfo']['id']}}, \'generate\')">Change Bed</button>');

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

                let infoData = "NSFAS";

                if(formId == 'inline_edit_student_type'){
                    if($("#studentType").val() == "Cash / Direct"){
                        var modal = document.getElementById("myModalSposorInformation");
                        modal.style.display = "block";
                        infoData = "CASH";
                    }else if($("#studentType").val() == "Bursary"){
                        var modal = document.getElementById("myModalBursarInformation");
                        modal.style.display = "block";
                        infoData = "BURSARY"
                    }
                }

                $.ajax({
                    type: "post",
                    url: "/save-inline-data",
                    data: $('#' + formId + ' form').serialize(),
        
                    success: function (data) {

                        $(btn).next().click();

                        if(infoData == "NSFAS"){
                            window.location.reload();
                        }
        
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
                                    ((data.replace("other", "other |")).replaceAll("_", " ")).toUpperCase()+
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

    $("#app_address_p").on('change', function(){

    if(document.getElementById('app_address_p').checked == true){
        $(".pAddress").addClass("hidden");
    }else{
        $(".pAddress").removeClass('hidden')
    }
    })

    $("#sponsorSubmit").on('click', function(){
        //Check if any hidden fields are required, remove required check
        $('.hidden .fieldError').removeClass('fieldError').val('');
        $("#sponsorSubmit").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')

        cell = $("#pCell_no").val()
        ValidCell(cell, "pCell_no", "pCell_no_error")

        if(isEmail($("#pEmail").val())){
            $("#pEmail").removeClass("fieldError")
            $("#pEmail_error").addClass('hidden')
        }else{
            $("#pEmail").addClass("fieldError")
            $("#pEmail_error").removeClass('hidden')
        }


        var req = 1;

        console.log($("#app_address_p").val())

        if(document.getElementById("app_address_p").checked !== true){
            postal = $("#pAddressPostal").val()

            if(/\b\d{4}\b/g.test(postal)){
                $("#pAddressPostal").removeClass("fieldError")
                $("#pAddressPostal_error").addClass('hidden')
                console.log("1")
            }else{
                $("#pAddressPostal").addClass("fieldError")
                $("#pAddressPostal_error").removeClass('hidden')
                console.log('2')
            }

            var sponArr = ["pFirstName", "pLastName", "pCell_no", "pEmail", "pNationality", "pAddress1", "pAddress2", "pAddressCity", "pAddressPostal"];
        }else{
            var sponArr = ["pFirstName", "pLastName", "pCell_no", "pEmail", "pNationality"];
        }

        if($("#pNationality").val() == "South Africa"){
            sponArr.push("pIdNumber")
        }else{
            sponArr.push("pPassportNo")
        }

        sponArr.forEach( function(value){
            if($("#"+value).val() === "" || $("#"+value).val() === null){

                $("#"+value).addClass("fieldError")
                $("#"+value+"_error").removeClass("hidden")
            }
        })

        if ($('.fieldDuplicate').length > 0) {
            req = 0;
        }

        if ($('.fieldError').length > 0) {
            req = 0;
        }

        if(req == 0){
            $("#sponsorSubmit").empty().append('Save Sponsor / Parent Information')
            Swal.fire(
                'Blank Fields',
                'Please fill in all Required Fields!',
                'error'
            )

            return false;
        }else{
            let total = 1
            $('.fieldError').each( function(){
                if($(this).val() === ""){
                    total = 0

                    $(this).addClass("fieldError")
                    id = $(this)[0]["id"]
                    $("#"+id+"_error").removeClass("hidden")
                }
            })

            if(total == 0){
                $("#sponsorSubmit").empty().append('Save Sponsor / Parent Information')
                Swal.fire(
                    'Blank Fields',
                    'Please fill in all Required Fields!',
                    'error'
                )

                return false;
            }else{
                $.ajax({
                    type: "post",
                    url: "/update-sponsor-details",
                    data: {
                        userId: "{{$user[0]['userInfo']['id']}}",
                        pFirstName: $("#pFirstName").val(),
                        pLastName: $("#pLastName").val(),
                        pCell_no: $("#pCell_no").val(),
                        pEmail: $("#pEmail").val(),
                        pNationality: $("#pNationality").val(),
                        pPassportNo: $("#pPassportNo").val(),
                        pIdNumber: $("#pIdNumber").val(),
                        app_address_p: document.getElementById("app_address_p").checked !== true ? '0' : '1',
                        pAddress1: $("#pAddress1").val(),
                        pAddress2: $("#pAddress2").val(),
                        pAddressCity: $("#pAddressCity").val(),
                        pAddressPostal: $("#pAddressPostal").val(),
                    },

                    success: function (data) {

                        window.location.reload();
                    },
                });
            }
        }
    })

    $("#saveBursarDetails").on('click', function(){
        //Check if any hidden fields are required, remove required check
        $('.hidden .fieldError').removeClass('fieldError').val('');
        $("#saveBursarDetails").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')

        cell = $("#bContactNumber").val()
        ValidCell(cell, "bContactNumber", "bContactNumber_error")

        if(isEmail($("#bEmail").val())){
            $("#bEmail").removeClass("fieldError")
            $("#bEmail_error").addClass('hidden')
        }else{
            $("#bEmail").addClass("fieldError")
            $("#bEmail_error").removeClass('hidden')
        }

        postal = $("#bAddressPostal").val()

        if(/\b\d{4}\b/g.test(postal)){
            $("#bAddressPostal").removeClass("fieldError")
            $("#bAddressPostal_error").addClass('hidden')
        }else{
            $("#bAddressPostal").addClass("fieldError")
            $("#bAddressPostal_error").removeClass('hidden')
        }


        var req = 1;

        var burArr = ["bCompanyName", "bRegistrationNo", "bContactPerson", "bContactNumber", "bEmail", "bAddress1", "bAddress2", "bAddressCity", "bAddressPostal", "bPayment"];


        if($("#bPayment").val() == "Via an INTERMEDIARY"){
            burArr.push("bint")
        }

        if($("#bint").val() == "Other"){
            burArr.push("bIntName")
        }

        burArr.forEach( function(value){
            if($("#"+value).val() === "" || $("#"+value).val() === null){

                $("#"+value).addClass("fieldError")
                $("#"+value+"_error").removeClass("hidden")
            }
        })

        if ($('.fieldDuplicate').length > 0) {
            req = 0;
        }

        if ($('.fieldError').length > 0) {
            req = 0;
        }

        if(req == 0){
            $("#saveBursarDetails").empty().append('Save Bursar Information')
            Swal.fire(
                'Blank Fields',
                'Please fill in all Required Fields!',
                'error'
            )

            return false;
        }else{
            let total = 1
            $('.fieldError').each( function(){
                if($(this).val() === ""){
                    total = 0

                    $(this).addClass("fieldError")
                    id = $(this)[0]["id"]
                    $("#"+id+"_error").removeClass("hidden")
                }
            })

            if(total == 0){
                $("#saveBursarDetails").empty().append('Save Bursar Information')
                Swal.fire(
                    'Blank Fields',
                    'Please fill in all Required Fields!',
                    'error'
                )

                return false;
            }else{
                $.ajax({
                    type: "post",
                    url: "/update-bursar-details",
                    data: {
                        userId: "{{$user[0]['userInfo']['id']}}",
                        bCompanyName: $("#bCompanyName").val(),
                        bRegistrationNo: $("#bRegistrationNo").val(),
                        bContactPerson: $("#bContactPerson").val(),
                        bContactNumber: $("#bContactNumber").val(),
                        bEmail: $("#bEmail").val(),
                        bAddress1: $("#bAddress1").val(),
                        bAddress2: $("#bAddress2").val(),
                        bAddressCity: $("#bAddressCity").val(),
                        bAddressPostal: $("#bAddressPostal").val(),
                        bPayment: $("#bPayment").val(),
                        bint: $("#bint").val(),
                        bIntName: $("#bIntName").val(),

                    },

                    success: function (data) {

                        window.location.reload();
                    },
                });
            }
        }
    })

</script>



@endsection
