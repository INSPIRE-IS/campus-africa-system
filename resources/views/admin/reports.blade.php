@extends('layouts.admin')

@section('content')


<div class="container">
    <br/>
    <h2>Student Report</h2>

    <div class="appSearchForm" style="margin-top:-35px">
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

    <hr/><br/>
    <h2>Other Reports</h2>

    <button type="button" class="detailsChange" id="downloadStudents" style="float:left; ">Download NSFAS Approval Types</button>
    <div class="hidden"><a href="/NSFAS Approval Type Export.xlsx" download><button type="button" id="downloadBtnNsfas">Download</button></a></div>

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
</script>


@endsection