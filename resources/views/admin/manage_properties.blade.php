@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <br/>
    <a href="/room-types"><button type="button" class="detailsChange" >Room Types</button></a>
    <a href="/properties-info"><button type="button" class="detailsChange" >Residence Info</button></a>
    <a href="/property/edit/0"><button type="button" class="detailsChange" >Add New</button></a>
    <br/>

    <div class="appSearchForm" style="margin-top: 22px;">
        <div class="searchField">
            <label>Property</label><br/>
            <select name="property" id="property">
                <option value="All" selected>All Residences</option>
                @if(Auth::user()->role_braamlofts == 1)
                    <option value="BRAAMLOFTS">Braamlofts</option>
                @endif
                @if(Auth::user()->role_80_jorissen == 1)
                    <option value="80 JORISSEN">80 Jorissen</option>
                @endif
                @if(Auth::user()->role_49_jorissen == 1)
                    <option value="49 JORISSEN">49 Jorissen</option>
                @endif
                @if(Auth::user()->role_dunvista_mansions == 1)
                    <option value="DUNVISTA MANSIONS">Dunvista Mansions</option>
                @endif
                @if(Auth::user()->role_wynton_joy == 1)
                    <option value="WYNTON JOY">Wynton Joy</option>
                @endif
                @if(Auth::user()->role_126_siemert == 1)
                    <option value="126 SIEMERT">126 Siemert</option>
                @endif
                @if(Auth::user()->role_ymca == 1)
                    <option value="YMCA">YMCA</option>
                @endif
            </select>
        </div>

        <div class="searchField">
            <label for="year">Year</label>
            <select name="year" id="year">
                <option value="All">All</option>
                <option value="current">Current Year</option>
                <option value="next">Next Year</option>
            </select>
        </div>
        
        <div class="searchField"></div>

        <div class="searchTab" >
            <button type="button" id="searchButton_prop">Search</button><br/>
            <input type="hidden" id="firstLoad_prop" value="1"/>
        </div>
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
                <th></th>
            </tr> 
        </thead>

        <tbody></tbody>
    </table>
    <br/>
</div>



<script>
    jQuery(document).ready( function($) {
        var datatable = $("#properties_table").DataTable();

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
                        var m = new Date(arrayItem["created_at"]);

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

                        var dateString =
                            m.getUTCFullYear() +
                            "-" +
                            ("0" + (m.getUTCMonth() + 1)).slice(-2) +
                            "-" +
                            ("0" + m.getUTCDate()).slice(-2) +
                            "<br />" +
                            ("0" + m.getUTCHours()).slice(-2) +
                            ":" +
                            ("0" + m.getUTCMinutes()).slice(-2) +
                            ":" +
                            ("0" + m.getUTCSeconds()).slice(-2);     
                            
                            
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

                        if (arrayItem["inventory"] == "0") {
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

        $("#property").val("<?php echo Session::get('property');?>").trigger('change');
        $("#year").val("<?php echo Session::get('year');?>").trigger('change');

        $("#searchButton_prop").click();
    });
</script>


@endsection