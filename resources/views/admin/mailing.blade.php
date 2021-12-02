@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script src="https://cdn.tiny.cloud/1/u3wbb2rax98pnwm5w8cxrjja5oxoqztklpt49ug97d5n009f/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

<div class="container">
    <br/>
    <table id="mailer_table" class="display">
        <thead>
            <tr>
                <th>Mailer</th>
                <th>Status</th>
                <th></th>
            </tr> 
        </thead>
        <tbody>

        @foreach($MailerList as $mailer)
            <tr>
                <td>{{$mailer->mailer_name}}</td>
                <td>@if($mailer->status == "0") <button type="button" class="inactiveBtn" onClick="activeMailer('{{$mailer->id}}')" id="mailer_{{$mailer->id}}"> Inactive </button>@else <button type="button" class="activeBtn" onClick="inactiveMailer('{{$mailer->id}}')" id="mailer_{{$mailer->id}}">Active</button> @endif</td>
                <td><button type="button" id="tableBtn" onClick="openModal('{{$mailer->mailer_name}}')" >View</button></td>
            </tr>
        @endforeach




            <!-- <tr>
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
            </tr> -->
        </tbody>
    </table>
    <br/>
</div>

<div id="ThankYou" class="modal">
    <div class="modal-content">
        <span class="close closeThankYou">&times;</span>
        <h2 id="modalHeader"></h2>
        <br/>
        <button type="button" class="saveTxtMailTy" onClick="saveTy(event)">Save Text</button>
        <br/><br/>
        <br/>
        <div class="mailSection" style="border: 1px solid #000">
            <table width="100%">
                <tr width="100%">
                    <td style="padding: 30px 6% 30px 6%; margin: 0;">
                        <img src='../../../images/logo/Campus_Africa_Logo.png' alt="Logo">
                    </td>
                </tr>
                <tr width="100%" id="mailTextSection" class="yellowBack" >
                    <td colspan="2" style="width: 100%; padding: 30px 6% 30px 6%;; line-height: 22px">   

                        <textarea id="mytextareaTy" name="mailerText" style="background-color:transparent">

                        @foreach($MailerList as $mailer)
                            @if($mailer->mailer_name == "Thank You")
                               {{$mailer->message}}
                            @endif
                        @endforeach
                    
                    </textarea>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div id="ApplicationApproval" class="modal">
    <div class="modal-content">
        <span class="close closeApplicationApproval">&times;</span>
        <h2 id="modalHeader">Application Approval</h2>
        <br/>
        <button type="button" class="saveTxtMailAa" onClick="saveAa(event)">Save Text</button>
        <br/><br/>
        <br/>
        <div class="mailSection" style="border: 1px solid #000">
            
            <table width="100%">
                <tr width="100%">
                    <td style="padding: 30px 6% 30px 6%; margin: 0;">
                        <img src='../../../images/logo/Campus_Africa_Logo.png' alt="Logo">
                    </td>
                </tr>
                <tr width="100%" id="mailTextSection" class="greenBack" >
                    <td colspan="2" style="width: 100%; padding: 30px 6% 30px 6%;; line-height: 22px">   

                        <textarea id="mytextareaAa" name="mailerText" style="background-color:transparent">

                        @foreach($MailerList as $mailer)
                            @if($mailer->mailer_name == "Application Approval")
                               {{$mailer->message}}
                            @endif
                        @endforeach
                    
                    </textarea>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div id="ApplicationApproved" class="modal">
    <div class="modal-content">
        <span class="close closeApplicationApproved">&times;</span>
        <h2 id="modalHeader">Application Approved</h2>
        <br/>
        <button type="button" class="saveTxtMailAav" onClick="saveAav(event)">Save Text</button>
        <br/><br/>
        <br/>
        <div class="mailSection" style="border: 1px solid #000">
        <table width="100%">
                <tr width="100%">
                    <td style="padding: 30px 6% 30px 6%; margin: 0;">
                        <img src='../../../images/logo/Campus_Africa_Logo.png' alt="Logo">
                    </td>
                </tr>
                <tr width="100%" id="mailTextSection" class="greenBack" >
                    <td colspan="2" style="width: 100%; padding: 30px 6% 30px 6%;; line-height: 22px">   

                        <textarea id="mytextareaAav" name="mailerText" style="background-color:transparent">

                        @foreach($MailerList as $mailer)
                            @if($mailer->mailer_name == "Application Approved")
                               {{$mailer->message}}
                            @endif
                        @endforeach
                    
                    </textarea>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div id="DocumentApproval" class="modal">
    <div class="modal-content">
        <span class="close closeDocumentApproval">&times;</span>
        <h2 id="modalHeader">Application Approval</h2>
        <br/>
        <button type="button" class="saveTxtMailAD" onClick="saveAD(event)">Save Text</button>
        <br/><br/>
        <br/>
        <div class="mailSection" style="border: 1px solid #000">
        <table width="100%">
                <tr width="100%">
                    <td style="padding: 30px 6% 30px 6%; margin: 0;">
                        <img src='../../../images/logo/Campus_Africa_Logo.png' alt="Logo">
                    </td>
                </tr>
                <tr width="100%" id="mailTextSection" class="greenBack" >
                    <td colspan="2" style="width: 100%; padding: 30px 6% 30px 6%;; line-height: 22px">   

                        <textarea id="mytextareaAD" name="mailerText" style="background-color:transparent">

                        @foreach($MailerList as $mailer)
                            @if($mailer->mailer_name == "Document Approval")
                               {{$mailer->message}}
                            @endif
                        @endforeach
                    
                    </textarea>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div id="ApplicationDenied" class="modal">
    <div class="modal-content">
        <span class="close closeApplicationDenied">&times;</span>
        <h2 id="modalHeader">Application Denied</h2>
        <br/>
        <button type="button" class="saveTxtMailADd" onClick="saveADd(event)">Save Text</button>
        <br/><br/>
        <br/>
        <div class="mailSection" style="border: 1px solid #000">
        <table width="100%">
                <tr width="100%">
                    <td style="padding: 30px 6% 30px 6%; margin: 0;">
                        <img src='../../../images/logo/Campus_Africa_Logo.png' alt="Logo">
                    </td>
                </tr>
                <tr width="100%" id="mailTextSection" class="redBack" >
                    <td colspan="2" style="width: 100%; padding: 30px 6% 30px 6%;; line-height: 22px">   

                        <textarea id="mytextareaADd" name="mailerText" style="background-color:transparent">

                        @foreach($MailerList as $mailer)
                            @if($mailer->mailer_name == "Application Denied")
                               {{$mailer->message}}
                            @endif
                        @endforeach
                    
                    </textarea>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div id="ApplicationRejected" class="modal">
    <div class="modal-content">
        <span class="close closeApplicationRejected">&times;</span>
        <h2 id="modalHeader">Application Rejected</h2>
        <br/>
        <button type="button" class="saveTxtMailAR" onClick="saveAR(event)">Save Text</button>
        <br/><br/>
        <br/>
        <div class="mailSection" style="border: 1px solid #000">
        <table width="100%">
                <tr width="100%">
                    <td style="padding: 30px 6% 30px 6%; margin: 0;">
                        <img src='../../../images/logo/Campus_Africa_Logo.png' alt="Logo">
                    </td>
                </tr>
                <tr width="100%" id="mailTextSection" class="redBack" >
                    <td colspan="2" style="width: 100%; padding: 30px 6% 30px 6%;; line-height: 22px">   

                        <textarea id="mytextareaAR" name="mailerText" style="background-color:transparent">

                        @foreach($MailerList as $mailer)
                            @if($mailer->mailer_name == "Application Rejected")
                               {{$mailer->message}}
                            @endif
                        @endforeach
                    
                    </textarea>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $(document).ready( function () {
        $('#mailer_table').DataTable();
    } );

    function activeMailer(mailerId){
        $("#mailer_"+mailerId).empty().append("...");
        $.ajax({
            type: "post",
            url: "/activate_mailer",
            data: {
                mailerId: mailerId, 
            },

            success: function (data) {
                console.log(data)
                $("#mailer_"+data).empty().append("Active");
                $("#mailer_"+data).removeClass("inactiveBtn")
                $("#mailer_"+data).addClass("activeBtn");
            },
        });
    }

    function inactiveMailer(mailerId){
        $("#mailer_"+mailerId).empty().append("...");
        $.ajax({
            type: "post",
            url: "/inactivate_mailer",
            data: {
                mailerId: mailerId, 
            },

            success: function (data) {
                console.log(data)
                $("#mailer_"+data).empty().append("Inactive");
                $("#mailer_"+data).removeClass("activeBtn")
                $("#mailer_"+data).addClass("inactiveBtn");
            },
        });
    }

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

    tinymce.init({
        selector: '#mytextareaTy',
        paste_data_images: true,
        plugins: "image,paste,hr",
        height: "600px",
        toolbar: 'undo redo | styleselect | bold italic | link image | alignleft aligncenter alignright alignright | outdent indent | hr',
        content_style: "body{ line-height:1.2;}"
    });

    tinymce.init({
        selector: '#mytextareaAa',
        paste_data_images: true,
        plugins: "image,paste,hr",
        height: "600px",
        toolbar: 'undo redo | styleselect | bold italic | link image | alignleft aligncenter alignright alignright | outdent indent | hr',
        content_style: "body{ line-height:1.2;}"
    });

    tinymce.init({
        selector: '#mytextareaAav',
        paste_data_images: true,
        plugins: "image,paste,hr",
        height: "600px",
        toolbar: 'undo redo | styleselect | bold italic | link image | alignleft aligncenter alignright alignright | outdent indent | hr',
        content_style: "body{ line-height:1.2;}"
    }); 

    tinymce.init({
        selector: '#mytextareaAD',
        paste_data_images: true,
        plugins: "image,paste,hr",
        height: "600px",
        toolbar: 'undo redo | styleselect | bold italic | link image | alignleft aligncenter alignright alignright | outdent indent | hr',
        content_style: "body{ line-height:1.2;}"
    }); 

    tinymce.init({
        selector: '#mytextareaADd',
        paste_data_images: true,
        plugins: "image,paste,hr",
        height: "600px",
        toolbar: 'undo redo | styleselect | bold italic | link image | alignleft aligncenter alignright alignright | outdent indent | hr',
        content_style: "body{ line-height:1.2;}"
    }); 

    tinymce.init({
        selector: '#mytextareaAR',
        paste_data_images: true,
        plugins: "image,paste,hr",
        height: "600px",
        toolbar: 'undo redo | styleselect | bold italic | link image | alignleft aligncenter alignright alignright | outdent indent | hr',
        content_style: "body{ line-height:1.2;}"
    }); 

    function saveTy(event){
        $(".saveTxtMailTy").empty().append("Saving...");
        $.ajax({
            type: "post",
            url: "/saveMailTy",
            data: {
                text: tinymce.get("mytextareaTy").getContent(), 
            },

            success: function (data) {
                console.log(data)
                $(".saveTxtMailTy").empty().append("Save Text")
            },
        });
    } 

    function saveAa(event){
        $(".saveTxtMailAa").empty().append("Saving...");
        $.ajax({
            type: "post",
            url: "/saveMailAa",
            data: {
                text: tinymce.get("mytextareaAa").getContent(), 
            },

            success: function (data) {
                console.log(data)
                $(".saveTxtMailAa").empty().append("Save Text")
            },
        });
    } 

    function saveAav(event){
        $(".saveTxtMailAav").empty().append("Saving...");
        $.ajax({
            type: "post",
            url: "/saveMailAav",
            data: {
                text: tinymce.get("mytextareaAav").getContent(), 
            },

            success: function (data) {
                console.log(data)
                $(".saveTxtMailAav").empty().append("Save Text")
            },
        });
    } 

    function saveAD(event){
        $(".saveTxtMailAD").empty().append("Saving...");
        $.ajax({
            type: "post",
            url: "/saveMailAD",
            data: {
                text: tinymce.get("mytextareaAD").getContent(), 
            },

            success: function (data) {
                console.log(data)
                $(".saveTxtMailAD").empty().append("Save Text")
            },
        });
    }

    function saveADd(event){
        $(".saveTxtMailADd").empty().append("Saving...");
        $.ajax({
            type: "post",
            url: "/saveMailADd",
            data: {
                text: tinymce.get("mytextareaADd").getContent(), 
            },

            success: function (data) {
                console.log(data)
                $(".saveTxtMailADd").empty().append("Save Text")
            },
        });
    } 

    function saveAR(event){
        $(".saveTxtMailAR").empty().append("Saving...");
        $.ajax({
            type: "post",
            url: "/saveMailAR",
            data: {
                text: tinymce.get("mytextareaAR").getContent(), 
            },

            success: function (data) {
                console.log(data)
                $(".saveTxtMailAR").empty().append("Save Text")
            },
        });
    }  


      

</script>


@endsection