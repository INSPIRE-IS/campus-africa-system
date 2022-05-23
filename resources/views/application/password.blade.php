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
        <div class="tab active">1: Applicant Details</div>
        <div class="tab">2: Sponsor/Burser Information</div>
        <div class="tab">3: Next of Kin</div>
        <div class="tab">4: Upload Documents</div>
        <div class="tab">5: Pay Booking Fee</div>
    </div>
    
    <form id="newStudent" class="form onlineForm" autocomplete="off">
        <br/>
        <h1 class="formHeading">Hello {{$user->userFirstName}}</h1>
        <br/>
        <p>Please choose a password</p>
        <br/>

        <div class="form-field">
            <label>Password * <span id="password_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
            <input type="password" name="password" id="password" class="required">
        </div>
        <div class="form-field">
            <label>Confirm Password * <span id="confirm_password_error" class="hidden" style="color:red"><small>This field is required</small></span> <span id="confirm_password_match_error" class="hidden" style="color:red"><small>Passwords don't match'</small></span></label>
            <input type="password" name="confirm_password" id="confirm_password" class="required" onKeyUp="match()">
        </div>

        <button type="button" class="detailsChange" id="submitForm" style="text-transform:uppercase; background-color:#e74f26">Save Password</button>

    </form>

</div>

<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("input").blur(function(){
        let fieldId = $(this)[0]["id"]
        var required = $("#"+fieldId).hasClass('required')

        if($("#"+fieldId).val() == "" && required == true){
            $("#"+$(this)[0]["id"]).addClass('fieldError')
            $("#"+$(this)[0]["id"]+"_error").removeClass('hidden')
        }else{
            $("#"+$(this)[0]["id"]).removeClass('fieldError')
            $("#"+$(this)[0]["id"]+"_error").addClass('hidden')
        }

    });

    function match(){
        let password = $("#password").val()
        let confirmed = $("#confirm_password").val();

        if(password !== confirmed){
            $("#confirm_password_match_error").removeClass('hidden');
            $("#confirm_password").addClass('fieldError')
        }else{
            $("#confirm_password_match_error").addClass('hidden')
            $("#confirm_password").removeClass('fieldError')
        }
    }

    

    $("#submitForm").on('click', function() {

        $("#submitForm").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');
        
        let password = $("#password").val()
        let confirmed = $("#confirm_password").val();

        if(password !== null && confirmed !== null && $("#password").hasClass('fieldError') == false && $("#confirm_password").hasClass('fieldError') == false){
            $.ajax({
            type: "post",
            url: "/confirm-password",
            data: {
                confirm_password: $("#confirm_password").val(), 
            },

            success: function (data) {
                window.location.replace("/dashboard")
            },
            });
        }else{
            $("#submitForm").empty().append('Save Password');
            Swal.fire(
                'Blank Fields',
                'Please fill in all Required Fields!',
                'error'
            )
        }
    })

    //     //Check if any hidden fields are required, remove required check

    //     var req = 1;


    //     $('.required').each( function(){
    //         if($(this).val() === "" || $(this).val() === null){
    //             req = 0

    //             $(this).addClass("fieldError")
    //             id = $(this)[0]["id"]
    //             $("#"+id+"_error").removeClass("hidden")
    //         }
    //     })

    //     if ($('.fieldDuplicate').length > 0) {
    //         req = 0;
    //     }

    //     if ($('.fieldError').length > 0) {
    //         req = 0;
    //     }

    //     if(req == 0){
    //         Swal.fire(
    //             'Blank Fields',
    //             'Please fill in all Required Fields!',
    //             'error'
    //         )

    //     }else{
    //         let total = 1
    //         $('.fieldError').each( function(){
    //             if($(this).val() === ""){
    //                 total = 0

    //                 $(this).addClass("fieldError")
    //                 id = $(this)[0]["id"]
    //                 $("#"+id+"_error").removeClass("hidden")
    //             }
    //         })

    //         if(total == 0){
    //             Swal.fire(
    //                 'Blank Fields',
    //                 'Please fill in all Required Fields!',
    //                 'error'
    //             )

    //         }else{

    //             $("#submitForm").empty().append("LOading")

    //             $.ajax({
    //                     type: "post",
    //                     url: "/veryfiy-user",
    //                     data: {
    //                         security_code: $("#security_code").val(), 
    //                     },

    //                     success: function (data) {
    //                         window.location.replace(data[1])
                            
    //                     },
    //                 });
    //         }
    //     }

    // });

</script>


@endsection
