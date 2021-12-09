@extends('layouts.admin')

@section('content')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>

<div class="container">
    <br/>
    <table id="lease_table" class="display">
        <thead>
            <tr>
                <th>Booking Reference</th>
                <th>Name</th>
                <th>ID Number</th>
                <th>Lease Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($leases as $lease)
                <tr>
                    <td>{{$lease['users']['bookingRef']}}</td>
                    <td>{{$lease['users']['userFirstName']}} {{$lease['users']['userLastname']}}</td>
                    <td>{{$lease['users']['userId']}}</td>
                    <td>
                        <span class="<?php if($lease['status'] == "Uploaded"){echo "uploaded";}else if($lease['status'] == "Approved"){echo "approved";}else if($lease['status'] == "Rejected"){echo "rejected";}?>">{{$lease['status']}}</span>
                    </td>
                    <td>
                        @if($lease['status'] == "Uploaded")
                            <button type="button" onClick="
                            openModal(
                                '{{$lease['fileId']}}', 
                                '{{$lease['fileUrl']}}',
                                '{{$lease['uploaded_by']}}',
                                '{{$lease['created_at']}}',
                                '{{$lease['id']}}',
                                '{{$lease['status']}}'
                            )" >Action</button>
                        @elseif($lease['status'] == "Rejected")
                            <button type="button" class="error">Send reminder</button>
                        @endif
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
            @if(Auth::user()->userRole !== 3)
                <input type="text" id="rejectReason" placeholder="Type field for rejection reason">
                <button type="button" class="rejected button" id="rejected" onClick="actionFile('Rejected', '')">Reject with reason</button>
                <button type="button" class="amend button" id="amend"  onClick="actionFile('Approved', 'Amend')">Amend &amp; Approve</button>
                <button type="button" class="approve button" id="approved" onClick="actionFile('Approved', '')">Approve</button>
                @endif
            </div>

        <div class="pdfSection"></div>
    </div>

</div>



<script>
    $(document).ready( function () {
        $('#lease_table').DataTable();
    } );

    function openModal(field, url, uploaded_by, created_at, fileId, status){
        var title = "Lease";

        $("#modalHeader").empty().append(title);
        $("#uploadedBy").empty().append(uploaded_by)
        $("#uploadedTime").empty().append(created_at.slice(11, 16))
        $("#uploadedDate").empty().append(created_at.slice(8, 10)+"|"+created_at.slice(5, 7)+"|"+created_at.slice(0, 4))
        $("#FileIdForApproval").val(fileId)

        if(status == "Approved" || status == "Rejected")
            $("#buttonSection").addClass('hidden')
        else
            $("#buttonSection").removeClass('hidden')


        $(".pdfSection").empty().append('<embed id="pdfView" src="/storage/'+url+'"  width="100%" height="600px" />')

        var modal = document.getElementById("myModal");
        modal.style.display = "block";
        }

        var modal = document.getElementById("myModal");
        var span = document.getElementsByClassName("close")[0];

        span.onclick = function() {
        modal.style.display = "none";
        }

        window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    function actionFile(type, action){

        if(type === "Rejected"){
            $("#rejected").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
            var preview1 = document.getElementById("amend");
            preview1.setAttribute("disabled", "disabled");
            var preview2 = document.getElementById("approved");
            preview2.setAttribute("disabled", "disabled");
        }else{
            if(action == "Amend"){
                $("#amend").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                var preview1 = document.getElementById("rejected");
                preview1.setAttribute("disabled", "disabled");
                var preview2 = document.getElementById("approved");
                preview2.setAttribute("disabled", "disabled");
            }else{
                $("#approved").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                var preview1 = document.getElementById("rejected");
                preview1.setAttribute("disabled", "disabled");
                var preview2 = document.getElementById("amend");
                preview2.setAttribute("disabled", "disabled");
            }
        }

        $.ajax({
            type: "post",
            url: "/action/file",
            data: {
                file: $("#FileIdForApproval").val(), 
                type: type,
                reason: $("#rejectReason").val(), 
            },

            success: function (data) {
                window.location.reload();
            },
        });

    }
</script>

@endsection