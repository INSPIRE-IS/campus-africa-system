@extends('layouts.app')

@section('content')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>

        .question-error {
            color: red;
        }
    </style>
    <div class="yellowGradientSmall">
        <div class="container">

            <nav class="navBar">
                <div class="leftMenu">
                    <a href="/login"><img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92"
                            height="91"></a>
                </div>

                <div class="rightMenu">

                    <a href="https://campusafrica.co.za/contact-us/" target="_blank"><button type="button"
                            class="contactButton">contact us</button></a>
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

        <form id="newStudents" class="form onlineForm" autocomplete="off" style="z-index: 20;">
            <br />
            <h1 class="formHeading">Applicant Details</h1>
            <br />

            <div class="form-field">
                <label>First Name * <span id="firstName_error" class="hidden" style="color:red"><small>This field is
                            required</small></span></label>
                <input type="text" name="firstName" id="firstName" class="required">
            </div>

            <div class="form-field">
                <label>Last Name * <span id="lastName_error" class="hidden" style="color:red"><small>This field is
                            required</small></span></label>
                <input type="text" name="lastName" id="lastName" class="required">
            </div>

            <div class="form-field">
                <label>Title * <span id="title_error" class="hidden" style="color:red"><small>This field is
                            required</small></span></label>
                <select name="title" id='title' class="required">
                    <option value="" disabled selected>Please select one</option>
                    <option value="Mr">Mr</option>
                    <option value="Ms">Ms</option>
                    <option value="Mrs">Mrs</option>
                    <option value="Miss">Miss</option>
                </select>
            </div>

            <div class="form-field">
                <label>Date of Birth * <span id="dob_error" class="hidden" style="color:red"><small>Not a valid date of
                            birth</small></span></label>
                <input type="date" name="dob" id="dob" class="required" max="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-field">
                <label>Cellphone Number * <span id="cell_no_error" class="hidden" style="color:red"><small>Not a valid
                            cellphone number</small></span><span id="cell_no_duplicate" class="hidden"
                        style="color:red"><small>. Duplicate detected!</small></span></label>
                <input type="text" name="userCellCode" id="userCellCode" style="width:18% !important;" value="+27">
                <input type="number" name="cell_no" id='cell_no' style="width:77% !important" class="required">
            </div>

            <div class="form-field">
                <label>Email Address * <span id="email_error" class="hidden" style="color:red"><small>Not a valid
                            email</small></span>
                    <span id="email_InUse" class="hidden" style="color:red"><small>Email Address already in
                            use</small></span></label>
                <input type="email" name="email" id="email" class="required">
            </div>

            <div class="form-field">
                <label>Alternative Email Address * <span id="email_alt_error" class="hidden" style="color:red"><small>Not a
                            valid email</small></span>
                    <span id="email_alt_InUse" class="hidden" style="color:red"><small>Email Address already in
                            use</small></span></label>
                <input type="email" name="email_alt" id="email_alt" class="required">
            </div>

            <div class="form-field">
                <label>Nationality * <span id="nationality_error" class="hidden" style="color:red"><small>This field is
                            required</small></span></label>
                <select name="nationality" id='nationality' class="required">
                    <option value="" selected disabled>Please select one</option>
                    @foreach ($lookups as $lookup)
                        @if ($lookup->group_id == '5')
                            <option value="{{ $lookup->lookup_name }}">{{ $lookup->lookup_name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-field" id="idField">
                <label>I.D Number * <span id="idNumber_error" class="hidden" style="color:red"><small>Not a valid ID
                            number</small></span>
                    <span id="idNumber_InUse" class="hidden" style="color:red"><small>ID Number already in use - please
                            <a href="<?php echo "https://$_SERVER[HTTP_HOST]/login"; ?>">login to your account</a> or email us at <a
                                href="mailto:info@campusafrica.co.za">info@campusafrica.co.za</a></small></span></label>
                <input type="text" name="idNumber" id="idNumber" class="required">
            </div>

            <div class="form-field hidden" id="passportField">
                <label>Passport Number * <span id="passport_error" class="hidden" style="color:red"><small>This field is
                            required</small></span>
                    <span id="passport_InUse" class="hidden" style="color:red"><small>Passport Number already in use -
                            please <a href="<?php echo "https://$_SERVER[HTTP_HOST]/login"; ?>">login to your account</a> or email us at <a
                                href="mailto:info@campusafrica.co.za">info@campusafrica.co.za</a></small></span>
                </label>
                <input type="text" name="passportNo" id="passportNo">
            </div>

            <div class="form-field">
                <label>Gender * <span id="gender_error" class="hidden" style="color:red"><small>This field is
                            required</small></span></label>
                <select name="gender" id='gender' class="required">
                    <option value="" disabled selected>Please select one</option>
                    <option value="1">Male</option>
                    <option value="2">Female</option>
                </select>
            </div>


            @foreach($questions as $question)
                <div style="width:100%; margin-top:20px;" >
                <label for="radio">{{ $question->question_text }} * <small style="font-size:12px;">Please select an option.</small></label>
                    @if($question->input_type === 'radio')
                    <div  
                    style="
                        display:flex;
                        flex-direction:row;
                        flex-wrap:wrap;
                        gap:8px;
                        margin-top:6px;
                        width:95%;
                    ">

                        @foreach($question->options as $option)

                    <div 
                        
                    style="
                        display:flex;
                        align-items:center;
                        justify-content:start;
                        gap:3px;
                        cursor:pointer;
                        background-color:#f0f0f0;
                        border-radius:7px;
                        padding:0px 4px;
                        flex:1 1 180px;">
                    <span style="width:14px;margin-top:6px;">
                        <input type="radio" 
                        class="required-question"
                        name="answers[{{ $question->id }}]" 
                        value="{{ $option->id }}"> 
                    </span>
                    
                    <span style="font-size:14px; font-weight:semi-bold; color:#000; width:100%;">
                        {{ $option->option_text }}
                    <div class="question-error hidden" style="color:red; font-size:12px;">
                        Please select an option.
                    </div>                        
                    </span>

                    </div>

                        @endforeach
                        
                        </div>                
                    @elseif($question->input_type === 'range')
                        <input type="range"
                            name="answers[{{ $question->id }}]"
                            min="{{ $question->min_value }}"
                            max="{{ $question->max_value }}"
                            step="{{ $question->step ?? 1 }}"
                            style=" width:98%"
                            class="required">
                    @endif
                </div>
            @endforeach
            <div class="form-field">
                
            </div>

            <div class="form-field-big">
                <label>Physical Address * <span id="userAddress1_error" class="hidden" style="color:red"><small>This
                            field is required</small></span></label>
                <input type="text" placeholder="Street No" name="userAddress1" id="userAddress1" style="width:98% "
                    class="required">
            </div>


            

            <div class="form-field-big" style="height:77px !important">
                <span id="userAddress2_error" class="hidden" style="color:red"><small>This field is
                        required</small></span>
                <input type="text" placeholder="Suburb" name="userAddress2" id="userAddress2" style=" width:98%"
                    class="required">
            </div>
            <div class="form-field" style="margin-top:-15px; height:60px !important">
                <span id="userAddressCity_error" class="hidden" style="color:red"><small>This field is
                        required</small></span>
                <input type="text" placeholder="City" name="userAddressCity" id='userAddressCity' class="required">
            </div>
            <div class="form-field" style="margin-top:-5px; height:60px !important">
                <span id="userAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal
                        code</small></span>
                <input type="number" placeholder="Postal Code" name="userAddressPostal" id="userAddressPostal"
                    class="required">
            </div>

            <div class="form-field">
                <label>Student Type * <span id="studentType_error" class="hidden" style="color:red"><small>This field is
                            required</small></span></label>
                <select name="studentType" id='studentType' class="required">
                    <option value="" selected disabled>Select a Student Type</option>
                    <option value="NSFAS" >NSFAS</option>
                    <option value="FUNZA" >FUNZA</option>
                    <option value="GCRA" >GCRA</option>
                    <option value="MQA" >MQA</option>
                    <option value="Bursary" >Bursary</option>
                    <option value="Cash / Direct" >Cash</option>
                </select>
            </div>

            <div class="form-field">
                <label>Year *<span id="year_error" class="hidden" style="color:red"><small>This
                            field is
                            required </small></span></label>
                <select name="year" id="year" class="required">
                    {{-- <option value="" disabled selected>Please select one</option> --}}
                    @foreach ($lookups->where('group_id', 4)->sortByDesc('lookup_name') as $lookup)
                        <option value="{{ $lookup->lookup_name }}"
                            {{ $application_year == $lookup->lookup_name ? 'selected' : 'disabled' }}>
                            {{ $lookup->lookup_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-field">
                <label>Institution of Study * <span id="institStudy_error" class="hidden" style="color:red"><small>This
                            field is required</small></span></label>
                <select name="institStudy" id="institStudy" class="required">
                    <option value="" selected disabled>Choose one</option>
                    @foreach ($lookups as $lookup)
                        @if ($lookup->group_id == '1')
                            <option value="{{ $lookup->id }}">{{ $lookup->lookup_name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-field">
                <label>Student Number * <span id="studNumber_error" class="hidden" style="color:red"><small>This field is
                            required</small></span></label>
                <input type="text" name="studNumber" id="studNumber" class="required">
            </div>

            <div class="form-field">
                <label>Year of study * <span id="yearOfStudy_error" class="hidden" style="color:red"><small>This field is
                            required</small></span></label>
                <select name="yearOfStudy" id="yearOfStudy" class="required">
                    <option value="" selected disabled>Please select one</option>
                    @foreach ($lookups as $lookup)
                        @if ($lookup->group_id == '6')
                            <option value="{{ $lookup->lookup_name }}">{{ $lookup->lookup_name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-field">
                <label>Faculty * <span id="faculty_error" class="hidden" style="color:red"><small>This field is
                            required</small></span></label>
                <select name="faculty" id="faculty" class="required">
                    <option value="" disabled selected>Choose one</option>
                    @foreach ($lookups as $lookup)
                        @if ($lookup->group_id == '3')
                            <option value="{{ $lookup->id }}">{{ $lookup->lookup_name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-field">
                <label>Residence * <span id="property_error" class="hidden" style="color:red"><small>This field is
                            required</small></span></label>
                <select name="property" id='property' class="required">
                    <option value="" selected disabled>Select a Residence</option>
                    @foreach ($residences as $property)
                        <option value="{{ $property->id }}">{{ $property->residence }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-field">
                <label>Room Type * <span id="roomType_error" class="hidden" style="color:red"><small>This field is
                            required</small></span></label>
                <select name="roomType" id='roomType' class="required">
                    <option value="" selected disabled>Select a Room Type</option>
                </select>
            </div>

            <div class="form-field">
                <label>Password * <span id="password_error" class="hidden" style="color:red"><small>This field is
                            required</small></span></label>
                <input type="password" name="password" id="password" class="required">
                <i class="far fa-eye" id="togglePassword" style="margin-left:-35px;"></i>
            </div>
            <div class="form-field" style="height:120px !important">
                <label>Confirm Password * <span id="confirm_password_error" class="hidden" style="color:red"><small>This
                            field is required</small></span> <span id="confirm_password_match_error" class="hidden"
                        style="color:red"><small>Passwords don't match</small></span></label>
                <input type="password" name="confirm_password" id="confirm_password" class="required"
                    onKeyUp="match()">
                <i class="far fa-eye" id="togglePasswordConfirm" style="margin-left:-35px;"></i>
            </div>

            <button type="button" class="detailsChange" id="submitForm"
                style="text-transform:uppercase; background-color:#e74f26;">Proceed to next step</button>


        </form>

    </div>
    <input type="hidden" id="firstRoom" value="" />
    <!-- <div class="footerClearfix"></div>
                                        <div class="footerClearfix"></div>-->
                                         
    


    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function match() {
            let password = $("#password").val()
            let confirmed = $("#confirm_password").val();

            if (password !== confirmed) {
                $("#confirm_password_match_error").removeClass('hidden');
                $("#confirm_password").addClass('fieldError')
            } else {
                $("#confirm_password_match_error").addClass('hidden')
                $("#confirm_password").removeClass('fieldError')
            }
        }

        var d = new Date()
        month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            yearBack = (d.getFullYear() - 100);

        document.getElementById("dob").setAttribute("min", yearBack + "-" + month + "-" + day);

        if ("{{ $propertyID }}" !== "0") {

            //Find Room to map Property & Room
            $.ajax({
                type: "post",
                url: "/get-room-maps",
                data: {
                    residence: "{{ $propertyID }}",
                    roomType: "{{ $roomType }}"
                },

                success: function(data) {
                    console.log(data[1])
                    $("#firstRoom").val(data[1]);
                    $("#property").val(data[0]).trigger("change")
                },
            });
        }

        $("#property").on('change', function() {
            $("#roomType").empty().append('<option value="" selected disabled>Loading</option>')

            $.ajax({
                type: "post",
                url: "/get-room-ids",
                data: {
                    residence: $("#property").val()
                },

                success: function(data) {
                    $("#roomType").empty().append(
                        '<option value="" selected disabled>Select a Room Type</option>')
                    data.forEach(function(index) {
                        var selected = ""
                        var disabled = ""
                        var disabledRoom = ""

                        if ($("#firstRoom").val() !== "") {
                            var selected = $("#firstRoom").val() == index.id ? "selected" : ""
                        }

                        if (index.online == '0') {
                            var disabled = "disabled"
                            var disabledRoom = "(Fully Booked)"
                            var selected = ""
                        }


                        $("#roomType").append('<option value="' + index.id + '" ' + selected +
                            ' ' + disabled + '>' + index.name + ' ' + disabledRoom +
                            '</option>')
                    })

                    $("#firstRoom").val('');
                },
            });
        })

        $("input").blur(function() {
            let fieldId = $(this)[0]["id"]

            // var required = document.getElementById(fieldId).required
            var required = $("#" + fieldId).hasClass('required')

            if ($("#" + fieldId).val() == "" && required == true) {
                $("#" + $(this)[0]["id"]).addClass('fieldError')
                $("#" + $(this)[0]["id"] + "_error").removeClass('hidden')
            } else {
                $("#" + $(this)[0]["id"]).removeClass('fieldError')
                $("#" + $(this)[0]["id"] + "_error").addClass('hidden')
            }

        });

        $("select").blur(function() {
            let fieldId = $(this)[0]["id"]

            var required = $("#" + fieldId).hasClass('required')

            if (($("#" + fieldId).val() == null || $("#" + fieldId).val() == "") && required == true) {
                $("#" + $(this)[0]["id"]).addClass('fieldError')
                $("#" + $(this)[0]["id"] + "_error").removeClass('hidden')
            } else {
                $("#" + $(this)[0]["id"]).removeClass('fieldError')
                $("#" + $(this)[0]["id"] + "_error").addClass('hidden')
            }
        });

        $("#cell_no").on('keyup blur', function() {
            cell = $("#cell_no").val()
            ValidCell(cell, "cell_no", "cell_no_error", $("#userCellCode").val());
        })
        $("#userCellCode").on('keyup blur', function() {
            cell = $("#cell_no").val()
            ValidCell(cell, "cell_no", "cell_no_error", $("#userCellCode").val());
        })

        $("#userAddressPostal").on('keyup blur', function() {
            postal = $("#userAddressPostal").val()

            if (/\b\d{4}\b/g.test(postal)) {
                $("#userAddressPostal").removeClass("fieldError")
                $("#userAddressPostal_error").addClass('hidden')
            } else {
                $("#userAddressPostal").addClass("fieldError")
                $("#userAddressPostal_error").removeClass('hidden')
            }
        })

        function ValidCell(cell, fieldId, fieldError, code) {

            if (code === "+27") {
                if (/^[1-9][0-9]{8}$/.test(cell)) {
                    $("#" + fieldId).removeClass("fieldError")
                    $("#" + fieldError).addClass('hidden')
                } else {
                    $("#" + fieldId).addClass("fieldError")
                    $("#" + fieldError).removeClass('hidden')
                }
            } else {
                if (/^[+][0-9]{1,3}$/.test(code)) {
                    if (/^(0|[1-9][0-9]*)$/.test(cell)) {
                        $("#" + fieldId).removeClass("fieldError")
                        $("#" + fieldError).addClass('hidden')
                    } else {
                        $("#" + fieldId).addClass("fieldError")
                        $("#" + fieldError).removeClass('hidden')
                    }
                } else {
                    $("#" + fieldId).addClass("fieldError")
                    $("#" + fieldError).removeClass('hidden')
                }
            }

            // let cellPrefix = cell.slice(0, 3)
            // if(cellPrefix === "+27"){
            //     console.log(/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell))
            //     if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
            //         $("#"+fieldId).removeClass("fieldError")
            //         $("#"+fieldError).addClass('hidden')
            //     }else{
            //         $("#"+fieldId).addClass("fieldError")
            //         $("#"+fieldError).removeClass('hidden')
            //     }
            // }else if (/^((\+)[0-9]{1,3})[1-9](\d{2}){4}$/.test(cell)) {
            //     $("#"+fieldId).removeClass("fieldError")
            //     $("#"+fieldError).addClass('hidden')
            // }else{
            //     $("#"+fieldId).addClass("fieldError")
            //     $("#"+fieldError).removeClass('hidden')
            // }
        }

        function ValidID(idNumber, idField, errorField) {
            let idArr = Array.from(idNumber);
            idArr.reverse()
            let newArr = [];

            for (let i = 0; i < idArr.length; i++) {
                if ((i % 2) !== 0) {
                    let newValue = idArr[i] * 2
                    if (newValue > 9) {
                        value1 = (newValue.toString()).substr(0, 1)
                        value2 = (newValue.toString()).substr(1, 2)
                        newValue = parseInt(value1) + parseInt(value2)

                    }
                    newArr.push(newValue)
                } else {
                    newArr.push(idArr[i])
                }
            }


            let sum = 0;
            for (let i = 0; i < newArr.length; i++) {
                sum += parseInt(newArr[i]);
            }

            let finalSum = sum / 10

            console.log(Number.isInteger(finalSum) && idNumber !== "")

            if (Number.isInteger(finalSum) && idNumber !== "") {
                $("#" + idField).removeClass("fieldError")
                $("#" + errorField).addClass('hidden')
            } else {
                $("#" + idField).addClass("fieldError")
                $("#" + errorField).removeClass('hidden')
            }
        }

        $("#idNumber").on('keyup blur', function() {
            let idNumber = $("#idNumber").val();
            if (idNumber.length == 13) {
                ValidID(idNumber, "idNumber", "idNumber_error")
            } else {
                $("#idNumber").addClass("fieldError")
                $("#idNumber_error").removeClass('hidden')
            }

            let element = document.getElementById('gender');

            if (idNumber != "" && parseFloat(idNumber.substr(6, 1)) >= 0 && parseFloat(idNumber.substr(6, 1)) <=
                4) {
                element.value = 2;
            } else if (idNumber != "" && parseFloat(idNumber.substr(6, 1)) >= 5 && parseFloat(idNumber.substr(6,
                    1)) <= 9) {
                element.value = 1;
            }

            // if ($("#studentType").val() == "NSFAS") {
            //     nsfasProp()
            // } else {
            //     allowAllProp()
            // }

            allowAllProp();

            cecheckIDEmail();
        })

        $("#dob").on('keyup blur', function() {
            let dob = $("#dob").val();

            var d = new Date()
            month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();
            yearBack = (d.getFullYear() - 100);

            if (dob < yearBack + "-01-01" || dob > year + "-" + month + "-" + day) {
                $("#dob").addClass("fieldError")
                $("#dob_error").removeClass('hidden')
            } else {
                $("#dob").removeClass("fieldError")
                $("#dob_error").addClass('hidden')
            }
        })

        function isEmail(email) {
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return regex.test(email);
        }

        $("#email").on('keyup blur', function() {
            if (isEmail($("#email").val())) {
                $("#email").removeClass("fieldError")
                $("#email_error").addClass('hidden')

                cecheckIDEmail();
            } else {
                $("#email").addClass("fieldError")
                $("#email_error").removeClass('hidden')
            }


        })

        $("#email_alt").on('keyup blur', function() {
            if (isEmail($("#email_alt").val())) {
                $("#email_alt").removeClass("fieldError")
                $("#email_alt_error").addClass('hidden')

                cecheckIDEmail();
            } else {
                $("#email_alt").addClass("fieldError")
                $("#email_alt_error").removeClass('hidden')
            }


        })

        $("#passportNo").on('keyup blur', function() {
            $.ajax({
                type: "post",
                url: "/passport-check_reg",
                data: {
                    passportNo: $("#passportNo").val(),
                },

                success: function(data) {

                    if ($("#passportNo").val() !== "") {
                        if (data[1] == false) {
                            $("#passport_InUse").removeClass('hidden')
                            $("#passportNo").addClass('fieldError')
                        } else {
                            $("#passport_InUse").addClass('hidden')
                            $("#passportNo").removeClass('fieldError')
                            $("#passportField").removeClass('idHeight');
                        }
                    }
                }
            });
        });

        function cecheckIDEmail() {
            $.ajax({
                type: "post",
                url: "/email-check-idNo",
                data: {
                    email: $("#email").val(),
                    idNumber: $("#idNumber").val(),
                },

                success: function(data) {
                    console.log(data)

                    if (data[0] == "done") {

                        if ($("#email").val() !== "") {
                            if (data[1] == false) {
                                $("#email_InUse").removeClass('hidden')
                                $("#email").addClass('fieldError')

                                Swal.fire(
                                    'Email In Use',
                                    'This Email address is already registered. Please Login to Continue',
                                    'error'
                                )

                            } else {
                                $("#email_InUse").addClass('hidden')
                                $("#email").removeClass('fieldError')
                            }
                        }

                        if ($("#idNumber").val() !== "") {
                            if (data[2] == false) {
                                $("#idNumber_InUse").removeClass('hidden')
                                $("#idNumber").addClass('fieldError')
                            } else {
                                $("#idNumber_InUse").addClass('hidden')
                                $("#idNumber").removeClass('fieldError')
                            }
                        }


                    }
                },
            });
        }

        $("#nationality").on('change', function() {
            if ($("#nationality").val() == "South Africa") {
                $("#passportField").addClass('hidden')
                $("#idField").removeClass('hidden')

                $("#passportNo").removeClass("required")
                $("#idNumber").addClass("required")
                $("#passportNo").removeClass("fieldError")
                // document.getElementById('gender').disabled = true;
            } else {
                $("#idField").addClass('hidden')
                $("#passportField").removeClass('hidden')

                $("#passportNo").addClass("required")
                $("#idNumber").removeClass("required")
                $("#idNumber").removeClass("fieldError")
                // document.getElementById('gender').disabled = false;
            }
        })

        $("#togglePassword").on('click', function() {
            var x = document.getElementById("password");
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        })

        $("#togglePasswordConfirm").on('click', function() {
            var x = document.getElementById("confirm_password");
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        })

        $("#studentType").on('change', function() {
            // if ($("#studentType").val() == "NSFAS") {
            //     nsfasProp()
            // } else {
            //     allowAllProp()
            // }
            allowAllProp();
        })

        $("#institStudy").on('change', function() {
            // if ($("#studentType").val() == "NSFAS") {
            //     nsfasProp()
            // }
            allowAllProp();
        })

        $("#gender").on('change', function() {
            // if($("#studentType").val() !== ""){
            //     if ($("#studentType").val() == "NSFAS") {
            //         nsfasProp()
            //     } else {
            //         allowAllProp()
            //     }
            // }else{
            //     allowAllProp()
            // }

            allowAllProp();
        })

        function allowAllProp() {
            $("#property").empty().append('<option value="" selected disabled>Loading</option>')

            $.ajax({
                type: "post",
                url: "/get-nsfas-properties-online",
                data: {
                    institStudy: $("#institStudy").val()
                },

                success: function(data) {

                    allprops = data[0];

                    inSelectedCash = data[1];
                    inSelectedBursary = data[2];
                    inSelectedNsfas = data[3];

                    $("#property").empty().append(
                        '<option value="" selected disabled>Select a Residence</option>')

                    let text = "";
                    let allow = 1;

                    if ($("#gender").val() == "") {
                        text = "(No gender selected)"
                        allow = 0
                    }

                    if ($("#institStudy").val() == "" || $("#institStudy").val() == null) {
                        allprops.forEach(function(index) {
                            $("#property").append('<option value="' + index.id + '" disabled>' + index
                                .residence + ' (No Institution Selected)</option>')
                        })
                    } else {
                        allprops.forEach(function(index) {

                            if ($("#gender").val() == 1 && index.male === null && index.female === 1) {
                                text = "(Female Only)"
                                allow = 0
                            } else if ($("#gender").val() == 2 && index.male === 1 && index.female ===
                                null) {
                                text = "(Male Only)"
                                allow = 0
                            } else if (index.male === null && index.female === null) {
                                text = "(Not Allowed)"
                                allow = 0
                            } else {
                                allow = 1
                                text = "";
                            }

                            if ($("#studentType").val() == "Cash / Direct" && inSelectedCash.includes(
                                    index.id)) {
                                if (allow == 0) {
                                    $("#property").append('<option value="' + index.id + '" disabled>' +
                                        index.residence + ' ' + text + '</option>')
                                } else {
                                    $("#property").append('<option value="' + index.id + '">' + index
                                        .residence + '</option>')
                                }
                            } else if ($("#studentType").val() == "Bursary" && inSelectedBursary
                                .includes(index.id)) {
                                if (allow == 0) {
                                    $("#property").append('<option value="' + index.id + '" disabled>' +
                                        index.residence + ' ' + text + '</option>')
                                } else {
                                    $("#property").append('<option value="' + index.id + '">' + index
                                        .residence + '</option>')
                                }
                            } else if (($("#studentType").val() == "NSFAS" || $("#studentType").val() === "FUNZA" || $("#studentType").val() == "GCRA" || $("#studentType").val() == "MQA") && inSelectedNsfas.includes(
                                    index.id)) {
                                if (allow == 0) {
                                    $("#property").append('<option value="' + index.id + '" disabled>' +
                                        index.residence + ' ' + text + '</option>')
                                } else {
                                    $("#property").append('<option value="' + index.id + '">' + index
                                        .residence + '</option>')
                                }
                            } else {
                                $("#property").append('<option value="' + index.id + '" disabled>' +
                                    index.residence + ' (Not Allowed)</option>')
                            }

                        })
                    }
                },
            });

            // $.ajax({
            //     type: "get",
            //     url: "/get-all-properties-online",

            //     success: function(data) {
            //         $("#property").empty().append('<option value="" selected disabled>Select a Residence</option>')

            //         let text = "";
            //         let allow = 1;

            //         if($("#gender").val() == ""){
            //             text = "(No gender selected)"
            //             allow = 0
            //         }
            //         data.forEach(function(index) {
            //             if($("#gender").val() == 1 && index.male === null && index.female === 1){
            //                 text = "(Female Only)"
            //                 allow = 0
            //             }else if($("#gender").val() == 2 && index.male === 1 && index.female === null){
            //                 text = "(Male Only)"
            //                 allow = 0
            //             }else if(index.male === null && index.female === null){
            //                 text = "(Not Allowed)"
            //                 allow = 0
            //             }else{
            //                 allow = 1
            //                 text = "";
            //             }

            //             if(allow == 0){
            //                 $("#property").append('<option value="' + index.id + '" disabled>' + index.residence + ' '+text+'</option>')
            //             }else{
            //                 $("#property").append('<option value="' + index.id + '">' + index.residence + '</option>')
            //             }
            //         })
            //     },
            // });
        }

        // function nsfasProp(){
        //     $("#property").empty().append('<option value="" selected disabled>Loading</option>')

        //     $.ajax({
        //         type: "post",
        //         url: "/get-nsfas-properties-online",
        //         data: {institStudy: $("#institStudy").val()},

        //         success: function(data) {

        //             allprops = data[0];
        //             allowed = data[1];
        //             $("#property").empty().append('<option value="" selected disabled>Select a Residence</option>')

        //             let text = "";
        //             let allow = 1;

        //             if($("#gender").val() == ""){
        //                 text = "(No gender selected)"
        //                 allow = 0
        //             }

        //             if($("#institStudy").val() == "" || $("#institStudy").val() == null){
        //                 allprops.forEach(function(index) {
        //                     $("#property").append('<option value="' + index.id + '" disabled>' + index.residence +' (No Institution Selected)</option>')
        //                 })
        //             }else{
        //                 allprops.forEach(function(index) {

        //                     if($("#gender").val() == 1 && index.male === null && index.female === 1){
        //                         text = "(Female Only)"
        //                         allow = 0
        //                     }else if($("#gender").val() == 2 && index.male === 1 && index.female === null){
        //                         text = "(Male Only)"
        //                         allow = 0
        //                     }else if(index.male === null && index.female === null){
        //                         text = "(Not Allowed)"
        //                         allow = 0
        //                     }else{
        //                         allow = 1
        //                         text = "";
        //                     }

        //                     if(allowed.includes(index.id)){
        //                         if(allow == 0){
        //                             $("#property").append('<option value="' + index.id + '" disabled>' + index.residence + ' '+text+'</option>')
        //                         }else{
        //                             $("#property").append('<option value="' + index.id + '">' + index.residence + '</option>')
        //                         }
        //                     }else{
        //                         $("#property").append('<option value="' + index.id + '" disabled>' + index.residence +' (Not Allowed)</option>')
        //                     }
        //                 })
        //             }
        //         },
        //     });
        // }


        $("#submitForm").on('click', function() {

            //Check if any hidden fields are required, remove required check
            $('.hidden .fieldError').removeClass('fieldError').val('');

            var req = 1;


            $('.required').each(function() {
                if ($(this).val() === "" || $(this).val() === null) {
                    req = 0

                    $(this).addClass("fieldError")
                    id = $(this)[0]["id"]
                    $("#" + id + "_error").removeClass("hidden")
                }
            })

            ValidCell($("#cell_no").val(), "cell_no", "cell_no_error", $("#userCellCode").val());

            if ($('.fieldDuplicate').length > 0) {
                req = 0;
            }

            if ($('.fieldError').length > 0) {
                req = 0;
            }
            // Validate multiple-choice questions
            $('.required-question').each(function() {
                let groupName = $(this).attr('name');
                if ($(`input[name="${groupName}"]:checked`).length === 0) {
                    req = 0;
                    $(this).closest('div').find('.question-error').removeClass('hidden');
                } else {
                    $(this).closest('div').find('.question-error').addClass('hidden');
                }
            });
            let answers = {};
            $("input[name^='answers']:checked").each(function () {
                let questionId = $(this).attr("name").match(/\d+/)[0];
                answers[questionId] = $(this).val();
            }); // 9903014800086

            if (req == 0) {
                Swal.fire(
                    'Blank Fields',
                    'Please fill in all Required Fields!',
                    'error'
                )

            } else {
                let total = 1
                $('.fieldError').each(function() {
                    if ($(this).val() === "") {
                        total = 0

                        $(this).addClass("fieldError")
                        id = $(this)[0]["id"]
                        $("#" + id + "_error").removeClass("hidden")
                    }
                })

                if (total == 0) {
                    Swal.fire(
                        'Blank Fields',
                        'Please fill in all Required Fields!',
                        'error'
                    )

                } else {

                    $("#submitForm").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

                    Swal.fire({
                        title: 'Please accept the POPI Act to continue',
                        text: "Consolidated Urban Management Corporation (Pty) Ltd and it's associated companies, which include the Campus Africa & 49 Jorissen brands, is compliant with the Protection of Personal Information Act (POPIA), also known as the POPI Act, which is mandatory for all organisations in South Africa. POPI makes it illegal for us to collect, use or store the personal information of customers and businesses unless it is done in accordance with the laws and regulations prescribed in the POPI Act.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Accept'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "post",
                                url: "/registration_step_one",
                                data: {
                                    firstName: $("#firstName").val(),
                                    lastName: $("#lastName").val(),
                                    title: $("#title").val(),
                                    dob: $("#dob").val(),
                                    cell_no: $("#cell_no").val(),
                                    userCellCode: $("#userCellCode").val(),
                                    email: $("#email").val(),
                                    email_alt: $("#email_alt").val(),
                                    idNumber: $("#idNumber").val(),
                                    nationality: $("#nationality").val(),
                                    passportNo: $("#passportNo").val(),
                                    institStudy: $("#institStudy").val(),
                                    studNumber: $("#studNumber").val(),
                                    faculty: $("#faculty").val(),
                                    year: $("#year").val(),
                                    userAddress1: $("#userAddress1").val(),
                                    userAddress2: $("#userAddress2").val(),
                                    userAddressCity: $("#userAddressCity").val(),
                                    userAddressPostal: $("#userAddressPostal").val(),
                                    studentType: $("#studentType").val(),
                                    yearOfStudy: $("#yearOfStudy").val(),
                                    property: $("#property").val(),
                                    roomType: $("#roomType").val(),
                                    password: $("#password").val(),
                                    gender: $("#gender").val(),
                                    answers: answers,
                                },
                                success: function(data) {
                                    window.location.replace('/online-application-verify/' +
                                        data)
                                },
                                error: function(textStatus, errorThrown) {

                                    let response = JSON.parse(textStatus.responseText)

                                    $("#submitForm").empty().append('Proceed to next step');

                                    if (errorThrown == "error") {
                                        Swal.fire(
                                            'invalid Email',
                                            response.errors.email[0],
                                            'error'
                                        )
                                    }
                                }
                            });
                        } else {
                            $("#submitForm").empty().append('Proceed to next step');
                        }
                    });


                }
            }

        });
    </script>
@endsection
