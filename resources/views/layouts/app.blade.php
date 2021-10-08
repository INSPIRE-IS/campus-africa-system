<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
    <!-- <script src="{{ asset('/js/multistep.js') }}" defer></script> -->

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/all.css') }}" rel="stylesheet">
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/multistep.css') }}" rel="stylesheet">
    
</head>
<body>
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
            <img src="/images/footer/facebook.png" alt="Facebook" width="43" height="43">
            <img src="/images/footer/instagram.png" alt="Instagram" width="43" height="43">
            <img src="/images/footer/youtube.png" alt="Youtube" width="43" height="43">
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
