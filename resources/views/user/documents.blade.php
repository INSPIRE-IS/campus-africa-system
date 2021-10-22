@extends('layouts.dash')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>

<div class="container">
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
                    <td>
                        <span class="rejected">Outstanding</span>
                    </td>
                    <td>
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
                    <td>
                        <span class="rejected">INVALID DOCUMENT - PLEASE REUPLOAD</span>
                    </td>
                    <td>
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
                    <td>
                        <span class="await">Pending Approval</span>
                    </td>
                    <td>
                      <a href="/storage/{{$file['fileUrl']}}" style="text-decoration:inherit" target="_blank"><span class="await">View</span></a>
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
                    <td>
                        <span class="approved">Approved</span>
                    </td>
                    <td>
                      <a href="/storage/{{$file['fileUrl']}}" style="text-decoration:inherit" target="_blank"><span class="approved">View</span></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <br/>
</div>

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

$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

  $(document).ready( function () {
        $('#documents_table').DataTable({
          "order": [[ 1, "desc" ]]
        });
    } );

    $("#copyID").on('change', function(){
        $("#copyIDSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('copyID').files[0]);
        form_data.append("userUid", {{Auth::user()->id}});
        form_data.append("fileType", "copyID");

        $.ajax({
            type: "post",
            url: "/move-in-form",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                    window.location.reload();
                }
            },
        });
    });

    $("#studentCard").on('change', function(){
        $("#studentCardSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('studentCard').files[0]);
        form_data.append("userUid", {{Auth::user()->id}});
        form_data.append("fileType", "studentCard");

        $.ajax({
            type: "post",
            url: "/move-in-form",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                  window.location.reload();
                }
            },
        });  
    })

    $("#bankStatement").on('change', function(){
        $("#bankStatementSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('bankStatement').files[0]);
        form_data.append("userUid", {{Auth::user()->id}});
        form_data.append("fileType", "bankStatement");

        $.ajax({
            type: "post",
            url: "/move-in-form",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                  window.location.reload();
                }
            },
        });
    })

    $("#proofRegistration").on('change', function(){

        $("#proofRegistrationSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('proofRegistration').files[0]);
        form_data.append("userUid", {{Auth::user()->id}});
        form_data.append("fileType", "proofRegistration");

        $.ajax({
            type: "post",
            url: "/move-in-form",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                  window.location.reload();
                }
            },
        });
    })


    $("#proof_of_payment").on('change', function(){
        $("#proofRegistrationSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('proof_of_payment').files[0]);
        form_data.append("userUid", {{Auth::user()->id}});
        form_data.append("fileType", "proof_of_payment");

        $.ajax({
            type: "post",
            url: "/move-in-form",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                  window.location.reload();
                }
            },
        });
    })
  </script>


@endsection