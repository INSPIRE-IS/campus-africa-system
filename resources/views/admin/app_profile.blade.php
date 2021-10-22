@extends('layouts.admin')

@section('content')

<div class="greyAppSection">

    <div class="container">

        <div class="col-3">
            <p class="listHeader">Next of Kin</p>
            <br/>
            <p class="list">
                {{$user[0]['userInfo']['kinFirstName']}} {{$user[0]['userInfo']['kinLastName']}}<br/>
                {{$user[0]['userInfo']['kinCellNo']}}<br/>
                {{$user[0]['userInfo']['kinEmail']}}<br/>
                {{$user[0]['userInfo']['kinId']}}<br/>
            </p>
        </div>
        <div class="col-3">
        <p class="listHeader">Sponsor</p>
            <br/>
            <p class="list">
                {{$user[0]['userInfo']['sponFirstName']}} {{$user[0]['userInfo']['sponLastName']}}<br/>
                {{$user[0]['userInfo']['sponCellNo']}}<br/>
                {{$user[0]['userInfo']['sponEmail']}}<br/>
                {{$user[0]['userInfo']['sponId']}}<br/>
            </p>
        </div>
        <div class="col-3">
        <p class="listHeader">Bursar</p>
            <br/>
            <p class="list">
                {{$user[0]['userInfo']['burFirstName']}} {{$user[0]['userInfo']['burLastName']}}<br/>
                {{$user[0]['userInfo']['burCellNo']}}<br/>
                {{$user[0]['userInfo']['burEmail']}}<br/>
                {{$user[0]['userInfo']['burId']}}<br/>
            </p>
        </div>
        
    </div>
</div>

