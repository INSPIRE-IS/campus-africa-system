<span class="close">&times;</span>
<h2 id="modalHeader">{{$popupTitle}}</h2>
<small>Uploaded by {{$file->uploaded_by}} at {{($file->created_at->format("H|m"))}} on the {{($file->created_at->format("d|m|Y"))}}</small>
<br/>

<input type="hidden" id="FileIdForApproval" value="{{$file->id}}">
<input type="hidden" id="FileNameForApproval" value="{{$file->fileId}}">

<span id="actionUserName_error" class="hidden" ><small style="color:red">This field is required</small></span>
<br/>
<input type="text" id="actionUserName" placeholder="Name" value="{{Auth::user()->userFirstName}}">

<br/>

<div class="flex" style="margin-top:35px;" id="buttonSection">
    <div id="reasonBox" style="height:38px; margin-top:-21px; width:65%">
        <span id="rejectReason_error" class="hidden" ><small style="color:red">This field is required</small></span>
        <br/>
        <input type="text" id="rejectReason" placeholder="Type field for rejection reason" style="width:100%">
    </div>

    @if(Auth::user()->role_approve_docs == 1 && (empty($field_data) || !empty($field_data) && $data_approved == 1) && $doc_approved == 1)
        <button type="button" class="rejected button" id="rejected" onClick="actionFile('Rejected', '')">Reject with reason</button>
    @endif

    <button type="button" class="amend button" id="amend"  onClick="actionFile('Approved', 'Amend')">Amend</button>

    @if(Auth::user()->role_approve_docs == 1 && (empty($field_data) || !empty($field_data) && $data_approved == 1) && $doc_approved == 1)
        <button type="button" class="approve button" id="approved" onClick="actionFile('Approved', '')">Approve</button>
    @endif

    @if(Auth::user()->role_delete_uploaded == 1)
        <button type="button" class="rejected button" id="delete" onClick="removeFile()">Delete</button>
    @endif
</div>

<div id="infoChecker">
    <br/><hr/><br/>

    @if (!empty($field_data))
        <div class="flex">
            <p id="infoToCheck" style="flex: 1;">
                @if($file->fileId == "copyID")
                    <span style='font-weight:bold'>Name:</span> {{$user->userFirstName}} {{$user->userLastname}}</span>
                    <br/><span style='font-weight:bold'>Nationality:</span> {{$user->userNationality}}</span>                    
                    @if ($user->userNationality == "South Africa")
                        <br/><span style='font-weight:bold'>I.D Number:</span> {{$user->userId}}</span>
                    @else
                        <br/><span style='font-weight:bold'>Passport:</span> {{$user->userPassport}}</span>
                    @endif
                    <br/><span style='font-weight:bold'>DOB:</span> {{$user->userDob}}</span>
                    <br/><span style='font-weight:bold'>Gender:</span> @if ($user->userTitle == "Mr") Male @else Female @endif</span>
                @endif

                @if($file->fileId == "studentCard")
                    <span style='font-weight:bold'>Student Number:</span> {{$user->studentNo}}</span>
                @endif

                @if($file->fileId == "proofRegistration")
                    <span style='font-weight:bold'>Institution:</span> @foreach($lookups as $lookup) @if($lookup->id == $user->institutionStudy) {{$lookup->lookup_name}} @endif @endforeach</span>
                    <br /><span style='font-weight:bold'>Faculty:</span> @foreach($lookups as $lookup) @if($lookup->id == $user->faculty) {{$lookup->lookup_name}} @endif @endforeach</span>
                @endif
                
                @if($file->fileId == "bursaryApproval")
                    <span style='font-weight:bold'>Company Name:</span> {{$user->burCompanyName}}</span>
                    <br /><span style='font-weight:bold'>Registration Number:</span> {{$user->burRegNo}}</span>
                    <br /><span style='font-weight:bold'>Contact Person:</span> {{$user->burContactPerson}}</span>
                    <br /><span style='font-weight:bold'>Contact Number:</span> {{$user->burContactNo}}</span>
                    <br /><span style='font-weight:bold'>Email Address:</span> {{$user->burEmail}}</span>
                    <br /><span style='font-weight:bold'>Physical Address:</span> {{$user->bAddress1}}, {{$user->bAddress2}}, {{$user->bAddressCity}}, {{$user->bAddressPostal}}</span>
                    <br /><span style='font-weight:bold'>How is the Bursary Paid:</span> {{$user->bPayment}}<br />{{$user->bint}} {{$user->bIntName}}</span>
                @endif

                @if($file->fileId == "bankStatement")
                    <span style='font-weight:bold'>Name:</span> {{$user->sponFirstName}} {{$user->sponLastName}}</span>
                    <br /><span style='font-weight:bold'>Cellphone Number:</span> {{$user->sponCell}}</span>
                    <br /><span style='font-weight:bold'>Email Address:</span> {{$user->sponEmail}}</span>                
                    <br /><span style='font-weight:bold'>Nationality:</span> {{$user->pNationality}}</span>
                    
                    @if($user->pNationality == "South Africa")
                        <br /><span style='font-weight:bold'>ID Number:</span> {{$user->sponId}}</span>
                    @else
                        <br /><span style='font-weight:bold'>Passport Number:</span> {{$user->pPassportNo}}</span>
                    @endif

                    <br /><span style='font-weight:bold'>Physical Address:</span> {{$user->pAddress1}}, {{$user->pAddress2}}, {{$user->pAddressCity}}, {{$user->pAddressPostal}}</span>
                @endif
            </p>        
            
            @if(Auth::user()->role_approve_docs == 1)
                <div id="infoButtons">
                    @if($data_approved == 0)
                        <button type="button" class="approve button" style="height:40px;" onClick="approveData(1)">Approve</button>
                        <button type="button" class="amend button" style="height:40px;" onClick="editData()">Edit</button>
                    @endif

                    @if($doc_approved == 1)
                        <button type="button" class="rejected button" style="height:40px;" onClick="approveData(0)">Reject</button>
                    @endif
                </div>
            @endif
        </div>

        <br/><br/><hr/>

        <div id="edit_data"></div>
    @endif
