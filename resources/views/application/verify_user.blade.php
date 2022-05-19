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
            <h1 class="booking">Online Application</h1>
        </div>
    
    </div>
</div>

<div class="container form-card">
    <div class="onlineTabs">
        1.Applicant Details<br/>
        2.Sponsor/Burser Information<br/>
        3.Next of Kin<br/>
        4.Upload Documents<br/>
        5.Pay Booking Fee<br/>
    </div>
    
    <form id="newStudent" class="form onlineForm" autocomplete="off">
        <br/>
        <h1 class="formHeading">Verify your account</h1>
        <br/>

        <div class="form-field">
            <label>Security Code * <span id="security_code_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="text" name="security_code" id="security_code" class="required">
        </div>


        <button type="button" class="detailsChange" id="submitForm" style="text-transform:uppercase; background-color:#e74f26">Proceed to next step</button>


    </form>

</div>

<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#submitForm").on('click', function() {

        //Check if any hidden fields are required, remove required check

        var req = 1;


        $('.required').each( function(){
            if($(this).val() === "" || $(this).val() === null){
                req = 0

                $(this).addClass("fieldError")
                id = $(this)[0]["id"]
                $("#"+id+"_error").removeClass("hidden")
            }
        })

        if ($('.fieldDuplicate').length > 0) {
            req = 0;
        }

        if ($('.fieldError').length > 0) {
            req = 0;
        }

        if(req == 0){
            Swal.fire(
                'Blank Fields',
                'Please fill in all Required Fields!',
                'error'
            )

        }else{
            let total = 1
            $('.fieldError').each( function(){
                if($(this).val() === ""){
                    total = 0

                    $(this).addClass("fieldError")
                    id = $(this)[0]["id"]
                    $("#"+id+"_error").removeClass("hidden")
                }
            })

            if(total == 0){
                Swal.fire(
                    'Blank Fields',
                    'Please fill in all Required Fields!',
                    'error'
                )

            }else{

                $("#submitForm").empty().append("LOading")

                $.ajax({
                        type: "post",
                        url: "/veryfiy-user",
                        data: {
                            applicationToken: "{{$applicationToken}}", 
                            security_code: $("#security_code").val(), 
                        },

                        success: function (data) {
                            window.location.replace(data[1])
                            
                        },
                    });
            }
        }

    });

</script>


@endsection
