@extends('layouts.app')

@section('content')

<div class="yellowGradientSmall">
    <div class="container">

        <nav class="navBar">
            <div class="leftMenu">
                <img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91">
            </div>

            <div class="rightMenu">
                
                <button type="button" class="contactButton">contact us</button>
                <button type="button" class="menuButton">Menu <i class="fa fa-bars"></i></button>
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
                                    <fieldset>
                                        <div class="form-card">
                                            <h2 class="fs-title">Applicant Details</h2>
                                            <br/>

                                            <div class="form-field">
                                                <label>First Name *</label>
                                                <input type="text" name="firstName" id="firstName" required>
                                            </div>

                                            <div class="form-field">
                                                <label>Last Name</label>
                                                <input type="text" name="lastName" id="lastName">
                                            </div>

                                            <div class="form-field">
                                                <label>Title</label>
                                                <select name="title" id='title'>
                                                    <option value="1">Mr</option>
                                                    <option value="2">Ms</option>
                                                    <option value="3">Mrs</option>
                                                    <option value="4">Miss</option>
                                                    <option value="5">Dr</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Date of Birth</label>
                                                <input type="date" name="dob" id="dob">
                                            </div>

                                            <div class="form-field">
                                                <label>Cellphone Number</label>
                                                <input type="text" name="cell_no" id="cell_no">
                                            </div>

                                            <div class="form-field">
                                                <label>Email Address *</label>
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
                                                <label>I.D Number</label>
                                                <input type="text" name="idNumber" id="idNumber">
                                            </div>

                                            <div class="form-field">
                                                <label>Nationality</label>
                                                <select name="nationality" id='nationality'>
                                                    <option value="1">South African</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Passport Number</label>
                                                <input type="text" name="passportNo" id="passportNo">
                                            </div>

                                            <div class="form-field">
                                                <label>Institution of Study</label>
                                                <select name="institStudy" id="institStudy">
                                                    <option value="1">Choose one</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Student Number</label>
                                                <input type="text" name="studNumber" id="studNumber">
                                            </div>

                                            <div class="form-field">
                                                <label>Insitution Campus</label>
                                                <select name="institCampus" id="institCampus">
                                                    <option value="1">Choose one</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Faculty</label>
                                                <select name="faculty" id="faculty">
                                                    <option value="1">Choose one</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Year</label>
                                                <select name="year" id="year">
                                                    <option value="2022">2022</option>
                                                    <option value="2021">2021</option>
                                                    <option value="2020">2020</option>
                                                    <option value="2019">2019</option>
                                                    <option value="2018">2018</option>
                                                    <option value="2017">2017</option>
                                                </select>
                                            </div>
                                            
                                            
                                        </div> 
                                        <input type="button" name="next" class="next action-button" value="proceed To Next Step" id="step1" />
                                    </fieldset>
                                    <fieldset>
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
                                    <fieldset>
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
                                                    <option value="1">Mr</option>
                                                    <option value="2">Ms</option>
                                                    <option value="3">Mrs</option>
                                                    <option value="4">Miss</option>
                                                    <option value="5">Dr</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>I.D Number</label>
                                                <input type="text" name="kIdNumber" id="kIdNumber">
                                            </div>

                                            <div class="form-field-big">
                                                <label>Physical Address</label>
                                                <input type="text" placeholder="Street No" name="kAddress1" id="kAddress1">
                                            </div>
                                            <div class="form-field-big">
                                                <input type="text" placeholder="Suburb" name="kAddress2" id="kAddress2">
                                            </div>
                                            <div class="form-field">
                                                <input type="text" placeholder="City" name="kAddressCity" id='kAddressCity'>
                                            </div>
                                            <div class="form-field">
                                                <input type="text" placeholder="postal Code" name="kAddressPostal" id="kAddressPostal">
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
                                    <fieldset>
                                        <div class="form-card">

                                            <div class="form-field-app" style="width:100%; text-align:center; display:flex; justify-content: center;">
                                                <label style="font-size:12px; line-height:38px;">Application Type &nbsp;&nbsp;&nbsp;&nbsp;</label>
                                                <select name="kTitle" style="width:30% !important">
                                                    <option value="1">Cash</option>
                                                </select>
                                            </div>

                                            <br/><br/>

                                            <div>
                                                <div class="cards">
                                                    <div class="whiteYellowCard" style="margin-right:30px">
                                                        <h2 class="cardHeader">Booking<br/>Documents</h2>

                                                        <div class="cardBody">
                                                            <div class="uploadSection" id="copyIdCard">
                                                                <h3 class="uploadName">I.D COPY</h3>
                                                                <div class="uploadFileSection">
                                                                <label for="copyID" id="copyIdUpload">
                                                                    <i class="fas fa-file-upload" ></i>
                                                                </label>
                                                                <input type="file" id="copyID" hidden/>
                                                                <label id="copyIdSucess" style="cursor: pointer" class="successLabel hidden">UPLOADED</label>
                                                                    
                                                                </div>
                                                            </div>

                                                            <br/>
                                                            
                                                            <div class="uploadSection">
                                                                <h3 class="uploadName" style="color:#9a9a9a">BOOKING FEE PROOF OF PAYMENT</h3>
                                                                <div class="uploadFileSection">
                                                                    <i class="fas fa-file-upload" style="color:rgba(231, 79, 38, 0.5);"></i>
                                                                    <label for="bookingFee" style="cursor: context-menu" class="uploadLabel">NEXT STEP</label>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        
                                                    </div>

                                                    <div class="whiteYellowCard">
                                                        <h2 class="cardHeader" style="height:80px;">Application</h2>
                                                        
                                                        <div class="cardBody">
                                                            <div class="uploadSection" id="copyStudentCard">
                                                                <h3 class="uploadName">COPY OF STUDENT CARD</h3>
                                                                <div class="uploadFileSection">
                                                                    <label for="studentCard" id="studentCardUpload">
                                                                        <i class="fas fa-file-upload" ></i>
                                                                    </label>
                                                                    <input type="file" id="studentCard" hidden/>
                                                                    <label id="copyStudCardSucess" style="cursor: pointer" class="successLabel hidden">UPLOADED</label>
                                                                    
                                                                </div>
                                                            </div>

                                                            <br/>
                                                            
                                                            <div class="uploadSection" id="copyBankStatement">
                                                                <h3 class="uploadName">3 MONTHS BANK STATEMENTS</h3>
                                                                <div class="uploadFileSection">
                                                                    <label for="bankStatement" id="bankStatementUpload">
                                                                        <i class="fas fa-file-upload" ></i>
                                                                    </label>
                                                                    <input type="file" id="bankStatement" hidden/>
                                                                    <label id="bankStatementSuccess" style="cursor: pointer" class="successLabel hidden">UPLOADED</label>
                                                                    
                                                                </div>
                                                            </div>

                                                            <br/>
                                                            
                                                            <div class="uploadSection" id="copyProofRegistration">
                                                                <h3 class="uploadName">PROOF OF REGISTRATION</h3>
                                                                <div class="uploadFileSection">
                                                                    <label for="proofRegistration" id="proofRegistrationUpload">
                                                                        <i class="fas fa-file-upload" ></i>
                                                                    </label>
                                                                    <input type="file" id="proofRegistration" hidden/>
                                                                    <label id="proofRegistrationSuccess" style="cursor: pointer" class="successLabel hidden">UPLOADED</label>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="button" name="previous" class="previous action-button-previous" value="Back to previous step" id="back4" onClick="PrevStep('back4')" /> 
                                        <input type="button" name="next" class="next action-button" value="Pay booking fee" id="step4" onClick="NextStep('step4')" />

                                        <div class="clearSpaceLogin"></div>
                                        <div class="clearSpaceLogin"></div>

                                        <div class="redBannerNotice">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <p>Please note that you can upload these documents via your online profile at a later date, however delays may cause you to not get the room you want! Your booking fee is processed in the next step; and you can upload proof of payment immediately or via your profile later.</p>
                                        </div>
                                    </fieldset>
                                    <fieldset>
                                        <div class="form-card">
                                            
                                        <div class="cards">
                                            <div class="whiteCard">

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
                                                    <span id="dispInstitCampus"></span><br/>
                                                    <span id="dispFaculty"></span><br/>
                                                    <span id="dispYear"></span></p>
                                                    
                                            </div>

                                            <div class="whiteCard" style="margin-left:11px;">
                                                <h4 class="cardSubHeaderGrey">Braamlofts</h4>
                                                <h2 class="cardHeader" >Single Room</h2>
                                                <h4 class="cardSubHeaderGrey">Room 23 Floor 2</h4>
                                            </div>
                                        </div>

                                        <br/>
                                        <div class="clearSpaceLogin"></div>
                                        <div class="footerClearfix"></div>

                                        <div class="paymentSection">

                                            <div class="form-field">
                                                <h1 class="header">Payment Information</h1>
                                            </div>

                                            <div class="form-field">
                                                <label >Payment Type &nbsp;&nbsp;&nbsp;&nbsp;</label>
                                                <select name="paymentType" >
                                                    <option value="1">Credit card</option>
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
                                                        <h4 class="cardSubHeaderGrey">Booking Deposit</h4>
                                                        <h2 class="cardHeader" >R5000</h2>
                                                        <h4 class="cardSubHeaderGrey">Your application deposit is deducted from the total of the room deposit required to make this booking.</h4>
                                                        
                                                    </div>
                                                    <div class="redBottom flex">
                                                            <i class="fas fa-clock"></i>
                                                            <p>Please note that your booking will only be reserved for 24hrs without payment</p>
                                                        </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="paymentTwo hidden">
                                            <h1 class="header">Payment Information</h1>
                                            <br/>
                                            <p class="cardSubHeaderGrey">Thank you for your payment. In order to speed up the process, please upload a proof of payment document below or via your online profile as soon as possible. Your room will only be reserved for 24 hours without payment.</p>
                                            <br/><br/>

                                            <label for="proof_of_payment" class="PaymentLabel"> UPLOAD PROOF OF PAYMENT</label>
                                            <input type="file" id="proof_of_payment" hidden/>
                                        </div>

                                        <div class="clearSpaceLogin"></div>
                                        <div class="footerClearfix"></div>

                                        <input type="button" name="previous" class="previous action-button-previous" value="Back to previous step" id="back5" onClick="PrevStep('back5')" /> 
                                        <input type="button" name="next" class="next action-button" value="Pay R2500.00 booking fee" onClick="PayFee()" />

                                        </div>
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

    $("#step1").on('click', function(){

        $("#step1").val("Processing");

        if($("#password").val() !== "" && $("#password").val() === $("#confirmPassword").val()){

            if($("#firstName").val() !== "" && $("#email").val() !== ""){

                if($("#userId").val() === ""){
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
                            institCampus: $("#institCampus").val(), 
                            faculty: $("#faculty").val(), 
                            year: $("#year").val() 
                        },

                        success: function (data) {
                            console.log(data.id)
                            $("#userId").val(data.id);
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
                            $("#dispInstitCampus").empty().append($("#institCampus").val());
                            $("#dispFaculty").empty().append($("#faculty").val());
                            $("#dispYear").empty().append($("#year").val());
                            
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
                            institCampus: $("#institCampus").val(), 
                            faculty: $("#faculty").val(), 
                            year: $("#year").val() 
                        },

                        success: function (data) {
                            console.log(data.id)
                            $("#userId").val(data.id);
                            NextStep("step1")
                        },
                    });
                }
                
                
            }else{
                $("#firstName").addClass('errorBorder')
                $("#firstName").focus();
                $("#email").addClass('email')
                $("#email").focus();
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
