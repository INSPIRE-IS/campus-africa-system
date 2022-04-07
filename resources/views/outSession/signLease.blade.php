@extends('layouts.app')

@section('content')
<div class="yellowGradientSmall">
    <div class="container">

        <nav class="navBar">
            <div class="leftMenu">
                <img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91">
            </div>

            <div class="rightMenu">
                
                <a href="https://campusafrica.co.za/contact-us/" target="_blank"><button type="button" class="contactButton">contact us</button></a>
            </div>
        </nav>

        <div class="loginHeader">
            <h1 class="booking">Sign Your Lease</h1>
        </div>
        
    
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <form class="form" id="sign_lease" method="post">
            @csrf
            <div class="scrollable">
                <h2>Echo lease here</h2>
            </div>


            <div class="column full center signature_container">
                <div>Please sign here</div>
                <div class="signature"></div>

                <input type="hidden" id="signature_string" name="signature_string">
                <input type="hidden" id="signature_given" value="0">
            </div>

            <div class="column full center flex">
                <button class="detailsChange" id="reset_signature" style="margin-left:0px;" type="button">
                    Reset Signature
                </button>
            </div>
        </form>
        <div>
            <br/><br/>
            <button type="button" class="detailsChange" id="submitLease" style="width:100%">Submit Lease</button>
        </div>
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
            
            if (sigdiv.jSignature('getData', 'native').length != 0) {
                $(this).html('<span></span>Generating <i class="fas fa-circle-notch fa-spin"></i>');
                // $(this).attr('disabled', 'disabled');

                $.ajax({
                    type: "post",
                    url: '/save-signed-lease/{{$userCustomId}}',
                    data: $('#sign_lease').serialize(),

                    success: function(data) {
                        // Swal.fire({
                        //     title: 'Information has been saved!',
                        //     text: '',
                        //     icon: 'success',
                        //     allowOutsideClick: true,
                        //     customClass: {
                        //         confirmButton: 'btn gold'
                        //     },                    
                        //     buttonsStyling: false
                        // }).then(()=> {                            
                        //     window.location.reload();
                        // })

                        console.log("SUCESS")
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
                Swal.fire({
                    icon: 'warning',
                    title: 'We need more information!',
                    text: 'Please sign the lease',
                })
            }
        })

        
    });
</script>
<div class="clearSpaceLoginPage"></div>
@endsection
