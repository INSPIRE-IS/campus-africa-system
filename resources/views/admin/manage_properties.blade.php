@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <br/>
    <button style="float: none;" type="button" id="exportAllBeds" class="detailsChange" >Export All beds</button>
    <div class="hidden"><a href="" download id="downloadLink"><button type="button" id="downloadBtn">Download</button></a></div>
    <button style="float: none;" type="button" id="exportBeds" class="detailsChange" >Export Allocated Bed Codes & waitlist</button>
    <div class="hidden"><a href="" download id="downloadLinkBed"><button type="button" id="downloadBtnBeds">Download</button></a></div>
    <a href="/room-types"><button type="button" class="detailsChange" >Room Types</button></a>
    <a href="/properties-info"><button type="button" class="detailsChange" >Residence Info</button></a>
    <a href="/property/edit/0"><button type="button" class="detailsChange" >Add New</button></a>
    <br/>

    <div class="appSearchForm" style="margin-top: 22px;">
        <div class="searchField">
            <label>Property</label><br/>
            <select name="property" id="property">
                <option value="All" selected>All Residences</option>
                @foreach($propertyList as $list)
                    <option value="{{$list->id}}">{{$list->residence}}</option>
                @endforeach
            </select>
        </div>

        <div class="searchField">
            <label for="year">Academic Year</label>
            <select name="year" id="year">
                <option value="All">All</option>
                <option value="current">{{$this_year}}</option>
                <option value="next">{{$this_year + 1}}</option>
            </select>
        </div>
        
        <div class="searchTab">
            <br/>
            <button type="button" id="clear_filters">Clear Filters</button>
        </div>

        <!-- <div class="searchTab" >
            <button type="button" id="searchButton_prop">Search</button><br/>
            <input type="hidden" id="firstLoad_prop" value="1"/>
        </div> -->
    </div>

    <div style="clear: both"></div>

    <div style="margin-top: 30px"></div>

    <table id="properties_table" class="display">
        <thead>
            <tr>
                <th>Residence</th>
                <th>Floor</th>
                <th>Unit</th>
                <th>Room</th>
                <th>Bed</th>
                <th>Bed Code</th>
                <th>Inventory</th>
                <th>Room Type</th>
                <th>2022 Rate</th>                
                <th style="width: 95px">Year</th>
                <th>Available</th>
            </tr> 
        </thead>
    </table>
    <br/>
</div>



