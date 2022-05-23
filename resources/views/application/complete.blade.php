@extends('layouts.app')

@section('content')

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="yellowGradientSmall">
    <div class="container">

        <nav class="navBar">
            <div class="leftMenu">
                <a href="/login"><img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91"></a>
            </div>

            <div class="rightMenu">
                
                <a href="https://campusafrica.co.za/contact-us/" target="_blank"><button type="button" class="contactButton">contact us</button></a>
                <!-- <button type="button" class="menuButton">Menu <i class="fa fa-bars"></i></button> -->
            </div>
        </nav>

        <div class="loginHeader">
            <h1 class="booking">Online Application</h1>
        </div>
    
    </div>
</div>

<div class="container">

<br/>

        <h1 style="font-weight:500">Thank you for your application</h1>

        <br/>
        <h3>Your application reference is #{{$booking->id}}</h3>
        <br/>
        <p>You will receive an email from us as soon as your application has been processed, and upon a <br/>successful application, you will be issued a unique username and password for your Campus <br/>Africa profile via email.</p>

        <a href="/dashboard"><button type="button" class="detailsChange" id="submitFormPay" style="text-transform:uppercase; background-color:#e74f26; float:left; margin-left:0px;">Complete</button></a>
    


</div>

<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });



</script>


@endsection
