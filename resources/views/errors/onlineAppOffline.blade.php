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
            <h1 class="booking">Unavailable</h1>
        </div>
    
    </div>
</div>

<div class="container">
    
    <form id="appOops" class="form onlineForm" autocomplete="off">
        <br/>
        <h1 class="formHeading">You are currently unavailable to Apply Online.</h1>
        <br/>
        <p>Please get in touch with us by emailing <a href="mailto:hello@campusafrica.co.za">hello@campusafrica.co.za</a> <br/>or contact us via our website by clicking below.</p>
        <a href="https://campusafrica.co.za/contact-us/"><button type="button" class="detailsChange" style="text-transform:uppercase; background-color:#e74f26; float:none; margin-left:0px;">Contact Us</button></a>
        <br/>
    </form>

</div>




@endsection
