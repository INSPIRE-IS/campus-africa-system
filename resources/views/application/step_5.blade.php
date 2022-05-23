@extends('layouts.app_img')

@section('content')

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="ImgGradient">
    <!-- <img src="/images/rooms/Braamlofts_single_1_.png" /> -->
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
            <br/><br/>
            <h1 style="color:#fff; font-weight:400;">{{$booking->property}}</h1>
            <h1 class="booking" style="color:#fff;">{{$booking->roomType}}</h1>
            <br/>
            <div style="display:flex;">

                <img src="/images/rooms/bed_1.png"/> 
                <p style="color:#fff; padding: 17px 10px 0px 10px;"><strong>01</strong></p>

                <img src="/images/rooms/bath_1.png"/> 
                <i class="fas fa-check" style="color:#fff;padding: 17px 10px 0px 10px;"></i>

                <img src="/images/rooms/food_1.png"/> 
                <i class="fas fa-check" style="color:#fff;padding: 17px 10px 0px 10px;"></i>

                <img src="/images/rooms/chair_1.png"/> 
                <i class="fas fa-check" style="color:#fff;padding: 17px 10px 0px 10px;"></i>

            </div>
            <br/><br/>
            <h1 style="color:#9799AB; font-weight:400;">PRICED FROM</h1>
            <h1 class="booking" style="color:#fff;">R 3500 <small>pm</small></h1>
            <h1 style="color:#fff; font-weight:400;">Please note you have 7 days for the deposit to be paid,<br/> otherwise your room reservation will be forfeited.  </h1>
        </div>
    
    </div>
</div>

