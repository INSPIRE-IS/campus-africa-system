@extends('layouts.admin')

@section('content')
<div class="container">


    <div class="appSearchForm">
        <div class="searchField">
        <label></label><br/>
            <select name="property">
                <option value="Property 1">Property 1</option>
                <option value="Property 2">Property 2</option>
                <option value="Property 3">Property 3</option>
            </select>
        </div>
        <div class="searchField">
        <label></label><br/>
            <select name="property">
                <option value="Property 1">Property 1</option>
                <option value="Property 2">Property 2</option>
                <option value="Property 3">Property 3</option>
            </select>
        </div>
        <div class="searchField">
            <label>Applicant</label><br/>
            <input type="text" name="applicant" id="applicant"/>
        </div>

        <div class="searchField">
        <label></label><br/>
            <select name="applicationStatus">
                <option value="all">All Applications</option>
                <option value="Uploaded">Applied</option>
                <option value="Accepted">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
        </div>
        <div class="searchField">
        <label>Date</label><br/>
            <select name="bookingStatus">
                <option value="all">All Bookings</option>
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
                <div id="indexOne"><div class="button rejected"></div><span class="text">Rejected</span></div>
                <div id="indexOne"><div class="button na"></div><span class="text">Not Applicable</span></div>
            </div>
        </div>
        
    </div>

    <div class="clearSpaceLogin"></div>

    <div class="container">
        <div class="ApplicationResults">



            <table class="applicationTable">
                <thead>
                    <tr>
                        <th class="first">BOOKING REFERENCE</th>
                        <th>APPLICANT</th>
                        <th>STUDENT TYPE</th>
                        <th style="border-top:3px solid #E74F26; border-left:3px solid #E74F26">BOOKING STATUS</th>
                        <th style="border-top:3px solid #E74F26">PROOF OF PAYMENT</th>
                        <th style="border-top:3px solid #E74F26; border-right:3px solid #E74F26">ID COPY</th>
                        <th style="border-top:3px solid #19e5cd; border-left:3px solid #19e5cd">APPLICATION STATUS</th>
                        <th style="border-top:3px solid #19e5cd">STUDENT CARD COPY</th>
                        <th style="border-top:3px solid #19e5cd">PROOF OF REGISTRATION</th>
                        <th style="border-top:3px solid #19e5cd">NSFAS | BURSARY APPROVAL</th>
                        <th style="border-top:3px solid #19e5cd; border-right:3px solid #19e5cd; border-top-right-radius:10px;">BANK STATEMENTS</th>
                    </tr>
                </thead>
                <tbody>

                <?php $i = 0; ?>

                    @foreach($users as $user)

                        <tr class=" <?php if($i % 2 == 0){echo 'dark';}else{echo 'light';} ?> clickable-row" data-href='/application-profile/{{$user["id"]}}'>
                            <td id="bookingRef">#{{$user["bookingRef"]}} </td>
                            <td id="appUser"><span class="bold">{{$user["userFirstName"]}} {{$user["userLastname"]}}</span> <br/> {{$user["userId"]}} | {{$user["roomBlock"]}}</td>
                            <td id="studType">XX<span class="bold">{{$user["studentType"]}}</span></td>
                            <td style="border-left:3px solid #E74F26" id="statusBarIcon">
                                @if($user["bookingStatus"] === "Pending" || $user["bookingStatus"] === null)
                                    <div class="button action"></div>
                                @elseif($user["bookingStatus"] === "Approved")
                                    <div class="button approved"></div>
                                @elseif($user["bookingStatus"] === "Denied")
                                    <div class="button rejected"></div>
                                @endif
                            </td>
                            
                            @foreach($user["files"] as $file)

                                @if($file["field"] === "proof_of_payment")
                                    @if($file["status"] === "Uploaded" || $file["status"] === "")
                                        <td id="statusBarIcon"><div class="button action"></div></td>
                                    @elseif($file["status"] === "Approved")
                                        <td id="statusBarIcon"><div class="button approved"></div></td>
                                    @elseif($file["status"] === "Rejected")
                                        <td id="statusBarIcon"><div class="button rejected"></div></td>
                                    @elseif($file["status"] === "na")
                                        <td id="statusBarIcon"><div class="button na"></div></td>
                                    @endif

                                @endif

                            @endforeach

                            @foreach($user["files"] as $file)

                                

                                @if($file["field"] === "copyID")
                                    <td style="border-right:3px solid #E74F26" id="statusBarIcon">
                                        @if($file["status"] === "Uploaded" || $file["status"] === "")
                                            <div class="button action"></div>
                                        @elseif($file["status"] === "Approved")
                                            <div class="button approved"></div>
                                        @elseif($file["status"] === "Rejected")
                                            <div class="button rejected"></div>
                                        @elseif($file["status"] === "na")
                                            <div class="button na"></div>
                                        @endif
                                    </td>
                                    
                                @endif

                            @endforeach

                            <td style="border-left: 3px solid #19e5cd;" id="statusBarIcon">
                                @if($user["applicationStatus"] === "Pending" || $user["applicationStatus"] === null)
                                    <div class="button action"></div>
                                @elseif($user["applicationStatus"] === "Approved")
                                    <div class="button approved"></div>
                                @elseif($user["applicationStatus"] === "Denied")
                                    <div class="button rejected"></div>
                                @endif
                            </td>



                            @foreach($user["files"] as $file)

                                @if($file["field"] === "studentCard" )
                                    @if($file["status"] === "Uploaded" || $file["status"] === "")
                                        <td id="statusBarIcon"><div class="button action"></div></td>
                                    @elseif($file["status"] === "Approved")
                                        <td id="statusBarIcon"><div class="button approved"></div></td>
                                    @elseif($file["status"] === "Rejected")
                                        <td id="statusBarIcon"><div class="button rejected"></div></td>
                                    @elseif($file["status"] === "na")
                                        <td id="statusBarIcon"><div class="button na"></div></td>
                                    @endif

                                    
                                @endif

                            @endforeach

                            @foreach($user["files"] as $file)

                                @if($file["field"] === "proofRegistration" )
                                    @if($file["status"] === "Uploaded" || $file["status"] == "")
                                        <td id="statusBarIcon"><div class="button action"></div></td>
                                    @elseif($file["status"] === "Approved")
                                        <td id="statusBarIcon"><div class="button approved"></div></td>
                                    @elseif($file["status"] === "Rejected")
                                        <td id="statusBarIcon"><div class="button rejected"></div></td>
                                    @elseif($file["status"] === "na")
                                        <td id="statusBarIcon"><div class="button na"></div></td>
                                    @endif

                                @endif

                            @endforeach

                            @foreach($user["files"] as $file)

                                @if($file["field"] === "bursaryApproval")
                                    @if($file["status"] === "Uploaded" || $file["status"] === "")
                                        <td id="statusBarIcon"><div class="button action"></div></td>
                                    @elseif($file["status"] === "Approved")
                                        <td id="statusBarIcon"><div class="button approved"></div></td>
                                    @elseif($file["status"] === "Rejected")
                                        <td id="statusBarIcon"><div class="button rejected"></div></td>
                                    @elseif($file["status"] === "na")
                                        <td id="statusBarIcon"><div class="button na"></div></td>
                                    @endif
                                @endif

                            @endforeach

                            @foreach($user["files"] as $file)

                                @if($file["field"] === "bankStatement")
                                <td style="border-right:3px solid #19e5cd" id="statusBarIcon">
                                    @if($file["status"] === "Uploaded" || $file["status"] === "")
                                        <div class="button action"></div>
                                    @elseif($file["status"] === "Approved")
                                        <div class="button approved"></div>
                                    @elseif($file["status"] === "Rejected")
                                        <div class="button rejected"></div>
                                    @elseif($file["status"] === "na")
                                        <div class="button na"></div>
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

    
</div>

<div class="clearSpaceLoginPage"></div>


<script>
    jQuery(document).ready(function($) {
        $(".clickable-row").click(function() {
            window.location = $(this).data("href");
        });
    });

    $("#searchButton").on('click', function(){
        //
    })
</script>




@endsection