<div class="container">
    <div class="appRow">
        <div class="cards">

            <div class="customWhiteCard" >
                <h2 class="appHeader">Applicant Details</h2>
                <p class="InfoList">
                    {{$user[0]['userInfo']['userFirstName']}} {{$user[0]['userInfo']['userLastname']}}<br/>
                    {{$user[0]['userInfo']['userDob']}}<br/>
                    {{$user[0]['userInfo']['userCell']}}<br/>
                    {{$user[0]['userInfo']['userEmail']}}<br/>
                    {{$user[0]['userInfo']['userId']}}<br/>
                    {{$user[0]['userInfo']['userNationality']}}<br/>
                    {{$user[0]['userInfo']['userPassport']}}<br/>
                    {{$user[0]['userInfo']['institutionStudy']}}<br/>
                    {{$user[0]['userInfo']['studentNo']}}<br/>
                    {{$user[0]['userInfo']['institutionCampus']}}<br/>
                    {{$user[0]['userInfo']['faculty']}}<br/>
                    {{$user[0]['userInfo']['year']}}
                </p>
            </div>

            <div class="customWhiteCard" id="applicationCard" style="margin-top:20px;">
                <h2 class="appHeader">Application made for</h2>
                <h4 class="cardSubHeaderGrey">Braamlofts</h4>
                <h2 class="cardHeader" >Single Room</h2>
                <h4 class="cardSubHeaderGrey">Room 23 Floor 2</h4>
                <br/>
                <h4 class="cardSubHeaderGrey">BOOKING FEE PAID</h4>
                <h2 class="cardHeader" >R2500</h2>
                <br/>
                <div class="buttons">
                    <button type="button" class="appBooking">Change this booking</button>
                    <button type="button" class="appBooking">Delete this booking</button>
                </div>
            </div>

            <div class="customWhiteCard" id="actionListCard" style="margin-top:20px;">
                <div class="">
                    <h2 class="appHeader">Action List
                        <select id="actionListSort">
                            <option selected value="all">All Users</option>
                            <option value="admin">Admin Users</option>
                            <option value="applicant">Applicant</option>
                        </select>
                    </h2>
                </div>

                <div class="actionItemList">

                    @foreach($actions as $action)

                        <div class="infoBlock">
                            <div class="responsible">
                                <p>{{$action->actionUserDisplayName}}</p>
                                @if($action->actionUserRole == "1")
                                    <button type="button" class="admin">Admin</button>
                                @else
                                    <button type="button" class="applicant">Applicant</button>
                                @endif
                                <p class="datetime">  {{substr($action->created_at, 8, 2)}} | {{substr($action->created_at, 5, 2)}} | {{substr($action->created_at, 0, 4)}} | {{substr($action->created_at, 11, 5)}} </p>
                            </div>
                            <div class="information">
                                <p>{{$action->action}}</p>
                            </div>
                        </div>

                    @endforeach
                </div>
            </div>

        </div>
    </div>
    <div class="appRow">
        <div class="cards">

            <div class="customWhiteCard">
                <button type="button" class="reminder">Send reminder</button>
                <h2 class="appSubHeader bold">PAYMENT TYPE </h2>
                <p class="appSubHeader">
                    NSFAS
                </p>
                <br/>

                <h2 class="appSubHeader bold">DEPOSIT</h2>
                <p class="appSubHeader">
                    CREDIT CARD
                </p>
                <br/>

                <h2 class="appSubHeader bold">PAID</h2>
                <p class="appSubHeader">
                    NO
                </p>
            </div>

            <div class="customWhiteCard" id="documentsCard" style="margin-top:20px;">
                <div class="bigHeader appSearchForm">
                    <h2 class="appHeader">Documents
                        <select id="documentSortList">
                            <option selected value="all">All Documents</option>
                            <option value="review">Uploaded For Review</option>
                            <option value="approved">Approved</option>
                            <option value="noDoc">No Document</option>
                        </select>
                    </h2>
                    <div class="indexTab flex " style="width:100%">
                        <div class="keys">
                            <div id="indexOne"><div class="button approved"></div><span class="text">APPROVED</span></div>
                            <div id="indexOne"><div class="button action"></div><span class="text">ACTION REQUIRED</span></div>
                            <div id="indexOne"><div class="button rejected"></div><span class="text">NO DOCUMENT</span></div>
                        </div>
                    </div>
                </div>

                <br/><br/><br/><br/>

                <div id="documentsSectionDisplay">

                    <?php $i = 0; ?>

                    @foreach($user[0]["files"] as $file)


                            <div class="section">
                                <div class="label">
                                    
                                    @if($file["field"] === "studentCard")
                                        STUDENT CARD / PROOF OF STUDENT NUMBER
                                    @elseif($file["field"] === "proof_of_payment")
                                        PROOF OF PAYMENT
                                    @elseif($file["field"] === "copyID")
                                        STUDENT ID DOCUMENT
                                    @elseif($file["field"] === "proofRegistration")
                                        PROOF OF REGISTRATION
                                    @elseif($file["field"] === "bursaryApproval")
                                        NSFAS APPROVAL
                                    @elseif($file["field"] === "bankStatement")
                                        BANK STATEMENT
                                    @elseif($file["field"] === "moveIn")
                                        LEASE
                                    @endif
                                </div>
                                <div class="banner">
                                    @if($file["status"] === "Uploaded" || $file["status"] === "Approved")
                                        <button type="button" onClick="
                                            openModal(
                                                '{{$file['field']}}', 
                                                '{{$file['url']}}',
                                                '{{$file['uploaded_by']}}',
                                                '{{$file['created_at']}}',
                                                '{{$file['id']}}',
                                                '{{$file['status']}}'
                                            )" 
                                            id="myBtn_<?php echo $i; ?>">View Document
                                        </button>
                                    @else
                                        <button type="button" class="error">Send reminder</button>
                                    @endif
                                </div>
                                <div class="status">
                                    @if($file["status"] === "Uploaded" )
                                        <div class="button action"></div>
                                    @elseif($file["status"] === "Approved")
                                        <div class="button approved"></div>
                                    @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                        <div class="button rejected"></div>
                                    @elseif($file["status"] === "na")
                                        <div class="button na"></div>
                                    @endif
                                </div>
                            </div>

                            <?php $i++; ?>


                    @endforeach

                </div>


            </div>

        </div>
    </div>
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

<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>


