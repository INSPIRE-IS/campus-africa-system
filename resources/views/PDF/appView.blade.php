<style>
    body {
        font-family: source-sans-pro, sans-serif;
    }
    .greyAppSection {
        width: 110%;
        min-height: 320px;
        background-color: #e8e8e8;
        margin-left: -50px;
        border-bottom-right-radius: 20px;
    }

    .greyAppSection_container {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding-left:20px;
    }
    .col-3 {
        width: 26.3%;
        margin: 12px 0;
        float:left;
    }
    .subSection {
        padding-top: 20px;
    }

    .listHeader {
        color: #464749;
        font-size: 24px;
        font-weight: 700;
        line-height: 29px;
    }
    .yellowGradientSmall {
        background-color:#e5b219;
        height: 316px;
        width: 110%;
        border-bottom-right-radius: 20px;
        margin-left: -50px;
        margin-top: -50px;
    }
    #bookingHeaderYellow {
        height: 300px;
    }
    .loginHeader {
        margin-top: 5px;
    }
    .bookingSub {
        color: #464749;
        font-size: 33px;
        font-weight: 400;
        padding:20px;
    }
    .booking_app {
        color: #464749;
        font-size: 40px;
        font-weight: 900;
        line-height: 46px;
        text-align: left;
        width: 57%;
        padding-left:20px;
    }
    /* .appRow {
        width: 100%;
        margin-right: 1%;
        float:left;
    } */
    .customWhiteCard {
        /* width: 100%; */
        /* float: left; */
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16);
        border-radius: 10px;
        background-color: #f8f8f8;
        padding: 20px;
    }
    .appHeader {
        color: #21232b;
        font-size: 28px;
        font-weight: 400;
        /* padding-bottom: 10px; */
    }
    .cardSubHeaderGrey {
        color: #9799ab;
        font-size: 21px;
        font-weight: 400;
        line-height: 28px;
        text-align: left;
    }
    .bigHeader {
        background-color: #e8e8e8;
        padding: 10px;
        height: 180px;
        border-radius: 10px 10px 0px 0px;
        margin-top: -40px;
        margin-left: -20px;
        margin-right: -20px;
    }
    .indexTab {
        width: 500px;
        float: left;
        border-radius: 10px;
        background-color: transparent;
        padding: 20px;
    }
    .flex{
        display: inline-block;
    }
    #indexOne {
        display: inline-block;
        width:30%;
        justify-content: flex-end;
    }
    .approved {
        background-color: #1cb915;
    }
    .action {
        background-color: #ffbb3c;
    }
    .rejected {
        background-color: #b72626;
    }
    .na {
        background-color: #cecece;
    }
    .button {
        border-radius: 50%;
        height: 29px;
        width: 29px;
        border: 3px solid #cecece;
    }
    .infoDocSection, .status, .label{
        display: inline-block;
    }
    .status{
        margin-top:5px;
    }
    .label{
        margin-top:-15px;
    }
    .infoBlock {
        border-radius: 10px;
        border: 1px solid #c3c3c3;
        background-color: #ffffff;
        margin: 10px;
    }
    .responsible {
        border-bottom: 1px solid #ccc;
        padding: 20px;
        display: flex;
    }
    .information {
        padding-left: 20px;
    }

    .admin {
        border-radius: 5px;
        background-color: #1cb915;
        border: none;
        color: #ffffff;
        font-size: 11px;
        font-weight: 400;
        line-height: 18px;
        text-transform: uppercase;
        padding: 2px 10px;
        margin-left: 10px;
    }

    .applicant {
        border-radius: 5px;
        background-color: #f1cc3e;
        border: none;
        color: #ffffff;
        font-size: 11px;
        font-weight: 400;
        line-height: 18px;
        text-transform: uppercase;
        padding: 2px 10px;
        margin-left: 10px;
    }



</style>

<div class="yellowGradientSmall" id="bookingHeaderYellow" style="z-index:1; border-bottom-right-radius:0px;">
    <div class="container">
        <nav class="navBar">
            <div class="leftMenu">
                <img src="https://apply.campusafrica.co.za/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91" style="padding:10px;">
            </div>
        </nav>

        <div class="loginHeader">
            <h1 class="bookingSub">
                Application 
                @if(isset($booking))
                    <strong>#{{$booking->booking_code}}</strong>
                @endif

                @if($userData->rob_50_approve == 2) : Approved @endif
            </h1>
            <h1 class="booking_app">{{$userData->userFirstName}} {{$userData->userLastname}}</h1>
        </div>

    </div>
