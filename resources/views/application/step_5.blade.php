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
            <h1 style="color:#fff; font-weight:400;">{{$propertyTypeInfo->residence}}</h1>
            <h1 class="booking" style="color:#fff;">{{$roomTypeInfo->name}}</h1>
            <br/>
            <div style="display:flex;">

                <img src="/images/rooms/bed_1.png" id="bannerImgIcon" id="bannerImgIcon"/> 
                <p style="color:#fff; padding: 17px 10px 0px 10px;"><strong>0{{$beds}}</strong></p>

                @if($bath == 'true')
                    <img src="/images/rooms/bath_1.png" id="bannerImgIcon"/> 
                    <i class="fas fa-check" style="color:#fff;padding: 17px 10px 0px 10px;"></i>
                @endif

                @if($kitchen == 'true')
                    <img src="/images/rooms/food_1.png" id="bannerImgIcon"/> 
                    <i class="fas fa-check" style="color:#fff;padding: 17px 10px 0px 10px;"></i>
                @endif

                @if($chair == 'true')
                    <img src="/images/rooms/chair_1.png" id="bannerImgIcon"/> 
                    <i class="fas fa-check" style="color:#fff;padding: 17px 10px 0px 10px;"></i>
                @endif

                @if($wifi == 'true')
                    <img src="/images/rooms/wifi_1.png" id="bannerImgIcon"/> 
                    <i class="fas fa-check" style="color:#fff;padding: 17px 10px 0px 10px;"></i>
                @endif

            </div>
            
            <h1 style="color:#9799AB; font-weight:400;">PRICED FROM</h1>
            <h1 class="booking" style="color:#fff;">R {{$priced_from}} <small>pm</small></h1>
            <!-- <h1 id="infoAppOnline" style="color:#fff; font-weight:400;">Please note you have 7 days for the deposit to be paid,<br/> otherwise your room reservation will be forfeited.  </h1> -->
        </div>
    
    </div>
</div>

