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

    <br/><br/><br/><br/><hr/><br/>
    <h2>All Applications</h2>
    <br/>

    @if(Auth::user()->role_all_apps_report == 1)

        <select id="studYear" name="studYear">
            <option value="All">All Years</option>
            @foreach($appYears as $year)
                <option value="{{$year->year}}">{{$year->year}}</option>
            @endforeach
        </select>
        <br/>

        <button type="button" class="detailsChange" id="downloadAllApps" style="float:left; margin-left:0px;">Download Applications</button>
        <div class="hidden"><a href="" download id="downloadAppsBtn"><button type="button" id="downloadBtnApps">Download</button></a></div>

        <br/><br/><br/><br/><br/>

        <h2>Actions</h2>

        <br/>
        <div class="flex">
            <div>
                <label>From Date:<label><br/>
                <input type="date" name="fromDate" id="fromDate"/>
            </div>
            <div style="padding-left:10px;">
                <label>To Date:<label><br/>
                <input type="date" name="toDate" id="toDate"/>
            </div>
            <div style="padding-left:10px;">
                <button type="button" id="actionBtn" class="detailsChange">Filter</button>
            </div>
        </div>

        <div id="propertyCountData" style="margin-bottom:20px;"></div>

        <div class="flex">
            <div id="propertyData"></div>
            <div id="cancelledData" style="margin-left:50px;"></div>
        </div>

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

    <br/><br/><br/><br/><hr/><br/>
    <h2>All Documents</h2>
    <br/>

    @if(Auth::user()->role_all_apps_report == 1)

        <select id="studYear2" name="studYear2">
            <option value="All">All Years</option>
            @foreach($appYears as $year)
                <option value="{{$year->year}}">{{$year->year}}</option>
            @endforeach
        </select>

        <select id="propdd2" name="propdd2">
            <option value="All">All Properties</option>
            @foreach($propertyList as $prop)
                <option value="{{$prop->id}}">{{$prop->residence}}</option>
            @endforeach
        </select>

        <select id="lease_filter" name="lease_filter">
            <option value="0">No Lease</option>    
            <option value="1">Lease Uploaded</option>    
            <option value="2">Lease Approved</option>
            
        </select>
        <br/>

        <button type="button" class="detailsChange" id="downloadAllAppDocs" style="float:left; margin-left:0px;">Download Documents</button>
        <a style="display: none;" href="/all_user_files.zip" download id="downloadAppDocsBtn">Download ZIP</a>

    @endif

