@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>

<div class="container">

<br/>

@if(Auth::user()->userRole !== 2)
    <a href="/new/student"><button type="button" class="inactiveBtn">Add New Student</button></a>
@endif


    <div class="appSearchForm" style="margin-top:-35px">
        <div class="searchField">
        <label></label><br/>
            <select name="property" id="property">
                <option value="" selected disabled>Select a Property</option>
                <option value="All">All</option>
                <option value="Braamlofts">Braamlofts</option>
                <option value="80 Jorissen">80 Jorissen</option>
                <option value="49 Jorissen">49 Jorissen</option>
                <option value="Dunvista Mansions">Dunvista Mansions</option>
                <option value="Wynton Joy">Wynton Joy</option>
                <option value="126 Siemert">126 Siemert</option>
                <option value="YMCA">YMCA</option>
            </select>
        </div>
        <div class="searchField">
        <label></label><br/>
            <select name="room_type" id="room_type">
                <option value="" selected disabled>Select a Room Type</option>
                <option value="All">All</option>
                <option value="Single Room">Single Room</option>
                <option value="2 Sharing">2 Sharing</option>
                <option value="3 Sharing">3 sharing</option>
                <option value="single room 2 bedroom">Single Room | 2 Bedroom</option>
                <option value="single room 3 bedroom">Single Room | 3 Bedroom</option>
                <option value="single room small">Single Room | small</option>
            </select>
        </div>
        <div class="searchField">
            <label>Applicant</label><br/>
            <input type="text" name="applicant" id="applicant"/>
        </div>

        <div class="searchField">
        <label></label><br/>
            <select name="student_type" id='student_type'>
                <option value="" selected disabled>Select a Student Type</option>
                <option value="All">All</option>
                <option value="NSFAS">NSFAS</option>
                <option value="Bursary">Bursary</option>
                <option value="Cash / Direct">Cash / Direct</option>
            </select>
        </div>
        <div class="searchField">
        <label></label><br/>
            <select name="applicationStatus" id='applicationStatus'>
                <option value="" selected disabled>Select a Booking Status</option>
                <option value="All">All</option>
                <option value="Approved">Valid</option>
                <option value="Action">Pending</option>
                <option value="Empty">Invalid</option>
            </select>
        </div>
        <div class="searchField">
            <label>Date</label><br/>
            <input type="date" name="date" id="date"/>
            <br/><br/><br/>
        </div>

        <div class="searchTab">
            <button type="button" id="searchButton">Search</button>
        </div>
        <div class="indexTab flex" id="applicationIndex">
            <label>Legend</label>
            <div class="keys">
                <div id="indexOne"><div class="button approved"></div><span class="text">Approved</span></div>
                <div id="indexOne"><div class="button action"></div><span class="text">Action Required</span></div>
                <div id="indexOne"><div class="button rejected"></div><span class="text">Rejected / Not Uploaded</span></div>
                <div id="indexOne"><div class="button na"></div><span class="text">Not Applicable</span></div>
            </div>
        </div>
        
    </div>

    <div class="clearSpaceLogin"></div>

    <!-- <div class="container"> -->
        <div class="ApplicationResults">



            <table class="applicationTable" id="application_table">
                <thead>
                    <tr>
                        <th class="first">BOOKING REFERENCE</th>
                        <th style="width:30%">APPLICANT | ROOM</th>
                        <th>STUDENT TYPE</th>
                        <th style="background-color:#ccc; ">BOOKING STATUS</th>
                        <th>PROOF OF PAYMENT</th>
                        <th>ID COPY</th>
                        <th style="background-color:#ccc">APPLICATION STATUS</th>
                        <th>STUDENT CARD COPY</th>
                        <th>PROOF OF REGISTRATION</th>
                        <th>NSFAS | BURSARY APPROVAL</th>
                        <th>BANK STATEMENTS</th>
                    </tr>
                </thead>
                <tbody id="applicantsSection">

                <?php $i = 0; ?>

                    @foreach($users as $user)

                        <tr class=" <?php if($i % 2 == 0){echo 'dark';}else{echo 'light';} ?> clickable-row table_id_{{$user['id']}}" data-href='/application-profile/{{$user["id"]}}'>
                            <td id="bookingRef">#{{$user["bookingRef"]}} </td>
                            <td id="appUser"><span class="bold">{{$user["userFirstName"]}} {{$user["userLastname"]}}</span> <br/> {{$user["property"]}} | {{$user["roomType"]}} | {{$user["created_at"]}}</td>
                            <td id="studType">{{$user["studentType"]}}</td>
                            <td id="statusBarIcon" style="background-color:#ccc">
                                @if($user["bookingStatus"] === "Pending" || $user["bookingStatus"] === "Action")
                                <span class="hidden">A</span><div class="button action"></div>
                                @elseif($user["bookingStatus"] === "Approved")
                                <span class="hidden">C</span><div class="button approved"></div>
                                @elseif($user["bookingStatus"] === "Denied" || $user["bookingStatus"] === null || $user["bookingStatus"] === "Empty" || $user["bookingStatus"] === "Rejected")
                                <span class="hidden">B</span><div class="button rejected"></div>
                                @endif
                            </td>
                            
                            @foreach($user["files"] as $file)

                                @if($file["field"] === "proof_of_payment")
                                    @if($file["status"] === "Uploaded")
                                        <td id="statusBarIcon"><span class="hidden">A</span><div class="button action"></div></td>
                                    @elseif($file["status"] === "Approved")
                                        <td id="statusBarIcon"><span class="hidden">C</span><div class="button approved"></div></td>
                                    @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                        <td id="statusBarIcon"><span class="hidden">B</span><div class="button rejected"></div></td>
                                    @elseif($file["status"] === "na")
                                        <td id="statusBarIcon"><span class="hidden">D</span><div class="button na"></div></td>
                                    @endif

                                @endif

                            @endforeach

                            @foreach($user["files"] as $file)

                                

                                @if($file["field"] === "copyID")
                                    <td id="statusBarIcon">
                                        @if($file["status"] === "Uploaded")
                                        <span class="hidden">A</span><div class="button action"></div>
                                        @elseif($file["status"] === "Approved")
                                        <span class="hidden">C</span><div class="button approved"></div>
                                        @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                        <span class="hidden">B</span><div class="button rejected"></div>
                                        @elseif($file["status"] === "na")
                                        <span class="hidden">D</span><div class="button na"></div>
                                        @endif
                                    </td>
                                    
                                @endif

                            @endforeach

                            <td id="statusBarIcon" style="background-color:#ccc">
                                @if($user["applicationStatus"] === "Pending" || $user["applicationStatus"] === "Action" || $user["applicationStatus"] === "Approved")
                                <span class="hidden">A</span><div class="button action"></div>
                                @elseif($user["applicationStatus"] === "Approved Confirmed")
                                <span class="hidden">C</span><div class="button approved"></div>
                                @elseif($user["applicationStatus"] === "Denied" || $user["applicationStatus"] === null || $user["applicationStatus"] === "Rejected" || $user["applicationStatus"] === "Empty" || $user["applicationStatus"] == "Denied Confimed")
                                <span class="hidden">B</span><div class="button rejected"></div>
                                @endif
                            </td>



                            @foreach($user["files"] as $file)

                                @if($file["field"] === "studentCard" )
                                    @if($file["status"] === "Uploaded")
                                    <span class="hidden">A</span><td id="statusBarIcon"><div class="button action"></div></td>
                                    @elseif($file["status"] === "Approved")
                                    <span class="hidden">C</span><td id="statusBarIcon"><div class="button approved"></div></td>
                                    @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                    <span class="hidden">B</span><td id="statusBarIcon"><div class="button rejected"></div></td>
                                    @elseif($file["status"] === "na")
                                    <span class="hidden">D</span><td id="statusBarIcon"><div class="button na"></div></td>
                                    @endif

                                    
                                @endif

                            @endforeach

                            @foreach($user["files"] as $file)

                                @if($file["field"] === "proofRegistration" )
                                    @if($file["status"] === "Uploaded")
                                        <td id="statusBarIcon"><span class="hidden">A</span><div class="button action"></div></td>
                                    @elseif($file["status"] === "Approved")
                                        <td id="statusBarIcon"><span class="hidden">C</span><div class="button approved"></div></td>
                                    @elseif($file["status"] === "Rejected" || $file["status"] == "")
                                        <td id="statusBarIcon"><span class="hidden">B</span><div class="button rejected"></div></td>
                                    @elseif($file["status"] === "na")
                                        <td id="statusBarIcon"><span class="hidden">D</span><div class="button na"></div></td>
                                    @endif

                                @endif

                            @endforeach

                            @foreach($user["files"] as $file)

                                @if($file["field"] === "bursaryApproval")
                                    @if($file["status"] === "Uploaded")
                                        <td id="statusBarIcon"><span class="hidden">A</span><div class="button action"></div></td>
                                    @elseif($file["status"] === "Approved")
                                        <td id="statusBarIcon"><span class="hidden">C</span><div class="button approved"></div></td>
                                    @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                        <td id="statusBarIcon"><span class="hidden">B</span><div class="button rejected"></div></td>
                                    @elseif($file["status"] === "na")
                                        <td id="statusBarIcon"><span class="hidden">D</span><div class="button na"></div></td>
                                    @endif
                                @endif

                            @endforeach

                            @foreach($user["files"] as $file)

                                @if($file["field"] === "bankStatement")
                                <td id="statusBarIcon">
                                    @if($file["status"] === "Uploaded")
                                    <span class="hidden">A</span><div class="button action"></div>
                                    @elseif($file["status"] === "Approved")
                                    <span class="hidden">C</span><div class="button approved"></div>
                                    @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                    <span class="hidden">B</span><div class="button rejected"></div>
                                    @elseif($file["status"] === "na")
                                    <span class="hidden">D</span><div class="button na"></div>
                                    @endif
                                </td>
                                @endif
                            
                            @endforeach
                            
                        </tr>

                        <?php $i++; ?>

                    @endforeach
                    
                </tbody>
            </table>

        </div>
