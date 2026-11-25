@extends('layouts.admin')

@section('content')


    <div class="container">
        <br />
        <h2>Reports</h2>
        {{-- Student Report --}}
        @if (Auth::user()->role_super == 1)
            <h2>Student Report</h2>

            <div class="appSearchForm" style="margin-top:-35px">
                <div class="searchField">
                    <label></label><br />
                    <select name="property" id="property" onChange="changeResidence()">
                        <option value="All" selected>All Residences</option>
                        @foreach ($propertyList as $residence)
                            <option value="{{ $residence->id }}">{{ $residence->residence }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="searchField">
                    <label></label><br />
                    <select name="room_type" id="room_type">
                        <option value="" selected>Select One</option>
                    </select>
                </div>
                <div class="searchField">
                    <label>Applicant</label><br />
                    <input type="text" name="applicant" id="applicant" />
                </div>

                <div class="searchField">
                    <label></label><br />
                    {{-- STUDENT TYPE --}}
                    <select name="student_type" id='student_type'>
                        <option value="All" selected>All Student Types</option>
                        <option value="NSFAS">NSFAS</option>
                        <option value="FUNZA">FUNZA</option>
                        <option value="GCRA">GCRA</option>
                        <option value="NRF">NRF</option>
                        <option value="MQA">MQA</option>
                        <option value="Bursary">Bursary</option>
                        <option value="Cash / Direct">Cash / Direct</option>
                    </select>
                </div>
                <div class="searchField">
                    <label></label><br />
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
                    <label>Year</label><br />
                    <select name="studentYear" id="studentYear">
                        @foreach ($appYears as $year)
                            <option value="{{ $year->year }}">{{ $year->year }}</option>
                        @endforeach
                    </select>
                    <br /><br /><br />
                </div>

                <div class="searchTab">
                    <button type="button" id="searchButton">Download</button>
                </div>

            </div>
            <div class="clearSpaceLogin"></div>
            <div class="hidden"><a href="/Student Export.xlsx" download><button type="button"
                        id="downloadBtn">Download</button></a></div>

            <hr /><br />
        @endif

        {{-- NSFAS Approval Types --}}
        @if (Auth::user()->role_nsfas_approval_upload == 1)
            <br /><br /><br /><br />
            <hr /><br />
            <h2>NSFAS Approval Types</h2>
            <br />
            <button type="button" class="detailsChange" id="downloadStudents" style="float:left; "
                onClick="downloadStudentsReport()">Download NSFAS Approval
                Types</button>
            <div class="hidden"><a href="/NSFAS Approval Type Export.xlsx" download><button type="button"
                        id="downloadBtnNsfas">Download</button></a></div>
        @endif
        {{-- Building Manager Report --}}
        @if (Auth::user()->role_bm_report == 1)
            <br /><br /><br /><br />
            <hr /><br />
            <h2>Building Manager Report</h2>
            <br />
            <select id="propdd" name="propdd">
                <option value="All">All Properties</option>
                @foreach ($propertyList as $prop)
                    <option value="{{ $prop->id }}">{{ $prop->residence }}</option>
                @endforeach
            </select>
            <select id="bmYear" name="bmYear">
                @foreach ($appYears as $year)
                    <option value="{{ $year->year }}">{{ $year->year }}</option>
                @endforeach
            </select>
            <br />

            <button type="button" class="detailsChange" id="downloadRooms" style="float:left; margin-left:0px;">Download
                Beds</button>
        @endif
        {{-- MDA Report --}}
        @if (Auth::user()->role_mda_report == 1)
            <br /><br /><br /><br />
            <hr /><br />
            <h2>MDA Report</h2>
            <br />
            <button type="button" class="detailsChange" id="downloadMda" style="float:left; margin-left:0px;"
                onClick="downloadMdaReport()">Download
                MDA</button>
            <div class="hidden"><a href="/MDA download.xlsx" download><button type="button"
                        id="downloadMdaDoc">Download</button></a></div>
        @endif

        {{-- All Applications --}}
        @if (Auth::user()->role_all_apps_report == 1)
            <br /><br /><br /><br />
            <hr /><br />
            <h2>All Applications</h2>
            <br />

            <select id="studYear" name="studYear">
                @foreach ($appYears as $year)
                    <option value="{{ $year->year }}">{{ $year->year }}</option>
                @endforeach
            </select>
            <br />

            <button type="button" class="detailsChange" id="downloadAllApps" style="float:left; margin-left:0px;">
                Download Applications
            </button>
            <div class="hidden">
                <a href="" download id="downloadAppsBtn">
                    <button type="button" id="downloadBtnApps">
                            Download
                    </button>
                </a>
            </div>

            <br /><br /><br /><br /><br />

            <h2>Actions</h2>

            <br />
            <div class="flex">
                <div>
                    <label>From Date:<label><br />
                            <input type="date" name="fromDate" id="fromDate" />
                </div>
                <div style="padding-left:10px;">
                    <label>To Date:<label><br />
                            <input type="date" name="toDate" id="toDate" />
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

        {{-- <br /><br /><br /><br />
            <hr /><br />
            <h2>Buildings Report</h2>
            <br />
            <div class="flex">
                <div>
                    <label>From Date:</label><br />
                    <input type="date" name="buildingsFrom" id="buildingsFrom" />
                </div>
                <div style="padding-left:10px;">
                    <label>To Date:</label><br />
                    <input type="date" name="buildingsTo" id="buildingsTo" />
                </div>
            </div>

            <button type="button" class="detailsChange" id="downloadMda" style="float:left; margin-left:0px;">Run
                Report</button>

            <div id="PeriodReport" style="margin-top:100px;">
                <div class="flex">
                    <div>
                        <p style="background-color:#ccc; padding:5px; border-right:1px solid #fff">{{ date('j F Y') }}</p>
                        <p
                            style="padding:5px; border-right:1px solid #ccc; border-bottom:1px solid #ccc; border-left:1px solid #ccc">
                            Nr Bookings</p>
                    </div>

                    @foreach ($allProp as $prop)
                        <div>
                            <p style="background-color:#ccc; padding:5px; border-right:1px solid #fff">{{ $prop->residence }}
                            </p>
                            @foreach ($bookingsPerProperty as $booking)
                                @if ($booking->property == $prop->id)
                                    <p style="padding:5px; border-right:1px solid #ccc; border-bottom:1px solid #ccc">
                                        {{ $booking->count }}</p>
                                @endif
                            @endforeach
                        </div>
                    @endforeach

                    <div>
                        <p style="background-color:#ccc; padding:5px;">Property 1</p>
                        <p style="padding:5px; border-right:1px solid #ccc; border-bottom:1px solid #ccc">5</p>
                    </div>
                    <div>
                        <p style="background-color:#ccc; padding:5px;">Property 2</p>
                        <p style="padding:5px; border-right:1px solid #ccc; border-bottom:1px solid #ccc">9</p>
                    </div>
                    <div>
                        <p style="background-color:#ccc; padding:5px;">Property 3</p>
                        <p style="padding:5px; border-right:1px solid #ccc; border-bottom:1px solid #ccc">7</p>
                    </div>
                </div>
            </div> 
        --}}


        {{-- All Documents --}}
        @if (Auth::user()->role_all_apps_report == 1 && Auth::user()->role_lookup == 1)
            <br /><br /><br /><br />
            <hr /><br />
            <h2>All Documents</h2>
            <br />
            <select id="studYear2" name="studYear2">
                @foreach ($appYears as $year)
                    <option value="{{ $year->year }}">{{ $year->year }}</option>
                @endforeach
            </select>

            <select id="propdd2" name="propdd2">
                @foreach ($propertyList as $prop)
                    <option value="{{ $prop->id }}" data-active-date="{{ $prop->active_date }}" data-inactive-date="{{ $prop->inactive_date }}">{{ $prop->residence }}</option>
                @endforeach
            </select>

            <select id="lease_filter" name="lease_filter">
                <option value="0">No Lease</option>
                <option value="1">Lease Uploaded</option>
                <option value="2">Lease Approved</option>

            </select>
            <br />

            <button type="button" class="detailsChange" id="downloadAllAppDocs"
                style="float:left; margin-left:0px;">Download Documents</button>
        @endif

        {{-- Institution and Funding Lease Documents --}}
        @if (Auth::user()->role_all_apps_report == 1 && Auth::user()->role_lookup == 1)
            <br /><br /><br /><br />
            <hr /><br />
            <h2>Institution and Funding Lease Documents</h2>
            <br />

            <select id="studYear3" name="studYear3">
                @foreach ($appYears as $year)
                    <option value="{{ $year->year }}">{{ $year->year }}</option>
                @endforeach
            </select>

            <select id="prop3" name="prop3">
                @foreach ($propertyList as $prop)
                    <option value="{{ $prop->id }}" data-active-date="{{ $prop->active_date }}" data-inactive-date="{{ $prop->inactive_date }}">{{ $prop->residence }}</option>
                @endforeach
            </select>

            <select id="institution3" name="institution3">
                <option value="All">All institution</option>
                @foreach ($institutionList as $prop)
                    <option value="{{ $prop->id }}">{{ $prop->lookup_name }}</option>
                @endforeach
            </select>


            <select name="student_type3" id='student_type3'>
                <option value="All">All Student Types</option>
                <option value="NSFAS">NSFAS</option>
                <option value="FUNZA">FUNZA</option>
                <option value="GCRA">GCRA</option>
                <option value="NRF">NRF</option>
                <option value="MQA">MQA</option>
                <option value="Bursary">Bursary</option>
                <option value="Cash / Direct">Cash</option>
            </select>

            {{-- <select name="fieldStatus" id='fieldStatus'>
                <option value="All" selected>- Select One - </option>
                <option value="rob_10_fee">Fee</option>
                <option value="rob_15_id">ID</option>
                <option value="rob_20_card">Card</option>
                <option value="rob_25_reg">Reg</option>
                <option value="rob_30_funds">Funds</option>
                <option value="rob_35_info">Info</option>
                <option value="rob_40_bed">Bed</option>
                <option value="rob_45_other">Other</option>
                <option value="rob_50_approve">Approve</option>
                <option value="rob_55_lease">Lease</option>
            </select> --}}

            <select id="lease_filter3" name="lease_filter3">
                <option value="0">No Lease</option>
                <option value="1">Lease Uploaded</option>
                <option value="2">Lease Approved</option>

            </select>
            <br />

            <button type="button" class="detailsChange" id="downloadSelectedDocs"
                style="float:left; margin-left:0px;">Download Documents</button>
        @endif

        {{-- Institution and Funding Lease Documents --}}
        @if (Auth::user()->role_all_apps_report == 1 && Auth::user()->role_lookup == 1)
            <br /><br /><br /><br />
            <hr /><br />
            <h2>Compatability Matching</h2>
            <br />

            <select id="studYear4" name="studYear4">
                @foreach ($appYears as $year)
                    <option value="{{ $year->year }}">{{ $year->year }}</option>
                @endforeach
            </select>

            <select id="prop4" name="prop4">
                <option value="All">All Properties</option>
                @foreach ($propertyList as $prop)
                    <option value="{{ $prop->id }}">{{ $prop->residence }}</option>
                @endforeach
            </select>
            <br />

            <button type="button" class="detailsChange" id="downloadCompatabilityDocs"
                style="float:left; margin-left:0px;">Download Documents</button>
        @endif        
    </div>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#actionBtn").on('click', function() {
            $("#actionBtn").empty().append('<option value="" selected disabled>Loading</option>')
            $.ajax({
                type: "post",
                url: "/get-actions",
                data: {
                    fromDate: $("#fromDate").val(),
                    toDate: $("#toDate").val()
                },

                success: function(data) {
                    $("#actionBtn").empty().append('Filter')

                    let properties = data[0];
                    let registered = data[1];
                    let cancelled = data[2];
                    let restored = data[3];
                    let moves = data[4];

                    $("#propertyData").empty().append('<table>')
                    // $("#propertyData").append('<tr><th style="padding:5px;">Property</th><th style="padding:5px;">New</th><th style="padding:5px;">Cancelled</th><th style="padding:5px;">Restored</th><th style="padding:5px;">Moved From</th><th style="padding:5px;">Moved To</th></tr>')

                    $("#propertyData").append(
                        '<tr><th style="padding:5px;">Property</th><th style="padding:5px;">New</th><th style="padding:5px;">Restored</th><th style="padding:5px;">Moved To</th><th style="padding:5px;">Moved From</th><th style="padding:5px;">Cancelled</th><th style="padding:5px;">Total</th></tr>'
                    )

                    properties.forEach(function(index) {

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

                        for (mov of moves) {
                            if (mov.building == target) {
                                propIn = mov.In
                                propOut = mov.Out
                            }
                        }

                        $string = '<tr>';

                        if (index.residence > 0) {
                            $string +=
                                '<td style="padding:5px; border:1px solid #ccc; color:green">' +
                                index.residence + '</td>'
                        } else {
                            $string += '<td style="padding:5px; border:1px solid #ccc;">' +
                                index.residence + '</td>'
                        }

                        if (register > 0) {
                            $string +=
                                '<td style="padding:5px; border:1px solid #ccc; color:green">' +
                                register + '</td>'
                        } else {
                            $string += '<td style="padding:5px; border:1px solid #ccc;"></td>'
                        }

                        if (restore > 0) {
                            $string +=
                                '<td style="padding:5px; border:1px solid #ccc; color:green">' +
                                restore + '</td>'
                        } else {
                            $string += '<td style="padding:5px; border:1px solid #ccc;"></td>'
                        }

                        if (propIn > 0) {
                            $string +=
                                '<td style="padding:5px; border:1px solid #ccc; color:green">' +
                                propIn + '</td>'
                        } else {
                            $string += '<td style="padding:5px; border:1px solid #ccc;"></td>'
                        }

                        if (propOut > 0) {
                            $string +=
                                '<td style="padding:5px; border:1px solid #ccc; color:red"> - ' +
                                propOut + '</td>'
                        } else {
                            $string += '<td style="padding:5px; border:1px solid #ccc;"></td>'
                        }

                        if (cancel > 0) {
                            $string +=
                                '<td style="padding:5px; border:1px solid #ccc; color:red"> - ' +
                                cancel + '</td>'
                        } else {
                            $string += '<td style="padding:5px; border:1px solid #ccc;"></td>'
                        }

                        let total = parseFloat(register) + parseFloat(restore) + parseFloat(
                            propIn) - parseFloat(propOut) - parseFloat(cancel);

                        if (total > 0) {
                            $string +=
                                '<td style="padding:5px; border:1px solid #ccc; color:green">' +
                                total + '</td>'
                        } else if (total < 0) {
                            $string +=
                                '<td style="padding:5px; border:1px solid #ccc; color:red">' +
                                total + '</td>'
                        } else {
                            $string += '<td style="padding:5px; border:1px solid #ccc;"></td>'
                        }


                        $string += '</tr>'


                        $("#propertyData").append($string)



                    })

                    let allIn = 0;
                    let allOut = 0;

                    for (mov of moves) {
                        allIn += mov.In
                        allOut += mov.Out
                    }

                    let allTotal = parseFloat(registered.length) + parseFloat(restored.length) +
                        parseFloat(allIn) - parseFloat(allOut) - parseFloat(cancelled.length);

                    $("#propertyData").append(
                        '<tr><td><b>Totals</b></td><td style="padding:5px; border:1px solid #ccc; color:green">' +
                        registered.length +
                        '</td><td style="padding:5px; border:1px solid #ccc; color:green">' +
                        restored.length +
                        '</td><td style="padding:5px; border:1px solid #ccc; color:green">' +
                        allIn +
                        '</td><td style="padding:5px; border:1px solid #ccc; color:red"> - ' +
                        allOut +
                        '</td><td style="padding:5px; border:1px solid #ccc; color:red"> - ' +
                        cancelled.length +
                        '</td><td style="padding:5px; border:1px solid #ccc;"><b>' + allTotal +
                        '</b></td></tr>')

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

        function changeResidence() {
            if ($("#property").val() !== "All") {
                $("#room_type").empty().append('<option value="" selected disabled>Loading</option>')

                $.ajax({
                    type: "post",
                    url: "/get-room-types-id",
                    data: {
                        residence: $("#property").val()
                    },

                    success: function(data) {
                        $("#room_type").empty().append(
                            '<option value="" selected disabled>Select a Room Type</option>')
                        data.forEach(function(index) {
                            let roomTypeSelected = '<?php echo Session::get('room_type'); ?>';
                            let roomPropertySelected = '<?php echo Session::get('property'); ?>';
                            let selected = "";

                            if ($("#property").val() == roomPropertySelected && roomTypeSelected ==
                                index.name) {
                                selected = "selected"
                            }
                            $("#room_type").append('<option value="' + index.id + '" ' + selected +
                                '>' + index.name + '</option>')
                        })
                    },
                });
            }
        };

        $("#searchButton").on('click', function() {

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
                    year: $("#studentYear").val(),
                },
                cache: false,
                contentType: false,
                processData: false,

                success: function(data) {
                    $("#downloadBtn").click();
                },
            });

        })

        function downloadStudentsReport() {
            $.ajax({
                type: "get",
                url: "/download-nsfas-report",

                success: function(data) {
                    $("#downloadBtnNsfas").click();
                },
            });
        }

        $("#downloadStudents").on('click', downloadStudentsReport);

        function downloadRoomsReport() {
            $("#downloadRooms").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
            $.ajax({
                type: "post",
                url: "/download-beds",
                data: {
                    property: $("#propdd").val(),
                    year: $("#bmYear").val()
                },
                success: function(data) {
                    $("#downloadRooms").empty().append('Download Beds')
                    alert(data.message || 'A link will be emailed to you once the beds report has been generated.');
                },
                error: function(data) {
                    $("#downloadRooms").empty().append('Download Beds')
                    alert('Something went wrong!');
                },
            });
        }

        $("#downloadRooms").on('click', downloadRoomsReport);

        function downloadMdaReport() {
            $("#downloadMda").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
            $.ajax({
                type: "get",
                url: "/download-mda",
                success: function(data) {
                    $("#downloadMdaDoc").click();
                    $("#downloadMda").empty().append('Download MDA')
                },
            });
        }

        $("#downloadMda").on('click', downloadMdaReport);

        function downloadAllAppsReport() {
            $("#downloadAllApps").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
            $.ajax({
                type: "post",
                url: "/download-apps",
                data: {
                    year: $("#studYear").val()
                },
                success: function(data) {
                    $("#downloadAllApps").empty().append('Download Applications')
                    alert('A link will be emailed to you once the applications file has been generated.');
                },
                error: function(data) {
                    $("#downloadAllApps").empty().append('Download Applications');
                    alert('Something went wrong!');
                }
            });
        }

        $("#downloadAllApps").on('click', downloadAllAppsReport);

        function downloadAllAppDocsReport() {
            $("#downloadAllAppDocs").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
            $.ajax({
                type: "post",
                url: "/download-app-docs",
                data: {
                    year: $("#studYear2").val(),
                    prop: $("#propdd2").val(),
                    lease_filter: $('#lease_filter').val()
                },
                success: function(data) {
                    $("#downloadAllAppDocs").empty().append('Download Documents')

                    alert('A link will be emailed to you once the documents have been generated.');
                },
                error: function(data) {
                    $("#downloadAllAppDocs").empty().append('Download Documents');
                    alert('Something went wrong!');
                }
            });
        }

        $("#downloadAllAppDocs").on('click', downloadAllAppDocsReport);

        function downloadSelectedDocsReport() {
            $("#downloadSelectedDocs").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
            $.ajax({
                type: "post",
                url: "/download-selected-docs",
                data: {
                    year: $("#studYear3").val(),
                    prop: $("#prop3").val(),
                    institution: $("#institution3").val(),
                    student_type: $("#student_type3").val(),
                    lease_filter: $('#lease_filter3').val()
                },
                success: function(data) {
                    $("#downloadSelectedDocs").empty().append('Download Documents')

                    alert('A link will be emailed to you once the documents have been generated.');
                },
                error: function(data) {
                    $("#downloadSelectedDocs").empty().append('Download Documents');
                    alert('Something went wrong!');
                }
            });
        }

        $("#downloadSelectedDocs").on('click', downloadSelectedDocsReport);

        function downloadCompatabilityDocsReport() {
            $("#downloadCompatabilityDocs").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

            $.ajax({
                type: "post",
                url: "/download-compatability-docs",
                data: {
                    year: $("#studYear4").val(),
                    prop: $("#prop4").val()
                },
                success: function(data) {
                    $("#downloadCompatabilityDocs").empty().append('Download Documents')

                    alert('A link will be emailed to you once the documents have been generated.');
                },
                error: function(data) {
                    $("#downloadCompatabilityDocs").empty().append('Download Documents');
                    alert('Something went wrong!');
                }
            });
        }

        $("#downloadCompatabilityDocs").on('click', downloadCompatabilityDocsReport);

        function yearToRange(year) {
            if (year === "All" || year === "" || year === null) {
                return null;
            }
            return {
                start: year + "-01-01",
                end: year + "-12-31"
            };
        }

        function inRange(dateValue, range) {
            if (!dateValue) {
                return true;
            }
            return dateValue >= range.start && dateValue <= range.end;
        }

        function filterPropertiesByYear(yearValue, selectId) {
            var range = yearToRange(yearValue);
            var $select = $("#" + selectId);
            var $options = $select.find("option");

            if (!range) {
                $options.removeClass("hidden");
                return;
            }

            $options.each(function() {
                var activeDate = $(this).data("active-date");
                var inactiveDate = $(this).data("inactive-date");

                var activeOk = inRange(activeDate, { start: "0000-01-01", end: range.end });
                var inactiveOk = !inactiveDate || inactiveDate >= range.start;

                if (activeOk && inactiveOk) {
                    $(this).removeClass("hidden");
                } else {
                    $(this).addClass("hidden");
                }
            });

            if ($select.find("option:selected").hasClass("hidden")) {
                $select.val($select.find("option:not(.hidden)").first().val());
            }
        }

        $("#studYear2").on("change", function() {
            filterPropertiesByYear($(this).val(), "propdd2");
        });

        $("#studYear3").on("change", function() {
            filterPropertiesByYear($(this).val(), "prop3");
        });

        filterPropertiesByYear($("#studYear2").val(), "propdd2");
        filterPropertiesByYear($("#studYear3").val(), "prop3");

    </script>


@endsection