<div class="container form-card">
    <div class="onlineTabs">
    <div class="onlineTabs">
        <div class="tab complete">1: Applicant Details</div>
        @if(isset($user) && $user->studentType !== "NSFAS") 
            <div class="tab complete">2: @if(isset($user) && $user->studentType == "Bursary") Burser @else Sponsor @endif Information</div>
            <div class="tab complete">3: Next of Kin</div>
            <div class="tab complete">4: Upload Documents</div>
            <div class="tab active">5: Pay Booking Fee</div>
        @else
            <div class="tab complete">2: Next of Kin</div>
            <div class="tab complete">3: Upload Documents</div>
            <div class="tab active">4: Pay Booking Fee</div>
        @endif
    </div>
    </div>
    
    <div id="newStudentPayment" class="form onlineForm @if($room_rate == '0') smallPayment @endif" autocomplete="off">
        <br/>
        <div class="cards">
            <div class="whiteCard paymentAppOnline" id="registerPaymentCards">

                <div class="flex flex-rev">
                    <!-- <button type="button" class="editButton" onClick="PrevStep('back2')">EDIT</button> -->
                </div>
            
                <p class="cardList" id="personalCardItems">
                    <h2 class="appHeader">Your Details</h2>
                    <span id="dispFirstName">{{$user->userFirstName}} {{$user->userLastname}}</span><br/>
                    <span id="dispDob">{{$user->userDob}}</span><br/>
                    <span id="dispNationality">{{$user->userNationality}}</span><br/>
                    <span id="dispPassport">
                        @if($user->userId){{$user->userId}}@endif
                        @if($user->userPassport){{$user->userPassport}}@endif
                    </span><br/>
                    <br/>
                    <hr/>
                    <br/>
                    <span id="dispEmail">{{$user->studentType}}</span><br/>
                    @foreach($lookups as $lookup) @if($lookup->id == $user->institutionStudy) {{$lookup->lookup_name}} @endif @endforeach<br/>
                    <span id="dispStudNo">{{$user->studentNo}}</span><br/>
                    @foreach($lookups as $lookup) @if($lookup->id == $user->faculty) {{$lookup->lookup_name}} @endif @endforeach<br/>
                    <span id="dispYear">{{$user->yearOfStudy}}</span><br/>
                    <br/>
                    <hr/>
                    <br/>
                    <span id="dispEmail">{{$user->userEmail}}</span><br/>
                    <span id="dispCell">{{$user->userCellCode}}{{$user->userCell}}</span><br/>
                </p>
            </div>

            <div class="whiteCard roomCardAppOnline" id="registerPaymentCards" style="margin-left:11px;">
                <div class="flex">
                    <div>
                        <h4 class="cardSubHeaderGrey">{{$propertyTypeInfo->residence}}</h4>
                        <h2 class="cardHeader" style="font-size:25px">{{$roomTypeInfo->name}}</h2>
                        <h4 class="cardSubHeaderGrey">Booking Fee</h4>
                        <h2 class="cardHeader" style="font-size:25px">R {{$room_rate}}</h2>
                        <h4 class="cardSubHeaderGrey">Booking Reference</h4>
                        <h2 class="cardHeader" style="font-size:25px">{{$booking->booking_code}}</h2>
                    </div>
                    <div>
                        <img src="{{$roomTypeInfo->img_url}}" class="roomImgApp">
                    </div>
                </div>
            </div>
        </div>

        <div class="clearSpaceLogin"></div>

        @if($room_rate == '0')
        <br/><br/>
        <br/><br/>
        <h2>No Booking Fee to be Paid</h2>
        <a href="/complete/online-application">
            <button type="button" class="detailsChange" style="text-transform:uppercase; background-color:#e74f26">Continue</button>
        </a>
        @endif

        @if($room_rate != '0')

            <div class="paymentSection <?php if($proofPayment && $proofPayment->status == "Uploaded"){echo 'hidden';} ?>" id="paymentDetailsSection" style="margin-top:100px;">
                <br/><br/>

                <div class="form-field">
                    <h1 class="header" id="paymentHeader">Payment Information</h1>
                </div>

                <div class="form-field">
                    <label >Payment Type &nbsp;&nbsp;&nbsp;&nbsp;</label>
                    <select name="paymentType" id="paymentType">
                        <option value="Credit/Debit Card">Credit/Debit Card</option>
                        <option value="Online EFT">Online EFT</option>
                        <option value="Manual EFT">Manual EFT</option>
                    </select>
                </div>

                <div class="form-field" id="cCard">
                    <button types="button" class="paymentBtn" onClick="createpayment()">Pay Now</button>
                    <form action="https://secure.paygate.co.za/payweb3/process.trans" method="POST" id="paygateForm">
                        <input type="hidden" name="PAY_REQUEST_ID" id="PAY_REQUEST_ID" value="">
                        <input type="hidden" name="CHECKSUM" id="CHECKSUM" value="">
                    </form>
                    <!-- <label>Cardholder Name* <small id="cardholderName_error" class="hidden" style="color:red;">This is a required field</small></label>
                    <input type="text" id="cardholderName" class="required">

                    <label>Card Number* <small id="cardNumber_error" class="hidden" style="color:red;">This is a required field</small></label>
                    <input type="text" id="cardNumber" class="required">

                    <label>Exp. Date* <small id="expDate_error" class="hidden" style="color:red;">This is a required field</small></label>
                    <input type="date" id="expDate" class="required">

                    <label>CVV* <small id="cvv_error" class="hidden" style="color:red;">This is a required field</small></label>
                    <input type="text" id="cvv" style="width:50%" class="required"> -->
                </div>

                <div class="form-field hidden" id="eftCard">
                    <p>
                        <strong>Bank: </strong>{{$propertyTypeInfo->bank_name}}<br/>
                        <strong>Account Name: </strong>{{$propertyTypeInfo->account_name}}<br/>
                        <strong>Account Number: </strong>{{$propertyTypeInfo->account_number}}<br/>
                        <!-- {{$propertyTypeInfo->branch}}<br/> -->
                        <strong>Branch Code: </strong>{{$propertyTypeInfo->branch_code}}<br/>
                        <strong>Reference: </strong>{{$booking->booking_code}}<br/><br/><br/>
                    </p>

                    <div id="paymentSection">
                        <label onClick="paymentRef()" id="proof_of_paymentSection" class="PaymentLabel">
                            UPLOAD PROOF OF PAYMENT
                        </label>
                    </div>


                    <!-- <label for="proof_of_payment" class="PaymentLabel"> UPLOAD PROOF OF PAYMENT</label> -->
                    <!-- <input type="file" id="proof_of_payment" hidden/> -->
                    <br/><br/>
                    <a href="/complete/online-application"><button type="button" id="homeStep" class="previousStepBtn hidden"> Back to home page</button></a>
                </div>

                <div class="form-field">
                <!-- seconCards -->
                    <div class="cards flex flex-col" id="">
                        <div class="whiteCard halfCard">
                            <h4 class="cardSubHeaderGrey">Non Refundable Booking Fee</h4>
                            <h2 class="cardHeader" >R {{$room_rate}}</h2>
                            <p>*Please use <strong>'{{$booking->booking_code}}'</strong> as the reference when paying by EFT or cash deposit</p>
                            
                        </div>
                        <div class="redBottom flex">
                                <i class="fas fa-clock"></i>
                                <p>Please note that your booking will only be reserved for 24hrs without payment</p>
                            </div>
                    </div>
                </div>

            </div>

            <a href="/complete/online-application">
                <?php if(($proofPayment && $proofPayment->status != "Uploaded") || !$proofPayment){echo '<button type="button" class="detailsChange payBtnLater" id="submitFormPay" style="text-transform:uppercase; background-color:#e74f26;">I\'ll Pay Later</button>';}else{echo '<button type="button" class="detailsChange" id="submitFormPay" style="text-transform:uppercase; background-color:#e74f26">Continue</button>';} ?>
                </a>
        @endif

        <!-- <div class="paymentTwo" style="margin-top:100px;">
            <h1 class="header">Payment Submitted!</h1>
            <br/>
            <p class="cardSubHeaderGrey">Thank you for your payment. In order to speed up the process, please upload a proof of payment document below or via your online profile as soon as possible. Your room will only be reserved for 24 hours without payment.</p>
            <br/><br/>

            <label for="proof_of_payment" class="PaymentLabel"> UPLOAD PROOF OF PAYMENT</label>
            <input type="file" id="proof_of_payment" hidden/>

            <br/><br/><br/>
            <a href="/complete/online-application"><button type="button" id="homeStep" class="previousStepBtn"> Back to home page</button></a>
            <br/><br/>
            <button type="button" id="previousStep" class="previousStepBtn"> Back to previous step</button>
            <div class="footerClearfix"></div>
        </div> -->


        

        
        

    </div>

