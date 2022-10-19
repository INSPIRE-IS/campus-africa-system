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
        padding:10px;
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
        padding:10px;
    }
    .booking_app {
        color: #464749;
        font-size: 40px;
        font-weight: 900;
        line-height: 46px;
        text-align: left;
        width: 57%;
        padding:10px;
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
        font-size: 33px;
        font-weight: 400;
        padding-bottom: 10px;
    }
    .cardSubHeaderGrey {
        color: #9799ab;
        font-size: 21px;
        font-weight: 400;
        line-height: 28px;
        text-align: left;
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

                <span class="appStatusText"></span>
            </h1>
            <h1 class="booking_app">{{$userData->userFirstName}} {{$userData->userLastname}}</h1>
        </div>

    </div>
</div>



<div class="greyAppSection  @if($userData->studentType == 'Cash / Direct') greyAppCash @endif">
    <div class="greyAppSection_container">
        <div class="col-3" id="col1">
        <div class="flex">
            <p class="listHeader">Physical Address</p></div>
            <div class="form" id="inline_edit_address">
                {{$userData->userAddress1}}<br/>
                {{$userData->userAddress2}}<br/>
                {{$userData->userAddressCity}}<br/>
                {{$userData->userAddressPostal}}<br/>

            </div>
        </div>
        <div class="col-3" id="col2">
            
        <div class="flex">
            <p class="listHeader">Next of Kin</p></div>
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
                    <p class="listHeader">Sponsor/Parent </p></div>
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
                    <p class="listHeader">Bursar </p></div>
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

<div class="container">
    <div class="appRow">
        <div class="cards">

            <div class="customWhiteCard" style="width:300px; margin-top:-100px;">
                <h2 class="appHeader">Applicant Details</h2>
            
                <p class="InfoList">
                    {{$userData->userFirstName}} {{$userData->userLastname}}<br/>
                    {{$userData->userDob}}<br/>
                    {{$userData->userNationality}}<br/>
                    @if($userData->userId){{$userData->userId}}<br/>@endif
                    @if($userData->userPassport){{$userData->userPassport}}<br/>@endif
                
                    @foreach($lookups as $lookup) @if($lookup->id == $userData->institutionStudy) {{$lookup->lookup_name}} @endif @endforeach<br/>
                    {{$userData->studentNo}}<br/>
                    @foreach($lookups as $lookup) @if($lookup->id == $userData->faculty) {{$lookup->lookup_name}} @endif @endforeach<br/>
                    {{$userData->yearOfStudy}}<br/>

                    <a href="mailto:{{$userData->userEmail}}?subject=Campus Africa Ref {{$booking->booking_code}}">{{$userData->userEmail}}</a> <br/>
                    <a href="https://wa.me/<?php echo substr($userData->userCellCode, 1).$userData->userCell;  ?>?text=Campus%20Africa%20Ref%20{{$booking->booking_code}}%0A%0A%0A%0A" target="_blank">{{$userData->userCellCode}}{{$userData->userCell}}</a><br/>
                </p>
                
            </div>

            <div class="customWhiteCard" id="applicationCard" style="width:250px; margin-top:400px;">
                <div class="flex">                    
                    <h2 class="appHeader">Application Summary</h2>
                </div>

                <div class="app_summary_details">
                    <h4 class="cardSubHeaderGrey">{{$bookingRes->residence}}</h4>
                    <h4 class="cardHeader" >{{$bookingRoom->name}}</h4>
                    <h4 class="cardHeader" id="prop_room_row1" >{{$bookingBed->meta_info_1}} {{$bookingBed->floor}} Floor, Unit {{$bookingBed->unit}}</h4>
                    <h4 class="cardHeader" id="prop_room_row2" >Bed Code: {{$bookingBed->bed_code}}</h4>
                    <h4 class="cardHeader">Lease From: {{$booking->lease_from_date}} at {{$booking->lease_from_time}}</h4>
                    <h4 class="cardHeader">Lease From: {{$booking->lease_to_date}} at {{$booking->lease_to_time}}</h4>
                </div>
                
            </div>

        </div>
    </div>

    <div class="appRow">
    <div class="customWhiteCard" id="applicationCard" style="width:250px; margin-top:400px; margin-left:300px;">
                <div class="flex">                    
                    <h2 class="appHeader">Application Summary</h2>
                </div>

                <div class="app_summary_details">
                    <h4 class="cardSubHeaderGrey">{{$bookingRes->residence}}</h4>
                    <h4 class="cardHeader" >{{$bookingRoom->name}}</h4>
                    <h4 class="cardHeader" id="prop_room_row1" >{{$bookingBed->meta_info_1}} {{$bookingBed->floor}} Floor, Unit {{$bookingBed->unit}}</h4>
                    <h4 class="cardHeader" id="prop_room_row2" >Bed Code: {{$bookingBed->bed_code}}</h4>
                    <h4 class="cardHeader">Lease From: {{$booking->lease_from_date}} at {{$booking->lease_from_time}}</h4>
                    <h4 class="cardHeader">Lease From: {{$booking->lease_to_date}} at {{$booking->lease_to_time}}</h4>
                </div>
                
            </div>
    </div>  
</div>