</div>


<div class="pdfSection">
    @if(!empty($file))
    <embed id="pdfView" src="/storage/{{$file->fileUrl}}"  width="100%" height="600px" />
        @else
        No File to show    
    @endif
</div>

<script>
    jQuery(document).ready( function() {
        $('.close').on("click", function(e) {
            $("#myModal > .modal-content").html("");
            $("#myModal").css("display", "none");
        });
    });

    function approveData(type) {
        let message = "You are about to approve this data";

        if (type === 0) {
            let message = "You are about to reject this data";
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
                            refresh_this_modal();

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
                                if (data > 0) {
                                    $("#"+fileForApproval+"Status").empty().append('<div class="button action"></div>');

                                    refreshActions();                                    

                                    refresh_this_modal();

                                } else {
                                    Swal.fire(
                                        'Error',
                                        data,
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
                    window.location.reload();

                } else {
                    $("#"+fileForApproval+"Status").empty().append('<div class="button '+type.toLowerCase()+'"></div>');

                    //Add back upload button
                    if (type == "Rejected") {
                        @if(Auth::user()->role_upload_docs == 1)
                            let uploadbtn = '<label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="{{$file->fileId}}" hidden/>';
                            $("#"+fileForApproval+"Banner").empty().append(uploadbtn);
                        @endif
                    }
                
                    refreshActions();

                    $("#myModal > .modal-content").html("");
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
                        @if(Auth::user()->role_upload_docs == 1)
                            let uploadbtn = '<label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="{{$file->fileId}}" hidden/>';
                            $("#"+fileForApproval+"Banner").empty().append(uploadbtn);
                        @endif
                        
                        refreshActions();

                        $("#myModal > .modal-content").html("");
                        $("#myModal").css("display", "none");

                    },
                });

            }
        })
    } 

    function refresh_this_modal() {
        $("#myModal > .modal-content").html("Loading...");

        $.ajax({
            type: "get",
            url: "/documents/view-document/{{$file->id}}",
            success: function (data) {
                $("#myModal > .modal-content").html(data);
                $("#myModal").css("display", "block");
            },
        });
    }

    function editData() {
        $("#edit_data").html("Loading form...");

        $.ajax({
            type: "get",
            url: "/documents/edit-data/{{$user->id}}/{{$file->fileId}}",
            success: function (data) {
                $('#infoButtons').hide();
                $("#edit_data").html(data);
            },
        });
    }
</script>