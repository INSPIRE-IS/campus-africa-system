<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title><?php echo $headerTitle;?> | Campus Africa</title>
    <link rel="icon" href="/images/logo/Campus_Africa_Logo.png">

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
    <script src="../../js/jSignature.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>



    
</head>
<body>

    @if(Auth::check())
        <div id="fullNavbar">
            <div class="topnav" id="myTopnav">
                <a class="active" href="/dashboard">Dashboard</a>
                <a href="https://campusafrica.whitehart.co.za/residences/" target="_blank">Residences</a>
                <a href="/room-booking">My Room</a>
                <a href="/my-documents">My Documents</a>
                <!-- <a href="/my-profile">My Profile</a> -->

                <a href="/logout">  @if (!$agent->isMobile()) <span class="name">Hi {{Auth::user()->userFirstName}} {{Auth::user()->userLastname}},</span> @endif Log out</a>
                
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
    @endif

        

    <div id="app">
        <!--  -->

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    <footer class="footer">
        <div class="footerBanner">
            <div class="container flex">
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
                    <p class="link"><a href="https://campusafrica.whitehart.co.za/residences/" style="color:inherit; text-decoration:none; cursor:pointer">Residences</a><br/>
                    <!-- Campus Africa Life<br/> -->
                    <a href="https://campusafrica.whitehart.co.za/faq/" style="color:inherit; text-decoration:none; cursor:pointer">FAQ</a><br/>
                    <a href="https://campusafrica.whitehart.co.za/announcements/" style="color:inherit; text-decoration:none; cursor:pointer">What's Hot</a></p>
                </div>
    
                <div class="col-3">
                <p class="linkHeader">Contact Us</p>
                <p class="link"><a href="mailto:info@campusafrica.co.za" style="color:inherit; text-decoration:none;">info@campusafrica.co.za</a><br/>
                <a href="tel:0101091700" style="color:inherit; text-decoration:none;">010 109 1700</a></p>
                </div>
    
                <div class="col-3">
                <p class="linkHeader">Follow Us</p>
                <a href="https://www.facebook.com/WeAreCampusAfrica/" target="_blank"><img src="/images/footer/facebook.png" alt="Facebook" width="43" height="43"></a>
                <a href="https://www.instagram.com/campus.africa/?hl=en" target="_blank"><img src="/images/footer/instagram.png" alt="Instagram" width="43" height="43"></a>
                <a href="https://www.youtube.com/channel/UCo4MDLF6rXA2_d3jxwxzTmg" target="_blank"</a><img src="/images/footer/youtube.png" alt="Youtube" width="43" height="43"></a>
                <br/>
                <a href="mailto:info@campusafrica.co.za"><img src="/images/footer/email.png" alt="Email" width="43" height="43"></a>
                <a href="https://wa.me/27719991246" target="_blank"><img src="/images/footer/whatsapp.png" alt="Whatsapp" width="43" height="43"></a>
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
                document.getElementById('fullNavbar').setAttribute("style","height:270px !important");
            } else {
                x.className = "topnav";
                document.getElementById('fullNavbar').setAttribute("style","height:85px !important");
            }
        }

        @if (App::environment('development'))
        (function (d, t, g) {
            var ph    = d.createElement(t), s = d.getElementsByTagName(t)[0];
            ph.type   = 'text/javascript';
            ph.async   = true;
            ph.charset = 'UTF-8';
            ph.src     = g + '&v=' + (new Date()).getTime();
            s.parentNode.insertBefore(ph, s);
        })(document, 'script', '//projects.whitehart.co.za/?p=11203&ph_apikey=1cc52d1e7e06bd85d6efc4a38794058a');
        @endif
    </script>

    </body>
</html>
