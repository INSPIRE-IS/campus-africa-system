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
            <h1 class="booking">Oops...</h1>
        </div>
    
    </div>
</div>

<div class="container">
    
    <form id="appOops" class="form onlineForm" autocomplete="off">
        <br/>
        <h1 class="formHeading">This link has expired or is invalid.</h1>
        <h1 class="formHeading">You can request a new Passwordless Login below.</h1>
        <a href="/login"><button type="button" class="detailsChange" style="text-transform:uppercase; background-color:#e74f26; float:none; margin-left:0px;">Request new Link</button></a>
        <br/>
    </form>

</div>




@endsection