<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function openModal(field, url, uploaded_by, created_at, fileId, status){

        if(field == "studentCard")
            var title = "Student card / Proof of student number";
        else if(field == "proof_of_payment")
            var title = "Proof of payment";
        else if(field == "copyID")
            var title = "Student ID document";
        else if(field == "proofRegistration")
            var title = "Proof of registration";
        else if(field == "bursaryApproval")
            var title = "NSFAS Approval";
        else if(field == "bankStatement")
            var title = "Bank statement";
        else if(field == "moveIn")
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

    $("#actionListSort").on("change", function(){
        $(".actionItemList").empty().append('<i class="fas fa-circle-notch fa-spin" style="font-size:41px; margin-left:41%;"></i>');

        $.ajax({
            type: "get",
            url: "/action-list/<?php echo $user[0]['userInfo']['id']; ?>/"+$("#actionListSort").val(),
            success: function (data) {
                $(".actionItemList").empty();
                
                data.forEach(myFunction);
                function myFunction(item, index) {
                    if(item['actionUserRole'] == "1"){
                        var role = '<button type="button" class="admin">Admin</button>';
                    }else{
                        var role = '<button type="button" class="applicant">Applicant</button>';
                    }

                    $(".actionItemList").append(
                        '<div class="infoBlock">'+
                            '<div class="responsible">'+
                                '<p>'+item['actionUserDisplayName']+'</p>'+
                                    role +
                                '<p class="datetime">  '+item["created_at"].slice(8, 10)+' | '+item["created_at"].slice(5, 7)+' | '+item["created_at"].slice(0, 4)+' | '+item["created_at"].slice(11, 16)+' </p>'+
                            '</div>'+
                            '<div class="information">'+
                                '<p>'+item['action']+'</p>'+
                           ' </div>'+
                        '</div>'
                    )
                }
            },
        });
    })

    $("#documentSortList").on('change', function(){
        
        $("#documentsSectionDisplay").empty().append('<i class="fas fa-circle-notch fa-spin" style="font-size:41px; margin-left:41%;"></i>');

        $.ajax({
            type: "get",
            url: "/document-list/<?php echo $user[0]['userInfo']['id']; ?>/"+$("#documentSortList").val(),
            success: function (data) {

                $("#documentsSectionDisplay").empty();
                
                data.forEach(myFunction);
                function myFunction(item, index) {
                    if(item['field'] == "studentCard"){
                        var name = 'STUDENT CARD / PROOF OF STUDENT NUMBER';
                    }else if(item['field'] == "proof_of_payment"){
                        var name = 'PROOF OF PAYMENT';
                    }else if(item['field'] == "copyID"){
                        var name = 'STUDENT ID DOCUMENT';
                    }else if(item['field'] == "proofRegistration"){
                        var name = 'PROOF OF REGISTRATION';
                    }else if(item['field'] == "bursaryApproval"){
                        var name = 'NSFAS APPROVAL';
                    }else if(item['field'] == "bankStatement"){
                        var name = 'BANK STATEMENT';
                    }else if(item['field'] == "moveIn"){
                        var name = 'LEASE';
                    }

                    if(item['status'] == "Uploaded" || item['status'] == "Approved"){
                        var button = '<button type="button" onClick="openModal(\''+item['field']+'\', \''+item['url']+'\',\''+item['uploaded_by']+'\',\''+item['created_at']+'\',\''+item['id']+'\',\''+item['status']+'\')" id="myBtn_'+index+'">View Document</button>';
                    }else{
                        var button = '<button type="button" class="error">Send reminder</button>';
                    }

                    if(item['status'] == "Uploaded"){
                        var status = '<div class="button action"></div>';
                    }else if(item['status'] == "Approved"){
                        var status = '<div class="button approved"></div>';
                    }else if(item['status'] == "Rejected" || item['status'] == ""){
                        var status = '<div class="button rejected"></div>';
                    }else if(item['status'] == "na"){
                        var status = '<div class="button na"></div>';
                    }

                    $("#documentsSectionDisplay").append(
                        '<div class="section">'+
                            '<div class="label">'+
                                name +
                            '</div>'+
                            '<div class="banner">'+
                                button +
                            '</div>'+
                            '<div class="status">'+
                                status + 
                            '</div>'+
                        '</div>'
                    )
                }
            },
        });
    })
</script>



@endsection