</div>



<div class="greyAppSection  @if($userData->studentType == 'Cash / Direct') greyAppCash @endif">
    <div class="greyAppSection_container">
        <div class="col-3" id="col1">
        <div class="flex">
        @if($userData->address_approved == "1")<div class="button approved" style="width:15px; height:15px; margin-bottom:0px;"></div>@else<div class="button action" style="width:15px; height:15px; margin-bottom:0px;"></div>@endif
            <p class="listHeader" style="margin-top:0px;"> Physical Address</p></div>
            <div class="form" id="inline_edit_address">
                {{$userData->userAddress1}}<br/>
                {{$userData->userAddress2}}<br/>
                {{$userData->userAddressCity}}<br/>
                {{$userData->userAddressPostal}}<br/>

            </div>
        </div>
        <div class="col-3" id="col2">
            
        <div class="flex">
            @if($userData->kin_approved == "1")
                <div class="button approved" style="width:15px; height:15px; margin-bottom:0px;"></div>
            @elseif($userData->kin_approved == "0" && ($userData->kinFirstName == "" || $userData->kinFirstName == null))
                <div class="button rejected" style="width:15px; height:15px; margin-bottom:0px;"></div>
            @else
                <div class="button action" style="width:15px; height:15px; margin-bottom:0px;"></div>
            @endif

            <p class="listHeader" style="margin-top:0px;"> Next of Kin</p></div>
            <div class="form" id="inline_edit_kin">
                {{$userData->kinTitle}} {{$userData->kinFirstName}} {{$userData->kinLastName}}<br/>
                {{$userData->kinRelationship}}<br/>
                {{$userData->kinCellCode}}{{$userData->kinCellNo}}<br/>
                {{$userData->kinEmail}}<br/>
                {{$userData->kinAddressStr}}, {{$userData->kinAddressSuburb}}, <br/>
                {{$userData->kinAddressCity}}, {{$userData->kinAddressPost}}<br/>
            </div>

        </div>
        
        @if($userData->studentType == "Cash / Direct")
            <div class="col-3" id="col3">
                <div class="flex">
                @if($userData->parent_approved == "1")
                    <div class="button approved" style="width:15px; height:15px; margin-bottom:0px;"></div>
                @elseif($userData->parent_approved == "0" && ($userData->sponFirstName == "" || $userData->sponFirstName == null))
                    <div class="button rejected" style="width:15px; height:15px; margin-bottom:0px;"></div>
                @else
                    <div class="button action" style="width:15px; height:15px; margin-bottom:0px;"></div>
                @endif

                    <p class="listHeader" style="margin-top:0px;">Sponsor/Parent </p></div>
                <div class="form">
                    <div class="sponInfoSection">
                        {{$userData->sponFirstName}} {{$userData->sponLastName}}<br/>
                        {{$userData->sponCellCode}} {{$userData->sponCell}}<br/>
                        {{$userData->sponEmail}}<br/>
                        {{$userData->pNationality}}<br/>
                        @if($userData->sponId == "") {{$userData->pPassportNo}} @else {{$userData->sponId}} @endif <br/>
                        {{$userData->pAddress1}}<br/>
                        {{$userData->pAddress2}}<br/>
                        {{$userData->pAddressCity}}<br/>
                        {{$userData->pAddressPostal}}<br/>
                    </div>
                </div>
            </div>
        @endif

        @if($userData->studentType == "Bursary")
            <div class="col-3" id="col3">
                <div class="flex">
                    @if($userData->burser_approved == "1")
                        <div class="button approved" style="width:15px; height:15px; margin-bottom:0px;"></div>
                    @elseif($userData->burser_approved == "0" && ($userData->burCompanyName == "" || $userData->burCompanyName == null))
                        <div class="button rejected" style="width:15px; height:15px; margin-bottom:0px;"></div>
                    @else
                        <div class="button action" style="width:15px; height:15px; margin-bottom:0px;"></div>
                    @endif
                    <p class="listHeader" style="margin-top:0px;">Bursar </p></div>
                <div class="form">
                    <div class="burInfoSection">
                    {{$userData->sponFirstName}} {{$userData->sponLastName}}<br/>
                        {{$userData->burCompanyName}}<br/>
                        {{$userData->burRegNo}}<br/>
                        {{$userData->burContactPerson}} - {{$userData->burContactCode}} {{$userData->burContactNo}}<br/>
                        {{$userData->burEmail}}<br/>
                        {{$userData->bAddress1}}<br/>
                        {{$userData->bAddress2}}<br/>
                        {{$userData->bAddressCity}}<br/>
                        {{$userData->bAddressPostal}}<br/>
                        NSFAS bursar? <b> @if($userData->nsfas_bursar == 1) Yes @else No @endif </b>
                    </div>
                </div>
            </div>
        @endif
       
        <div class="col-3" id="col3">
            <div class="form" id="inline_edit_student_type">
                <p class="listHeader">Student Type</p>
                <span id="studentUserType" class="{{$userData->studentType}}"> @if($userData->studentType == "Cash / Direct") Cash @else {{$userData->studentType}} @endif </span><br/>
            </div>
        </div>
        
        
    </div>
