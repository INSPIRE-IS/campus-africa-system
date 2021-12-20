@extends('layouts.admin')

@section('content')


<div class="container">
    <br/>

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
            <button type="button" id="searchButton">Download</button>
        </div>
        
    </div>

    <div class="clearSpaceLogin"></div>
    <div class="hidden"><a href="/Student Export.xlsx" download><button type="button" id="downloadBtn">Download</button></a></div>

</div>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

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
</script>


@endsection