<script>
    jQuery(document).ready( function($) {
        var datatable = $("#properties_table").DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": "/manage-properties-filter-v2",
            "columns": [
                { "data": "residence" },
                { "data": "floor" },
                { "data": "unit" },
                { "data": "room" },
                { "data": "bed" },
                { "data": "bed_code" },
                {
                    "data": "inventory",
                    "render": function( data, type, row) {
                        let inventory = "&#128994; &nbsp; Yes";

                        if (row["inventory"] == "0" || row["inventory"] == null) {
                            inventory = "&#128308; &nbsp; No";
                        }

                        return inventory;
                    }
                },
                { "data": "room_type" },
                { "data": "room_rate" },
                { 
                    "data": "year",
                    "render": function( data, type, row ) {
                        let year = "";

                        if (row['year_end'] == 0) {
                            year = row['year'] + " to ~";
                        } else if (row['year'] == 0) {
                            year = "~ to " + row['year_end'];
                        } else {
                            year = row['year'] + " to " + row['year_end'];
                        }

                        return year
                    }
                },
                { 
                    "data": "room_available",
                    "render": function( data, type, row ) {
                        let available = "&#128994; &nbsp; Yes";

                        if (row['room_available'] == "1" && row["inventory"] != -1) {
                            available = "&#128308; &nbsp; No";
                        }

                        if ("{{$this_year}}" < row['year']) {
                            available = "⚪ N/A";
                        }

                        return available;
                    }
                },
            ],
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "stateSave": true
        });

        $("#properties_table").on("click", "tr", function(event){
            window.location = "/property/edit/" + datatable.row(this).data()['id'];
	    });

        $('#property').change( function() {
	    	datatable.column(0).search($(this).val()).draw();
	    });
        
        $('#year').change( function() {
	    	datatable.column(9).search($(this).val()).draw();
	    });

        $("#searchButton_prop").on("click", function () {
            $("#searchButton_prop").empty().append("Searching...");

            $.ajax({
                type: "post",
                url: "/manage-properties-filter",
                data: {
                    property: $("#property").val(),
                    year: $("#year").val(),
                },

                success: function (data) {
                    $("#firstLoad_prop").val("0");
                    datatable.clear().draw();

                    results = JSON.parse(data)[0];

                    sortDirection = JSON.parse(data)[1];
                    sortId = JSON.parse(data)[2];
                    page = JSON.parse(data)[3];

                    var i = 0;

                    results.forEach(function (arrayItem) {
                        if (page == null || page == "NaN" || page == "") {
                            page = 0;
                        } else {
                            page = parseFloat(page);
                        }

                        if (sortId == "" || sortId == null || sortDirection == "" || sortDirection == null) {
                            var order = [];
                        } else {
                            var order = [sortId, sortDirection];
                        }                            
                            
                        let year = "";

                        if (arrayItem['year_end'] == 0) {
                            year = arrayItem['year'] + " to ~";
                        } else if (arrayItem['year'] == 0) {
                            year = "~ to " + arrayItem['year_end'];
                        } else {
                            year = arrayItem['year'] + " to " + arrayItem['year_end'];
                        }

                        let available = "&#128994; &nbsp; Yes";

                        if (arrayItem['room_available'] == "1" && arrayItem["inventory"] != -1) {
                            available = "&#128308; &nbsp; No";
                        }

                        let inventory = "Yes";

                        if (arrayItem["inventory"] == "0" || arrayItem["inventory"] == null) {
                            inventory = "No";
                        }

                        var trDOM = datatable.row
                            .add([
                                arrayItem["residence"],
                                arrayItem["floor"],
                                arrayItem["unit"],
                                arrayItem["room"],
                                arrayItem["bed"],
                                arrayItem["bed_code"],
                                inventory,
                                arrayItem["room_type"],
                                arrayItem["room_rate"],
                                year,
                                available,
                                '<a href="/property/edit/'+arrayItem['id']+'"><button type="button" style="margin:5px;" id="tableBtn" onClick="openModal('+arrayItem['id']+')" class="detailsChange">Edit</button></a>'
                            ])
                            .page(page)
                            .order(order)
                            .draw(false)
                            .node();

                        i++;
                    });

                    $("#searchButton_prop").empty().append("Search");
                },
            });
        });                

        console.log("<?php echo Session::get('manage_property');?>")
        $("#property").val("<?php echo Session::get('manage_property');?>").trigger('change');
        $("#year").val("<?php if (Session::get('manage_year')) { echo Session::get('manage_year'); } else { echo 'current'; } ?>").trigger('change');

        // $("#searchButton_prop").click();
    });

    $("#clear_filters").on('click', function(){
        $("#property").val("All").trigger('change');
        $("#year").val("current").trigger('change');
    })

    $("#exportAllBeds").on('click', function(){
        $("#exportAllBeds").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
        
        $.ajax({
            type: "post",
            url: "/download-beds",
            data: {
                property: $("#property").val(),
                year: $("#year").val(),
            },

            success: function (data) {

                $("#downloadLink").attr("href", "/"+data)
                $("#exportAllBeds").empty().append('Export All beds')
                $("#downloadBtn").click();
            },
        });

    })

    $("#exportBeds").on('click', function(){
        $("#exportBeds").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
        
        $.ajax({
            type: "post",
            url: "/download-bed-codes",
            data: {
                property: $("#property").val(),
                year: $("#year").val(),
            },

            success: function (data) {

                $("#downloadLinkBed").attr("href", "/"+data)
                $("#exportBeds").empty().append('Export Allocated Bed Codes & waitlist')
                $("#downloadBtnBeds").click();
            },
        });
    })
</script>


@endsection