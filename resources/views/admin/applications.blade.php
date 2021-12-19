@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>

<div class="container">

<br/>

    @if(Auth::user()->role_create_app == 1)
        <a href="/new/student"><button type="button" class="inactiveBtn">Add New Student</button></a>
    @endif


    <div class="appSearchForm" style="margin-top:-35px">
        <div class="searchField">
        <label></label><br/>
            <select name="property" id="property">
                <option value="All" selected>All Residences</option>
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
                <option value="All" selected>All Room Types</option>
                <option value="Single">Single</option>
                <option value="2 Sharing">2 Sharing</option>
                <option value="3 Sharing">3 Sharing</option>
                <option value="Single 2 bedroom">Single | 2 Bedroom</option>
                <option value="Single 3 bedroom">Single | 3 Bedroom</option>
                <option value="Single small">Single | Small</option>
                <option value="Single medium">Single | Medium</option>
                <option value="Single large">Single | Large</option>
            </select>
        </div>
        <div class="searchField">
            <label>Applicant</label><br/>
            <input type="text" name="applicant" id="applicant"/>
        </div>

        <div class="searchField">
        <label></label><br/>
            <select name="student_type" id='student_type'>
                <option value="All" selected>All Student Types</option>
                <option value="NSFAS">NSFAS</option>
                <option value="Bursary">Bursary</option>
                <option value="Cash / Direct">Cash / Direct</option>
            </select>
        </div>
        <div class="searchField">
        <label></label><br/>
            <select name="bookingStatus" id='bookingStatus' style="width:45%">
                <option value="All" selected>All Bookings</option>
                <option value="Approved">&#128994; Valid Booking</option>
                <option value="Action">&#128993; Pending Booking</option>
                <option value="Empty">&#128308; Invalid Booking</option>
            </select>
            <select name="applicationStatus" id='applicationStatus' style="width:45%">
                <option value="All" selected>All Applications</option>
                <option value="Approved">&#128994; Valid Application</option>
                <option value="Action">&#128993; Pending Application</option>
                <option value="Empty">&#128308; Invalid Application</option>
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
                        <th style="width:50%">APPLICANT | ROOM</th>
                        <th style="width:10%">DATE</th>
                        <th>STUDENT TYPE</th>
                        <th style="background-color:#ccc; color:#000">BOOKING STATUS</th>
                        <th>PROOF OF PAYMENT</th>
                        <th>ID COPY</th>
                        <th style="background-color:#ccc; color:#000">APPLICATION STATUS</th>
                        <th>STUDENT CARD COPY</th>
                        <th>PROOF OF REGISTRATION</th>
                        <th>NSFAS | BURSARY APPROVAL</th>
                        <th>BANK STATEMENTS</th>
                        <th style="background-color:#ccc; color:#000">Lease</th>
                    </tr>
                </thead>
                <tbody id="applicantsSection">

                <?php $i = 0; ?>

                    @foreach($users as $user)

                        <tr class=" <?php if($i % 2 == 0){echo 'dark';}else{echo 'light';} ?> clickable-row table_id_{{$user['id']}}" data-href='/application-profile/{{$user["id"]}}'>
                            <td id="bookingRef">#{{$user["bookingRef"]}} </td>
                            <td id="appUser"><span class="bold">{{$user["userFirstName"]}} {{$user["userLastname"]}}</span> <br/> {{$user["property"]}} | {{$user["roomType"]}}</td>
                            <td id="date">{{$user["created_at"]}}</td>
                            <td id="studType">@if($user["studentType"] == "Cash / Direct") Cash @else {{$user["studentType"]}} @endif</td>
                            <td id="statusBarIcon" style="background-color:#ccc">
                                @if($user["bookingStatus"] === "Pending" || $user["bookingStatus"] === "Action")
                                <span class="hidden">A</span><div class="button action bigButton"></div>
                                @elseif($user["bookingStatus"] === "Approved")
                                <span class="hidden">C</span><div class="button approved bigButton"></div>
                                @elseif($user["bookingStatus"] === "Denied" || $user["bookingStatus"] === null || $user["bookingStatus"] === "Empty" || $user["bookingStatus"] === "Rejected")
                                <span class="hidden">B</span><div class="button rejected bigButton"></div>
                                @endif
                            </td>
                            
                            @foreach($user["files"] as $file)

                                @if($file["field"] === "proof_of_payment")
                                    @if($file["status"] === "Uploaded")
                                        <td id="statusBarIcon"><span class="hidden">A</span><div class="button action noBoarder"></div></td>
                                    @elseif($file["status"] === "Approved")
                                        <td id="statusBarIcon"><span class="hidden">C</span><div class="button approved noBoarder"></div></td>
                                    @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                        <td id="statusBarIcon"><span class="hidden">B</span><div class="button rejected noBoarder"></div></td>
                                    @elseif($file["status"] === "na")
                                        <td id="statusBarIcon"><span class="hidden">D</span><div class="button na noBoarder"></div></td>
                                    @endif

                                @endif

                            @endforeach

                            @foreach($user["files"] as $file)

                                

                                @if($file["field"] === "copyID")
                                    <td id="statusBarIcon">
                                        @if($file["status"] === "Uploaded")
                                        <span class="hidden">A</span><div class="button action noBoarder"></div>
                                        @elseif($file["status"] === "Approved")
                                        <span class="hidden">C</span><div class="button approved noBoarder"></div>
                                        @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                        <span class="hidden">B</span><div class="button rejected noBoarder"></div>
                                        @elseif($file["status"] === "na")
                                        <span class="hidden">D</span><div class="button na noBoarder"></div>
                                        @endif
                                    </td>
                                    
                                @endif

                            @endforeach

                            <td id="statusBarIcon" style="background-color:#ccc">
                                @if($user["applicationStatus"] === "Pending" || $user["applicationStatus"] === "Action" || $user["applicationStatus"] === "Approved")
                                <span class="hidden">A</span><div class="button action bigButton"></div>
                                @elseif($user["applicationStatus"] === "Approved Confirmed")
                                <span class="hidden">C</span><div class="button approved bigButton"></div>
                                @elseif($user["applicationStatus"] === "Denied" || $user["applicationStatus"] === null || $user["applicationStatus"] === "Rejected" || $user["applicationStatus"] === "Empty" || $user["applicationStatus"] == "Denied Confimed")
                                <span class="hidden">B</span><div class="button rejected bigButton"></div>
                                @endif
                            </td>



                            @foreach($user["files"] as $file)

                                @if($file["field"] === "studentCard" )
                                    @if($file["status"] === "Uploaded")
                                    <span class="hidden">A</span><td id="statusBarIcon"><div class="button action noBoarder"></div></td>
                                    @elseif($file["status"] === "Approved")
                                    <span class="hidden">C</span><td id="statusBarIcon"><div class="button approved noBoarder"></div></td>
                                    @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                    <span class="hidden">B</span><td id="statusBarIcon"><div class="button rejected noBoarder"></div></td>
                                    @elseif($file["status"] === "na")
                                    <span class="hidden">D</span><td id="statusBarIcon"><div class="button na noBoarder"></div></td>
                                    @endif

                                    
                                @endif

                            @endforeach

                            @foreach($user["files"] as $file)

                                @if($file["field"] === "proofRegistration" )
                                    @if($file["status"] === "Uploaded")
                                        <td id="statusBarIcon"><span class="hidden">A</span><div class="button action noBoarder"></div></td>
                                    @elseif($file["status"] === "Approved")
                                        <td id="statusBarIcon"><span class="hidden">C</span><div class="button approved noBoarder"></div></td>
                                    @elseif($file["status"] === "Rejected" || $file["status"] == "")
                                        <td id="statusBarIcon"><span class="hidden">B</span><div class="button rejected noBoarder"></div></td>
                                    @elseif($file["status"] === "na")
                                        <td id="statusBarIcon"><span class="hidden">D</span><div class="button na noBoarder"></div></td>
                                    @endif

                                @endif

                            @endforeach

                            @foreach($user["files"] as $file)

                                @if($file["field"] === "bursaryApproval")
                                    @if($file["status"] === "Uploaded")
                                        <td id="statusBarIcon"><span class="hidden">A</span><div class="button action noBoarder"></div></td>
                                    @elseif($file["status"] === "Approved")
                                        <td id="statusBarIcon"><span class="hidden">C</span><div class="button approved noBoarder"></div></td>
                                    @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                        <td id="statusBarIcon"><span class="hidden">B</span><div class="button rejected noBoarder"></div></td>
                                    @elseif($file["status"] === "na")
                                        <td id="statusBarIcon"><span class="hidden">D</span><div class="button na noBoarder"></div></td>
                                    @endif
                                @endif

                            @endforeach

                            @foreach($user["files"] as $file)

                                @if($file["field"] === "bankStatement")
                                <td id="statusBarIcon">
                                    @if($file["status"] === "Uploaded")
                                    <span class="hidden">A</span><div class="button action noBoarder"></div>
                                    @elseif($file["status"] === "Approved")
                                    <span class="hidden">C</span><div class="button approved noBoarder"></div>
                                    @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                    <span class="hidden">B</span><div class="button rejected noBoarder"></div>
                                    @elseif($file["status"] === "na")
                                    <span class="hidden">D</span><div class="button na noBoarder"></div>
                                    @endif
                                </td>
                                @endif
                            
                            @endforeach

                            @foreach($user["files"] as $file)

                                @if($file["field"] === "moveIn")
                                <td id="statusBarIcon" style="background-color:#ccc">
                                    @if($file["status"] === "Uploaded")
                                    <span class="hidden">A</span><div class="button action bigButton"></div>
                                    @elseif($file["status"] === "Approved")
                                    <span class="hidden">C</span><div class="button approved bigButton"></div>
                                    @elseif($file["status"] === "Rejected")
                                    <span class="hidden">B</span><div class="button rejected bigButton"></div>
                                    @elseif($file["status"] === "na" || $file["status"] === "")
                                    <span class="hidden">D</span><div class="button na bigButton"></div>
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

    
</div>

<div class="clearSpaceLoginPage"></div>


@endsection
