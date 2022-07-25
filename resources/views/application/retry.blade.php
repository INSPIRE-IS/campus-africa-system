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
            <h1 class="booking">Payment Unsuccessful</h1>
        </div>
    
    </div>
</div>

<div class="container form-card">
    <div class="onlineTabs">
        <div class="tab">1: Applicant Details</div>
        <div class="tab">2: Sponsor/Burser Information</div>
        <div class="tab">3: Next of Kin</div>
        <div class="tab">4: Upload Documents</div>
        <div class="tab active">5: Pay Booking Fee</div>
    </div>
    
        <br/>
        <h1 class="formHeading">Your Payment was Unsuccessful: <span style="color:red;">{{$transaction->status}}</span></h1>
        <br/>

            <p>Please try again or procceed to your dashboard</p>
            <br/><br/>
                <label >Payment Type &nbsp;&nbsp;&nbsp;&nbsp;</label>
                <select name="paymentType" id="paymentType" style="width:300px !important;">
                    <option value="Credit/Debit Card">Credit/Debit Card</option>
                    <option value="Online EFT">Online EFT</option>
                    <option value="Manual EFT">Manual EFT</option>
                </select>
                <br/>
                <br/>

                <div id="ccPay">
                    <button types="button" class="paymentBtn" onClick="createpayment()" style="width:300px; margin-left:0px;">Pay Now</button>
                    <br/><br/>
                    <form action="https://secure.paygate.co.za/payweb3/process.trans" method="POST" id="paygateForm">
                        <input type="hidden" name="PAY_REQUEST_ID" id="PAY_REQUEST_ID" value="">
                        <input type="hidden" name="CHECKSUM" id="CHECKSUM" value="">
                    </form>
                </div>

                <div class="hidden" id="eftCard">
                    <p>
                        <strong>Bank: </strong>{{$propertyTypeInfo->bank_name}}<br/>
                        <strong>Account Name: </strong>{{$propertyTypeInfo->account_name}}<br/>
                        <strong>Account Number: </strong>{{$propertyTypeInfo->account_number}}<br/>
                        <strong>Branch Code: </strong>{{$propertyTypeInfo->branch_code}}<br/>
                        <strong>Reference: </strong>{{$booking->booking_code}}<br/><br/><br/>
                    </p>

                    <div id="paymentSection">
                        <label onClick="paymentRef()" id="proof_of_paymentSection" class="PaymentLabel">
                            UPLOAD PROOF OF PAYMENT
                        </label>
                    </div>
                </div>

                <a href="/complete/online-application"><button type="button" class="detailsChange" id="submitFormPay" style="text-transform:uppercase; background-color:#e74f26; float:left; margin-left:0px;">Continue to Dashboard</button></a>
                <br/><br/>


</div>

<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#paymentType").on('change', function(){
        //Manual EFT

        if($("#paymentType").val() == "Manual EFT"){
            $("#eftCard").removeClass('hidden');
            $("#ccPay").addClass('hidden');
        }else{
            $("#eftCard").addClass('hidden');
            $("#ccPay").removeClass('hidden');
        }
    })

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

                        $("#paymentSection").empty().append('<label for="proof_of_payment" class="PaymentLabel"> UPLOAD PROOF OF PAYMENT</label><input type="file" id="proof_of_payment" hidden/>');

                        $("#proof_of_payment").click();

                    },
                });
            }
        })
    }

    $(document).off('change').on('change', '#proof_of_payment', function(e) {
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

                    window.location.replace('/complete/online-application')
                }
            },
        });
    })

    function createpayment(){
        $.ajax({
            type: "post",
            url: "/initiate-payment",
            data: {
                reference: "{{$booking->booking_code}}",
                amount: "{{$booking->room_rate}}",
                id: "{{Auth::user()->id}}",
                payment_type: $("#paymentType").val(),
                req_type: "retry",
            },

            success: function (data) {

                $("#PAY_REQUEST_ID").val(data[0])
                $("#CHECKSUM").val(data[1])

                $("#paygateForm").submit();

            },
        });
    }


</script>


@endsection
