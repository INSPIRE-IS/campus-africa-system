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
    
    <!-- <form id="appRetry" class="form onlineForm" autocomplete="off"> -->
        <br/>
        <h1 class="formHeading">Your Payment was Unsuccessful: {{$transaction->status}}</h1>
        <br/>

        <!-- <div class="form-field"> -->
            <p>Please try again or procceed to your dashboard</p>
            <br/><br/>
            <!-- <div class="form-field"> -->
                <label >Payment Type &nbsp;&nbsp;&nbsp;&nbsp;</label>
                <select name="paymentType" id="paymentType" style="width:300px !important;">
                    <option value="Credit/Debit Card">Credit/Debit Card</option>
                    <option value="Online EFT">Online EFT</option>
                    <option value="Manual EFT">Manual EFT</option>
                </select>
                <br/>
                <br/>
            <!-- </div> -->
            <!-- @if(!$agent->isMobile())
                <div class="form-field">
                    <br/>
                </div>
            @endif -->
            <!-- <div class="form-field"> -->
                <button types="button" class="paymentBtn" onClick="createpayment()" style="width:300px; margin-left:0px;">Pay Now</button>
                <br/><br/>
                <form action="https://secure.paygate.co.za/payweb3/process.trans" method="POST" id="paygateForm">
                    <input type="hidden" name="PAY_REQUEST_ID" id="PAY_REQUEST_ID" value="">
                    <input type="hidden" name="CHECKSUM" id="CHECKSUM" value="">
                </form>
            <!-- </div> -->

            <!-- @if(!$agent->isMobile())
                <div class="form-field">
                    <br/>
                </div>
            @endif -->

            <!-- <div class="form-field"> -->
                <a href="/complete/online-application"><button type="button" class="detailsChange" id="submitFormPay" style="text-transform:uppercase; background-color:#e74f26; float:left; margin-left:0px;">Continue to Dashboard</button></a>
                <br/><br/>
            <!-- </div> -->
        <!-- </div> -->




    <!-- </form> -->

</div>

<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

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
