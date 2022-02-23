<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title><?php echo $title;?> | Campus Africa</title>
    <link rel="icon" href="/images/logo/Campus_Africa_Logo.png">

    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js" type="text/javascript"></script>
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

    
    <script type="text/javascript">let page = '<?php echo Session::get('page'); ?>';</script>
    <script src="../../js/applications.js?v=<?php echo rand();?>"></script>

    <link rel="stylesheet" type="text/css" href="/css/dropzone.css">
    <script src="/js/dropzone.js"></script>




    
</head>
<body>

    

        <div id="fullNavbar">
            <div class="topnav" id="myTopnav">
            <a class="active" href="/dashboard">Dashboard</a>
                <a href="/applications">Applications</a>
                <a href="/leases">Leases</a>
                <a href="/invoices">Invoices</a>
                <a href="/mailing">Mailing</a>
                <a href="/lookups">Lookups</a>
                <a href="/reports">Reports</a>

                @if(Auth::user()->role_manage_users == "1")
                    <a href="/users">Users</a>
                @endif
                
                <a href="/logout">Log out</a>
                <a href="javascript:void(0);" class="icon" onclick="myFunction()">
                    <i class="fa fa-bars"></i>
                </a>
            </div>
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
                        @if($user[0]['userInfo']['applicationStatus'] == "Approved Confirmed")
                            : Approved
                        @elseif($user[0]['userInfo']['applicationStatus'] == "Rejected Confirmed")
                            : Rejected
                        @endif
                    </h1>
                @endif
                <h1 class="booking_app">{{$headerTitle}}</h1>

                <div class="buttonSidePanel" style="margin-top:-50px;">

                    @if (isset($appProfile) && $appProfile === "true")
                        @if($user[0]['userInfo']['applicationStatus'] !== "Approved Confirmed")
                            <div id="MainAppSection" class="hidden">
                                @if(Auth::user()->role_approve_docs == 1)
                                    <button type="button" class="appBtn" id="approveProfile" onClick="approveApplication('<?php echo $user[0]['userInfo']['id']; ?>')">APPROVE APPLICATION AND SEND CONFIRMATION</button>
                                    <br/><br/><br/>
                                    <button type="button" class="appBtn" id="rejectApplication" onClick="rejectApplication('<?php echo $user[0]['userInfo']['id']; ?>')">REJECT APPLICATION AND SEND NOTIFICATION</button>        
                                    <br/><br/><br/>   
                                @endif  
                            </div>
                        @endif

                        @if(Auth::user()->role_edit_app == 1 && $user[0]['userInfo']['overall_status'] != "Approved")
                            <a href="/student/edit/<?php echo $user[0]['userInfo']['id']; ?>"><button type="button" class="appBtn" style="background-color:#e74f26; border:none">EDIT PROFILE</button></a>
                        @endif
                    @endif

                    
                </div>

            </div>
        
        </div>
    </div>

        

    <div id="app">
        <!--  -->

        <main class="py-4">
            @yield('content')
        </main>
    </div>
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


    @if (isset($user) && isset($user[0]))
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function approveApplication(userId){

            if($("#address_approved").val() !== "2" || $("#kin_approved").val() !== "2"){

                if($("#address_approved").val() !== "2"){
                    addressApp()
                }else if($("#kin_approved").val() !== "2"){
                    kinApp();
                }
                        
            }else{

                Swal.fire({
                    icon: 'warning',
                    title: 'Approve this application',
                    text: "You are about to approve this application and send approval email. Continue?",
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, approve'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#approveProfile").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

                        $.ajax({
                            type: "post",
                            url: "/approve-application",
                            data: {
                                userId: "{{$user[0]['userInfo']['id']}}", 
                            },

                            success: function (data) {
                                // window.location.reload();
                                $.ajax({
                                    type: "post",
                                    url: "/createLease/{{$user[0]['userInfo']['id']}}",
                                    data: {
                                        bookingId: "{{$user[0]['userInfo']['id']}}", 
                                        property: $("#property").val(),
                                        roomType: $("#roomType").val(), 
                                        floor: $("#floor").val(),
                                        unit: $("#unit").val(),
                                        room: $("#room").val(),
                                        bed: $("#bed").val(),
                                        bed_code: $("#bed_code").val(),
                                        lease_from_date: $("#lease_from_date").val(),
                                        lease_from_time: $("#lease_from_time").val(),
                                        lease_to_date: $("#lease_to_date").val(),
                                        lease_to_time: $("#lease_to_time").val(),
                                        type: "auto",
                                    },

                                    success: function (data) {
                                        
                                        window.location.reload();
                                    },
                                });
                            },
                        });
                    }
                })
            }

            
        }

        function addressApp(){
            Swal.fire({
                title: 'Please Approve or Reject this data first.',
                html: '<span style="font-weight:bold">Physical Address:</span><br/>{{$user[0]["userInfo"]["userAddress1"]}}<br/>{{$user[0]["userInfo"]["userAddress2"]}}<br/>{{$user[0]["userInfo"]["userAddressCity"]}}<br/>{{$user[0]["userInfo"]["userAddressPostal"]}}<br/>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1cb915',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Approve',
                cancelButtonText: 'Reject'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#approvedAddr").removeClass('hidden');
                    $("#rejectedAddr").addClass('hidden');
                    $("#address_approved").val('2');
                    $.ajax({
                        type: "post",
                        url: "/approve-app-data",
                        data: {
                            type: "Address", 
                            userId: "{{$user[0]['userInfo']['id']}}",
                        },
                    });

                    approveApplication();
                }
            })
        }

        function kinApp(){
            Swal.fire({
                title: 'Please Approve or Reject this data first.',
                html: '<span style="font-weight:bold">Next of Kin:</span><br/>'+
                '{{$user[0]["userInfo"]["kinTitle"]}} {{$user[0]['userInfo']['kinFirstName']}} {{$user[0]['userInfo']['kinLastName']}}<br/>'+
                '{{$user[0]['userInfo']['kinRelationship']}}<br/>'+
                '{{$user[0]['userInfo']['kinNationality']}}<br/>'+
                '<?php if($user[0]['userInfo']['kinId']){echo $user[0]['userInfo']['kinId'];}?><br/>'+
                '<?php if($user[0]['userInfo']['kinPassportNo']){ echo $user[0]['userInfo']['kinPassportNo'];}?><br/>'+
                '{{$user[0]['userInfo']['kinCellNo']}}<br/>{{$user[0]['userInfo']['kinEmail']}}<br/>'+
                '{{$user[0]['userInfo']['kinAddressStr']}}, {{$user[0]['userInfo']['kinAddressSuburb']}}, <br/>'+
                '{{$user[0]['userInfo']['kinAddressCity']}}, {{$user[0]['userInfo']['kinAddressPost']}}<br/>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1cb915',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Approve',
                cancelButtonText: 'Reject'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#approvedKin").removeClass('hidden');
                    $("#rejectedKin").addClass('hidden');
                    $("#kin_approved").val('2');
                    $.ajax({
                        type: "post",
                        url: "/approve-app-data",
                        data: {
                            type: "Kin", 
                            userId: "{{$user[0]['userInfo']['id']}}",
                        },
                    });

                    approveApplication();
                }
            })
        }

        function rejectApplication(userId){
            Swal.fire({
                icon: 'warning',
                title: 'Reject this application',
                text: "You are about to reject this application and send rejection email. Continue?",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, reject'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#approveProfile").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

                    $.ajax({
                        type: "post",
                        url: "/reject-application",
                        data: {
                            userId: userId, 
                        },

                        success: function (data) {
                            window.location.reload();
                        },
                    });
                }
            })
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
    @endif

    @if (App::environment('development'))
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
    @endif
</body>

</html>


