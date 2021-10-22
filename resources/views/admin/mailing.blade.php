@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>

<div class="container">
    <br/>
    <table id="mailer_table" class="display">
        <thead>
            <tr>
                <th>Mailer</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Thank You</td>
                <td><button type="button" id="tableBtn" onClick="openModal('Thank You')" >View</button></td>
            </tr>
            <tr>
                <td>Application Approval</td>
                <td><button type="button" onClick="openModal('Application Approval')" >View</button></td>
            </tr>
            <tr>
                <td>Application Approved</td>
                <td><button type="button" onClick="openModal('Application Approved')" >View</button></td>
            </tr>
            <tr>
                <td>Document Approval</td>
                <td><button type="button" onClick="openModal('Document Approval')" >View</button></td>
            </tr>
            <tr>
                <td>Application Denied</td>
                <td><button type="button" onClick="openModal('Application Denied')" >View</button></td>
            </tr>
            <tr>
                <td>Application Rejected</td>
                <td><button type="button" onClick="openModal('Application Rejected')" >View</button></td>
            </tr>
        </tbody>
    </table>
    <br/>
</div>

<div id="ThankYou" class="modal">
    <div class="modal-content">
        <span class="close closeThankYou">&times;</span>
        <h2 id="modalHeader"></h2>
        <br/>
        <div class="mailSection" style="border: 1px solid #000">
            @include('email.application_thank_you')
        </div>
    </div>
</div>

<div id="ApplicationApproval" class="modal">
    <div class="modal-content">
        <span class="close closeApplicationApproval">&times;</span>
        <h2 id="modalHeader"></h2>
        <br/>
        <div class="mailSection" style="border: 1px solid #000">
            @include('email.application_approval')
        </div>
    </div>
</div>

<div id="ApplicationApproved" class="modal">
    <div class="modal-content">
        <span class="close closeApplicationApproved">&times;</span>
        <h2 id="modalHeader"></h2>
        <br/>
        <div class="mailSection" style="border: 1px solid #000">
            @include('email.application_approved')
        </div>
    </div>
</div>

<div id="DocumentApproval" class="modal">
    <div class="modal-content">
        <span class="close closeDocumentApproval">&times;</span>
        <h2 id="modalHeader"></h2>
        <br/>
        <div class="mailSection" style="border: 1px solid #000">
            @include('email.document_approval')
        </div>
    </div>
</div>

<div id="ApplicationDenied" class="modal">
    <div class="modal-content">
        <span class="close closeApplicationDenied">&times;</span>
        <h2 id="modalHeader"></h2>
        <br/>
        <div class="mailSection" style="border: 1px solid #000">
            @include('email.application_declined')
        </div>
    </div>
</div>

<div id="ApplicationRejected" class="modal">
    <div class="modal-content">
        <span class="close closeApplicationRejected">&times;</span>
        <h2 id="modalHeader"></h2>
        <br/>
        <div class="mailSection" style="border: 1px solid #000">
            @include('email.application_rejected')
        </div>
    </div>
</div>

<script>
    $(document).ready( function () {
        $('#mailer_table').DataTable();
    } );

    function openModal(title){

        $("#modalHeader").empty().append(title);

        if(title == "Thank You"){
            var modal = document.getElementById("ThankYou");
        }else if(title == "Application Approval"){
            var modal = document.getElementById("ApplicationApproval");
        }else if(title == "Application Approved"){
            var modal = document.getElementById("ApplicationApproved");
        }else if(title == "Document Approval"){
            var modal = document.getElementById("DocumentApproval");
        }else if(title == "Application Denied"){
            var modal = document.getElementById("ApplicationDenied");
        }else if(title == "Application Rejected"){
            var modal = document.getElementById("ApplicationRejected");
        }
        
        modal.style.display = "block";
    }

    var closeThankYou = document.getElementsByClassName("closeThankYou")[0];
    var closeApplicationApproval = document.getElementsByClassName("closeApplicationApproval")[0];
    var closeApplicationApproved = document.getElementsByClassName("closeApplicationApproved")[0];
    var closeDocumentApproval = document.getElementsByClassName("closeDocumentApproval")[0];
    var closeApplicationDenied = document.getElementsByClassName("closeApplicationDenied")[0];
    var closeApplicationRejected = document.getElementsByClassName("closeApplicationRejected")[0];

    closeThankYou.onclick = function() {
        var modal = document.getElementById("ThankYou");
        modal.style.display = "none";
    }
    closeApplicationApproval.onclick = function() {
        var modal = document.getElementById("ApplicationApproval");
        modal.style.display = "none";
    }
    closeApplicationApproved.onclick = function() {
        var modal = document.getElementById("ApplicationApproved");
        modal.style.display = "none";
    }
    closeDocumentApproval.onclick = function() {
        var modal = document.getElementById("DocumentApproval");
        modal.style.display = "none";
    }
    closeApplicationDenied.onclick = function() {
        var modal = document.getElementById("ApplicationDenied");
        modal.style.display = "none";
    }
    closeApplicationRejected.onclick = function() {
        var modal = document.getElementById("ApplicationRejected");
        modal.style.display = "none";
    }

</script>


@endsection