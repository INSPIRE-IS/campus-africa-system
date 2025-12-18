@extends('layouts.dash')

@section('content')

@if(!Auth::check())
<div class="yellowGradientSmall">
    <div class="container">

        <nav class="navBar">
            <div class="leftMenu">
                <img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91">
            </div>

            <div class="rightMenu">
                
                <a href="https://campusafrica.co.za/contact-us/" target="_blank"><button type="button" class="contactButton" <?php if($agent->isMobile()){ echo 'style="width:120px !important"';} ?>>contact us</button></a>
            </div>
        </nav>

        <div class="loginHeader">
            <h1 class="booking">Sign Your Lease</h1>
        </div>
        
    
    </div>
</div>
@endif

<div class="container">
    <div class="row justify-content-center">
        <form class="form" id="sign_lease" method="post">
            @csrf
            <div class="scrollable">
                <div class="pdfSection" style="margin-top: 0; height:80vh; overflow-y:scroll; border:1px solid #000; margin-top:50px;">
                        {!!$pdf!!}
                </div>
            </div>

            <div style="margin-top:30px; margin-bottom:20px;">
                <label for="signed_at">Lease Accept</label><br/><br/>
                <ul style="list-style-type:none">
                    <li style=" <?php if(!$agent->isMobile()){ echo 'display:inline-flex';} ?> ">
                        <input type="checkbox" id="lease_accept" name="lease_accept" value="1"  style="margin:4px;">
                        <label for="lease_accept" style="width:500px">I have read and accept the Lease</label>
                    </li>
                </ul>
            </div>

            <div>
                <label for="signed_at">Signed At</label><br/>
                <input type="text" name="signed_at" id="signed_at" <?php if($agent->isMobile()){ echo 'style="width:100%"';} ?>>
                <div><small>Place / Location</small></div>
            </div>


            <div class="column full center signature_container">
                <div style="display:flex; width:70%">
                    <div style="width:80%">Please sign here</div>
                    <div style="width:20%; justify-content:right;">
                        <button class="detailsChange" id="reset_signature" style="margin-top:-10px;" type="button">
                            Reset Signature
                        </button>
                    </div>
                </div>
                <div class="signature"></div>

                <input type="hidden" id="signature_string" name="signature_string">
                <input type="hidden" id="signature_given" value="0">
            </div>

            <div class="column full center flex">
                <button type="button" class="detailsChange" id="submitLease" style="margin-left:0px;">Submit Lease</button>
            </div>
        </form>
    </div>
</div>

<script>
    jQuery(document).ready( function($) {
        
        var sigdiv = $(".signature").jSignature({
            'background-color': 'transparent',
            'decor-color': 'transparent',
        });

        $(".signature").on('click',function(e) {
            $('#signature_given').val('1');
            document.getElementById('signed_at').blur();
        });

        $("#reset_signature").on('click',function(e) {
            sigdiv.jSignature("reset");
            $('#signature_given').val('0');
        });

        $("#submitLease").on('click', function(){

            // Get response of type image
            var data = sigdiv.jSignature('getData', 'image');
            // Alter image source
            $('#signature_string').val(data);
            
            if (sigdiv.jSignature('getData', 'native').length != 0 && $("#signed_at").val() !== "" && $("#lease_accept").is(':checked') === true) {
                $(this).html('<span></span>Generating <i class="fas fa-circle-notch fa-spin"></i>');
                $(this).attr('disabled', 'disabled');

                $.ajax({
                    type: "post",
                    url: '/save-signed-temp-lease/{{$userCustomId}}',
                    data: $('#sign_lease').serialize(),

                    success: function(data) {

                        // Swal.fire({
                        //     title: 'Your lease has been uploaded!',
                        //     text: '',
                        //     icon: 'success',
                        //     allowOutsideClick: true,
                        // }).then(()=> {          
                            $(this).html('<span></span>Completed');                  
                            // window.location.replace('/dashboard');

                            if("{{Auth::check()}}" == true){

                                Swal.fire({
                                    title: 'Thank you!',
                                    text: "Your temp lease has been uploaded successfully! Continue to your dashboard?",
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Yes!'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.replace('/dashboard');
                                    }
                                })
                            }else{
                                Swal.fire({
                                    title: 'Thank you!',
                                    text: "Your lease has been uploaded successfully! Login to your dashboard.",
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Login!'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.replace('/login');
                                    }
                                })
                            }
                        // })

                    },

                    error: function(data) {
                        Swal.fire({
                            title: 'Error!',
                            html: data,
                            allowOutsideClick: true,
                        });
                        $("#next").html('<span></span>Agree and Continue<i class="fas fa-chevron-right"></i>');
                        $("#next").prop('disabled', false);
                    }
                });

            } else {
                var reason = "";

                if(sigdiv.jSignature('getData', 'native').length == 0){
                    var reason = "Please sign the lease"
                }

                if($("#signed_at").val() == ""){
                    var reason = "Please fill in the signed at section"
                }

                if($("#lease_accept").is(':checked') === false){
                    var reason = "Please accept the lease"
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'We need more information!',
                    text: reason,
                })
            }
        })

        
    });
</script>
<div class="clearSpaceLoginPage"></div>
@endsection
