<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login | Campus Africa</title>
    <link rel="icon" href="/images/logo/Campus_Africa_Logo.png">

    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
    <!-- <script src="{{ asset('/js/multistep.js') }}" defer></script> -->

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/all.css') }}" rel="stylesheet">
    <link href="../../css/styles.css?v=<?php echo rand();?>" rel="stylesheet">
    <!-- <link href="{{ asset('css/styles.css') }}" rel="stylesheet"> -->
    <link href="{{ asset('css/multistep.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://use.typekit.net/yos0sjx.css">

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
    
</head>
<body>
    @if(Auth::check())
        <div id="fullNavbar">
            <div class="topnav" id="myTopnav" style='margin-left:50%'>

                @if (!$agent->isMobile())
                    <a href="#"><span class="name">{{Auth::user()->userFirstName}} {{Auth::user()->userLastname}}</span></a>
                @endif
                
                <a href="/logout">Log out</a>
                <a href="javascript:void(0);" class="icon" onclick="myFunction()">
                    <i class="fa fa-bars"></i>
                </a>
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
                <a href="https://www.facebook.com/CampusAfricaLife" target="_blank"><img src="/images/footer/facebook.png" alt="Facebook" width="43" height="43"></a>
                <a href="https://www.instagram.com/campus.africa/" target="_blank"><img src="/images/footer/instagram.png" alt="Instagram" width="43" height="43"></a>
                <a href="https://www.youtube.com/channel/UCo4MDLF6rXA2_d3jxwxzTmg" target="_blank"><img src="/images/footer/youtube.png" alt="Youtube" width="43" height="43"></a>
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

    <!-- @if (App::environment('development'))
        <script>
            (function (d, t, g) {
                var ph    = d.createElement(t), s = d.getElementsByTagName(t)[0];
                ph.type   = 'text/javascript';
                ph.async   = true;
                ph.charset = 'UTF-8';
                ph.src     = g + '&v=' + (new Date()).getTime();
                s.parentNode.insertBefore(ph, s);
            })(document, 'script', '//projects.whitehart.co.za/?p=11203&ph_apikey=1cc52d1e7e06bd85d6efc4a38794058a');
        </script>
    @endif -->
</body>
</html>
