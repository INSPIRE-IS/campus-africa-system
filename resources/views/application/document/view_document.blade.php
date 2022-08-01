<script src="//mozilla.github.io/pdf.js/build/pdf.js"></script>
<div class="modal-content" style="width: @if(!empty($file) && !empty($file->fileName)) {{ "99%" }} @else @if($agent->isMobile()) {{ "99%" }} @else {{ "50%" }} @endif @endif">
    <span class="close">&times;</span>

    <style>
        .panes {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }
        #the-canvas {
            border: 1px solid black;
        }

        .left_pane {
            width: 30%;
            padding-right: 20px;
        }

        .right_pane {
            flex: 70%;
        }

        .noedit, .disablededit {
            background-color: white !important;
            outline: none;
        }

        .edit_info input:read-only, .edit_info select:read-only {
            opacity: 1;
        }

        .edit_info input, .edit_info select {
            padding: 5px;
        }

        .edit_info select {
            background-color: #f0f0f0;
            border-radius: 7px;
            border: none;
            width: 65%;        
        }

        .form-field {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .form-field span {
            width: 40%;
        }

        .form-field input, .form-field select {
            width: 60%;
        }
        
    </style>

    <div class="@if(!empty($file) && !empty($file->fileName)) {{ "panes" }} @endif">
        <div class="@if(!empty($file) && !empty($file->fileName)) {{ "left_pane" }} @endif">
            <h2 id="modalHeader">{{$popupTitle}}</h2>
            @if (!empty($file->uploaded_by) && !empty($file->created_at))
                <small>Uploaded by {{$file->uploaded_by}} at {{($file->created_at->format("H|m"))}} on the {{($file->created_at->format("d|m|Y"))}}</small>
            @else
                <small>Uploaded by {{$file->uploaded_by}}</small>
            @endif
            <br/>
            
            <input type="hidden" id="FileIdForApproval" value="{{$file->id}}">
            <input type="hidden" id="FileNameForApproval" value="{{$file->fileId}}">
            
            <!-- && $data_approved == 1 -->
            <!-- && $data_approved == 1 -->
            @if(($file->fileId !== "bankStatement" && $file->fileId !== "bursaryApproval" && Auth::user()->role_non_funding == 1 && (empty($field_data) || !empty($field_data) )) || (($file->fileId == "bankStatement" || $file->fileId == "bursaryApproval") && Auth::user()->role_funding == 1&& (empty($field_data) || !empty($field_data) )))
                <span id="actionUserName_error" class="hidden" ><small style="color:red">This field is required</small></span>
                <br/>
                <input type="text" id="actionUserName" placeholder="Name" value="{{Auth::user()->userFirstName}}">
                
                <br/>

                <div id="reasonBox" style="height:38px; margin-top:-21px; width:65%">
                    <span id="rejectReason_error" class="hidden" ><small style="color:red">This field is required</small></span>
                    <br/>
                    <input type="text" id="rejectReason" placeholder="Type field for rejection reason" style="width:100%">
                </div>
            @endif

            
            
            <div class="flex" style="margin: 35px 0;" id="buttonSection">
                @if ($user->rob_60_cancelled == 0)
                    @if ($doc_approved == 2)

                        @if($user->rob_50_approve != 2 || Auth::user()->role_delete_uploaded == 1)
                            @if (($file->fileId !== "bankStatement" && $file->fileId !== "bursaryApproval" && explode("_", $file->fileId)[0] !== "other" && $file->fileId !== "moveIn" && Auth::user()->role_non_funding == 1) || (($file->fileId == "bankStatement" || $file->fileId == "bursaryApproval" || explode("_", $file->fileId)[0] == "other") && $file->fileId !== "moveIn" && Auth::user()->role_funding == 1) || ($file->fileId == "moveIn" && Auth::user()->role_app_lease == 1))
                                <button type="button" class="rejected button" id="rejected" onClick="actionFile('Rejected', '')">Reject</button>
                            @endif
                        @endif
                       

                    @else
                        @if(Auth::user()->role_upload_docs == 1)
                            <button type="button" class="amend button" id="amend"  onClick="actionFile('Approved', 'Amend')">Amend</button>

                            <!-- && $data_approved == 1 -->
                            <!-- && $data_approved == 1 -->
                            <!-- && $data_approved == 1 -->

                            @if(
                                ($file->fileId !== "bankStatement" && $file->fileId !== "bursaryApproval" && explode("_", $file->fileId)[0] !== "other" && $file->fileId !== "moveIn" && Auth::user()->role_non_funding == 1  ) || (($file->fileId == "bankStatement" || $file->fileId == "bursaryApproval" || explode("_", $file->fileId)[0] == "other") && $file->fileId !== "moveIn" && Auth::user()->role_funding == 1 ) || ($file->fileId == "moveIn" && Auth::user()->role_app_lease == 1))
                                @if ((!empty($field_data) && $data_approved == 0) && explode("_", $file->fileId)[0] !== "other")
                                    <button type="button" class="rejected button" id="rejected" onClick="actionFile('Rejected', '')">Reject</button>
                                @endif
                            @endif

                        @endif

                        @if(($file->fileId !== "bankStatement" && $file->fileId !== "bursaryApproval" && explode("_", $file->fileId)[0] !== "other" && $file->fileId !== "moveIn" && Auth::user()->role_non_funding == 1) || (($file->fileId == "bankStatement" || $file->fileId == "bursaryApproval" || explode("_", $file->fileId)[0] == "other") && $file->fileId !== "moveIn" && Auth::user()->role_funding == 1) || ($file->fileId == "moveIn" && Auth::user()->role_app_lease == 1))
                            @if ((empty($field_data) || !empty($field_data) && $data_approved == 1) || explode("_", $file->fileId)[0] == "other")
                                <button type="button" class="approve button" id="approved" onClick="actionFile('Approved', '')">Approve</button>
                                <button type="button" class="rejected button" id="rejected" onClick="actionFile('Rejected', '')">Reject</button>
                            @endif
                        @endif
                    @endif
                @endif
            </div>

            <hr style="margin-bottom: 35px;">

            <h3>Info</h3>

            

            <div>
                @if($file->fileId != "copyID" && $file->fileId != "proof_of_payment")
                    <span style='font-weight:bold'>Name:</span> {{$user->userFirstName}} {{$user->userLastname}}
                @endif

                @if($file->fileId == "proofRegistration")
                    <br/><span style='font-weight:bold'>Nationality:</span> {{$user->userNationality}}
                    @if ($user->userNationality == "South Africa")
                        <br/><span style='font-weight:bold'>I.D Number:</span> {{$user->userId}}
                    @else
                        <br/><span style='font-weight:bold'>Passport:</span> {{$user->userPassport}}
                    @endif
                    <br/><span style='font-weight:bold'>Student Number:</span> {{$user->studentNo}}
                    <br/><span style='font-weight:bold'>Email:</span> {{$user->userEmail}}
                    <br/><span style='font-weight:bold'>Cell:</span> {{$user->userCell}}
                    <br /><span style='font-weight:bold'>Academic Year:</span> @foreach($lookups as $lookup) @if($lookup->group_id == "4") {{$lookup->lookup_name}} @endif @endforeach</span>
                @endif

                @if($file->fileId == "proof_of_payment")
                    <br/><span style='font-weight:bold'>Booking Fee:</span> {{ $booking->room_rate }}
                    <br/><span style='font-weight:bold'>Booking Fee Waived:</span> TBD    
                    <br/><span style='font-weight:bold'>Owner:</span> {{ $property->owner }}    
                    <br /><span style='font-weight:bold'>Name:</span> {{$user->userFirstName}} {{$user->userLastname}}
                    <br/><span style='font-weight:bold'>Student Number:</span> {{$user->studentNo}}                
                    <!-- <br/><span style='font-weight:bold'>Application Number:</span> #{{$user->bookingRef}} -->
                    <br/><span style='font-weight:bold'>Booking Reference:</span> #{{$booking->booking_code}}
                    <br/><span style='font-weight:bold'>Payment Type:</span> {{$booking->payment_type}}
                    @if($booking->payment_type != "Manual EFT")
                        <br/><span style='font-weight:bold'>Payment Method Detail:</span> {{$onlinePayment->payment_method_detail}}
                        <br/><span style='font-weight:bold'>Payment Status:</span> {{$onlinePayment->status}}
                        <br/><span style='font-weight:bold'>Payment Amount:</span> R {{$onlinePayment->payment_amount/100}}
                        <br/><span style='font-weight:bold'>Transaction ID:</span> {{$onlinePayment->payment_transaction_id}}
                        <br/><span style='font-weight:bold'>Payment Initiated:</span> {{$onlinePayment->created_at}}
                        <br/><span style='font-weight:bold'>Payment Last Update:</span> {{$onlinePayment->updated_at}}
                    @endif
                    <!-- <br/><span style='font-weight:bold'>Payment Reference:</span> <span id="booking_payment">{{$booking->booking_payment_ref}}</span> -->
                @endif

                @if($file->fileId == "copyID")
                    <span style='font-weight:bold'>Gender:</span> @if ($user->userTitle == "Mr") Male @else Female @endif</span>
                @endif

                @if($file->fileId == "studentCard")
                    <br /><span style='font-weight:bold'>Academic Year:</span> @foreach($lookups as $lookup) @if($lookup->group_id == "4") {{$lookup->lookup_name}} @endif @endforeach</span>
                    <br /><span style='font-weight:bold'>Institution:</span> @foreach($lookups as $lookup) @if($user->institutionStudy == $lookup->id) {{$lookup->lookup_name}} @endif @endforeach</span>
                @endif

                @if($file->fileId == "bursaryApproval" || $file->fileId == "bankStatement")
                    <br/><span style='font-weight:bold'>Nationality:</span> {{$user->userNationality}}
                    @if ($user->userNationality == "South Africa")
                        <br/><span style='font-weight:bold'>I.D Number:</span> {{$user->userId}}
                    @else
                        <br/><span style='font-weight:bold'>Passport:</span> {{$user->userPassport}}
                    @endif
                    <br /><span style='font-weight:bold'>Academic Year:</span> @foreach($lookups as $lookup) @if($lookup->group_id == "4") {{$lookup->lookup_name}} @endif @endforeach</span>
                    <br /><span style='font-weight:bold'>Institution:</span> @foreach($lookups as $lookup) @if($user->institutionStudy == $lookup->id) {{$lookup->lookup_name}} @endif @endforeach</span>
                    <br/><span style='font-weight:bold'>Student Number:</span> {{$user->studentNo}}                
                    <br/><span style='font-weight:bold'>Email:</span> {{$user->userEmail}}
                @endif
            </div>
            
            @if (!empty($field_data))
                <hr style="margin: 35px 0;">

                <h3>Review</h3>

                <form class="edit_info">
                    @csrf

                    @if($file->fileId == "proof_of_payment")
                        <div class="form-field">
                            <span style='font-weight:bold'>Payment Reference*</span>
                            <input class="noedit required" name="booking_payment_ref" id="booking_payment_ref" disabled type="text" value="{{$booking->booking_payment_ref}}">
                        </div>
                        <div class="form-field">
                            <span style='font-weight:bold'>Payment Type *</span>
                            <select name="payment_type" id="payment_type" class="noedit required" disabled>
                                <option value="" selected disabled>Please select one</option>
                                <option value="Credit/Debit Card" <?php if($booking->payment_type == "Credit/Debit Card"){echo "selected";}?>>Credit/Debit Card</option>
                                <option value="Online EFT" <?php if($booking->payment_type == "Online EFT"){echo "selected";}?>>Online EFT</option>
                                <option value="Manual EFT" <?php if($booking->payment_type == "Manual EFT"){echo "selected";}?>>Manual EFT</option>
                            </select>
                        </div>
                        @if($booking->payment_type != "Manual EFT")
                        <div class="form-field">
                            <span style='font-weight:bold'>Payment Status*</span>
                            <input class="disablededit required" name="payment_status" id="payment_status" disabled type="text" value="{{$onlinePayment->status}}">
                        </div>
                        <div class="form-field">
                            <span style='font-weight:bold'>Payment Amount*</span>
                            <input class="disablededit required" name="payment_amount" id="payment_amount" disabled type="text" value="R {{$onlinePayment->payment_amount/100}}">
                        </div>
                        <div class="form-field">
                            <span style='font-weight:bold'>Transaction ID*</span>
                            <input class="disablededit required" name="payment_transaction_id" id="payment_transaction_id" disabled type="text" value="{{$onlinePayment->payment_transaction_id}}">
                        </div>
                        <div class="form-field">
                            <span style='font-weight:bold'>Payment Method Detail*</span>
                            <input class="disablededit required" name="payment_method_detail" id="payment_method_detail" disabled type="text" value="{{$onlinePayment->payment_method_detail}}">
                        </div>
                        <div class="form-field">
                            <span style='font-weight:bold'>Payment Initiated*</span>
                            <input class="disablededit required" name="created_at" id="created_at" disabled type="text" value="{{$onlinePayment->created_at}}">
                        </div>
                        <div class="form-field">
                            <span style='font-weight:bold'>Payment Last Update*</span>
                            <input class="disablededit required" name="updated_at" id="updated_at" disabled type="text" value="{{$onlinePayment->updated_at}}">
                        </div>
                        
                        

                        @endif
                    @endif

                    @if($file->fileId == "copyID")
                        <div class="form-field">
                            <span style='font-weight:bold'>First Name *</span>
                            <input class="noedit required" name="userFirstName" id="userFirstName" disabled type="text" value="{{$user->userFirstName}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Last Name *</span> <input class="noedit required" name="userLastname" id="userLastname" disabled type="text" value="{{$user->userLastname}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Nationality *</span>
                            <select name="nationality" id="nationality" class="noedit required" disabled>
                                <option value="" selected disabled>Please select one</option>
                                @foreach($lookups as $lookup)
                                    @if($lookup->group_id == "5")
                                        <option value="{{$lookup->lookup_name}}" <?php if($user->userNationality == $lookup->lookup_name){echo "selected";}?>>{{$lookup->lookup_name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="form-field <?php if($user->userNationality !== "South Africa"){echo "hidden";} ?>" id="idField">
                            <span style='font-weight:bold'>I.D Number * <span id="idNumber_error" class="hidden"><small style="color:red">Not a valid ID number</small></span></span> <input class="noedit required" name="userId" id="idNumber" disabled type="text" value="{{$user->userId}}">
                        </div>

                        <div class="form-field <?php if($user->userNationality == "South Africa"){echo "hidden";} ?>" id="passportField">
                            <span style='font-weight:bold'>Passport *</span> <input class="noedit required" name="userPassport" id="passportNo" disabled type="text" value="{{$user->userPassport}}">                                       
                        </div>
                        
                        <div class="form-field">
                            <span style='font-weight:bold'>DOB * <span id="dob_error" class="hidden"><small style="color:red">Not a valid date of birth</small></span> </span><input class="noedit required" name="userDob" id="userDob" disabled type="date" value="{{$user->userDob}}">       
                        </div>
                    @endif

                    @if($file->fileId == "studentCard")
                        <div class="form-field">
                            <span style='font-weight:bold'>Student Number *</span> <input class="noedit required" name="studentNo" id="studentNo" disabled type="text" value="{{$user->studentNo}}">
                        </div>
                    @endif

                    @if($file->fileId == "proofRegistration")
                        <div class="form-field">
                            <span style='font-weight:bold'>Institution *</span>
                            <select name="institStudy" id="institStudy" class="noedit required" disabled>
                                <option value="Choose one" selected disabled>Choose one</option>
                                @foreach($lookups as $lookup)
                                    @if($lookup->group_id == "1")
                                        <option value="{{$lookup->id}}" <?php if($user->institutionStudy == $lookup->id){echo "selected";} ?>>{{$lookup->lookup_name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Faculty *</span>
                            <select name="faculty" id="faculty" class="noedit required" disabled>
                                <option value="" disabled selected>Select One</option>
                                @foreach($lookups as $lookup)
                                    @if($lookup->group_id == "3")
                                        <option value="{{$lookup->id}}" <?php if($user->faculty == $lookup->id){echo "selected";} ?>>{{$lookup->lookup_name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <span style='font-weight:bold'>Year of Study *</span>
                            <select name="yearOfStudy" id="yearOfStudy" class="noedit required" disabled>
                                <option value="" selected disabled>Please select one</option>
                                @foreach($lookups as $lookup)
                                    @if($lookup->group_id == "6")
                                        <option value="{{$lookup->lookup_name}}" <?php if($user->yearOfStudy == $lookup->lookup_name){echo "selected";} ?>>{{$lookup->lookup_name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if($file->fileId == "bursaryApproval" && $user->studentType == "NSFAS")
                        <div class="form-field">    
                            <span style='font-weight:bold'>Student Type *</span>
                            <select name="studentType" id='studentType' class="noedit required" disabled>
                                <option value="" selected disabled>Select a Student Type</option>
                                <option value="NSFAS" <?php if($user->studentType == "NSFAS"){echo "selected";} ?>>NSFAS</option>
                                <option value="Bursary" <?php if($user->studentType == "Bursary"){echo "selected";} ?>>Bursary</option>
                                <option value="Cash / Direct" <?php if($user->studentType == "Cash / Direct"){echo "selected";} ?>>Cash</option>
                            </select>
                        </div> 
                    @endif
                    
                    @if($file->fileId == "bursaryApproval" && $user->studentType == "Bursary")
                        <div class="form-field">    
                            <span style='font-weight:bold'>Student Type *</span>
                            <select name="studentType" id='studentType' class="noedit required" disabled>
                                <option value="" selected disabled>Select a Student Type</option>
                                <option value="NSFAS" <?php if($user->studentType == "NSFAS"){echo "selected";} ?>>NSFAS</option>
                                <option value="Bursary" <?php if($user->studentType == "Bursary"){echo "selected";} ?>>Bursary</option>
                                <option value="Cash / Direct" <?php if($user->studentType == "Cash / Direct"){echo "selected";} ?>>Cash</option>
                            </select>
                        </div>
                        
                        <div class="form-field">    
                            <span style='font-weight:bold'>How Is the Bursary Paid *</span>
                            <select name="bPayment" id='bPayment' class="noedit required" disabled>
                                <option value="" disabled selected>Please select one</option>
                                <option value="Directly to the RESIDENCE" <?php if($user->bPayment == "Directly to the RESIDENCE"){echo "selected";} ?>>Directly to the RESIDENCE</option>
                                <option value="Directly to the STUDENT" <?php if($user->bPayment == "Directly to the STUDENT"){echo "selected";} ?>>Directly to the STUDENT</option>
                                <option value="Via an INTERMEDIARY" <?php if($user->bPayment == "Via an INTERMEDIARY"){echo "selected";} ?>>Via an INTERMEDIARY</option>
                            </select>
                        </div>

                        <div class="form-field <?php if($user->bPayment !== 'Via an INTERMEDIARY'){echo 'hidden';}?>" id="viaInt">    
                            <span style='font-weight:bold'>Intermediary *</span>
                            <select name="bint" id='bint' class="noedit <?php if($user->bPayment !== 'Via an INTERMEDIARY'){echo 'hidden';}?>" disabled>
                                <option value="" disabled selected>Please select one</option>
                                <option value="Fundi" <?php if($user->bint == "Fundi"){echo "selected";} ?>>Fundi</option>
                                <option value="Intellicard" <?php if($user->bint == "Intellicard"){echo "selected";} ?>>Intellicard</option>
                                <option value="Other" <?php if($user->bint == "Other"){echo "selected";} ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-field <?php if($user->bint !== 'Other' || $user->bPayment !== 'Via an INTERMEDIARY'){echo 'hidden';}?>" id="spesificInt">    
                            <span style='font-weight:bold'>Specify Intermediary Name *</span> <input class="noedit <?php if($user->bint !== 'Other'){echo 'hidden';}?>" name="bIntName" id="bIntName" disabled type="text" value="{{$user->bIntName}}">
                        </div>
                        

                        <div class="form-field">    
                            <span style='font-weight:bold'>Company Name *</span> <input class="noedit required" name="burCompanyName" id="burCompanyName" disabled type="text" value="{{$user->burCompanyName}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Registration Number *</span> <input class="noedit required" name="burRegNo" id="burRegNo" disabled type="text" value="{{$user->burRegNo}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Contact Person *</span> <input class="noedit required" name="burContactPerson" id="burContactPerson" disabled type="text" value="{{$user->burContactPerson}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Contact Number * <span id="burContactNo_error" class="hidden"><small style="color:red">Not a valid cellphone number</small></span></span> <input class="noedit required" name="burContactCode" id="burContactCode" disabled type="text" value="{{$user->burContactCode}}" style="width:10%; margin-right:2%"><input class="noedit required" name="burContactNo" id="burContactNo" disabled type="text" value="{{$user->burContactNo}}" style="width:48%">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Email Address * <span id="burEmail_error" class="hidden"><small style="color:red">Not a valid email</small></span><span id="burEmail_InUse" class="hidden" ><small style="color:red">Email Address already in use</small></span></span> <input class="noedit required" name="burEmail" id="burEmail" disabled type="text" value="{{$user->burEmail}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>NSFAS Bursar *</span>
                            <select name="nsfas_bursar" id='nsfas_bursar' class="noedit required" disabled>                            
                                <option value="0" <?php if($user->nsfas_bursar == "" || $user->nsfas_bursar == "0"){echo "selected";} ?>>No</option>
                                <option value="1" <?php if($user->nsfas_bursar == "1"){echo "selected";} ?>>Yes</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Street No * </span> <input class="noedit required" name="bAddress1" id="bAddress1" disabled type="text" value="{{$user->bAddress1}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Suburb * </span> <input class="noedit required" name="bAddress2" id="bAddress2" disabled type="text" value="{{$user->bAddress2}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>City * </span> <input class="noedit required" name="bAddressCity" id="bAddressCity" disabled type="text" value="{{$user->bAddressCity}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Postal * <span id="bAddressPostal_error" class="hidden"><small style="color:red">Not a valid postal code</small></span></span> <input class="noedit required" name="bAddressPostal" id="bAddressPostal" disabled type="text" value="{{$user->bAddressPostal}}">
                        </div>
                        
                    @endif

                    @if($file->fileId == "bankStatement")
                        <div class="form-field">    
                            <span style='font-weight:bold'>Student Type *</span>
                            <select name="studentType" id='studentType' class="noedit required" disabled>
                                <option value="" selected disabled>Select a Student Type</option>
                                <option value="NSFAS" <?php if($user->studentType == "NSFAS"){echo "selected";} ?>>NSFAS</option>
                                <option value="Bursary" <?php if($user->studentType == "Bursary"){echo "selected";} ?>>Bursary</option>
                                <option value="Cash / Direct" <?php if($user->studentType == "Cash / Direct"){echo "selected";} ?>>Cash</option>
                            </select>
                        </div> 

                        <div class="form-field">
                            <span style='font-weight:bold'>Sponsor First Name *</span> <input class="noedit required" name="sponFirstName" id="sponFirstName" disabled type="text" value="{{$user->sponFirstName}}">                        
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Sponsor Last Name *</span> <input class="noedit required" name="sponLastName" id="sponLastName" disabled type="text" value="{{$user->sponLastName}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Sponsor Nationality *</span>
                            <select name="pNationality" id="pNationality" class="noedit required" disabled>
                                <option value="" selected disabled>Please select one</option>
                                @foreach($lookups as $lookup)
                                    @if($lookup->group_id == "5")
                                        <option value="{{$lookup->lookup_name}}" <?php if($user->pNationality == $lookup->lookup_name){echo "selected";}?>>{{$lookup->lookup_name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="form-field <?php if($user->pNationality !== "South Africa"){echo "hidden";} ?>" id="pIdField">
                            <span style='font-weight:bold'>Sponsor I.D Number *</span> <input class="noedit required" name="sponId" id="pIdNumber" disabled type="text" value="{{$user->sponId}}">
                        </div>

                        <div class="form-field <?php if($user->pNationality == "South Africa"){echo "hidden";} ?>" id="pPassportField">
                            <span style='font-weight:bold'>Sponsor Passport *</span> <input class="noedit required" name="pPassportNo" id="pPassportNo" disabled type="text" value="{{$user->pPassportNo}}">                                       
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Sponsor Cellphone * <span id="sponCellNo_error" class="hidden"><small style="color:red">Not a valid cellphone number</small></span></span>
                            <input class="noedit required" name="sponCellCode" id="sponCellCode" disabled type="text" value="{{$user->sponCellCode}}" style="width:10%; margin-right:2%"><input class="noedit required" name="sponCellNo" id="sponCellNo" disabled type="text" value="{{$user->sponCell}}" style="width:48%">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Sponsor Email *</span> <input class="noedit required" name="sponEmail" id="sponEmail" disabled type="text" value="{{$user->sponEmail}}">                                       
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Street No * </span> <input class="noedit required" name="pAddress1" id="pAddress1" disabled type="text" value="{{$user->pAddress1}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Suburb * </span> <input class="noedit required" name="pAddress2" id="pAddress2" disabled type="text" value="{{$user->pAddress2}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>City * </span> <input class="noedit required" name="pAddressCity" id="pAddressCity" disabled type="text" value="{{$user->pAddressCity}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Postal * <span id="pAddressPostal_error" class="hidden"><small style="color:red">Not a valid postal code</small></span></span> <input class="noedit required" name="pAddressPostal" id="pAddressPostal" disabled type="text" value="{{$user->pAddressPostal}}">
                        </div>
                    @endif

                    @if(explode("_", $file->fileId)[0] !== "other")
                
                        @if($user->rob_60_cancelled == 0)
                            <div style="margin-top: 35px;" class="btns_approve_data">
                                @if(($file->fileId !== "bankStatement" && $file->fileId !== "bursaryApproval" && Auth::user()->role_non_funding == 1 && $data_approved == 0) || (($file->fileId == "bankStatement" || $file->fileId == "bursaryApproval") && Auth::user()->role_funding == 1 && $data_approved == 0))
                                    <button type="button" class="approve button" style="height:40px;" onClick="approveData('1')">Approve</button>
                                @endif

                                @if(Auth::user()->role_edit_app == 1 && $data_approved == 0)
                                    <button type="button" class="amend button" style="height:40px;" onClick="editData()">Edit</button>
                                @endif

                                @if(($file->fileId !== "bankStatement" && $file->fileId !== "bursaryApproval" && Auth::user()->role_non_funding == 1 && ($doc_approved == 1 || $data_approved == 0)) || (($file->fileId == "bankStatement" || $file->fileId == "bursaryApproval") && Auth::user()->role_funding == 1 && ($doc_approved == 1 || $data_approved == 0)))
                                    <button type="button" class="rejected button" style="height:40px;" onClick="approveData('0')">Reject</button>
                                @endif
                            </div>

                            @if(Auth::user()->role_edit_app == 1 && $data_approved == 0)
                                <div style="margin-top: 35px;display: none;" class="btns_save_data">
                                    <input type="hidden" name="userUid" value="{{$user->userUid}}">
                                    <input type="hidden" name="type" value="{{$file->fileId}}">

                                    <button type="button" class="approve button" style="height:40px;" onClick="updateData()">Update</button>
                                    <button type="button" class="rejected button" style="height:40px;" onClick="cancelEditData('cancel')">Cancel</button>
                                </div>
                            @endif
                        @endif
                    @endif
                </form>
        
                <div id="edit_data"></div>
            @endif
        </div>

        @if(!empty($file) && !empty($file->fileName))
            <div class="right_pane">
                <div class="pdfSection" style="margin-top: 0;">
                    @if(strpos($file->fileUrl, "pdf") > 0)
                    <?php if((!$agent->isMobile())){ ?>
                        <embed id="pdfView" src="/storage/{{$file->fileUrl}}"  width="100%" height="800px" />
                    <?php }else{ ?>
                        <div style="margin:20px 0px 20px 0px">
                            <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
                            <br/><br/>
                            <button id="prev">Previous</button>
                            <button id="next">Next</button>
                        </div>
                        <canvas id="the-canvas" style="width:100%"></canvas>
                    <?php } ?>
                        
                    @else
                        <img src="/storage/{{$file->fileUrl}}" alt="">
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<div id="footer"></div>

<script>
    jQuery(document).ready( function() {

        var url = '/storage/{{$file->fileUrl}}';

        const script = document.createElement('script');
        script.type = 'text/javascript';
        script.onload=function(){

            // Loaded via <script> tag, create shortcut to access PDF.js exports.
                    var pdfjsLib = window['pdfjs-2.14.305-dist/build/pdf'];

            console.log("pdfjsLib");
            console.log(window.pdfjsLib);

            // The workerSrc property shall be specified.
            window.pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.js';

            var pdfDoc = null,
                pageNum = 1,
                pageRendering = false,
                pageNumPending = null,
                scale = 0.8,
                canvas = document.getElementById('the-canvas'),
                ctx = canvas.getContext('2d');

            /**
             * Get page info from document, resize canvas accordingly, and render page.
             * @param num Page number.
             */
            function renderPage(num) {
            pageRendering = true;
            // Using promise to fetch the page
            pdfDoc.getPage(num).then(function(page) {
                var viewport = page.getViewport({scale: scale});
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                // Render PDF page into canvas context
                var renderContext = {
                canvasContext: ctx,
                viewport: viewport
                };
                var renderTask = page.render(renderContext);

                // Wait for rendering to finish
                renderTask.promise.then(function() {
                pageRendering = false;
                if (pageNumPending !== null) {
                    // New page rendering is pending
                    renderPage(pageNumPending);
                    pageNumPending = null;
                }
                });
            });

            // Update page counters
            document.getElementById('page_num').textContent = num;
            }

            /**
             * If another page rendering in progress, waits until the rendering is
             * finised. Otherwise, executes rendering immediately.
             */
            function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
            }

            /**
             * Displays previous page.
             */
            function onPrevPage() {
            if (pageNum <= 1) {
                return;
            }
            pageNum--;
            queueRenderPage(pageNum);
            }
            document.getElementById('prev').addEventListener('click', onPrevPage);

            /**
             * Displays next page.
             */
            function onNextPage() {
            if (pageNum >= pdfDoc.numPages) {
                return;
            }
            pageNum++;
            queueRenderPage(pageNum);
            }
            document.getElementById('next').addEventListener('click', onNextPage);

            /**
             * Asynchronously downloads PDF.
             */
            window.pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            document.getElementById('page_count').textContent = pdfDoc.numPages;

            // Initial/first page rendering
            renderPage(pageNum);
            });
        };
        script.src = '//mozilla.github.io/pdf.js/build/pdf.js';
        document.querySelector('#footer').after(script);

        $('.close').on("click", function(e) {
            $("#myModal").html("");
            $("#myModal").css("display", "none");
        });

        $("input").focus(function(){
            let fieldId = $(this)[0]["id"]
            if (!$("#"+fieldId).data('old')) {
                const div = document.getElementById(fieldId)
                div.dataset.old = $(this)[0]['value']
            }
            
        });

        $("select").focus(function(){
            let fieldId = $(this)[0]["id"]
            if (!$("#"+fieldId).data('old')) {
                const div = document.getElementById(fieldId)
                div.dataset.old = $(this)[0]['value']
            }
            
        });

        $("#bPayment").on('change', function(){
            if($("#bPayment").val() == "Via an INTERMEDIARY"){
                $("#viaInt").removeClass("hidden")
                $("#bint").addClass("required")
                $("#bint").removeClass("hidden")
                if($("#bint").val() == "Other"){
                    $("#spesificInt").removeClass("hidden")
                    $("#bIntName").addClass("required")
                    $("#bIntName").removeClass("hidden")
                }
            }else{
                $("#viaInt").addClass("hidden")
                $("#bint").removeClass("required")
                $("#bint").addClass("hidden")
                $("#bIntName").removeClass("required")
                $("#spesificInt").addClass("hidden")
            }
        })

        $("#bint").on('change', function(){
        
            if($("#bint").val() == "Other"){
                $("#spesificInt").removeClass("hidden")
                $("#bIntName").addClass("required")
                $("#bIntName").removeClass("hidden")
            }else{
                $("#spesificInt").addClass("hidden")
                $("#bIntName").removeClass("required")
                $("#bIntName").addClass("hidden")
            }
        })


        $("input").blur(function(){
            let fieldId = $(this)[0]["id"]

            // var required = document.getElementById(fieldId).required
            var required = $("#"+fieldId).hasClass('required')

            if($("#"+fieldId).val() == "" && required == true){
                $("#"+$(this)[0]["id"]).addClass('fieldError')
                $("#"+$(this)[0]["id"]+"_error").removeClass('hidden')
            }else{
                $("#"+$(this)[0]["id"]).removeClass('fieldError')
                $("#"+$(this)[0]["id"]+"_error").addClass('hidden')
            }
        });

        $("select").blur(function(){
            let fieldId = $(this)[0]["id"]

            console.log($(this)[0])

            // var required = document.getElementById(fieldId).required
            var required = $("#"+fieldId).hasClass('required')

            if($("#"+fieldId).val() == "" && required == true){
                $("#"+$(this)[0]["id"]).addClass('fieldError')
                $("#"+$(this)[0]["id"]+"_error").removeClass('hidden')
            }else{
                $("#"+$(this)[0]["id"]).removeClass('fieldError')
                $("#"+$(this)[0]["id"]+"_error").addClass('hidden')
            }
        });

        @if($file->fileId == "copyID")
            $("#nationality").on('change', function(){
                if($("#nationality").val() == "South Africa"){
                    $("#passportField").addClass('hidden')
                    $("#idField").removeClass('hidden')

                    $("#passportNo").removeClass("required")
                    $("#passportNo").removeClass("fieldError")
                    $("#idNumber").addClass("required")

                    let idNumber = $("#idNumber").val();
                    if(idNumber.length == 13){
                        ValidID(idNumber, "idNumber", "idNumber_error")
                    }else{
                        $("#idNumber").addClass("fieldError")
                        $("#idNumber_error").removeClass('hidden')
                    } 
                }else{
                    $("#idField").addClass('hidden')
                    $("#passportField").removeClass('hidden')

                    $("#passportNo").addClass("required")
                    $("#idNumber").removeClass("required")
                    $("#idNumber").removeClass("fieldError")
                }
            });
        @endif

        @if($file->fileId == "bankStatement")
            $("#pNationality").on('change', function(){
                if($("#pNationality").val() == "South Africa"){
                    $("#pPassportField").addClass('hidden')
                    $("#pIdField").removeClass('hidden')
                    
                    $("#pPassportNo").removeClass("required")
                    $("#pIdNumber").addClass("required")
                    $("#pPassportNo").removeClass("fieldError")
                }else{
                    $("#pIdField").addClass('hidden')
                    $("#pPassportField").removeClass('hidden')
                    
                    $("#pPassportNo").addClass("required")
                    $("#pIdNumber").removeClass("required")
                    $("#pIdNumber").removeClass("fieldError")
                }
            });
        @endif
    });

    $("#bAddressPostal").on('keyup blur', function(){
        postal = $("#bAddressPostal").val()

        if(/\b\d{4}\b/g.test(postal)){
            $("#bAddressPostal").removeClass("fieldError")
            $("#bAddressPostal_error").addClass('hidden')
        }else{
            $("#bAddressPostal").addClass("fieldError")
            $("#bAddressPostal_error").removeClass('hidden')
        }
        
    })

    $("#pAddressPostal").on('keyup blur', function(){
        postal = $("#pAddressPostal").val()

        if(/\b\d{4}\b/g.test(postal)){
            $("#pAddressPostal").removeClass("fieldError")
            $("#pAddressPostal_error").addClass('hidden')
        }else{
            $("#pAddressPostal").addClass("fieldError")
            $("#pAddressPostal_error").removeClass('hidden')
        }
        
    })

    $("#userDob").on('keyup blur', function(){
        let dob = $("#userDob").val();

        var d = new Date()
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();
        yearBack =(d.getFullYear() - 100);

        if(dob < yearBack+"-01-01" || dob > year+"-"+month+"-"+day){
            $("#userDob").addClass("fieldError")
            $("#dob_error").removeClass('hidden')
        }else{
            $("#userDob").removeClass("fieldError")
            $("#dob_error").addClass('hidden')
        }
    })

    $("#idNumber").on('keyup blur', function(){
        let idNumber = $("#idNumber").val();
        if(idNumber.length == 13){
            ValidID(idNumber, "idNumber", "idNumber_error")
        }else{
            $("#idNumber").addClass("fieldError")
            $("#idNumber_error").removeClass('hidden')
        }  
    })

    function ValidID(idNumber, idField, errorField){
        let idArr = Array.from(idNumber);
        idArr.reverse()
        let newArr = [];

        for (let i = 0; i < idArr.length; i++) {
            if((i % 2) !== 0){
                let newValue = idArr[i]*2
                if(newValue > 9){
                    value1 = (newValue.toString()).substr(0,1)
                    value2 = (newValue.toString()).substr(1, 2)
                    newValue = parseInt(value1) + parseInt(value2)

                }
                newArr.push(newValue)
            }else{
                newArr.push(idArr[i])
            }
        }


        let sum = 0;
        for (let i = 0; i < newArr.length; i++) {
            sum += parseInt(newArr[i]);
        }

        let finalSum = sum/10

        console.log(Number.isInteger(finalSum) && idNumber !== "")

        if(Number.isInteger(finalSum) && idNumber !== ""){
            $("#"+idField).removeClass("fieldError")
            $("#"+errorField).addClass('hidden')
        }else{
            $("#"+idField).addClass("fieldError")
            $("#"+errorField).removeClass('hidden')
        }
    }

    $("#pIdNumber").on('keyup blur', function(){
        let idNumber = $("#pIdNumber").val();

        if(idNumber.length == 13){
            ValidID(idNumber, "pIdNumber", "pIdNumber_error")
        }else{
            $("#pIdNumber").addClass("fieldError")
            $("#pIdNumber_error").removeClass('hidden')
        }

    })

    function approveData(type) {
        let message = "You are about to approve this data";

        console.log(type);

        if (type == "0") {
            message = "You are about to reject this data";
        }
        Swal.fire({
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#approveData").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')                

                $.ajax({
                    type: "post",
                    url: "/action-data",
                    data: {
                        FileNameForApproval: "{{$file->fileId}}", 
                        type: type, 
                        userId: "{{$user->userUid}}",
                    },

                    success: function (data) {

                        checkUserStatus();
                        refreshActions();

                        if (data === "true") {
                            refreshActions();

                            if (type == "0") {
                                $("#myModal").html("");
                                $("#myModal").css("display", "none");   
                            } else {                                
                                refresh_this_modal();
                            }
                            

                        } else {
                            alert("error");
                            console.log(data);
                        }

                    }
                })

            }
        });
    }

    function actionFile(type, action) {

        if (type === "Rejected") {
            actionName = "Reject"
        } else {
            if(action == "Amend") {
                actionName = "Amend"
            } else {
                actionName = "Approve"
            }
        }

        if (type === "Rejected") {
            if ($("#rejectReason").val() !== "" && $("#actionUserName").val() !== ""){
                actionSwal(actionName, type, action)
            }else{
                if($("#actionUserName").val() == ""){
                    $("#actionUserName").addClass("errorBorder")
                    $("#actionUserName_error").removeClass("hidden")
                }

                if($("#rejectReason").val() == ""){
                    $("#rejectReason").addClass("errorBorder")
                    $("#rejectReason_error").removeClass("hidden")
                }
                
                document.getElementById("reasonBox").style.marginTop = "-27px";
            }
            
        } else {
            if(action == "Amend"){
                if($("#actionUserName").val() !== ""){

                    start();

                async function start() {
                    const { value: file } = await Swal.fire({
                        title: 'Select file',
                        input: 'file',
                        inputAttributes: {
                            'aria-label': 'Upload your file'
                        }
                    })

                    if (file) {
                        fileForApproval = $("#FileNameForApproval").val();

                        $("#amend").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                        $("#rejected").attr("disabled", "disabled");
                        $("#approved").attr("disabled", "disabled");

                        // $("#copyIDSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');
                        let userId = "{{$user->userUid}}";

                        var form_data = new FormData();
                        form_data.append("file", file);
                        form_data.append("userUid", userId);
                        form_data.append("fileType", fileForApproval);
                        form_data.append("adminName", $("#actionUserName").val());

                        $.ajax({
                            type: "post",
                            url: "/move-in-form-admin",
                            data: form_data,
                            cache: false,
                            contentType: false,
                            processData: false,

                            success: function (data) {
                                if (data[0] > 0) {
                                    $("#"+fileForApproval+"Status").empty().append('<div class="button action"></div>');

                                    refreshActions();                                    

                                    refresh_this_modal();

                                } else {
                                    Swal.fire(
                                        'Error',
                                        data[0],
                                        'error'
                                    )

                                    window.location.reload();
                                }
                            },
                        });
                    }
                }
                    
                }else{
                    $("#actionUserName").addClass("errorBorder")
                    $("#actionUserName_error").removeClass("hidden")
                }

                
            }else{
                if($("#actionUserName").val() !== ""){
                    actionSwal(actionName, type, action)
                }else{
                    $("#actionUserName").addClass("errorBorder")
                    $("#actionUserName_error").removeClass("hidden")
                }
                
            }
        } 

    }

    function actionSwal(actionName, type, action){
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to "+actionName+" this file",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.isConfirmed) {
                if(type === "Rejected"){
                        $("#rejected").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');
                        $("#amend").attr("disabled", "disabled");
                        $("#approved").attr("disabled", "disabled");

                        sendAction(type, action);

                }else{
                    if(action == "Amend"){
                            $("#amend").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');
                            $("#rejected").attr("disabled", "disabled");
                            $("#approved").attr("disabled", "disabled");

                            sendAction(type, action);
                        
                    }else{
                            $("#approved").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');
                            $("#rejected").attr("disabled", "disabled");
                            $("#amend").attr("disabled", "disabled");

                            sendAction(type, action);

                            $("#files_to_download").val('true')
                        
                    }
                }
            }
        })
    }

    function sendAction(type, action) {
        fileForApproval = $("#FileNameForApproval").val();

        $.ajax({
            type: "post",
            url: "/action/file",
            data: {
                file: $("#FileIdForApproval").val(), 
                type: type,
                reason: $("#rejectReason").val(), 
                userName: $("#actionUserName").val(),
            },

            success: function (data) {

                if(type == "Approved" && $("#FileNameForApproval").val() == "moveIn"){
                    // window.location.reload();

                    $("#"+fileForApproval+"Status").empty().append('<div class="button '+type.toLowerCase()+'"></div>');

                    let uploadbtn = '<a href="/storage/'+data[0]['fileUrl']+'" download="'+data[0]['fileName']+'" style="margin-right:5px;"><button type="button" id="myBtn_download">Download Lease</button></a> <button type="button" onclick="btn_view_document_ajax('+data[0]['id']+')">View Document</button>';

                    $("#"+fileForApproval+"Banner").empty().append(uploadbtn)

                    refreshActions();
                    checkUserStatus();

                    $("#myModal").html("");
                    $("#myModal").css("display", "none");

                    @if(Auth::user()->role_modify_approved == 1)
                        $("#buttonTop").empty().append('<button type="button" class="appBooking" id="changeLeaseCancel" onClick="cancelLease(' + data[1]['id'] + ', \'generate\')">Change Bed</button>');
                    @endif

                    // $("#btn_clear_bed").addClass('hidden')
                    $("#btn_clear_bed").data('lease', 'true');
                    $(".studentType_btn").addClass('hidden')

                } else {
                    $("#"+fileForApproval+"Status").empty().append('<div class="button '+type.toLowerCase()+'"></div>');

                    //Add back upload button
                    if (type == "Rejected" && fileForApproval.substring(0, 5) == "other") {
                        @if(Auth::user()->role_upload_docs == 1)
                            let uploadbtn = '<label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="{{$file->fileId}}" onChange="UploadDocument(\'{{$file->fileId}}\')" hidden/> <button type="button" style="margin-left:5px;" onClick="deleteFile(\'{{$file->fileId}}\')">Delete</button>';
                            $("#"+fileForApproval+"Banner").empty().append(uploadbtn);
                        @endif
                    }else if(type == "Rejected" && fileForApproval !== "moveIn" ){

                        if(fileForApproval == "proof_of_payment"){
                            @if(Auth::user()->role_upload_docs == 1)
                                let uploadbtn = '<label onClick="paymentRef()" id="{{$file->fileId}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label>';
                                $("#"+fileForApproval+"Banner").empty().append(uploadbtn);
                            @endif
                        }else{

                            @if(Auth::user()->role_upload_docs == 1)
                                let uploadbtn = '<label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="{{$file->fileId}}" onChange="UploadDocument(\'{{$file->fileId}}\')" hidden/>';
                                $("#"+fileForApproval+"Banner").empty().append(uploadbtn);
                            @endif
                        }
                    }else if(type == "Rejected" && fileForApproval == "moveIn"){
                        let uploadbtn = '<a href="/storage/'+data[0]['fileUrl']+'" download="'+data[0]['fileName']+'" style="margin-right:5px;"><button type="button" id="myBtn_download">Download Lease</button></a> '
                        
                        if("<?php echo Auth::user()->role_lease_regen;?>" == "1"){
                            uploadbtn += '<button type="button" id="myBtn_regen_lease" style="margin-right:5px;">Regen Lease</button>'
                            uploadbtn += '<button type="button" id="myBtn_resend_lease" onClick="resendLease()">Resend Lease</button>'
                        }
                        
                        uploadbtn += '<label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="{{$file->fileId}}" onChange="UploadDocument(\'{{$file->fileId}}\')" hidden/>';
                        $("#"+fileForApproval+"Banner").empty().append(uploadbtn);

                        bookingTypeDetails();
                        resendLease();
                    }
                
                    refreshActions();
                    checkUserStatus();

                    $("#myModal").html("");
                    $("#myModal").css("display", "none");
                }
                
            },
        });
    }

    function removeFile(){
        fileForApproval = $("#FileNameForApproval").val();

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete this file",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#delete").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')

                $.ajax({
                    type: "post",
                    url: "/action/file",
                    data: {
                        file: $("#FileIdForApproval").val(), 
                        type: "Rejected",
                        reason: "", 
                        userName: "",
                    },

                    success: function (data) {

                        $("#"+fileForApproval+"Status").empty().append('<div class="button rejected"></div>');

                        //Add back upload button
                        if (fileForApproval !== "moveIn") {
                            @if(Auth::user()->role_upload_docs == 1)
                                let uploadbtn = '<label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="{{$file->fileId}}" onChange="UploadDocument(\'{{$file->fileId}}\')" hidden/>';
                                $("#"+fileForApproval+"Banner").empty().append(uploadbtn);
                            @endif
                        }else if(fileForApproval == "moveIn"){
                            let uploadbtn = '<a href="/storage/'+data[0]['fileUrl']+'" download="'+data[0]['fileName']+'" style="margin-right:5px;"><button type="button" id="myBtn_download">Download Lease</button></a> '

                            if("<?php echo Auth::user()->role_lease_regen;?>" == "1"){
                                uploadbtn += '<button type="button" id="myBtn_regen_lease" style="margin-right:5px;">Regen Lease</button> '
                                uploadbtn += '<button type="button" id="myBtn_resend_lease" onClick="resendLease()">Resend Lease</button>'
                            }
                            
                            uploadbtn += '<label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="{{$file->fileId}}" onChange="UploadDocument(\'{{$file->fileId}}\')" hidden/>';
                            $("#"+fileForApproval+"Banner").empty().append(uploadbtn);

                        }
                        
                        refreshActions();
                        checkUserStatus();

                        $("#myModal").html("");
                        $("#myModal").css("display", "none");

                    },
                });

            }
        })
    } 

    function refresh_this_modal() {
        $("#myModal").html("<div class='modal-content'>Loading</div>");

        $.ajax({
            type: "get",
            url: "/documents/view-document/{{$file->id}}",
            success: function (data) {
                $("#myModal").html(data);
                $("#myModal").css("display", "block");
            },
        });
    }

    function editData() {

        $('.edit_info .noedit').removeAttr('disabled');

        $('.edit_info .noedit').addClass('curedit');
        $('.edit_info .noedit').removeClass('noedit');

        $('.btns_approve_data').hide();
        $('.btns_save_data').show();
    }

    function cancelEditData(type) {

        if(type == "cancel"){
            checkUserStatus();
            refreshActions();
            $('.edit_info .curedit').each( function(){
                let fieldId = $(this)[0]["id"]
                if($("#"+fieldId).data('old')){

                    if($("#"+fieldId).data('new')){
                        var data_old = $("#"+fieldId).data('new')
                        $("#"+fieldId).val(data_old)
                    }else{
                        var data_old = $("#"+fieldId).data('old')
                        $("#"+fieldId).val(data_old)
                    }
                    
                    const div = document.getElementById(fieldId)
                    delete div.dataset.old

                    if(fieldId == "bPayment"){
                        if(data_old == "Via an INTERMEDIARY"){
                            $("#viaInt").removeClass("hidden")
                            $("#bint").removeClass("hidden")
                        }else{
                            $("#viaInt").addClass("hidden")
                            $("#bint").addClass("hidden")
                        }
                    }

                    if(fieldId == "bint"){
                        if(data_old == "Other"){
                            $("#spesificInt").removeClass("hidden")
                            $("#bIntName").removeClass("hidden")
                        }else{
                            $("#spesificInt").addClass("hidden")
                            $("#bIntName").addClass("hidden")
                        }
                    }

                    if(fieldId == "nationality"){
                        if(data_old == "International"){
                            $("#passportField").removeClass('hidden')
                            $("#idField").addClass('hidden')
                        }else{
                            $("#idField").removeClass('hidden')
                            $("#passportField").addClass('hidden')
                        }
                    }
                }

                if(fieldId == "bint"){
                    if(!data_old && $("#bint").val() == "Other"){
                        $("#spesificInt").removeClass("hidden")
                        $("#bIntName").removeClass("hidden")
                    }else{
                        $("#spesificInt").addClass("hidden")
                        $("#bIntName").addClass("hidden")
                    }
                }
            });
        }else{
            checkUserStatus();
            refreshActions();
            $('.edit_info .curedit').each( function(){
                let fieldId = $(this)[0]["id"]
                if($("#"+fieldId).data('old')){
                    var data_curr = $("#"+fieldId).val()
                    $("#"+fieldId).val(data_curr)
                    const div = document.getElementById(fieldId)
                    delete div.dataset.old
                    div.dataset.new = data_curr

                    if(fieldId == "nationality"){
                        if(data_curr == "International"){
                            $("#passportField").removeClass('hidden')
                            $("#idField").addClass('hidden')
                        }else{
                            $("#idField").removeClass('hidden')
                            $("#passportField").addClass('hidden')
                        }
                    }
                }
            });
        }

        $('.edit_info .curedit').attr('disabled', true);
        $('.edit_info .curedit').addClass('noedit');
        $('.edit_info .curedit').removeClass('curedit');        

        $('.btns_approve_data').show();
        $('.btns_save_data').hide();
    }

    function ValidCell(cell, fieldId, fieldError, code){
        // if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
        //     $("#"+fieldId).removeClass("fieldError")
        //     $("#"+fieldError).addClass('hidden')
        // }else{
        //     $("#"+fieldId).addClass("fieldError")
        //     $("#"+fieldError).removeClass('hidden')
        // }

        if(code === "+27"){
            if (/^[1-9][0-9]{8}$/.test(cell)) {
                $("#"+fieldId).removeClass("fieldError")
                $("#"+fieldError).addClass('hidden')
            }else{
                $("#"+fieldId).addClass("fieldError")
                $("#"+fieldError).removeClass('hidden')
            }
        }else{
            if(/^[+][0-9]{1,3}$/.test(code)){
                if(/^(0|[1-9][0-9]*)$/.test(cell)){
                    $("#"+fieldId).removeClass("fieldError")
                    $("#"+fieldError).addClass('hidden')
                }else{
                    $("#"+fieldId).addClass("fieldError")
                    $("#"+fieldError).removeClass('hidden')
                }
            }else{
                $("#"+fieldId).addClass("fieldError")
                $("#"+fieldError).removeClass('hidden')
            }
        }
    }

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    $("#burEmail").on('keyup blur', function(){

        if(isEmail($("#burEmail").val())){
            $("#burEmail").removeClass("fieldError")
            $("#burEmail_error").addClass('hidden')
        }else{
            $("#burEmail").addClass("fieldError")
            $("#burEmail_error").removeClass('hidden')
        }
    })
    $("#sponEmail").on('keyup blur', function(){

        if(isEmail($("#sponEmail").val())){
            $("#sponEmail").removeClass("fieldError")
            $("#sponEmail_error").addClass('hidden')
        }else{
            $("#sponEmail").addClass("fieldError")
            $("#sponEmail_error").removeClass('hidden')
        }
    })

    $("#burEmail, #idNumber").on('blur', function(){
        $.ajax({
            type: "post",
            url: "/email-check",
            data: {
                email: $("#burEmail").val(),
                idNumber: $("#idNumber").val(),
            },

            success: function (data) {
                console.log(data)
                
                if (data[0] == "done") {

                    if($("#burEmail").val() !== ""){
                        if(data[1] == false){
                            $("#burEmail_InUse").removeClass('hidden')
                            $("#burEmail").addClass('fieldError')
                        }else{
                            $("#burEmail_InUse").addClass('hidden')
                            $("#burEmail").removeClass('fieldError')
                        }
                    }

                    // if($("#idNumber").val() !== ""){
                    //     if(data[2] == false){
                    //         $("#idNumber_InUse").removeClass('hidden')
                    //         $("#idNumber").addClass('fieldError')
                    //     }else{
                    //         $("#idNumber_InUse").addClass('hidden')
                    //         $("#idNumber").removeClass('fieldError')
                    //     }
                    // }


                }
            },
        });
    })
    

    $("#burContactNo").on('keyup blur', function(){
        cell = $("#burContactNo").val()
        ValidCell(cell, "burContactNo", "burContactNo_error", $("#burContactCode").val());
    })
    $("#burContactCode").on('keyup blur', function(){
        cell = $("#burContactNo").val()
        ValidCell(cell, "burContactNo", "burContactNo_error", $("#burContactCode").val());
    })
    $("#sponCellNo").on('keyup blur', function(){
        cell = $("#sponCellNo").val()
        ValidCell(cell, "sponCellNo", "sponCellNo_error", $("#sponCellCode").val());
    })
    $("#sponCellCode").on('keyup blur', function(){
        cell = $("#sponCellNo").val()
        ValidCell(cell, "sponCellNo", "sponCellNo_error", $("#sponCellCode").val());
    })


    function updateData() {
        //Check if any hidden fields are required, remove required check

        if($("#nationality").val() == "South Africa"){
            let idNumber = $("#idNumber").val();
            if(idNumber.length == 13){
                ValidID(idNumber, "idNumber", "idNumber_error")
            }else{
                $("#idNumber").addClass("fieldError")
                $("#idNumber_error").removeClass('hidden')
            } 
        }

        $('.edit_info .hidden .fieldError').removeClass('fieldError').val('');
        
        var req = 1;

        $('.edit_info .required').each( function(){
            if($(this).val() === "" || $(this).val() === null){
                if (!$(this).parent().hasClass('hidden')) {
                    req = 0

                    console.log($(this)[0]["id"])

                    $(this).addClass("fieldError")
                    id = $(this)[0]["id"]
                    $("#"+id+"_error").removeClass("hidden")
                }
            }

            if(!$(this).parent().hasClass('hidden') && $(this).hasClass('fieldError')){
                req = 0
            }
        });

        if (req === 1) {
            $.ajax({
                type: "post",
                url: "/documents/save-data",
                data: $('.edit_info').serialize(),

                success: function (data) {
                    

                    if (data[0] === "done") {
                        cancelEditData('done');

                    } else {
                        alert("error");
                        console.log(data);
                    }

                    if(data[1] == '1'){
                        window.location.reload();
                    }

                }
            });            

        } else {
            Swal.fire(
                'Blank Fields',
                'Please fill in all Required Fields!',
                'error'
            );
        }

        return false;
    }
</script>