<div class="container form-card">
    <div class="onlineTabs">
    <div class="onlineTabs">
        <div class="tab complete">1: Applicant Details</div>
        <div class="tab complete">2: Burser Information</div>
        <div class="tab complete">3: Next of Kin</div>
        <div class="tab complete">4: Upload Documents</div>
        <div class="tab active">5: Pay Booking Fee</div>
    </div>
    </div>
    
    <form id="newStudent" class="form onlineForm" autocomplete="off">
        <br/>
        <div class="cards">
            <div class="whiteCard" id="registerPaymentCards">

                <div class="flex flex-rev">
                    <!-- <button type="button" class="editButton" onClick="PrevStep('back2')">EDIT</button> -->
                </div>
            
                <p class="cardList">
                    <span id="dispFirstName">{{$user->userFirstName}}</span> <span id="dispLastName">{{$user->userLastName}}</span><br/>
                    <span id="dispDob">{{$user->userDob}}</span><br/>
                    <span id="dispCell">{{$user->userCell}}</span><br/>
                    <span id="dispEmail">{{$user->userEmail}}</span><br/>
                    <span id="dispNationality">{{$user->userNationality}}</span><br/>
                    <span id="dispPassport">
                        @if($user->userId){{$user->userId}}@endif
                        @if($user->userPassport){{$user->userPassport}}@endif
                    </span><br/>
                    <span id="dispInstit">
                    @foreach($lookups as $lookup) @if($lookup->id == $user->institutionStudy) {{$lookup->lookup_name}} @endif @endforeach<br/>
                    
                    @foreach($lookups as $lookup) @if($lookup->id == $user->faculty) {{$lookup->lookup_name}} @endif @endforeach
                    </span><br/>
                    <span id="dispStudNo">{{$user->studentNo}}</span><br/>
                    <span id="dispFaculty">{{$user->yearOfStudy}}</span><br/>
                    
            </div>

            <div class="whiteCard" id="registerPaymentCards" style="margin-left:11px;">
                <h4 class="cardSubHeaderGrey">{{$booking->property}}</h4>
                <h2 class="cardHeader" >{{$booking->roomType}}</h2>
            </div>
        </div>

        <div class="clearSpaceLogin"></div>

        <div class="paymentSection <?php if($proofPayment && $proofPayment->status == "Uploaded"){echo 'hidden';} ?>" style="margin-top:100px;">
            <br/><br/>

            <div class="form-field">
                <h1 class="header" >Payment Information</h1>
            </div>

            <div class="form-field">
                <label >Payment Type &nbsp;&nbsp;&nbsp;&nbsp;</label>
                <select name="paymentType" >
                    <option value="Credit card">Credit card</option>
                </select>
            </div>

            <div class="form-field">
                <label>Cardholder Name* <small id="cardholderName_error" class="hidden" style="color:red;">This is a required field</small></label>
                <input type="text" id="cardholderName" class="required">

                <label>Card Number* <small id="cardNumber_error" class="hidden" style="color:red;">This is a required field</small></label>
                <input type="text" id="cardNumber" class="required">

                <label>Exp. Date* <small id="expDate_error" class="hidden" style="color:red;">This is a required field</small></label>
                <input type="date" id="expDate" class="required">

                <label>CVV* <small id="cvv_error" class="hidden" style="color:red;">This is a required field</small></label>
                <input type="text" id="cvv" style="width:50%" class="required">
            </div>

            <div class="form-field">
                <div class="cards flex flex-col">
                    <div class="whiteCard halfCard">
                        <h4 class="cardSubHeaderGrey">Non Refundable Booking Fee</h4>
                        <h2 class="cardHeader" >R5000</h2>
                        <h4 class="cardSubHeaderGrey">Your application fee is deducted from the total of the room fee required to make this booking.</h4>
                        
                    </div>
                    <div class="redBottom flex">
                            <i class="fas fa-clock"></i>
                            <p>Please note that your booking will only be reserved for 24hrs without payment</p>
                        </div>
                </div>
            </div>

            <div class="clearSpaceLogin"></div>
            <div class="footerClearfix"></div>

        </div>

        <div class="paymentTwo <?php if(($proofPayment && $proofPayment->status != "Uploaded") || !$proofPayment){echo 'hidden';} ?>" style="margin-top:100px;">
            <h1 class="header">Payment Submitted!</h1>
            <br/>
            <p class="cardSubHeaderGrey">Thank you for your payment. In order to speed up the process, please upload a proof of payment document below or via your online profile as soon as possible. Your room will only be reserved for 24 hours without payment.</p>
            <br/><br/>

            <label for="proof_of_payment" class="PaymentLabel <?php if($proofPayment && $proofPayment->status == "Uploaded"){echo 'greenPayment';}?>"> <?php if($proofPayment && $proofPayment->status != "Uploaded"){echo 'UPLOAD PROOF OF PAYMENT';}else{echo 'UPLOADED';}?></label>
            <input type="file" id="proof_of_payment" hidden/>

            <br/><br/><br/>
            <a href="/complete/online-application"><button type="button" id="homeStep" class="previousStepBtn <?php if(($proofPayment && $proofPayment->status != "Uploaded") || !$proofPayment){echo 'hidden';}?>"> Back to home page</button></a>
            <br/><br/>
            <button type="button" id="previousStep" class="previousStepBtn"> Back to previous step</button>
            <div class="footerClearfix"></div>
        </div>


        <?php if(($proofPayment && $proofPayment->status != "Uploaded") || !$proofPayment){echo '<button type="button" class="detailsChange" id="submitFormPay" style="text-transform:uppercase; background-color:#e74f26">PAY R '.number_format($booking->room_rate,2,"."," ").' BOOKING FEE</button>';} ?>
        

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

    $("#submitFormPay").on('click', function(){
        
        $('.hidden .fieldError').removeClass('fieldError').val('');

        var req = 1;

        if($("#cardholderName").val() === "" || $("#cardholderName").val() === null){
            $("#cardholderName_error").removeClass('hidden')
            $("#cardholderName").addClass('fieldError')
            req = 0;
        }

        if($("#cardNumber").val() === "" || $("#cardNumber").val() === null){
            $("#cardNumber_error").removeClass('hidden')
            $("#cardNumber").addClass('fieldError')
            req = 0;
        }

        if($("#expDate").val() === "" || $("#expDate").val() === null){
            $("#expDate_error").removeClass('hidden')
            $("#expDate").addClass('fieldError')
            req = 0;
        }

        if($("#cvv").val() === "" || $("#cvv").val() === null){
            $("#cvv_error").removeClass('hidden')
            $("#cvv").addClass('fieldError')
            req = 0;
        }

        if($("#cardholderName").val() === "" || $("#cardNumber").hasClass('fieldError') === true || $("#expDate").hasClass('fieldError') === true || $("#cvv").hasClass('fieldError') === true){
            req = 0;
        }

        if($("#cardholderName").hasClass('fieldError') === true || $("#cardNumber").hasClass('fieldError') === true || $("#expDate").hasClass('fieldError') === true || $("#cvv").hasClass('fieldError') === true){
            req = 0;
        }


        if(req == 0){
            Swal.fire(
                'Blank Fields',
                'Please fill in all Required Fields!',
                'error'
            )

        }else{


            $("#submitFormPay").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

            $(".paymentSection").addClass('hidden')
            $("#submitFormPay").addClass('hidden')
            $(".paymentTwo").removeClass('hidden')

            // $.ajax({
            //     type: "post",
            //     url: "/",
            //     data: {
            //         cardholderName: $("#cardholderName").val(), 
            //         cardNumber: $("#cardNumber").val(), 
            //         expDate: $("#expDate").val(), 
            //         cvv: $("#cvv").val(),  
            //     },
            //     success: function (data) {
            //         window.location.replace(data)                            
            //     },
            // });
            
        }

    });


    $("#proof_of_payment").on('change', function(){
        $(".PaymentLabel").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('proof_of_payment').files[0]);
        form_data.append("userUid", "<?php echo $user->id; ?>");
        form_data.append("fileType", "proof_of_payment");

        $.ajax({
            type: "post",
            url: "/application-file-upload",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0] > 0) {
                    $(".PaymentLabel").empty().append('UPLOADED')
                    $("#proofRegistrationSuccess").removeClass('hidden')
                    $(".PaymentLabel").addClass('greenPayment')

                    $("#homeStep").removeClass('hidden')
                }
            },
        });
    })


</script>


@endsection
