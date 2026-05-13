@extends('layouts.app')

@section('content')

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

<div class="container">
    
        <!-- MultiStep Form -->
        <div class="container-fluid" id="grad1">
            <div class="row justify-content-center mt-0">
                <div class="col-11 col-sm-9 col-md-7 col-lg-6 text-center p-0 mt-3 mb-2">
                    <div class="card px-0 pt-4 pb-0 mt-3 mb-3">
                        <div class="row">
                            <div class="col-md-12 mx-0">
                                <form id="msform">
                                    <!-- progressbar -->
                                    <ul id="progressbar">
                                        <li class="active" id="applicant"></li>
                                        <li id="sponsor"></li>
                                        <li id="nextKin"></li>
                                        <li id="upload"></li>
                                        <li id="booking"></li>
                                    </ul> <!-- fieldsets -->
                                    <fieldset id="appStep1">
                                        <div class="form-card">
                                            <h2 class="fs-title">Applicant Details</h2>
                                            <br/>

                                            <div class="form-field">
                                                <label>First Name *</label>
                                                <input type="text" name="firstName" id="firstName" required>
                                            </div>

                                            <div class="form-field">
                                                <label>Last Name *</label>
                                                <input type="text" name="lastName" id="lastName" required>
                                            </div>

                                            <div class="form-field">
                                                <label>Title *</label>
                                                <select name="title" id='title' required>
                                                    <option value="" selected disabled>Select One</option>
                                                    <option value="Mr">Mr</option>
                                                    <option value="Ms">Ms</option>
                                                    <option value="Mrs">Mrs</option>
                                                    <option value="Miss">Miss</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Date of Birth *</label>
                                                <input type="date" name="dob" id="dob" required>
                                            </div>

                                            <div class="form-field">
                                                <label>Cellphone Number * <span id="cellError" class="hidden" style="color:red"><small>Not a valid cellphone number</small></span></label>
                                                <input type="text" name="cell_no" id="cell_no" required>
                                            </div>

                                            <div class="form-field">
                                                <label>Email Address * <span id="emailError" class="hidden" style="color:red"><small>Not a valid email</small></span></label>
                                                <input type="email" name="email" id="email" required>
                                            </div>

                                            <div class="form-field">
                                                <label>Password *</label>
                                                <input type="password" name="password" id="password" required>
                                            </div>

                                            <div class="form-field">
                                                <label>Confirm Password *  <span id="passhide" class="hidden" style="color:red">Passwords don't match</span></label>
                                                <input type="password" name="confirmPassword" id="confirmPassword" required>
                                            </div>

                                            <div class="form-field">
                                                <label>Nationality *</label>
                                                <select name="nationality" id='nationality' required>
                                                    <option value="" selected disabled>Select One</option>
                                                    @foreach($lookups as $lookup)
                                                        @if($lookup->group_id == "5")
                                                            <option value="{{$lookup->lookup_name}}" >{{$lookup->lookup_name}}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        
                                            <div class="form-field hidden" id="passportField">
                                                <label>Passport Number *</label>
                                                <input type="text" name="passportNo" id="passportNo" >
                                            </div>

                                            <div class="form-field" id="idField">
                                                <label>I.D Number * <span id="idNumberError" class="hidden" style="color:red"><small>Not a valid ID number</small></span></label>
                                                <input type="text" name="idNumber" id="idNumber" required>
                                            </div>

                                            <div class="form-field">
                                                <label>Student Type *</label>
                                                <select name="studentType" id='studentType' required>
                                                    <option value="" selected disabled>Select One</option>
                                                    <option value="NSFAS">NSFAS</option>
                                                    <option value="Bursary">Bursary</option>
                                                    <option value="Cash / Direct">Cash</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Year *</label>
                                                <select name="year" id="year" required>
                                                <option value="" selected disabled>Select One</option>
                                                @foreach($lookups as $lookup)
                                                    @if($lookup->group_id == "4")
                                                        <option value="{{$lookup->lookup_name}}" >{{$lookup->lookup_name}}</option>
                                                    @endif
                                                @endforeach
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Institution of Study *</label>
                                                <select name="institStudy" id="institStudy" required>
                                                    <option value="" selected disabled>Select One</option>
                                                    @foreach($lookups as $lookup)
                                                        @if($lookup->group_id == "1")
                                                            <option value="{{$lookup->id}}">{{$lookup->lookup_name}}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Year of study *</label>
                                                <select name="yearOfStudy" id="yearOfStudy" required>
                                                    <option value="" selected disabled>Select One</option>
                                                    @foreach($lookups as $lookup)
                                                        @if($lookup->group_id == "6")
                                                            <option value="{{$lookup->lookup_name}}" >{{$lookup->lookup_name}}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <br/>
                                            </div>

                                            <div class="form-field">
                                                <br/>
                                            </div>

                                            <div class="form-field">
                                                <label>Student Number *</label>
                                                <input type="text" name="studNumber" id="studNumber" required>
                                            </div>

                                            <div class="form-field">
                                                <label>Faculty *</label>
                                                <select name="faculty" id="faculty" required>
                                                    <option value="" disabled selected>Select One</option>
                                                    @foreach($lookups as $lookup)
                                                        @if($lookup->group_id == "3")
                                                            <option value="{{$lookup->id}}">{{$lookup->lookup_name}}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>

                                            
                                            
                                        </div> 
                                        <input type="button" name="next" class="next action-button" value="proceed To Next Step" id="step1" />
                                    </fieldset>
                                    <fieldset id="appStep1">
                                        <div class="form-card">
                                            <h2 class="fs-title">Sponsor / Burser Information</h2>
                                            <h4 class="fs-subtitle">Parent / guardians / sponsor details</h4>
                                            <br/>

                                            <div class="form-field">
                                                <label>First Name</label>
                                                <input type="text" name="pFirstName" id="pFirstName">
                                            </div>

                                            <div class="form-field">
                                                <label>Last Name</label>
                                                <input type="text" name="pLastName" id="pLastName">
                                            </div>

                                            <div class="form-field">
                                                <label>Cellphone Number</label>
                                                <input type="text" name="pCell_no" id="pCell_no">
                                            </div>

                                            <div class="form-field">
                                                <label>Email Address</label>
                                                <input type="email" name="pEmail" id='pEmail'>
                                            </div>

                                            <div class="form-field">
                                                <label>I.D Number</label>
                                                <input type="text" name="pIdNumber" id="pIdNumber">
                                            </div>

                                            <div class="form-field">
                                                <br/>
                                            </div>

                                            <h4 class="fs-subtitle">Bursar Details</h4>
                                            <br/>

                                            <div class="form-field">
                                                <label>Company Name</label>
                                                <input type="text" name="bCompanyName" id='bCompanyName'>
                                            </div>

                                            <div class="form-field">
                                                <label>Registration Number</label>
                                                <input type="text" name="bRegistrationNo" id="bRegistrationNo">
                                            </div>

                                            <div class="form-field">
                                                <label>Contact Person</label>
                                                <input type="text" name="bContactPerson" id="bContactPerson">
                                            </div>

                                            <div class="form-field">
                                                <label>Contact Number</label>
                                                <input type="text" name="bContactNumber" id="bContactNumber">
                                            </div>

                                            <div class="form-field">
                                                <label>Email Address</label>
                                                <input type="email" name="bEmail" id="bEmail">
                                            </div>

                                            <div class="form-field">
                                                <br/>
                                            </div>
                                            
                                        </div>
                                        
                                        <input type="button" name="previous" class="previous action-button-previous" id="back2" onClick="PrevStep('back2')" value="Back to Previous Step" />
                                        <input type="button" name="next" class="next action-button" value="Proceed to Next Step" id="step2" />
                                    </fieldset>
                                    <fieldset id="appStep1">
                                        <div class="form-card">
                                            <h2 class="fs-title">Next of Kin</h2>
                                            <br/>

                                            <div class="form-field">
                                                <label>First Name</label>
                                                <input type="text" name="kFirstName" id="kFirstName">
                                            </div>
                                            <div class="form-field">
                                                <label>Last Name</label>
                                                <input type="text" name="kLastName" id="kLastName">
                                            </div>

                                            <div class="form-field">
                                                <label>Title</label>
                                                <select name="kTitle" id='kTitle'>
                                                    <option value="Mr">Mr</option>
                                                    <option value="Ms">Ms</option>
                                                    <option value="Mrs">Mrs</option>
                                                    <option value="Miss">Miss</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>I.D Number</label>
                                                <input type="text" name="kIdNumber" id="kIdNumber">
                                            </div>

                                            <div class="form-field-big">
                                                <label>Physical Address</label>
                                                <input type="text" placeholder="Street No" name="kAddress1" id="kAddress1" style="width:98% !important">
                                            </div>
                                            <div class="form-field-big">
                                                <input type="text" placeholder="Suburb" name="kAddress2" id="kAddress2" style="width:98% !important">
                                            </div>
                                            <div class="form-field">
                                                <input type="text" placeholder="City" name="kAddressCity" id='kAddressCity'>
                                            </div>
                                            <div class="form-field">
                                                <input type="text" placeholder="Postal Code" name="kAddressPostal" id="kAddressPostal">
                                            </div>

                                            <div class="form-field">
                                                <label>Relationship</label>
                                                <input type="text" name="kRelationship" id="kRelationship">
                                            </div>

                                            <div class="form-field">
                                                <label>Telephone Number</label>
                                                <input type="text" name="kTeleNo" id='kTeleNo'>
                                            </div>

                                            <div class="form-field">
                                                <label>Cellphone Number</label>
                                                <input type="text" name="kCellNo" id='kCellNo'>
                                            </div>

                                            <div class="form-field">
                                                <label>Work Number</label>
                                                <input type="text" name="kWorkNo" id="kWorkNo">
                                            </div>

                                            <div class="form-field">
                                                <label>Email Address</label>
                                                <input type="email" name="kEmail" id="kEmail">
                                            </div>

                                            <div class="form-field">
                                                <br/>
                                            </div>
                                            
                                        </div> 
                                        
                                        <input type="button" name="previous" class="previous action-button-previous" value="Back to previous step" id="back3" onClick="PrevStep('back3')" /> 
                                        <input type="button" name="next" class="next action-button" value="Proceed to Next Step" id="step3" />
                                        <!-- <input type="button" name="make_payment" class="next action-button" value="Confirm" /> -->
                                    </fieldset>
                                    <fieldset id="appStep1">
                                        <div class="form-card">

                                            <div class="form-field-app" id="applicationType"style="width:100%; text-align:center; display:flex; justify-content: center;">
                                                <label style="font-size:12px; line-height:38px;">Application Type &nbsp;&nbsp;&nbsp;&nbsp;</label>
                                                <select name="kTitle" style="width:30% !important">
                                                    <option value="Cash">Cash</option>
                                                </select>
                                            </div>

                                            <br/><br/>

                                            <div>
                                                <div class="cards" id="registerCards">
                                                    <div class="whiteYellowCard" style="margin-right:30px">
                                                        <h2 class="cardHeader">Booking<br/>Documents</h2>

                                                        <div class="cardBody">
                                                            <label for="copyID" id="copyIdUpload" style="cursor:pointer">
                                                                <div class="uploadSection" id="copyIdCard">
                                                                    <h3 class="uploadName">I.D COPY</h3>
                                                                    <div class="uploadFileSection">
                                                                    
                                                                        <i class="fas fa-file-upload" ></i>
                                                                    
                                                                    <input type="file" id="copyID" accept="image/*, application/pdf" hidden/>
                                                                    <label id="copyIdSucess" style="cursor: pointer" class="successLabel hidden">UPLOADED</label>
                                                                        
                                                                    </div>
                                                                </div>
                                                            </label>

                                                            <br/>
                                                            
                                                            <div class="uploadSection">
                                                                <h3 class="uploadName" id="bookingFeeUploadTab" style="color:#9a9a9a">BOOKING FEE PROOF OF PAYMENT</h3>
                                                                <div class="uploadFileSection">
                                                                    <i class="fas fa-file-upload" style="color:rgba(231, 79, 38, 0.5);"></i>
                                                                    <label for="bookingFee" style="cursor: context-menu" class="uploadLabel">NEXT STEP</label>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        
                                                    </div>

                                                    <div class="whiteYellowCard">
                                                        <h2 class="cardHeader" style="height:130px;">Application</h2>
                                                        
                                                        <div class="cardBody">
                                                            <label for="studentCard" id="studentCardUpload" style="cursor:pointer">
                                                                <div class="uploadSection" id="copyStudentCard">
                                                                    <h3 class="uploadName">COPY OF STUDENT CARD</h3>
                                                                    <div class="uploadFileSection">
                                                                        
                                                                            <i class="fas fa-file-upload" ></i>
                                                                        
                                                                        <input type="file" id="studentCard" accept="image/*, application/pdf" hidden/>
                                                                        <label id="copyStudCardSucess" style="cursor: pointer" class="successLabel hidden">UPLOADED</label>
                                                                        
                                                                    </div>
                                                                </div>
                                                            </label>

                                                            <br/>
                                                            
                                                            <label for="bankStatement" id="bankStatementUpload" style="cursor:pointer">
                                                                <div class="uploadSection" id="copyBankStatement">
                                                                    <h3 class="uploadName">3 MONTHS BANK STATEMENTS</h3>
                                                                    <div class="uploadFileSection">
                                                                        
                                                                            <i class="fas fa-file-upload" ></i>
                                                                        
                                                                        <input type="file" id="bankStatement" accept="image/*, application/pdf" hidden/>
                                                                        <label id="bankStatementSuccess" style="cursor: pointer" class="successLabel hidden">UPLOADED</label>
                                                                        
                                                                    </div>
                                                                </div>
                                                            </label>

                                                            <br/>
                                                            
                                                            <label for="proofRegistration" id="proofRegistrationUpload" style="cursor:pointer">
                                                                <div class="uploadSection" id="copyProofRegistration">
                                                                    <h3 class="uploadName">PROOF OF REGISTRATION</h3>
                                                                    <div class="uploadFileSection">
                                                                        
                                                                            <i class="fas fa-file-upload" ></i>
                                                                        
                                                                        <input type="file" id="proofRegistration" accept="image/*, application/pdf" hidden/>
                                                                        <label id="proofRegistrationSuccess" style="cursor: pointer" class="successLabel hidden">UPLOADED</label>
                                                                        
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="button" name="previous" class="previous action-button-previous" value="Back to previous step" id="back4" onClick="PrevStep('back4')" /> 
                                        <input type="button" name="next" class="next action-button" value="Pay booking fee" id="step4" onClick="NextStep('step4')"  />

                                        <div class="clearSpaceLogin"></div>
                                        <div class="clearSpaceLogin"></div>

                                        <div class="redBannerNotice">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <p>Please note that you can upload these documents via your online profile at a later date, however delays may cause you to not get the room you want! Your booking fee is processed in the next step; and you can upload proof of payment immediately or via your profile later.</p>
                                        </div>
                                    </fieldset>
                                    <fieldset id='appStep1'>
                                        <div class="form-card">
                                            
                                            <div class="cards">
                                                <div class="whiteCard" id="registerPaymentCards">

                                                    <div class="flex flex-rev">
                                                        <button type="button" class="editButton" onClick="PrevStep('back2')">EDIT</button>
                                                    </div>
                                                
                                                    <p class="cardList">
                                                        <span id="dispFirstName"></span> <span id="dispLastName"></span><br/>
                                                        <span id="dispDob"></span><br/>
                                                        <span id="dispCell"></span><br/>
                                                        <span id="dispEmail"></span><br/>
                                                        <span id="dispID"></span><br/>
                                                        <span id="dispNationality"></span><br/>
                                                        <span id="dispPassport"></span><br/>
                                                        <span id="dispInstit"></span><br/>
                                                        <span id="dispStudNo"></span><br/>
                                                        <span id="dispFaculty"></span><br/>
                                                        <span id="dispYear"></span></p>
                                                        
                                                </div>

                                                <div class="whiteCard" id="registerPaymentCards" style="margin-left:11px;">
                                                    <h4 class="cardSubHeaderGrey">Braamlofts</h4>
                                                    <h2 class="cardHeader" >Single</h2>
                                                    <h4 class="cardSubHeaderGrey">Room 23 Floor 2</h4>
                                                </div>
                                            </div>

                                            <br/>
                                            <div class="clearSpaceLogin"></div>
                                            <br/><br/><br/><br/><br/>

                                            <div class="paymentSection">

                                                <div class="form-field">
                                                    <h1 class="header" >Payment Information</h1>
                                                </div>

                                                <div class="form-field">
                                                    <label >Payment Type &nbsp;&nbsp;&nbsp;&nbsp;</label>
                                                    <select name="paymentType" >
                                                        <option value="Credit/Debit Card">Credit/Debit Card</option>
                                                    </select>
                                                </div>

                                                <div class="form-field">
                                                    <label>Cardholder Name</label>
                                                    <input type="text" name="cardholderName">

                                                    <label>Card Number</label>
                                                    <input type="text" name="cardNumber">

                                                    <label>Exp. Date</label>
                                                    <input type="date" name="expDate">

                                                    <label>CVV</label>
                                                    <input type="text" name="cvv" style="width:50%">
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

                                                <!-- <input type="button" name="previous" class="previous action-button-previous" value="Back to previous step" id="back5" onClick="PrevStep('back5')" />  -->
                                                <input type="button" name="next" id="payBtnReg" class="next action-button" value="Pay R2500.00 booking fee" onClick="PayFee()" style="margin-top:55px;" />

                                            </div>

                                            <div class="paymentTwo hidden">
                                            <div class="footerClearfix"></div>
                                            <br/><br/><br/><br/>
                                                <h1 class="header">Payment Submitted!</h1>
                                                <br/>
                                                <p class="cardSubHeaderGrey">Thank you for your payment. In order to speed up the process, please upload a proof of payment document below or via your online profile as soon as possible. Your room will only be reserved for 24 hours without payment.</p>
                                                <br/><br/>

                                                <label for="proof_of_payment" class="PaymentLabel"> UPLOAD PROOF OF PAYMENT</label>
                                                <input type="file" id="proof_of_payment" accept="image/*, application/pdf" hidden/>

                                                <div class="clearSpaceLogin"></div>
                                        <!-- <div class="footerClearfix"></div> --><br/><br/><br/><br/><br/><br/><br/><br/>

                                        <!-- <input type="button" name="previous" class="previous action-button-previous" value="Back to previous step" id="back5" onClick="PrevStep('back5')" style="margin-left: -7px !important;"/> <br/><br/><br/><br/> -->
                                        <a href="https://campusafrica.co.za/"><input type="button" name="previous" class="previous action-button-previous" value="Back to home page" style="margin-left: -7px !important; margin-top:59px" /> </a>
                                        <!-- <input type="button" name="next" class="next action-button" value="Pay R2500.00 booking fee" onClick="PayFee()" /> -->
                                        </div>

                                        

                                        

                                        </div>

                                        <input type="button" name="previous" class="previous action-button-previous" value="Back to previous step" id="back5" onClick="PrevStep('back5')" /> 
                                    </fieldset>
                                    <input type="hidden" id="userId" value="" />
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    $("#step1").on('click', function(){

        

        if($("#password").val() !== "" && $("#password").val() === $("#confirmPassword").val()){

            if($("#firstName").val() !== "" && $("#email").val() !== "" && isEmail($("#email").val())){

                if($("#userId").val() === ""){

                    $("#step1").val("Processing");

                    $.ajax({
                        type: "post",
                        url: "/registration_step_one",
                        data: {
                            firstName: $("#firstName").val(), 
                            lastName: $("#lastName").val(), 
                            title: $("#title").val(), 
                            dob: $("#dob").val(), 
                            cell_no: $("#cell_no").val(), 
                            email: $("#email").val(), 
                            password: $("#password").val(), 
                            password_confirmation: $("#confirmPassword").val(), 
                            idNumber: $("#idNumber").val(), 
                            nationality: $("#nationality").val(),
                            passportNo: $("#passportNo").val(), 
                            institStudy: $("#institStudy").val(), 
                            studNumber: $("#studNumber").val(), 
                            faculty: $("#faculty").val(), 
                            year: $("#year").val() 
                        },

                        success: function (data) {
                            console.log(data.id)
                            $("#userId").val(data.id);
                            $("#step1").val("Proceed to Next Step");
                            NextStep("step1");

                            $("#dispFirstName").empty().append($("#firstName").val());
                            $("#dispLastName").empty().append($("#lastName").val());
                            $("#dispDob").empty().append($("#dob").val());
                            $("#dispCell").empty().append($("#cell_no").val());
                            $("#dispEmail").empty().append($("#email").val());
                            $("#dispID").empty().append($("#idNumber").val());
                            $("#dispNationality").empty().append($("#nationality").val());
                            $("#dispPassport").empty().append($("#passportNo").val());
                            $("#dispInstit").empty().append($("#institStudy").val());
                            $("#dispStudNo").empty().append($("#studNumber").val());
                            $("#dispFaculty").empty().append($("#faculty").val());
                            $("#dispYear").empty().append($("#year").val());
                            
                        },
                        error: function(xhr) {
                            $("#step1").val("Proceed to Next Step");
                            var msg = "Something went wrong. Please try again.";
                            if (xhr && xhr.responseText) {
                                try {
                                    var resp = JSON.parse(xhr.responseText);
                                    if (resp && resp.message) {
                                        msg = resp.message;
                                    } else if (resp && resp.errors && resp.errors.email && resp.errors.email[0]) {
                                        msg = resp.errors.email[0];
                                    }
                                } catch (e) {}
                            }
                            alert(msg);
                        },
                    });
                }else{
                    $.ajax({
                        type: "post",
                        url: "/registration_step_one_update",
                        data: {
                            userUid: $("#userId").val(),
                            firstName: $("#firstName").val(), 
                            lastName: $("#lastName").val(), 
                            title: $("#title").val(), 
                            dob: $("#dob").val(), 
                            cell_no: $("#cell_no").val(), 
                            email: $("#email").val(), 
                            password: $("#password").val(), 
                            password_confirmation: $("#confirmPassword").val(), 
                            idNumber: $("#idNumber").val(), 
                            nationality: $("#nationality").val(),
                            passportNo: $("#passportNo").val(), 
                            institStudy: $("#institStudy").val(), 
                            studNumber: $("#studNumber").val(), 
                            faculty: $("#faculty").val(), 
                            year: $("#year").val() 
                        },

                        success: function (data) {
                            $("#step1").val("Proceed to Next Step");
                            console.log(data.id)
                            $("#userId").val(data.id);
                            NextStep("step1")
                        },
                    });
                }
                
                
            }else{
                // $("#firstName").addClass('errorBorder')
                // $("#firstName").focus();
                // $("#email").addClass('email')
                // $("#email").focus();
                // $("#emailhide").removeClass('hidden')
            }
            
        }else{
            $("#password").addClass('errorBorder')
            $("#password").focus();
            $("#confirmPassword").addClass('errorBorder')
            $("#passhide").removeClass('hidden')
        }
        
    });

    $("#step2").on('click', function(){
        $("#step2").val("Processing");

        $.ajax({
            type: "post",
            url: "/registration_step_two",
            data: {
                userUid: $("#userId").val(), 
                pFirstName: $("#pFirstName").val(), 
                lastName: $("#pLastName").val(), 
                cell: $("#pCell_no").val(), 
                email: $("#pEmail").val(), 
                idNo: $("#pIdNumber").val(), 

                companyName: $("#bCompanyName").val(), 
                regNo: $("#bRegistrationNo").val(), 
                contactPerson: $("#bContactPerson").val(), 
                contactNo: $("#bContactNumber").val(),
                emailAddress: $("#bEmail").val(), 
 
            },

            success: function (data) {
                $("#step2").val("Proceed to Next Step");
                NextStep("step2")
            },
        });
    })

    $("#step3").on('click', function(){
        $("#step3").val("Processing");

        $.ajax({
            type: "post",
            url: "/registration_step_three",
            data: {
                userUid: $("#userId").val(), 
                firstName: $("#kFirstName").val(), 
                lastName: $("#kLastName").val(), 
                title: $("#kTitle").val(), 
                idNo: $("#kIdNumber").val(), 
                address1: $("#kAddress1").val(), 
                address2: $("#kAddress2").val(), 
                addressCity: $("#kAddressCity").val(), 
                addressPostal: $("#kAddressPostal").val(), 
                relationship: $("#kRelationship").val(),
                telNo: $("#kTeleNo").val(), 
                celNo: $("#kCellNo").val(), 
                workNo: $("#kWorkNo").val(), 
                email: $("#kEmail").val(), 
 
            },

            success: function (data) {
                $("#step3").val("Proceed to Next Step");
                NextStep("step3")
            },
        });
    })

    function NextStep(stepId){
        let thisStep = document.getElementById(stepId)
        current_fs = $(thisStep).parent();
        next_fs = $(thisStep).parent().next();

        //Add Class Active
        $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
        $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");
        $("#progressbar li").eq($("fieldset").index(current_fs)).addClass("done");
        
        //show the next fieldset
        next_fs.show();

        //hide the current fieldset with style
        current_fs.animate({opacity: 0}, {
            step: function(now) {
                // for making fielset appear animation
                opacity = 1 - now;
                
                current_fs.css({
                    'display': 'none',
                    'position': 'relative'
                });
                next_fs.css({'opacity': opacity});
            },
            duration: 600
        });
    }

    function PrevStep(stepId){
        let thisStep = document.getElementById(stepId)

        current_fs = $(thisStep).parent();
        previous_fs = $(thisStep).parent().prev();
        
        //Remove class active
        $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");

        console.log(current_fs)
        console.log(previous_fs)
        
        //show the previous fieldset
        previous_fs.show();
        
        //hide the current fieldset with style
        current_fs.animate({opacity: 0}, {
            step: function(now) {
                // for making fielset appear animation
                opacity = 1 - now;
                
                current_fs.css({
                    'display': 'none',
                    'position': 'relative'
                });
                previous_fs.css({'opacity': opacity});
            },
            duration: 600
        });

    }

    $("#copyID").on('change', function(){
        $("#copyIdUpload").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('copyID').files[0]);
        form_data.append("userUid", $("#userId").val());
        form_data.append("fileType", "copyID");

        $.ajax({
            type: "post",
            url: "/register/fileUpload",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                    $("#copyIdUpload").empty().append('<i class="fas fa-file successful"></i>')
                    $("#copyIdSucess").removeClass('hidden')
                    $("#copyIdCard").addClass('greenBorder')
                }
            },
        });
    });

    $("#studentCard").on('change', function(){
        $("#studentCardUpload").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('studentCard').files[0]);
        form_data.append("userUid", $("#userId").val());
        form_data.append("fileType", "studentCard");

        $.ajax({
            type: "post",
            url: "/register/fileUpload",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                    $("#studentCardUpload").empty().append('<i class="fas fa-file successful"></i>')
                    $("#copyStudCardSucess").removeClass('hidden')
                    $("#copyStudentCard").addClass('greenBorder')
                }
            },
        });  
    })

    $("#bankStatement").on('change', function(){
        $("#bankStatementUpload").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('bankStatement').files[0]);
        form_data.append("userUid", $("#userId").val());
        form_data.append("fileType", "bankStatement");

        $.ajax({
            type: "post",
            url: "/register/fileUpload",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                    $("#bankStatementUpload").empty().append('<i class="fas fa-file successful"></i>')
                    $("#bankStatementSuccess").removeClass('hidden')
                    $("#copyBankStatement").addClass('greenBorder')
                }
            },
        });
    })

    $("#proofRegistration").on('change', function(){

        $("#proofRegistrationUpload").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('proofRegistration').files[0]);
        form_data.append("userUid", $("#userId").val());
        form_data.append("fileType", "proofRegistration");

        $.ajax({
            type: "post",
            url: "/register/fileUpload",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                    $("#proofRegistrationUpload").empty().append('<i class="fas fa-file successful"></i>')
                    $("#proofRegistrationSuccess").removeClass('hidden')
                    $("#copyProofRegistration").addClass('greenBorder')
                }
            },
        });
    })

    function PayFee(){
        $(".paymentSection").addClass("hidden")
        $(".paymentTwo").removeClass('hidden')
        $(".clearSpaceLogin").removeClass('clearSpaceLogin')
    }

    $("#proof_of_payment").on('change', function(){
    // function PaymentUpload()
        $("#proofRegistrationUpload").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('proof_of_payment').files[0]);
        form_data.append("userUid", $("#userId").val());
        form_data.append("fileType", "proof_of_payment");

        $.ajax({
            type: "post",
            url: "/register/fileUpload",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                    $(".PaymentLabel").empty().append('UPLOADED')
                    $("#proofRegistrationSuccess").removeClass('hidden')
                    $(".PaymentLabel").addClass('greenPayment')
                }
            },
        });
    })
    

    
</script>

@endsection
