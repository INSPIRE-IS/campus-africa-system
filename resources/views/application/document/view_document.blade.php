<div class="modal-content" style="width: @if(!empty($file) && !empty($file->fileName)) {{ "99%" }} @else {{ "50%" }}@endif">
    <span class="close">&times;</span>

    <style>
        .panes {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        .left_pane {
            width: 30%;
            padding-right: 20px;
        }

        .right_pane {
            flex: 70%;
        }

        .noedit {
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
            
            @if(Auth::user()->role_approve_docs == 1 && (empty($field_data) || !empty($field_data) && $data_approved == 1))
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
                        @if (Auth::user()->role_delete_uploaded == 1)
                            <button type="button" class="rejected button" id="rejected" onClick="actionFile('Rejected', '')">Reject</button>
                        @endif

                    @else
                        @if(Auth::user()->role_upload_docs == 1)
                            <button type="button" class="amend button" id="amend"  onClick="actionFile('Approved', 'Amend')">Amend</button>
                        @endif

                        @if(Auth::user()->role_approve_docs == 1)
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
                    <br/><span style='font-weight:bold'>Application Number:</span> #{{$user->bookingRef}}
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

                    @if($file->fileId == "copyID")
                        <div class="form-field">
                            <span style='font-weight:bold'>First Name:</span>
                            <input class="noedit required" name="userFirstName" id="userFirstName" disabled type="text" value="{{$user->userFirstName}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Last Name:</span> <input class="noedit required" name="userLastname" id="userLastname" disabled type="text" value="{{$user->userLastname}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Nationality:</span>
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
                            <span style='font-weight:bold'>I.D Number:</span> <input class="noedit required" name="userId" id="idNumber" disabled type="text" value="{{$user->userId}}">
                        </div>

                        <div class="form-field <?php if($user->userNationality == "South Africa"){echo "hidden";} ?>" id="passportField">
                            <span style='font-weight:bold'>Passport:</span> <input class="noedit required" name="userPassport" id="passportNo" disabled type="text" value="{{$user->userPassport}}">                                       
                        </div>
                        
                        <div class="form-field">
                            <span style='font-weight:bold'>DOB:</span> <input class="noedit required" name="userDob" id="userDob" disabled type="text" value="{{$user->userDob}}">                    
                        </div>
                    @endif

                    @if($file->fileId == "studentCard")
                        <div class="form-field">
                            <span style='font-weight:bold'>Student Number:</span> <input class="noedit required" name="studentNo" id="studentNo" disabled type="text" value="{{$user->studentNo}}">
                        </div>
                    @endif

                    @if($file->fileId == "proofRegistration")
                        <div class="form-field">
                            <span style='font-weight:bold'>Institution:</span>
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
                            <span style='font-weight:bold'>Faculty:</span>
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
                            <span style='font-weight:bold'>Year of Study:</span>
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
                            <span style='font-weight:bold'>Student Type:</span>
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
                            <span style='font-weight:bold'>Student Type:</span>
                            <select name="studentType" id='studentType' class="noedit required" disabled>
                                <option value="" selected disabled>Select a Student Type</option>
                                <option value="NSFAS" <?php if($user->studentType == "NSFAS"){echo "selected";} ?>>NSFAS</option>
                                <option value="Bursary" <?php if($user->studentType == "Bursary"){echo "selected";} ?>>Bursary</option>
                                <option value="Cash / Direct" <?php if($user->studentType == "Cash / Direct"){echo "selected";} ?>>Cash</option>
                            </select>
                        </div>    
                        
                        <div class="form-field">    
                            <span style='font-weight:bold'>Company Name:</span> <input class="noedit required" name="burCompanyName" id="burCompanyName" disabled type="text" value="{{$user->burCompanyName}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Registration Number:</span> <input class="noedit required" name="burRegNo" id="burRegNo" disabled type="text" value="{{$user->burRegNo}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Contact Person:</span> <input class="noedit required" name="burContactPerson" id="burContactPerson" disabled type="text" value="{{$user->burContactPerson}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Contact Number:</span> <input class="noedit required" name="burContactNo" id="burContactNo" disabled type="text" value="{{$user->burContactNo}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Email Address:</span> <input class="noedit required" name="burEmail" id="burEmail" disabled type="text" value="{{$user->burEmail}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>NSFAS Bursar:</span>
                            <select name="nsfas_bursar" id='nsfas_bursar' class="noedit required" disabled>                            
                                <option value="0" <?php if($user->nsfas_bursar == "" || $user->nsfas_bursar == "0"){echo "selected";} ?>>No</option>
                                <option value="1" <?php if($user->nsfas_bursar == "1"){echo "selected";} ?>>Yes</option>
                            </select>
                        </div>
                    @endif

                    @if($file->fileId == "bankStatement")
                        <div class="form-field">    
                            <span style='font-weight:bold'>Student Type:</span>
                            <select name="studentType" id='studentType' class="noedit required" disabled>
                                <option value="" selected disabled>Select a Student Type</option>
                                <option value="NSFAS" <?php if($user->studentType == "NSFAS"){echo "selected";} ?>>NSFAS</option>
                                <option value="Bursary" <?php if($user->studentType == "Bursary"){echo "selected";} ?>>Bursary</option>
                                <option value="Cash / Direct" <?php if($user->studentType == "Cash / Direct"){echo "selected";} ?>>Cash</option>
                            </select>
                        </div> 

                        <div class="form-field">
                            <span style='font-weight:bold'>Sponsor First Name:</span> <input class="noedit required" name="sponFirstName" id="sponFirstName" disabled type="text" value="{{$user->sponFirstName}}">                        
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Sponsor Last Name:</span> <input class="noedit required" name="sponLastName" id="sponLastName" disabled type="text" value="{{$user->sponLastName}}">
                        </div>

                        <div class="form-field">
                            <span style='font-weight:bold'>Sponsor Nationality:</span>
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
                            <span style='font-weight:bold'>Sponsor I.D Number:</span> <input class="noedit required" name="sponId" id="pIdNumber" disabled type="text" value="{{$user->sponId}}">
                        </div>

                        <div class="form-field <?php if($user->pNationality == "South Africa"){echo "hidden";} ?>" id="pPassportField">
                            <span style='font-weight:bold'>Sponsor Passport:</span> <input class="noedit required" name="pPassportNo" id="pPassportNo" disabled type="text" value="{{$user->pPassportNo}}">                                       
                        </div>
                    @endif

                    @if(explode("_", $file->fileId)[0] !== "other")
                
                        @if($user->rob_60_cancelled == 0)
                            <div style="margin-top: 35px;" class="btns_approve_data">
                                @if(Auth::user()->role_approve_docs == 1 && $data_approved == 0)
                                    <button type="button" class="approve button" style="height:40px;" onClick="approveData('1')">Approve</button>
                                @endif

                                @if(Auth::user()->role_edit_app == 1 && $data_approved == 0)
                                    <button type="button" class="amend button" style="height:40px;" onClick="editData()">Edit</button>
                                @endif

                                @if(Auth::user()->role_approve_docs == 1 && ($doc_approved == 1 || $data_approved == 0))
                                    <button type="button" class="rejected button" style="height:40px;" onClick="approveData('0')">Reject</button>
                                @endif
                            </div>

                            @if(Auth::user()->role_edit_app == 1 && $data_approved == 0)
                                <div style="margin-top: 35px;display: none;" class="btns_save_data">
                                    <input type="hidden" name="userUid" value="{{$user->id}}">
                                    <input type="hidden" name="type" value="{{$file->fileId}}">

                                    <button type="button" class="approve button" style="height:40px;" onClick="updateData()">Update</button>
                                    <button type="button" class="rejected button" style="height:40px;" onClick="cancelEditData()">Cancel</button>
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
                        <embed id="pdfView" src="/storage/{{$file->fileUrl}}"  width="100%" height="800px" />
                    @else
                        <img src="/storage/{{$file->fileUrl}}" alt="">
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    jQuery(document).ready( function() {
        $('.close').on("click", function(e) {
            $("#myModal").html("");
            $("#myModal").css("display", "none");
        });

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
                        userId: "{{$user->id}}",
                    },

                    success: function (data) {

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
                        let userId = "{{$user->id}}";

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

                    let uploadbtn = '<a href="/storage/'+data[0]['fileUrl']+'" download="'+data[1]['userFirstName']+'_'+data[1]['userLastname']+'_'+data[1]['userId']+'_Lease.pdf" style="margin-right:5px;"><button type="button" id="myBtn_download">Download Lease</button></a> <button type="button" onclick="btn_view_document_ajax('+data[0]['id']+')">View Document</button>';

                    $("#"+fileForApproval+"Banner").empty().append(uploadbtn)

                    refreshActions();

                    $("#myModal").html("");
                    $("#myModal").css("display", "none");

                } else {
                    $("#"+fileForApproval+"Status").empty().append('<div class="button '+type.toLowerCase()+'"></div>');

                    //Add back upload button
                    if (type == "Rejected" && fileForApproval.substring(0, 5) == "other") {
                        @if(Auth::user()->role_upload_docs == 1)
                            let uploadbtn = '<label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="{{$file->fileId}}" onChange="UploadDocument(\'{{$file->fileId}}\')" hidden/> <button type="button" style="margin-left:5px;" onClick="deleteFile(\'{{$file->fileId}}\')">Delete</button>';
                            $("#"+fileForApproval+"Banner").empty().append(uploadbtn);
                        @endif
                    }else if(type == "Rejected" && fileForApproval !== "moveIn" ){
                        @if(Auth::user()->role_upload_docs == 1)
                            let uploadbtn = '<label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="{{$file->fileId}}" onChange="UploadDocument(\'{{$file->fileId}}\')" hidden/>';
                            $("#"+fileForApproval+"Banner").empty().append(uploadbtn);
                        @endif
                    }else if(type == "Rejected" && fileForApproval == "moveIn"){
                        let uploadbtn = '<a href="/storage/'+data[0]['fileUrl']+'" download="'+data[1]['userFirstName']+'_'+data[1]['userLastname']+'_'+data[1]['userId']+'_Lease.pdf" style="margin-right:5px;"><button type="button" id="myBtn_download">Download Lease</button></a> <button type="button" id="myBtn_regen_lease" style="margin-right:5px;">Regen Lease</button> <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="{{$file->fileId}}" onChange="UploadDocument(\'{{$file->fileId}}\')" hidden/>';
                        $("#"+fileForApproval+"Banner").empty().append(uploadbtn);
                    }
                
                    refreshActions();

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
                            let uploadbtn = '<a href="/storage/'+data[0]['fileUrl']+'" download="'+data[1]['userFirstName']+'_'+data[1]['userLastname']+'_'+data[1]['userId']+'_Lease.pdf" style="margin-right:5px;"><button type="button" id="myBtn_download">Download Lease</button></a> <button type="button" id="myBtn_regen_lease" style="margin-right:5px;">Regen Lease</button> <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="{{$file->fileId}}" onChange="UploadDocument(\'{{$file->fileId}}\')" hidden/>';
                            $("#"+fileForApproval+"Banner").empty().append(uploadbtn);
                        }
                        
                        refreshActions();

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

    function cancelEditData() {
        $('.edit_info .curedit').attr('disabled', true);
        $('.edit_info .curedit').addClass('noedit');
        $('.edit_info .curedit').removeClass('curedit');        

        $('.btns_approve_data').show();
        $('.btns_save_data').hide();
    }

    function updateData() {
        //Check if any hidden fields are required, remove required check
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
        });

        if (req === 1) {
            $.ajax({
                type: "post",
                url: "/documents/save-data",
                data: $('.edit_info').serialize(),

                success: function (data) {

                    if (data === "done") {
                        cancelEditData();

                    } else {
                        alert("error");
                        console.log(data);
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