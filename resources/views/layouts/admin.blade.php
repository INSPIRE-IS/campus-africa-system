<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
    <!-- <script src="{{ asset('/js/multistep.js') }}" defer></script> -->

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/all.css') }}" rel="stylesheet">
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/multistep.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://use.typekit.net/yos0sjx.css">



    
</head>
<body>

    

        <div id="fullNavbar">
            <div class="topnav" id="myTopnav">
            <a class="active" href="/dashboard">Dashboard</a>
                <a href="/applications">Aplications</a>
                <a href="/leases">Leases</a>
                <a href="/invoices">Invoices</a>
                <a href="/mailing">Mailing</a>
                <a href="/logout">Log out</a>
                <a href="javascript:void(0);" class="icon" onclick="myFunction()">
                    <i class="fa fa-bars"></i>
                </a>
            </div>
        </div>

        <!-- <div class="yellowGradientSmall" style="z-index:1;"> -->

        @if (isset($appProfile) && $appProfile === "true")
            <div class="yellowGradientSmall" id="bookingHeaderYellow" style="z-index:1; border-bottom-right-radius:0px;">
        @else
            <div class="yellowGradientSmall" style="z-index:1;">
        @endif

            <div class="container">

                <nav class="navBar">
                    <div class="leftMenu">
                        <img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91">
                    </div>

                    <div class="rightMenu">
                        <!-- <button type="button" class="menuButton">Menu <i class="fa fa-bars"></i></button> -->
                    </div>
                </nav>

                <div class="loginHeader">
                    @if (isset($appProfile) && $appProfile === "true")
                        <h1 class="bookingSub">
                            Application Profile 
                            @if($user[0]['userInfo']['bookingStatus'] == "Approved" || $user[0]['userInfo']['bookingStatus'] == "Rejected")
                                : {{$user[0]['userInfo']['bookingStatus']}}
                            @endif
                        </h1>
                    @endif
                    <h1 class="booking">{{$headerTitle}}</h1>

                    @if (isset($appProfile) && $appProfile === "true")
                        @if($user[0]['userInfo']['bookingStatus'] !== "Approved" && $user[0]['userInfo']['bookingStatus'] !== "Rejected")
                            <button type="button" class="appBtn" id="approveProfile" onClick="approveApplication('<?php echo $user[0]['userInfo']['id']; ?>')">APPROVE APPLICATION AND SEND CONFIRMATION</button>
                            <br/><br/><br/>
                            <button type="button" class="appBtn" id="rejectApplication">REJECT APPLICATION AND SEND NOTIFICATION</button>                          
                        @endif
                    @endif
                </div>
            
            </div>
        </div>

        

    <div id="app">
        <!--  -->

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
<footer class="footer">
    <div class="footerBanner">
        <div class="container">
            <div class="col-3">
                <h2 class="footerHeader">
                    Campus Africa
                </h2>
                <p class="footerSubHeader">
                    If your looking for a place<br/> of learning and fun to grow<br/> and express yourself, then<br/> Campus Africa is your home.
                </p>
            </div>

            <div class="col-3" style="padding-left:6%; width:19%">
                <p class="linkHeader">Home</p>
                <p class="link">Residences<br/>
                Campus Africa Life<br/>
                FAQ<br/>
                What's Hot</p>
            </div>

            <div class="col-3">
            <p class="linkHeader">Contact Us</p>
            <p class="link">info@campusafrica.co.za<br/>
            010 109 1700</p>
            </div>

            <div class="col-3">
            <p class="linkHeader">Follow Us</p>
            <a href="https://www.facebook.com/WeAreCampusAfrica/" target="_blank"><img src="/images/footer/facebook.png" alt="Facebook" width="43" height="43"></a>
            <a href="https://www.instagram.com/campus.africa/" target="_blank"><img src="/images/footer/instagram.png" alt="Instagram" width="43" height="43"></a>
            <a href="https://www.youtube.com/channel/UCo4MDLF6rXA2_d3jxwxzTmg" target="_blank"><img src="/images/footer/youtube.png" alt="Youtube" width="43" height="43"></a>
            <br/>
            <img src="/images/footer/email.png" alt="Email" width="43" height="43">
            <img src="/images/footer/whatsapp.png" alt="Whatsapp" width="43" height="43">
            </div>
        </div>
    </div>
    <div class="footerClearfix"></div>
    <div class="container">
        <br/>
        <p class="footerRights">Campus Africa - All Right Reserved</p>
    </div>
</footer>
</html>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function approveApplication(userId){

        $("#approveProfile").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        $.ajax({
            type: "post",
            url: "/approve-application",
            data: {
                userId: userId, 
            },

            success: function (data) {
                window.location.reload();
            },
        });
    }

    function myFunction() {
    var x = document.getElementById("myTopnav");
    if (x.className === "topnav") {
        x.className += " responsive";
        document.getElementById('fullNavbar').setAttribute("style","height:330px !important");
    } else {
        x.className = "topnav";
        document.getElementById('fullNavbar').setAttribute("style","height:85px !important");
    }
    }

    
    
</script>