</div>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#actionBtn").on('click', function(){
        $("#actionBtn").empty().append('<option value="" selected disabled>Loading</option>')
        $.ajax({
            type: "post",
            url: "/get-actions",
            data: {fromDate: $("#fromDate").val(), toDate: $("#toDate").val()},

            success: function (data) {      
                $("#actionBtn").empty().append('Filter')

                let properties = data[0];
                let registered = data[1];
                let cancelled = data[2];
                let restored = data[3];
                let moves = data[4];

                $("#propertyData").empty().append('<table>')
                // $("#propertyData").append('<tr><th style="padding:5px;">Property</th><th style="padding:5px;">New</th><th style="padding:5px;">Cancelled</th><th style="padding:5px;">Restored</th><th style="padding:5px;">Moved From</th><th style="padding:5px;">Moved To</th></tr>')

                $("#propertyData").append('<tr><th style="padding:5px;">Property</th><th style="padding:5px;">New</th><th style="padding:5px;">Restored</th><th style="padding:5px;">Moved To</th><th style="padding:5px;">Moved From</th><th style="padding:5px;">Cancelled</th><th style="padding:5px;">Total</th></tr>')

                properties.forEach(function(index){
                   
                    let register = 0;
                    let target = index.residence;
                    for (reg of registered) {
                        if (reg.registered_building == target) {
                            register++;
                        }
                    };

                    let cancel = 0;
                    for (can of cancelled) {
                        if (can.registered_building == target) {
                            cancel++;
                        }
                    };

                    let restore = 0;
                    for (res of restored) {
                        if (res.registered_building == target) {
                            restore++;
                        }
                    };

                    let propIn = 0;
                    let propOut = 0;

                    for( mov of moves){
                        if(mov.building == target){
                            propIn = mov.In
                            propOut = mov.Out
                        }
                    }

                    $string = '<tr>';

                    if(index.residence > 0){
                        $string += '<td style="padding:5px; border:1px solid #ccc; color:green">'+index.residence+'</td>'
                    }else{
                        $string += '<td style="padding:5px; border:1px solid #ccc;">'+index.residence+'</td>'
                    }

                    if(register > 0){
                        $string += '<td style="padding:5px; border:1px solid #ccc; color:green">'+register+'</td>'
                    }else{
                        $string += '<td style="padding:5px; border:1px solid #ccc;"></td>'
                    }

                    if(restore > 0){
                        $string += '<td style="padding:5px; border:1px solid #ccc; color:green">'+restore+'</td>'
                    }else{
                        $string += '<td style="padding:5px; border:1px solid #ccc;"></td>'
                    }

                    if(propIn > 0){
                        $string += '<td style="padding:5px; border:1px solid #ccc; color:green">'+propIn+'</td>'
                    }else{
                        $string += '<td style="padding:5px; border:1px solid #ccc;"></td>'
                    }             

                    if(propOut > 0){
                        $string += '<td style="padding:5px; border:1px solid #ccc; color:red"> - '+propOut+'</td>'
                    }else{
                        $string += '<td style="padding:5px; border:1px solid #ccc;"></td>'
                    }

                    if(cancel > 0){
                        $string += '<td style="padding:5px; border:1px solid #ccc; color:red"> - '+cancel+'</td>'
                    }else{
                        $string += '<td style="padding:5px; border:1px solid #ccc;"></td>'
                    }

                    let total = parseFloat(register) + parseFloat(restore) + parseFloat(propIn) - parseFloat(propOut) - parseFloat(cancel);

                    if(total > 0){
                        $string += '<td style="padding:5px; border:1px solid #ccc; color:green">'+total+'</td>'
                    }else if(total < 0){
                        $string += '<td style="padding:5px; border:1px solid #ccc; color:red">'+total+'</td>'
                    }else{
                        $string += '<td style="padding:5px; border:1px solid #ccc;"></td>'
                    }


                    $string += '</tr>'


                    $("#propertyData").append($string)


                    
                })

                let allIn = 0;
                let allOut = 0;

                for( mov of moves){
                    allIn += mov.In
                    allOut += mov.Out
                }

                let allTotal = parseFloat(registered.length) + parseFloat(restored.length) + parseFloat(allIn) - parseFloat(allOut) - parseFloat(cancelled.length);

                $("#propertyData").append('<tr><td><b>Totals</b></td><td style="padding:5px; border:1px solid #ccc; color:green">'+registered.length+'</td><td style="padding:5px; border:1px solid #ccc; color:green">'+restored.length+'</td><td style="padding:5px; border:1px solid #ccc; color:green">'+allIn+'</td><td style="padding:5px; border:1px solid #ccc; color:red"> - '+allOut+'</td><td style="padding:5px; border:1px solid #ccc; color:red"> - '+cancelled.length+'</td><td style="padding:5px; border:1px solid #ccc;"><b>'+allTotal+'</b></td></tr>')

                $("#propertyData").append('</table>')

                // // $("#propertyData").empty().append('<table>')
                // // $("#propertyData").append('<tr><th>User ID</th><th>Action</th><th>Date</th></tr>')

                
                // let allPropsFrom = [];
                // let allPropsTo = [];

                // data1.forEach(function(index){
                //     // $("#propertyData").append('<tr><td style="padding:5px; border:1px solid #ccc">'+index.userUid+'</td><td style="padding:5px; border:1px solid #ccc">'+index.action+'</td><td style="padding:5px; border:1px solid #ccc">'+index.created_at+'</td></tr>')
                    
                //     let removeFirst = (index.action).replace('Updated Property From ', '');
                //     let removeSecond = removeFirst.replace('Updated Property from ', '');
                //     let myArray = removeSecond.split(" to ");

                //     allPropsFrom.push(myArray[0])
                //     allPropsTo.push(myArray[1])
                // })

                

                // // $("#propertyData").append('</table>')

                // // $("#cancelledData").empty().append('<table>')
                // // $("#cancelledData").append('<tr><th>User ID</th><th>Action</th><th>Date</th></tr>')

                // // data2.forEach(function(index){
                // //     $("#cancelledData").append('<tr><td style="padding:5px; border:1px solid #ccc">'+index.userUid+'</td><td style="padding:5px; border:1px solid #ccc">'+index.action+'</td><td style="padding:5px; border:1px solid #ccc">'+index.created_at+'</td></tr>')
                    
                // // })

                // // $("#cancelledData").append('</table>')


                // $("#propertyCountData").empty().append('<table>')
                // $("#propertyCountData").append('<tr><th style="padding:5px;">Property Name</th><th style="padding:5px;">Moved From</th><th style="padding:5px;">Moved To</th></tr>')

                // const allProps = allPropsFrom.concat(allPropsTo);
                // var unique_from = allProps.filter((v, i, a) => a.indexOf(v) === i);

                // unique_from.forEach(function(item){

                //     let target = item;

                //     let counter_from = 0;
                //     for (from of allPropsFrom) {
                //         if (from == target) {
                //             counter_from++;
                //         }
                //     };

                //     let counter_to = 0;
                //     for (to of allPropsTo) {
                //         if (to == target) {
                //             counter_to++;
                //         }
                //     };

                //     $("#propertyCountData").append('<tr><td style="padding:5px; border:1px solid #ccc">'+item+'</td><td style="padding:5px; border:1px solid #ccc; color:red"> - '+counter_from+'</td><td style="padding:5px; border:1px solid #ccc; color:green"> + '+counter_to+'</td></tr>')
                // })

                
            },
        });
    })

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

    $("#downloadAllApps").on('click', function(){
        $("#downloadAllApps").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
        $.ajax({
            type: "post",
            url: "/download-apps",
            data: { year: $("#studYear").val()},
            success: function (data) {
                var a = document.getElementById('downloadAppsBtn');
                a.href = data
                $("#downloadBtnApps").click();
                $("#downloadAllApps").empty().append('Download Applications')
            },
        });
    })

    $("#downloadAllAppDocs").on('click', function(){
        $("#downloadAllAppDocs").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
        $.ajax({
            type: "post",
            url: "/download-app-docs",
            data: { year: $("#studYear2").val(), prop: $("#propdd2").val(), lease_filter: $('#lease_filter').val()},
            success: function (data) {
                $("#downloadAppDocsBtn").show();
                $("#downloadAllAppDocs").empty().append('Download Documents')
            },
            error: function (data) {
                $("#downloadAllAppDocs").empty().append('Download Documents');
                alert('Either something went wrong or no documents found');
            }
        });
    })
</script>


@endsection