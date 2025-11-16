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

        <h1 style="font-weight:500">Thank you for your application!</h1>

        <br/>
        <h3>Your application reference is #{{isset($booking) && $booking->booking_code ?? ''}}</h3>
        <br/>
        <p>We will keep you updated by email on the progress of your application & any documents
	        you have uploaded.
        </p>
        <br/>
        <p>
            <b><u>Pro-tip!</u></b> the sooner all your documents are uploaded, the sooner we can give you an
            answer & the better your chances of securing the room you want 💪
        </p>

        <br/>
        <p>In the meantime, you can login to your account anytime to view the status of your
	        application & upload any outstanding documents using the email you signed up with.
        </p>
        <br/>

        <a href="/dashboard"><button type="button" class="detailsChange" id="submitFormPay" style="text-transform:uppercase; background-color:#e74f26; float:left; margin-left:0px;">Continue to Dashboard</button></a>
    


</div>

<br/><br/><br/>

<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });



</script>


@endsection