<!-- </div> -->

    
</div>

<div class="clearSpaceLoginPage"></div>


<script>
    // jQuery(document).ready(function($) {
    //     $(".clickable-row").click(function() {
    //         window.location = $(this).data("href");
    //     });
    // });

    $(document).ready( function () {
        $('#application_table').DataTable({searching: false, "aaSorting": [], "stripeClasses": [ 'odd-row', 'even-row' ]});
    } );

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#application_table tbody').on('click', 'tr', function () {
        var datatable = $( "#application_table" ).DataTable();
        var data2 = datatable.row( this );
        var rowClass = data2['selector']['rows'].className;
        var rowId = (rowClass.split("table_id_"))[1];
        var rowId = rowId.replace(" odd-row", "");
        var rowId = rowId.replace(" even-row", "");

        window.location = "/application-profile/"+rowId;
    } );

    $("#searchButton").on('click', function(){
        $("#searchButton").empty().append("Searching...")

        $.ajax({
            type: "post",
            url: "/applications/filter",
            data: {
                property: $("#property").val(), 
                room_type: $("#room_type").val(), 
                applicant: $("#applicant").val(), 
                student_type: $("#student_type").val(),
                applicationStatus: $("#applicationStatus").val(), 
                date: $("#date").val(), 
            },

            success: function (data) {
                results = JSON.parse(data)

                var i = 0;
                var datatable = $( "#application_table" ).DataTable();
                datatable.clear();

                if(results.length == "0"){
                    var datatable = $( "#application_table" ).DataTable();
                    datatable.clear().draw();
                }

                results.forEach(function (arrayItem) {
                    console.log(i)
                    let files = arrayItem['files'];

                    if( i % 2 == 0){
                        color = "dark";
                    }else{
                        color = "light";
                    }

                    if(arrayItem["bookingStatus"] == "Pending" || arrayItem["bookingStatus"] == "Action"){
                        var bookingStat = '<span class="hidden">A</span><div class="button action"></div>';
                    }else if(arrayItem["bookingStatus"] == "Approved"){
                        var bookingStat = '<span class="hidden">C</span><div class="button approved"></div>';
                    }else if(arrayItem["bookingStatus"] == "Denied" || arrayItem["bookingStatus"] == "" || arrayItem["bookingStatus"] == "Empty" || arrayItem["bookingStatus"] == "Rejected"){
                        var bookingStat = '<span class="hidden">B</span><div class="button rejected"></div>';
                    }else{
                        var bookingStat = '<span class="hidden">D</span><div class="button rejected"></div>';
                    }

                    if(arrayItem["applicationStatus"] === "Pending" || arrayItem["applicationStatus"] === "Action"){
                        applicationStatus = '<span class="hidden">A</span><div class="button action"></div>';
                    }else if(arrayItem["applicationStatus"] === "Approved"){
                        applicationStatus = '<span class="hidden">C</span><div class="button approved"></div>';
                    }else if(arrayItem["applicationStatus"] === "Denied" || arrayItem["applicationStatus"] === null || arrayItem["applicationStatus"] === "Rejected" || arrayItem["applicationStatus"] === "Empty"){
                        applicationStatus = '<span class="hidden">B</span><div class="button rejected"></div>';
                    }

                    files.forEach(function (file) {
                        if(file['field'] == "proof_of_payment"){
                            if(file["status"] === "Uploaded"){
                                pop = '<span class="hidden">A</span><div class="button action"></div>';
                            }else if(file["status"] === "Approved"){
                                pop = '<span class="hidden">C</span><div class="button approved"></div>';
                            }else if(file["status"] === "Rejected" || file["status"] === ""){
                                pop = '<span class="hidden">B</span><div class="button rejected"></div>'
                            }else if(file["status"] === "na"){
                                pop = '<span class="hidden">D</span><div class="button na"></div>';
                            }
                        }

                        if(file["field"] === "copyID"){
                            if(file["status"] === "Uploaded"){
                                copyId = '<span class="hidden">A</span><div class="button action"></div>';
                            }else if(file["status"] === "Approved"){
                                copyId = '<span class="hidden">C</span><div class="button approved"></div>';
                            }else if(file["status"] === "Rejected" || file["status"] === ""){
                                copyId = '<span class="hidden">B</span><div class="button rejected"></div>';
                            }else if(file["status"] === "na"){
                                copyId = '<span class="hidden">D</span><div class="button na"></div>';
                            }
                        }

                        if(file["field"] === "studentCard" ){
                            if(file["status"] === "Uploaded"){
                                studentCard = '<span class="hidden">A</span><div class="button action"></div>';
                            }else if(file["status"] === "Approved"){
                                studentCard= '<span class="hidden">C</span><div class="button approved"></div>';
                            }else if(file["status"] === "Rejected" || file["status"] === ""){
                                studentCard = '<span class="hidden">B</span><div class="button rejected"></div>';
                            }else if(file["status"] === "na"){
                                studentCard = '<span class="hidden">D</span><div class="button na"></div>';
                            }        
                        }

                        if(file["field"] === "proofRegistration" ){
                            if(file["status"] === "Uploaded"){
                                proofRegistration = '<span class="hidden">A</span><div class="button action"></div>';
                            }else if(file["status"] === "Approved"){
                                proofRegistration = '<span class="hidden">C</span><div class="button approved"></div>';
                            }else if(file["status"] === "Rejected" || file["status"] == ""){
                                proofRegistration = '<span class="hidden">B</span><div class="button rejected"></div>';
                            }else if(file["status"] === "na"){
                                proofRegistration = '<span class="hidden">D</span><div class="button na"></div>';
                            }
                        }

                        if(file["field"] === "bursaryApproval"){
                            if(file["status"] === "Uploaded"){
                                bursaryApproval = '<span class="hidden">A</span><div class="button action"></div>';
                            }else if(file["status"] === "Approved"){
                                bursaryApproval = '<span class="hidden">C</span><div class="button approved"></div>';
                            }else if(file["status"] === "Rejected" || file["status"] === ""){
                                bursaryApproval = '<span class="hidden">B</span><div class="button rejected"></div>';
                            }else if(file["status"] === "na"){
                                bursaryApproval = '<span class="hidden">D</span><div class="button na"></div>';
                            }
                        }

                        if(file["field"] === "bankStatement"){
                                
                            if(file["status"] === "Uploaded"){
                                bankStatement = '<span class="hidden">A</span><div class="button action"></div>';
                            }else if(file["status"] === "Approved"){
                                bankStatement = '<span class="hidden">C</span><div class="button approved"></div>';
                            }else if(file["status"] === "Rejected" || file["status"] === ""){
                                bankStatement = '<span class="hidden">B</span><div class="button rejected"></div>';
                            }else if(file["status"] === "na"){
                                bankStatement = '<span class="hidden">D</span><div class="button na"></div>';
                            }
                                
                        }
                                    
                    });

                    var datatable = $( "#application_table" ).DataTable();

                    var trDOM = datatable.row.add( [
                        '#'+arrayItem["bookingRef"] + '<span class="hidden">dataID '+arrayItem["id"]+'</span>',
                        '<span class="bold">'+arrayItem["userFirstName"]+' '+arrayItem["userLastname"]+'</span> <br/> '+arrayItem["property"]+' | '+arrayItem["roomType"]+' | '+arrayItem["created_at"],
                        arrayItem["studentType"],
                        bookingStat,
                        pop,
                        copyId,
                        applicationStatus,
                        studentCard,
                        proofRegistration,
                        bursaryApproval,
                        bankStatement,
                    ] ).draw().node();


                    $( trDOM ).addClass('table_id_'+arrayItem["id"]);
                    $( trDOM ).find('td').eq(3).addClass('bookingBg');
                    $( trDOM ).find('td').eq(6).addClass('bookingBg');


                    i++;
                });

                $("#searchButton").empty().append("Search")
            },
        });

        // 
    })

    function relocate(userId){
        window.location="/application-profile/"+userId;
    }
</script>




@endsection
