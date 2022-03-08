<span class="close">&times;</span>

<style>
    .panes {
        display: flex;
    }

    .left_pane {
        width: 30%;
    }

    .right_pane {
        flex: 1;
    }

    .noedit {
        background-color: white !important;
        outline: none;
    }

    .edit_info input:read-only, .edit_info select:read-only {
        opacity: 1;
    }

    .edit_info select {
        background-color: #f0f0f0;
        border-radius: 7px;
        border: none;
        width: 65%;
        padding: 10px;
    }
</style>

<div class="panes">
    <div class="left_pane">
        <h2 id="modalHeader">{{$popupTitle}}</h2>
        <small>Uploaded by {{$file->uploaded_by}} at {{($file->created_at->format("H|m"))}} on the {{($file->created_at->format("d|m|Y"))}}</small>
        <br/>
        
        <input type="hidden" id="FileIdForApproval" value="{{$file->id}}">
        <input type="hidden" id="FileNameForApproval" value="{{$file->fileId}}">
        
        <span id="actionUserName_error" class="hidden" ><small style="color:red">This field is required</small></span>
        <br/>
        <input type="text" id="actionUserName" placeholder="Name" value="{{Auth::user()->userFirstName}}">
        
        <br/>

        <div id="reasonBox" style="height:38px; margin-top:-21px; width:65%">
            <span id="rejectReason_error" class="hidden" ><small style="color:red">This field is required</small></span>
            <br/>
            <input type="text" id="rejectReason" placeholder="Type field for rejection reason" style="width:100%">
        </div>
        
        <div class="flex" style="margin: 35px 0;" id="buttonSection">
            @if(Auth::user()->role_approve_docs == 1 && (empty($field_data) || !empty($field_data) && $data_approved == 1) && $doc_approved == 1)
                <button type="button" class="rejected button" id="rejected" onClick="actionFile('Rejected', '')">Reject</button>
            @endif
        
            <button type="button" class="amend button" id="amend"  onClick="actionFile('Approved', 'Amend')">Amend</button>
        
            @if(Auth::user()->role_approve_docs == 1 && (empty($field_data) || !empty($field_data) && $data_approved == 1) && $doc_approved == 1)
                <button type="button" class="approve button" id="approved" onClick="actionFile('Approved', '')">Approve</button>
            @endif
        
            @if(Auth::user()->role_delete_uploaded == 1)
                <button type="button" class="rejected button" id="delete" onClick="removeFile()">Delete</button>
            @endif
        </div>

        <hr style="margin-bottom: 35px;">

        <h3>Info</h3>

        

        <div>
            @if($file->fileId == "studentCard")
                <span style='font-weight:bold'>Name:</span> {{$user->userFirstName}} {{$user->userLastname}}
            @endif

            @if($file->fileId == "proofRegistration")
                <span style='font-weight:bold'>Name:</span> {{$user->userFirstName}} {{$user->userLastname}}
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
        </div>
        
        @if (!empty($field_data))
            <hr style="margin: 35px 0;">

            <h3>Review</h3>

            <form class="edit_info">
                @csrf

                @if($file->fileId == "copyID")
                    <span style='font-weight:bold'>Name:</span> {{$user->userFirstName}} {{$user->userLastname}}
                    <br/><span style='font-weight:bold'>Nationality:</span> {{$user->userNationality}}                    
                    @if ($user->userNationality == "South Africa")
                        <br/><span style='font-weight:bold'>I.D Number:</span> {{$user->userId}}
                    @else
                        <br/><span style='font-weight:bold'>Passport:</span> {{$user->userPassport}}
                    @endif
                    <br/><span style='font-weight:bold'>DOB:</span> {{$user->userDob}}
                    <br/><span style='font-weight:bold'>Gender:</span> @if ($user->userTitle == "Mr") Male @else Female @endif</span>
                @endif

                @if($file->fileId == "studentCard")
                    <span style='font-weight:bold'>Student Number:</span> <input class="noedit required" name="studentNo" disabled type="text" value="{{$user->studentNo}}">
                @endif

                @if($file->fileId == "proofRegistration")
                    <span style='font-weight:bold'>Institution:</span>
                    <select name="institStudy" class="noedit required" disabled>
                        <option value="Choose one" selected disabled>Choose one</option>
                        @foreach($lookups as $lookup)
                            @if($lookup->group_id == "1")
                                <option value="{{$lookup->id}}" <?php if($user->institutionStudy == $lookup->id){echo "selected";} ?>>{{$lookup->lookup_name}}</option>
                            @endif
                        @endforeach
                    </select>

                    <br /><span style='font-weight:bold'>Faculty:</span>
                    <select name="faculty" class="noedit required" disabled>
                        <option value="" disabled selected>Select One</option>
                        @foreach($lookups as $lookup)
                            @if($lookup->group_id == "3")
                                <option value="{{$lookup->id}}" <?php if($user->faculty == $lookup->id){echo "selected";} ?>>{{$lookup->lookup_name}}</option>
                            @endif
                        @endforeach
                    </select>
                    
                    <br /><span style='font-weight:bold'>Year of Study:</span>
                    <select name="yearOfStudy" class="noedit required" disabled>
                        <option value="" selected disabled>Please select one</option>
                        @foreach($lookups as $lookup)
                            @if($lookup->group_id == "6")
                                <option value="{{$lookup->lookup_name}}" <?php if($user->yearOfStudy == $lookup->lookup_name){echo "selected";} ?>>{{$lookup->lookup_name}}</option>
                            @endif
                        @endforeach
                    </select>
                @endif
                
                @if($file->fileId == "bursaryApproval")
                    <span style='font-weight:bold'>Company Name:</span> {{$user->burCompanyName}}
                    <br /><span style='font-weight:bold'>Registration Number:</span> {{$user->burRegNo}}
                    <br /><span style='font-weight:bold'>Contact Person:</span> {{$user->burContactPerson}}
                    <br /><span style='font-weight:bold'>Contact Number:</span> {{$user->burContactNo}}
                    <br /><span style='font-weight:bold'>Email Address:</span> {{$user->burEmail}}
                    <br /><span style='font-weight:bold'>Physical Address:</span> {{$user->bAddress1}}, {{$user->bAddress2}}, {{$user->bAddressCity}}, {{$user->bAddressPostal}}
                    <br /><span style='font-weight:bold'>How is the Bursary Paid:</span> {{$user->bPayment}}<br />{{$user->bint}} {{$user->bIntName}}
                @endif

                @if($file->fileId == "bankStatement")
                    <span style='font-weight:bold'>Name:</span> {{$user->sponFirstName}} {{$user->sponLastName}}
                    <br /><span style='font-weight:bold'>Cellphone Number:</span> {{$user->sponCell}}
                    <br /><span style='font-weight:bold'>Email Address:</span> {{$user->sponEmail}}                
                    <br /><span style='font-weight:bold'>Nationality:</span> {{$user->pNationality}}
                    
                    @if($user->pNationality == "South Africa")
                        <br /><span style='font-weight:bold'>ID Number:</span> {{$user->sponId}}
                    @else
                        <br /><span style='font-weight:bold'>Passport Number:</span> {{$user->pPassportNo}}
                    @endif

                    <br /><span style='font-weight:bold'>Physical Address:</span> {{$user->pAddress1}}, {{$user->pAddress2}}, {{$user->pAddressCity}}, {{$user->pAddressPostal}}
                @endif
            
                @if(Auth::user()->role_approve_docs == 1)
                    <div style="margin-top: 35px;" class="btns_approve_data">
                        @if($data_approved == 0)
                            <button type="button" class="approve button" style="height:40px;" onClick="approveData('1')">Approve</button>
                            <button type="button" class="amend button" style="height:40px;" onClick="editData()">Edit</button>
                        @endif

                        @if($doc_approved == 1)
                            <button type="button" class="rejected button" style="height:40px;" onClick="approveData('0')">Reject</button>
                        @endif
                    </div>

                    @if($data_approved == 0)
                        <div style="margin-top: 35px;display: none;" class="btns_save_data">
                            <input type="hidden" name="userUid" value="{{$user->id}}">
                            <input type="hidden" name="type" value="{{$file->fileId}}">

                            <button type="button" class="approve button" style="height:40px;" onClick="updateData()">Update</button>
                            <button type="button" class="rejected button" style="height:40px;" onClick="cancelEditData()">Cancel</button>
                        </div>
                    @endif
                @endif
            </form>
    
            <div id="edit_data"></div>
        @endif
    </div>

    <div class="right_pane">
        <div class="pdfSection" style="margin-top: 0;">
            @if(!empty($file))
                @if(strpos($file->fileUrl, "pdf") > 0)
                    <embed id="pdfView" src="/storage/{{$file->fileUrl}}"  width="100%" height="800px" />
                @else
                    <img src="/storage/{{$file->fileUrl}}" alt="">
                @endif
            @else
                No File to show    
            @endif
        </div>
    </div>
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

        console.log(type);

        if (type === "0") {
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
        var req = 1;

        $('.edit_info .required').each( function(){
            if($(this).val() === "" || $(this).val() === null){
                req = 0

                console.log($(this)[0]["id"])

                $(this).addClass("fieldError")
                id = $(this)[0]["id"]
                $("#"+id+"_error").removeClass("hidden")
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