</div>

<div class="customWhiteCard" style="width:300px; margin-top:10px;">
        @if($userData->email_data_approved == "1")
            <div class="button approved" style="width:15px; height:15px; margin-bottom:0px;"></div>
        @else
            <div class="button action" style="width:15px; height:15px; margin-bottom:0px;"></div>
        @endif
    <h2 class="appHeader" style="margin-top:0px;">Applicant Details</h2> 
    <p class="InfoList">
        {{$userData->userFirstName}} {{$userData->userLastname}}<br/>
        {{$userData->userDob}}<br/>
        {{$userData->userNationality}}<br/>
        @if($userData->userId){{$userData->userId}}<br/>@endif
        @if($userData->userPassport){{$userData->userPassport}}<br/>@endif
        <br/>
    
        @foreach($lookups as $lookup) @if($lookup->id == $userData->institutionStudy) {{$lookup->lookup_name}} @endif @endforeach<br/>
        {{$userData->studentNo}}<br/>
        @foreach($lookups as $lookup) @if($lookup->id == $userData->faculty) {{$lookup->lookup_name}} @endif @endforeach<br/>
        {{$userData->yearOfStudy}}<br/>
        <br/>

        <a href="mailto:{{$userData->userEmail}}?subject=Campus Africa Ref {{$booking->booking_code}}">{{$userData->userEmail}}</a> <br/>
        <a href="https://wa.me/<?php echo substr($userData->userCellCode, 1).$userData->userCell;  ?>?text=Campus%20Africa%20Ref%20{{$booking->booking_code}}%0A%0A%0A%0A" target="_blank">{{$userData->userCellCode}}{{$userData->userCell}}</a><br/>
    </p>
</div>

<div class="customWhiteCard" style="width:350px; margin-left:350px; margin-top:-400px;">
    <table style="width:100%; text-align:left">
        <tr>
            <th>
                <h2 class="appHeader bold" style="text-transform:uppercase;"> @if($userData->studentType == "Cash / Direct") Cash @else {{$userData->studentType}}  @endif </h2>
            </th>
            <th >Upfront</th>
            <th>Monthly</th>
            <th>Total</th>
        </tr>
        <tr>
            <td>Booking Fee</td>
            <td class="flex">
                <div id="booking_info">
                    @if($paymentBooking->booking_fee_waived == '0')
                        @if($paymentBooking->booking_fee != '0') R {{$paymentBooking->booking_fee}} @else - @endif 
                    @else
                        @if($paymentBooking->rob_b_waived == '2') <div class="button approved" style="width:15px; height:15px; margin-bottom:0px;"></div> @else <div class="button action" style="width:15px; height:15px; margin-bottom:0px;"></div> @endif
                        <span style="color:red; padding-right:5px;">Waived</span>
                    @endif
                </div>
                
                
            </td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Loan Admin Fee</td>
            <td class="flex">
                <div id="loan_info">
                    @if($paymentBooking->loan_admin_fee_waived == '0')
                        @if($paymentBooking->loan_admin_fee != '0') R {{$paymentBooking->loan_admin_fee}} @else - @endif
                    @else
                        @if($paymentBooking->rob_loan_waived == '2') <div class="button approved" style="width:15px; height:15px; margin-bottom:0px;"></div> @else <div class="button action" style="width:15px; height:15px; margin-bottom:0px;"></div> @endif
                        <span style="color:red; padding-right:5px;">Waived</span>
                    @endif
                </div>
                
            </td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Deposit</td>
            <td class="flex">
                <div id="deposit_info"> 
                    @if($paymentBooking->deposit_waived == '0')
                        @if($paymentBooking->deposit != '0') R {{$paymentBooking->deposit}} @else - @endif
                    @else
                    @if($paymentBooking->rob_deposit_waived == '2') <div class="button approved" style="width:15px; height:15px; margin-bottom:0px;"></div> @else <div class="button action" style="width:15px; height:15px; margin-bottom:0px;"></div> @endif
                        <span style="color:red; padding-right:5px;">Waived</span>
                    @endif
                </div>
            
                
            </td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Monthly Rental</td>
            <td></td>
            <td id="monthlyRentalAmnt">R {{$paymentBooking->monthly_rental}}</td>
            <td id="monthlyRentalTotalAmnt">R {{$paymentBooking->monthly_rental_total}}</td>
        </tr>
        <tr>
            <td>Funded</td>
            <td></td>
            <td id="mthlyfunded">@if($paymentBooking->funded_monthly != '0') R {{$paymentBooking->funded_monthly}} @else - @endif</td>
            <td id="fundedAmnt">@if($paymentBooking->total_funded != '0' && $paymentBooking->total_funded != null) R {{$paymentBooking->total_funded}} @else - @endif</td>
        </tr>
        <tr>
            <td>Top-Up</td>
            <td></td>
            <td></td>
            <td id="topupAmnt">@if($paymentBooking->total_top_up != '0') R {{$paymentBooking->total_top_up}} @else - @endif</td>
        </tr>
    </table>
