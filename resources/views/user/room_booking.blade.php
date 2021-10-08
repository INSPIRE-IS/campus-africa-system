@extends('layouts.dash')

@section('content')

<div class="container bookingPage">
    <br/>
    <h2 class="bookingHeader">Booking Summary</h2>
    <h2 class="bookingHeader">Booking Reference #0000000</h2>
    <br/>
    <div class="buttonSection">
        <button type="button" class="download">DOWNLOAD INVOICE</button>
        <a href="/sign-lease"><button type="button" class="sign">SIGN LEASE</button></a>
    </div>
    <p class="bookingInfo">Thank you for your payment. A representative will be in touch with more details with regards to your booking.
        <br/>Please email … or call … if you have any queries.
    </p>

    <div class="cards">
        <div class="whiteCard">
        
            <p class="cardList">
                {{Auth::user()->userFirstName}} {{Auth::user()->userLastname}}<br/>
                {{Auth::user()->userDob}}<br/>
                {{Auth::user()->userCell}}<br/>
                {{Auth::user()->userEmail}}<br/>
                {{Auth::user()->userId}}<br/>
                {{Auth::user()->userNationality}}<br/>
                {{Auth::user()->userPassport}}<br/>
                {{Auth::user()->institutionStudy}}<br/>
                {{Auth::user()->studentNo}}<br/>
                {{Auth::user()->institutionCampus}}<br/>
                {{Auth::user()->faculty}}<br/>
                {{Auth::user()->year}}</p>
                
        </div>

        <div class="whiteCard roomImgBack" style="margin-left:11px;">
            <div class="overlayCustom">
                <h4 class="cardSub">Braamlofts</h4>
                <h2 class="cardHeaderSub" >Single Room</h2>
                <h4 class="cardSub">Room 23 Floor 2</h4>
            </div>
        </div>
    </div>
</div>

<div class="clearSpaceLogin"></div>
<div class="clearSpaceLoginPage"></div>



@endsection