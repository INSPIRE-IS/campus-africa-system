@extends('layouts.admin')

@section('content')


<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <br/>
      <div class="appSearchForm">
        <div class="indexTab flex" id="applicationIndex">
            <label>Legend</label>
            <div class="keys">
                <div id="indexOne"><div class="button approved"></div><span class="text">Approved</span></div>
                <div id="indexOne"><div class="button action"></div><span class="text">Pending Approval</span></div>
                <div id="indexOne"><div class="button rejected"></div><span class="text">Action Required</span></div>
                <div id="indexOne"><div class="button na"></div><span class="text">Not Applicable</span></div>
            </div>
        </div>
      </div>
        <br/>
        <br/>
        <div style="height:50px;"></div>
        <br/>
    <table id="documents_table" class="display">
        <thead>
            <tr>
                <th>Document Name</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($outstanding as $file)
                <tr>
                    <td>
                        @if($file == 'copyID')
                          I.D COPY
                        @elseif($file == 'studentCard')
                          STUDENT CARD
                        @elseif($file == 'bankStatement')
                          BANK STATEMENT
                        @elseif($file == 'proofRegistration')
                          REGISTRATION
                        @elseif($file == 'proof_of_payment')
                          PROOF OF PAYMENT
                        @elseif($file == 'moveIn')
                          LEASE
                        @endif
                    </td>
                    <td><span class="flex"><div id="robot_r_{{$file}}" class="rejectedRobot"></div> &nbsp;&nbsp; <span id="text_r_{{$file}}">Outstanding</span></span></td>
                    <td id="file_{{$file}}">
                      <label for="{{$file}}" id="{{$file}}Section" class="outstandingUpload">
                          <span class="text">Upload</span>
                          <i class="fas fa-file-upload" ></i>
                      </label>
                      </h3>
                      <input type="file" id="{{$file}}" hidden/>
                    </td>
                </tr>
            @endforeach

            @foreach($rejectedFiles as $file)
                <tr>
                    <td>
                        @if($file['fileId'] == 'copyID')
                          I.D COPY
                        @elseif($file['fileId'] == 'studentCard')
                          STUDENT CARD
                        @elseif($file['fileId'] == 'bankStatement')
                          BANK STATEMENT
                        @elseif($file['fileId'] == 'proofRegistration')
                          REGISTRATION
                        @elseif($file['fileId'] == 'proof_of_payment')
                          PROOF OF PAYMENT
                        @elseif($file['fileId'] == 'moveIn')
                          LEASE
                        @endif
                    </td>
                    <td><span class="flex"><div id="robot_r_{{$file}}" class="rejectedRobot"></div> &nbsp;&nbsp; <span id="text_r_{{$file}}">Invalid Document Please re-upload</span></span></td>
                    <td id="file_{{$file}}">
                      <label for="{{$file['fileId']}}" id="{{$file['fileId']}}Section" class="outstandingUpload">
                          <span class="text">Upload</span>
                          @if (!$agent->isMobile())
                            <i class="fas fa-file-upload" ></i>
                          @endif
                          
                      </label>
                      </h3>
                      <input type="file" id="{{$file['fileId']}}" hidden/>
                    </td>
                </tr>
            @endforeach

            @foreach($pendingFiles as $file)
                <tr>
                    <td>
                        @if($file['fileId'] == 'copyID')
                          I.D COPY
                        @elseif($file['fileId'] == 'studentCard')
                          STUDENT CARD
                        @elseif($file['fileId'] == 'bankStatement')
                          BANK STATEMENT
                        @elseif($file['fileId'] == 'proofRegistration')
                          REGISTRATION
                        @elseif($file['fileId'] == 'proof_of_payment')
                          PROOF OF PAYMENT
                        @elseif($file['fileId'] == 'moveIn')
                          LEASE
                        @endif
                    </td>
                    <td><span class="flex"><div class="pendingRobot"></div> &nbsp;&nbsp; Pending Approval</span></td>
                    <td>
                      <a href="/storage/{{$file['fileUrl']}}" style="text-decoration:inherit" target="_blank"><span class="outstandingUpload" style="padding:5px 67px;">View</span></a>
                    </td>
                </tr>
            @endforeach

            @foreach($approvedFiles as $file)
                <tr>
                    <td>
                        @if($file['fileId'] == 'copyID')
                          I.D COPY
                        @elseif($file['fileId'] == 'studentCard')
                          STUDENT CARD
                        @elseif($file['fileId'] == 'bankStatement')
                          BANK STATEMENT
                        @elseif($file['fileId'] == 'proofRegistration')
                          REGISTRATION
                        @elseif($file['fileId'] == 'proof_of_payment')
                          PROOF OF PAYMENT
                        @elseif($file['fileId'] == 'moveIn')
                          LEASE
                        @endif
                    </td>
                    <td><span class="flex"><div class="approvedRobot"></div> &nbsp;&nbsp; Approved</span></td>
                    <td>
                      <a href="/storage/{{$file['fileUrl']}}" style="text-decoration:inherit" target="_blank"><span class="outstandingUpload" style="padding:5px 67px;">View</span></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <br/>