</div>

<div class="customWhiteCard" id="applicationCard" style="width:300px; margin-top:100px;">
    @if($bookingBed && $bookingBed->bed_code != null)
        <div class="button approved" style="width:15px; height:15px; margin-bottom:0px;"></div>
    @else
        <div class="button action" style="width:15px; height:15px; margin-bottom:0px;"></div>
    @endif

    <h2 class="appHeader" style="margin-top:0px;">Application Summary</h2>

    <p class="InfoList">
        <span class="cardSubHeaderGrey">{{$bookingRes->residence}}</span><br/>
        {{$bookingRoom->name}}<br/>
        @if($bookingBed && $bookingBed->meta_info_1) {{$bookingBed->meta_info_1}} @endif @if($bookingBed && $bookingBed->floor){{$bookingBed->floor}} Floor,@endif @if($bookingBed && $bookingBed->unit)Unit {{$bookingBed->unit}}@endif<br/>
        @if($bookingBed && $bookingBed->bed_code)Bed Code: {{$bookingBed->bed_code}}<br/>@endif
        Lease From: {{$booking->lease_from_date}} at {{$booking->lease_from_time}}<br/>
        Lease From: {{$booking->lease_to_date}} at {{$booking->lease_to_time}}<br/>

    </p>
</div>



<div class="customWhiteCard" id="documentsCard" style="margin-top:40px;">
    <div class="bigHeader appSearchForm">
        <h2 class="appHeader">Documents</h2>
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

        @foreach($data as $file)

        @if(($file["field"] !== "moveIn") || ($file["field"] == "moveIn" && $userData->rob_50_approve == '2'))


                <div class="section">
                    <div class="infoDocSection">
                    
                        <div class="status" id="{{$file['field']}}Status">

                            @if(explode("_", $file["field"])[0] == "other" && $file["field"] == $file["field"])
                                @if($file["status"] == "XApproved" || $file["status"] == "Approved")
                                    <div class="button approved"></div>
                                @elseif($file["status"] == "AUploaded")
                                    <div class="button action"></div>
                                @else
                                    <div class="button rejected"></div>
                                @endif
                            @endif

                            

                            @if($file["field"] === "proof_of_payment")
                                @if ($userData->rob_10_fee == 0)
                                    <div class="button rejected"></div>
                                @elseif ($userData->rob_10_fee == 1)
                                    <div class="button action"></div>
                                @elseif ($userData->rob_10_fee == 2)
                                    <div class="button approved"></div>
                                @else
                                    <div class="button na"></div>
                                @endif
                            @endif

                            @if($file["field"] === "copyID")
                                @if ($userData->rob_15_id == 0)
                                    <div class="button rejected"></div>
                                @elseif ($userData->rob_15_id == 1)
                                    <div class="button action"></div>
                                @elseif ($userData->rob_15_id == 2)
                                    <div class="button approved"></div>
                                @else
                                    <div class="button na"></div>
                                @endif
                            @endif


                            @if($file["field"] === "studentCard")
                                @if ($userData->rob_20_card == 0)
                                    <div class="button rejected"></div>
                                @elseif ($userData->rob_20_card == 1)
                                    <div class="button action"></div>
                                @elseif ($userData->rob_20_card == 2)
                                    <div class="button approved"></div>
                                @else
                                    <div class="button na"></div>
                                @endif
                            @endif

                            @if($file["field"] === "proofRegistration")
                                @if ($userData->rob_25_reg == 0)
                                    <div class="button rejected"></div>
                                @elseif ($userData->rob_25_reg == 1)
                                    <div class="button action"></div>
                                @elseif ($userData->rob_25_reg == 2)
                                    <div class="button approved"></div>
                                @else
                                    <div class="button na"></div>
                                @endif
                            @endif

                            @if($file["field"] === "bursaryApproval" || $file["field"] === "bankStatement")
                                @if ($userData->rob_30_funds == 0)
                                    <div class="button rejected"></div>
                                @elseif ($userData->rob_30_funds == 1)
                                    <div class="button action"></div>
                                @elseif ($userData->rob_30_funds == 2)
                                    <div class="button approved"></div>
                                @else
                                    <div class="button na"></div>
                                @endif
                            @endif

                            @if($file["field"] === "moveIn")
                                @if ($userData->rob_55_lease == 0)
                                    <div class="button rejected"></div>
                                @elseif ($userData->rob_55_lease == 1)
                                    <div class="button action"></div>
                                @elseif ($userData->rob_55_lease == 2)
                                    <div class="button approved"></div>
                                @else
                                    <div class="button na"></div>
                                @endif
                            @endif
                            
                        </div>

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
                                @if($userData->studentType == "NSFAS")
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
                        
                    </div>
                    <hr/>
                </div>

                <?php $i++; ?>


            @endif
        @endforeach

    </div>