</div>


<script>

    function createpayment(){
        $.ajax({
            type: "post",
            url: "/initiate-payment",
            data: {
                reference: "{{$booking->booking_code}}",
                amount: "{{$room_rate}}",
                id: "{{$user->userUid}}",
                payment_type: $("#paymentType").val(),
                req_type: "new",
            },

            success: function (data) {

                $("#PAY_REQUEST_ID").val(data[0])
                $("#CHECKSUM").val(data[1])

                $("#paygateForm").submit();

            },
        });
    }

function paymentRef(){
        swal.fire({
                title: 'Please enter the Payment Reference you used:',
                input: 'text',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Submit',
                showLoaderOnConfirm: true,
            }).then((result) => {
                if (result.isConfirmed && result.value !== "") {
                    $("#booking_payment").empty().append(result.value);

                    $.ajax({
                        type: "post",
                        url: "/UpdatePaymentRef",
                        data: {
                            bookingId: "{{$booking->id}}",
                            reason: result.value
                        },

                        success: function (data) {

                            $("#paymentSection").empty().append('<label for="proof_of_payment" class="PaymentLabel"> UPLOAD PROOF OF PAYMENT</label><input type="file" id="proof_of_payment" accept="image/*, application/pdf" hidden/>');

                            $("#proof_of_payment").click();

                        },
                    });
                }
            })
    }

    $("#paymentType").on('change', function(){
        if($("#paymentType").val() == "Manual EFT"){
            $("#cCard").addClass('hidden');
            $("#eftCard").removeClass('hidden')
            $("#cardholderName").removeClass('required')
            $("#cardNumber").removeClass('required')
            $("#expDate").removeClass('required')
            $("#cvv").removeClass('required')
            $("#submitFormPay").addClass('hidden')
        }else{
            $("#cCard").removeClass('hidden');
            $("#eftCard").addClass('hidden')
            $("#cardholderName").addClass('required')
            $("#cardNumber").addClass('required')
            $("#expDate").addClass('required')
            $("#cvv").addClass('required')
            $("#submitFormPay").removeClass('hidden')
        }
    })

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

    // $("#submitFormPay").on('click', function(){
        
    //     $('.hidden .fieldError').removeClass('fieldError').val('');

    //     var req = 1;

    //     if($("#cardholderName").val() === "" || $("#cardholderName").val() === null){
    //         $("#cardholderName_error").removeClass('hidden')
    //         $("#cardholderName").addClass('fieldError')
    //         req = 0;
    //     }

    //     if($("#cardNumber").val() === "" || $("#cardNumber").val() === null){
    //         $("#cardNumber_error").removeClass('hidden')
    //         $("#cardNumber").addClass('fieldError')
    //         req = 0;
    //     }

    //     if($("#expDate").val() === "" || $("#expDate").val() === null){
    //         $("#expDate_error").removeClass('hidden')
    //         $("#expDate").addClass('fieldError')
    //         req = 0;
    //     }

    //     if($("#cvv").val() === "" || $("#cvv").val() === null){
    //         $("#cvv_error").removeClass('hidden')
    //         $("#cvv").addClass('fieldError')
    //         req = 0;
    //     }

    //     if($("#cardholderName").val() === "" || $("#cardNumber").hasClass('fieldError') === true || $("#expDate").hasClass('fieldError') === true || $("#cvv").hasClass('fieldError') === true){
    //         req = 0;
    //     }

    //     if($("#cardholderName").hasClass('fieldError') === true || $("#cardNumber").hasClass('fieldError') === true || $("#expDate").hasClass('fieldError') === true || $("#cvv").hasClass('fieldError') === true){
    //         req = 0;
    //     }


    //     if(req == 0){
    //         Swal.fire(
    //             'Blank Fields',
    //             'Please fill in all Required Fields!',
    //             'error'
    //         )

    //     }else{


    //         $("#submitFormPay").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

    //         $(".paymentSection").addClass('hidden')
    //         $("#submitFormPay").addClass('hidden')
    //         $(".paymentTwo").removeClass('hidden')

    //         // $.ajax({
    //         //     type: "post",
    //         //     url: "/",
    //         //     data: {
    //         //         cardholderName: $("#cardholderName").val(), 
    //         //         cardNumber: $("#cardNumber").val(), 
    //         //         expDate: $("#expDate").val(), 
    //         //         cvv: $("#cvv").val(),  
    //         //     },
    //         //     success: function (data) {
    //         //         window.location.replace(data)                            
    //         //     },
    //         // });
            
    //     }

    // });

    $(document).off('change').on('change', '#proof_of_payment', function(e) {
    // $("#proof_of_payment").on('change', function(){

        if(document.getElementById('proof_of_payment').files[0].type == "application/pdf" || document.getElementById('proof_of_payment').files[0].type.split("/")[0] == "image"){
            $(".PaymentLabel").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

            var form_data = new FormData();
            form_data.append("file", document.getElementById('proof_of_payment').files[0]);
            form_data.append("userUid", "<?php echo $user->userUid; ?>");
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

                        // $("#homeStep").removeClass('hidden')
                        window.location.replace('/complete/online-application')
                    }
                },
            });
        }else{
            Swal.fire(
                'Error',
                'Only PDF and Images are allowed',
                'error'
            )
        }
    })


</script>


@endsection
