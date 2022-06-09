@extends('layouts.dash')

@section('content')

<div class="container bookingPage">
    <br/>
    <h2 class="bookingHeader">Booking Summary</h2>
    <h2 class="bookingHeader">Booking Reference #0000000</h2>
    <br/>
    <div class="buttonSection">
            <button type="button" class="download">DOWNLOAD INVOICE</button>

        @if($lease === null)
            <a href="/sign-lease"><button type="button" class="sign">SIGN LEASE</button></a>
        @endif
    </div>

    @if($proof_of_payment !== null)
        <p class="bookingInfo">Thank you for your payment. A representative will be in touch with more details with regards to your booking.
            <br/>Please email hello@campusafrica.co.za or call 101 109 1700 if you have any queries.
        </p>
    @endif

    <div class="cards">
        <div class="whiteCard room">
        
            <p class="cardList">
                {{Auth::user()->userFirstName}} {{Auth::user()->userLastname}}<br/>
                {{Auth::user()->userDob}}<br/>
                {{Auth::user()->userCell}}<br/>
                {{Auth::user()->userEmail}}<br/>
                {{Auth::user()->userNationality}}<br/>
                @if(Auth::user()->userId){{Auth::user()->userId}}<br/>@endif
                @if(Auth::user()->userPassport){{Auth::user()->userPassport}}<br/>@endif
                @foreach($lookups as $lookup) @if($lookup->id == Auth::user()->institutionStudy) {{$lookup->lookup_name}} @endif @endforeach<br/>
                {{Auth::user()->studentNo}}<br/>
                @foreach($lookups as $lookup) @if($lookup->id == Auth::user()->faculty) {{$lookup->lookup_name}} @endif @endforeach<br/>
                {{Auth::user()->yearOfStudy}}
                
        </div>

        <div class="whiteCard roomImgBack room" style="margin-left:11px;">
            <div class="overlayCustom">
                <h4 class="cardSub">{{$booking->property}}</h4>
                <h2 class="cardHeaderSub" >{{$booking->roomType}}</h2>
                <h4 class="cardSub">
                    @if($booking->floor !== null) 
                    {{$booking->floor}} Floor 
                    @endif 
                    
                    @if($booking->unit !== null) 
                    , Unit {{$booking->unit}}
                    @endif

                    @if($booking->bed_code !== null) 
                    , Bed Code: {{$booking->bed_code}}
                    @endif
                </h4>
            </div>
        </div>
    </div>
</div>

<div class="clearSpaceLogin"></div>
<div class="clearSpaceLoginPage"></div>



@endsection