@extends('layouts.app')

@section('content')
<div class="yellowGradientSmall">
    <div class="container">

        <nav class="navBar">
            <div class="leftMenu">
                <img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91">
            </div>

            <div class="rightMenu">
                
                <a href="https://campusafrica.co.za/contact-us/" target="_blank"><button type="button" class="contactButton">contact us</button></a>
            </div>
        </nav>

        <div class="loginHeader">
            <h1 class="booking">Verified</h1>
        </div>
        
    
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-6">
            <br/>
            <h2>Application #:{{$booking->booking_code}}</h2>
            <br/>
            <p>Thanks! Your email has been successfully verified! </p>
                
            <br/>
            <p>If you have any queries regarding your application, please contact the Building Manager <br/>directly or email <a href="mailto:info@campusafrica.co.za">info@campusafrica.co.za</a> quoting your Application <b>#{{$booking->booking_code}}</b> as reference</p>
        </div>
    </div>
</div>
<div class="clearSpaceLoginPage"></div>
@endsection