</div>

<input type="hidden" id="adminName"/>

<div id="myModal" class="modal">

    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalHeader"></h2>
        <small>Uploaded by <span id="uploadedBy"></span> at <span id="uploadedTime"></span> on the <span id="uploadedDate"></span></small>
        <br/>
        <input type="hidden" id="FileIdForApproval">
        <div class="flex" style="margin-top:35px;" id="buttonSection">
            <input type="text" id="rejectReason" placeholder="Type field for rejection reason">
            <button type="button" class="rejected button" id="rejected" onClick="actionFile('Rejected', '')">Reject with reason</button>
            <button type="button" class="amend button" id="amend"  onClick="actionFile('Approved', 'Amend')">Amend &amp; Approve</button>
            <button type="button" class="approve button" id="approved" onClick="actionFile('Approved', '')">Approve</button>
        </div>

        <div class="pdfSection"></div>
    </div>

</div>

<script>
    Swal.fire({
        title: 'Please state your name',
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: false,
        confirmButtonText: 'Confirm',
        showLoaderOnConfirm: true,
        allowOutsideClick: false
        }).then((result) => {
        if (result.isConfirmed) {
            $("#adminName").val(result.value)
        }
    })

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

  $(document).ready( function () {
        $('#documents_table').DataTable({
          "order": [[ 2, "asc" ]]
        });
    } );

    $("#copyID").on('change', function(){
        $("#copyIDSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('copyID').files[0]);
        form_data.append("userUid", {{$user->id}});
        form_data.append("fileType", "copyID");
        form_data.append("adminName", $("#adminName").val());

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0] == "done") {
                    $("#text_r_copyID").empty().append("Pending Approval")
                    $("#robot_r_copyID").removeClass("rejectedRobot")
                    $("#robot_r_copyID").addClass("pendingRobot")
                    $("#file_copyID").empty().append('<a href="/storage/'+data[1]+'" style="text-decoration:inherit" target="_blank"><span class="outstandingUpload" style="padding:5px 67px;">View</span></a>')
                }
            },
        });
    });

    $("#studentCard").on('change', function(){
        $("#studentCardSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('studentCard').files[0]);
        form_data.append("userUid", {{$user->id}});
        form_data.append("fileType", "studentCard");
        form_data.append("adminName", $("#adminName").val());

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0] == "done") {
                    $("#text_r_studentCard").empty().append("Pending Approval")
                    $("#robot_r_studentCard").removeClass("rejectedRobot")
                    $("#robot_r_studentCard").addClass("pendingRobot")
                    $("#file_studentCard").empty().append('<a href="/storage/'+data[1]+'" style="text-decoration:inherit" target="_blank"><span class="outstandingUpload" style="padding:5px 67px;">View</span></a>')
                }
            },
        });  
    })

    $("#bankStatement").on('change', function(){
        $("#bankStatementSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('bankStatement').files[0]);
        form_data.append("userUid", {{$user->id}});
        form_data.append("fileType", "bankStatement");
        form_data.append("adminName", $("#adminName").val());

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0] == "done") {
                    $("#text_r_bankStatement").empty().append("Pending Approval")
                    $("#robot_r_bankStatement").removeClass("rejectedRobot")
                    $("#robot_r_bankStatement").addClass("pendingRobot")
                    $("#file_bankStatement").empty().append('<a href="/storage/'+data[1]+'" style="text-decoration:inherit" target="_blank"><span class="outstandingUpload" style="padding:5px 67px;">View</span></a>')
                }
            },
        });
    })

    $("#proofRegistration").on('change', function(){

        $("#proofRegistrationSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('proofRegistration').files[0]);
        form_data.append("userUid", {{$user->id}});
        form_data.append("fileType", "proofRegistration");
        form_data.append("adminName", $("#adminName").val());

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0] == "done") {
                    $("#text_r_proofRegistration").empty().append("Pending Approval")
                    $("#robot_r_proofRegistration").removeClass("rejectedRobot")
                    $("#robot_r_proofRegistration").addClass("pendingRobot")
                    $("#file_proofRegistration").empty().append('<a href="/storage/'+data[1]+'" style="text-decoration:inherit" target="_blank"><span class="outstandingUpload" style="padding:5px 67px;">View</span></a>')
                }
            },
        });
    })


    $("#proof_of_payment").on('change', function(){
        $("#proof_of_paymentSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('proof_of_payment').files[0]);
        form_data.append("userUid", {{$user->id}});
        form_data.append("fileType", "proof_of_payment");
        form_data.append("adminName", $("#adminName").val());

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0] == "done") {

                    $("#text_r_proof_of_payment").empty().append("Pending Approval")
                    $("#robot_r_proof_of_payment").removeClass("rejectedRobot")
                    $("#robot_r_proof_of_payment").addClass("pendingRobot")
                    $("#file_proof_of_payment").empty().append('<a href="/storage/'+data[1]+'" style="text-decoration:inherit" target="_blank"><span class="outstandingUpload" style="padding:5px 67px;">View</span></a>')

                }
            },
        });
    })
  </script>

@endsection