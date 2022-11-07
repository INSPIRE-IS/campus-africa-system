@extends('layouts.admin')

@section('content')


<div class="container">
    <br/>
    <!-- <h2>Student Report</h2> -->

    <!-- <div class="appSearchForm" style="margin-top:-35px">
        <div class="searchField">
        <label></label><br/>
            <select name="property" id="property" onChange="changeResidence()">
                <option value="All" selected>All Residences</option>
                @foreach($propertyList as $residence)
                    <option value="{{$residence->id}}" >{{$residence->residence}}</option>
                @endforeach
            </select>
        </div>
        <div class="searchField">
        <label></label><br/>
            <select name="room_type" id="room_type">
                <option value="" selected>Select One</option>
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
            <button type="button" id="searchButton">Download</button>
        </div>
        
    </div>

    <div class="clearSpaceLogin"></div>
    <div class="hidden"><a href="/Student Export.xlsx" download><button type="button" id="downloadBtn">Download</button></a></div>

    <hr/><br/> -->
    <h2>Reports</h2>

    @if(Auth::user()->role_nsfas_approval_upload == 1)
        <button type="button" class="detailsChange" id="downloadStudents" style="float:left; ">Download NSFAS Approval Types</button>
        <div class="hidden"><a href="/NSFAS Approval Type Export.xlsx" download><button type="button" id="downloadBtnNsfas">Download</button></a></div>
    @endif

    <br/><br/><br/><br/><hr/><br/>
    <h2>Building Manager Report</h2>
    <br/>

    @if(Auth::user()->role_bm_report == 1)
        <select id="propdd" name="propdd">
            <option value="All">All Properties</option>
            @foreach($propertyList as $prop)
                <option value="{{$prop->id}}">{{$prop->residence}}</option>
            @endforeach
        </select>
        <br/>

        <button type="button" class="detailsChange" id="downloadRooms" style="float:left; margin-left:0px;">Download Beds</button>
        <div class="hidden"><a href="" download id="downloadBedsBtn"><button type="button" id="downloadBtnBeds">Download</button></a></div>
    @endif

    <br/><br/><br/><br/><hr/><br/>
    <h2>MDA Report</h2>
    <br/>

    @if(Auth::user()->role_mda_report == 1)
        <button type="button" class="detailsChange" id="downloadMda" style="float:left; margin-left:0px;">Download MDA</button>
        <div class="hidden"><a href="/MDA download.xlsx" download><button type="button" id="downloadMdaDoc">Download</button></a></div>
    @endif

    <!-- <br/><br/><br/><br/><hr/><br/>
    <h2>Buildings Report</h2>
    <br/>
    <div class="flex">
        <div>
            <label>From Date:</label><br/>
            <input type="date" name="buildingsFrom" id="buildingsFrom"/>
        </div>
        <div style="padding-left:10px;">
            <label>To Date:</label><br/>
            <input type="date" name="buildingsTo" id="buildingsTo"/>
        </div>
    </div>

    <button type="button" class="detailsChange" id="downloadMda" style="float:left; margin-left:0px;">Run Report</button>

    <div id="PeriodReport" style="margin-top:100px;">
        <div class="flex">
            <div>
                <p style="background-color:#ccc; padding:5px; border-right:1px solid #fff">{{date('j F Y')}}</p>
                <p style="padding:5px; border-right:1px solid #ccc; border-bottom:1px solid #ccc; border-left:1px solid #ccc">Nr Bookings</p>
            </div> -->

            {{--@foreach($allProp as $prop)
                <div>
                    <p style="background-color:#ccc; padding:5px; border-right:1px solid #fff">{{$prop->residence}}</p>
                    @foreach($bookingsPerProperty as $booking)
                        @if($booking->property == $prop->id)
                            <p style="padding:5px; border-right:1px solid #ccc; border-bottom:1px solid #ccc">{{$booking->count}}</p>
                        @endif
                    @endforeach
                </div>
            @endforeach--}}

            <!-- <div>
                <p style="background-color:#ccc; padding:5px;">Property 1</p>
                <p style="padding:5px; border-right:1px solid #ccc; border-bottom:1px solid #ccc">5</p>
            </div>
            <div >
                <p style="background-color:#ccc; padding:5px;">Property 2</p>
                <p style="padding:5px; border-right:1px solid #ccc; border-bottom:1px solid #ccc">9</p>
            </div>
            <div>
                <p style="background-color:#ccc; padding:5px;">Property 3</p>
                <p style="padding:5px; border-right:1px solid #ccc; border-bottom:1px solid #ccc">7</p>
            </div> -->
        <!-- </div>
    </div> -->

</div>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function changeResidence(){
        if($("#property").val() !== "All"){
            $("#room_type").empty().append('<option value="" selected disabled>Loading</option>')

            $.ajax({
                type: "post",
                url: "/get-room-types-id",
                data: {residence: $("#property").val()},

                success: function (data) {          
                    $("#room_type").empty().append('<option value="" selected disabled>Select a Room Type</option>')
                    data.forEach(function(index){
                        let roomTypeSelected = '<?php echo Session::get("room_type"); ?>';
                        let roomPropertySelected = '<?php echo Session::get("property"); ?>';
                        let selected = "";

                        if($("#property").val() == roomPropertySelected && roomTypeSelected == index.name){
                            selected = "selected"
                        }
                        $("#room_type").append('<option value="'+index.id+'" '+selected+'>'+index.name+'</option>')
                    })
                },
            });
        }
    };

    $("#searchButton").on('click', function(){
        
        $.ajax({
            type: "post",
            url: "/download-report",
            data: {
                property: $("#property").val(), 
                room_type: $("#room_type").val(), 
                applicant: $("#applicant").val(), 
                student_type: $("#student_type").val(),
                bookingStatus: $("#bookingStatus").val(),
                applicationStatus: $("#applicationStatus").val(), 
                date: $("#date").val(),
            },
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                $("#downloadBtn").click();
            },
        });

    })

    $("#downloadStudents").on('click', function(){
        $.ajax({
            type: "get",
            url: "/download-nsfas-report",

            success: function (data) {
                $("#downloadBtnNsfas").click();
            },
        });
    })

    $("#downloadRooms").on('click', function(){
        $("#downloadRooms").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
        $.ajax({
            type: "post",
            url: "/download-beds",
            data: { property: $("#propdd").val()},
            success: function (data) {
                var a = document.getElementById('downloadBedsBtn');
                a.href = data
                $("#downloadBtnBeds").click();
                $("#downloadRooms").empty().append('Download Beds')
            },
        });
    })

    $("#downloadMda").on('click', function(){
        $("#downloadMda").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
        $.ajax({
            type: "get",
            url: "/download-mda",
            success: function (data) {
                $("#downloadMdaDoc").click();
                $("#downloadMda").empty().append('Download MDA')
            },
        });
    })
</script>


@endsection