</div>

<div class="customWhiteCard" id="Comments" style="margin-top:20px; margin-bottom:20px;">
    <h2 class="appHeader">Comments</h2>

    @if(count($comments) === 0)
        <p style="text-align:center">There are no comments!</p>
    @else
        @foreach($comments as $comment)
            <div class="infoBlock">
                <div class="responsible" style="width:100%">
                    {{substr($comment->created_at, 8, 2)}}/{{substr($comment->created_at, 5, 2)}}/{{substr($comment->created_at, 0, 4)}} {{substr($comment->created_at, 11, 5)}}
                    | {{$comment->users->email}} | {{$comment->users->user_role_name}}</p>
                </div>
                <div class="information">
                    <p>{{$comment->comment}}</p>
                </div>
            </div>
        @endforeach
    @endif
        
</div>

<div class="customWhiteCard" id="actionListCard" style="margin-top:20px; margin-bottom:20px;">
    <div class="">
        <h2 class="appHeader">Previous Bookings</h2>
    </div>

    <div class="actionItem">

        @foreach($previousBookings as $pBooking)

            <div class="infoBlock">
                <div class="responsible">
                    @if(!empty($pBooking->year))
                        <p>{{$pBooking->year}}</p>
                    @endif
                    
                </div>
                <div class="information">
                    <p><b>Application Reference:</b> {{$pBooking->booking_code}}</p>
                </div>
            </div>

        @endforeach
    </div>
</div>

<div class="customWhiteCard" id="actionListCard" style="margin-top:20px;">
    <div class="">
        <h2 class="appHeader">Action List
        </h2>
    </div>

    <div class="actionItemList">

        @foreach($actions as $action)

            <div class="infoBlock">
                <div class="responsible flex">
                                                    
                    @if($action->actionUserRole !== 0 )

                        @if(!empty($action->users))
                            <p class="flex">{{$action->users->userFirstName}} {{$action->users->userLastname}}</p>
                        @else
                            <p>{{$action->actionUserDisplayName}}</p>
                        @endif

                        @if(!empty($action->users))
                            @if($action->users->user_role_name == "" || $action->users->user_role_name == null)
                                <button type="button" class="admin flex">Admin</button>
                            @else
                            <button type="button" class="admin flex">{{$action->users->user_role_name}}</button>
                            @endif
                        @else
                            <button type="button" class="admin flex">Admin</button>
                        @endif
                        
                    @else

                        @if(!empty($action->usersInfo))
                            <p class="flex">{{$action->usersInfo->userFirstName}} {{$action->usersInfo->userLastname}}</p>
                        @else
                            <p class="flex">{{$action->actionUserDisplayName}}</p>
                        @endif

                        <button type="button" class="applicant flex">Applicant</button>
                    @endif
                    <p class="datetime flex">  {{substr($action->created_at, 8, 2)}} | {{substr($action->created_at, 5, 2)}} | {{substr($action->created_at, 0, 4)}} | {{substr($action->created_at, 11, 5)}} </p>
                </div>
                <div class="information">
                    <p>{{$action->action}}</p>
                </div>
            </div>

        @endforeach
    </div>
</div>
