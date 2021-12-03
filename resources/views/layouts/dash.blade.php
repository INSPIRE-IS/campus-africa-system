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
                <a href="javascript:void(0)">Residences</a>
                <a href="/room-booking">My Room</a>
                <a href="/my-documents">My Documents</a>
                <a href="/my-profile">My Profile</a>

                @if (!$agent->isMobile())
                    <a href="/my-profile"><span class="name">{{Auth::user()->userFirstName}} {{Auth::user()->userLastname}}</span></a>
                @endif
                
                <a href="/logout">Log out</a>
                <a href="javascript:void(0);" class="icon" onclick="myFunction()">
                    <i class="fa fa-bars"></i>
                </a>
            </div>
        </div>

        

        <div class="yellowGradientSmall" style="z-index:1;">
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
                    @if($headerTitle == "Congratulations!")
                        <h1 class="booking" style="font-size:41px">{{$headerTitle}}</h1>
                    @else
                        <h1 class="booking">{{$headerTitle}}</h1>
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
            <a href="https://www.instagram.com/campus.africa/?hl=en" target="_blank"><img src="/images/footer/instagram.png" alt="Instagram" width="43" height="43"></a>
            <a href="https://www.youtube.com/channel/UCo4MDLF6rXA2_d3jxwxzTmg" target="_blank"</a><img src="/images/footer/youtube.png" alt="Youtube" width="43" height="43"></a>
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

<script>
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
</html>
