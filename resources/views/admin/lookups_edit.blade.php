@extends('layouts.admin')

@section('content')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container form-card">
<form action="/save-lookup" method="post" autocomplete="off" id="lookupForm">
    @csrf
        <br/>        <br/>

        <div class="form-field">
            <label>Title</label>
            <select name="group_id" id='group_id'>
                <option value="0" disabled selected>Select one</option>
                <option value="1" <?php if($lookup->group_id == "1"){echo "selected";}?>>Institution of Study</option>
                <!-- <option value="2" <?php if($lookup->group_id == "2"){echo "selected";}?>>Insitution Campus</option> -->
                <option value="3" <?php if($lookup->group_id == "3"){echo "selected";}?>>Faculty</option>
                <option value="4" <?php if($lookup->group_id == "4"){echo "selected";}?>>Year</option>
                <option value="5" <?php if($lookup->group_id == "5"){echo "selected";}?>>Nationality</option>
                <option value="6" <?php if($lookup->group_id == "6"){echo "selected";}?>>Year of Study</option>
                <option value="7" <?php if($lookup->group_id == "7"){echo "selected";}?>>Waive Booking Fee</option>
                <option value="8" <?php if($lookup->group_id == "8"){echo "selected";}?>>Lease Admin Fee</option>
                <option value="9" <?php if($lookup->group_id == "9"){echo "selected";}?>>Online Applications</option>
                <option value="10" <?php if($lookup->group_id == "10"){echo "selected";}?>>Email - Restrict to Test Domain(s)</option>
                <option value="11" <?php if($lookup->group_id == "11"){echo "selected";}?>>Email - BCC Email Addresses</option>
            </select>
            <input type="hidden" name="lookupId" id="lookupId" value="{{$lookup->id}}"/>
        </div>

        <div class="form-field <?php if($lookup->group_id == "7" || $lookup->group_id == "9"){echo "hidden";} ?>" <?php if($lookup->group_id == "10"){echo 'style="height:170px !important;"';}?>>
            <label>Value</label>
            <input type="text" name="value" id="value" value="<?php echo $lookup->lookup_name;?>">
            <small id="mailWording" class="<?php if($lookup->group_id != "10"){echo "hidden";} ?>">
            <br/>- When this is set, emails are limited to the specified domains.<br/> 
                - These should be domains only<br/>
                - When this is not set, no restrictions on email sending<br/>
                - Please seperate by semicolon</small>
            <small id="mailCCWording" class="<?php if($lookup->group_id != "11"){echo "hidden";} ?>">Specify individual email addresses to BCC, Please seperate email addresses by semicolon</small>
        </div>

        <div class="form-field" style="height:400px !important;">
            <label>Properties</label>
            <ul style="list-style-type:none; width:100%">
                @foreach($allProperties as $prop)
                <li style="display:inline-flex"><input type="checkbox" id="{{$prop->id}}_prop" name="{{$prop->id}}_prop" style="width:27px !important" <?php if(in_array($prop->id, $instProps)){echo "checked";}?> value='1'><label for="{{$prop->id}}_prop">&nbsp; &nbsp;{{$prop->residence}}</label></li><br/>
                @endforeach
            </ul>
        </div>

        @if($lookup->group_id == "4")
            <div class="form-field">
                <div class="form-field">
                    <label>Lease From Date (Month)</label>
                    <select name="month_from" id='month_from'>
                        <option value="0" disabled selected>Select one</option>
                        <option value="1" <?php if($lookup->month_from == '1'){ echo "selected";} ?>>January</option>
                        <option value="2" <?php if($lookup->month_from == '2'){ echo "selected";} ?>>February</option>
                        <option value="3" <?php if($lookup->month_from == '3'){ echo "selected";} ?>>March</option>
                        <option value="4" <?php if($lookup->month_from == '4'){ echo "selected";} ?>>April</option>
                        <option value="5" <?php if($lookup->month_from == '5'){ echo "selected";} ?>>May</option>
                        <option value="6" <?php if($lookup->month_from == '6'){ echo "selected";} ?>>June</option>
                        <option value="7" <?php if($lookup->month_from == '7'){ echo "selected";} ?>>July</option>
                        <option value="8" <?php if($lookup->month_from == '8'){ echo "selected";} ?>>August</option>
                        <option value="9" <?php if($lookup->month_from == '9'){ echo "selected";} ?>>September</option>
                        <option value="10" <?php if($lookup->month_from == '10'){ echo "selected";} ?>>October</option>
                        <option value="11" <?php if($lookup->month_from == '11'){ echo "selected";} ?>>November</option>
                        <option value="12" <?php if($lookup->month_from == '12'){ echo "selected";} ?>>December</option>
                    </select>
                </div>
                <div class="form-field">
                    <label>Lease From Date (Day)</label>
                    <select name="day_from" id='day_from'>
                        <option value="0" disabled selected>Select one</option>
                        <option value="1" <?php if($lookup->day_from == '1'){ echo "selected";} ?>>01</option>
                        <option value="2" <?php if($lookup->day_from == '2'){ echo "selected";} ?>>02</option>
                        <option value="3" <?php if($lookup->day_from == '3'){ echo "selected";} ?>>03</option>
                        <option value="4" <?php if($lookup->day_from == '4'){ echo "selected";} ?>>04</option>
                        <option value="5" <?php if($lookup->day_from == '5'){ echo "selected";} ?>>05</option>
                        <option value="6" <?php if($lookup->day_from == '6'){ echo "selected";} ?>>06</option>
                        <option value="7" <?php if($lookup->day_from == '7'){ echo "selected";} ?>>07</option>
                        <option value="8" <?php if($lookup->day_from == '8'){ echo "selected";} ?>>08</option>
                        <option value="9" <?php if($lookup->day_from == '9'){ echo "selected";} ?>>09</option>
                        <option value="10" <?php if($lookup->day_from == '10'){ echo "selected";} ?>>10</option>
                        <option value="11" <?php if($lookup->day_from == '11'){ echo "selected";} ?>>11</option>
                        <option value="12" <?php if($lookup->day_from == '12'){ echo "selected";} ?>>12</option>
                        <option value="13" <?php if($lookup->day_from == '13'){ echo "selected";} ?>>13</option>
                        <option value="14" <?php if($lookup->day_from == '14'){ echo "selected";} ?>>14</option>
                        <option value="15" <?php if($lookup->day_from == '15'){ echo "selected";} ?>>15</option>
                        <option value="16" <?php if($lookup->day_from == '16'){ echo "selected";} ?>>16</option>
                        <option value="17" <?php if($lookup->day_from == '17'){ echo "selected";} ?>>17</option>
                        <option value="18" <?php if($lookup->day_from == '18'){ echo "selected";} ?>>18</option>
                        <option value="19" <?php if($lookup->day_from == '19'){ echo "selected";} ?>>19</option>
                        <option value="20" <?php if($lookup->day_from == '20'){ echo "selected";} ?>>20</option>
                        <option value="21" <?php if($lookup->day_from == '21'){ echo "selected";} ?>>21</option>
                        <option value="22" <?php if($lookup->day_from == '22'){ echo "selected";} ?>>22</option>
                        <option value="23" <?php if($lookup->day_from == '23'){ echo "selected";} ?>>23</option>
                        <option value="24" <?php if($lookup->day_from == '24'){ echo "selected";} ?>>24</option>
                        <option value="25" <?php if($lookup->day_from == '25'){ echo "selected";} ?>>25</option>
                        <option value="26" <?php if($lookup->day_from == '26'){ echo "selected";} ?>>26</option>
                        <option value="27" <?php if($lookup->day_from == '27'){ echo "selected";} ?>>27</option>
                        <option value="28" <?php if($lookup->day_from == '28'){ echo "selected";} ?>>28</option>
                        <option value="29" <?php if($lookup->day_from == '29'){ echo "selected";} ?>>29</option>
                        <option value="30" <?php if($lookup->day_from == '30'){ echo "selected";} ?>>30</option>
                        <option value="31" <?php if($lookup->day_from == '31'){ echo "selected";} ?>>31</option>
                    </select>
                </div>
            </div>
            <div class="form-field">
                <label>Lease From Time</label>
                <input type="time" id="lease_from_time" name="lease_from_time" value="{{$lookup->lease_from_time}}">
            </div>
            <div class="form-field">
                <div class="form-field">
                    <label>Lease To Date (Month)</label>
                    <select name="month_to" id='month_to'>
                        <option value="0" disabled selected>Select one</option>
                        <option value="1" <?php if($lookup->month_to == '1'){ echo "selected";} ?>>January</option>
                        <option value="2" <?php if($lookup->month_to == '2'){ echo "selected";} ?>>February</option>
                        <option value="3" <?php if($lookup->month_to == '3'){ echo "selected";} ?>>March</option>
                        <option value="4" <?php if($lookup->month_to == '4'){ echo "selected";} ?>>April</option>
                        <option value="5" <?php if($lookup->month_to == '5'){ echo "selected";} ?>>May</option>
                        <option value="6" <?php if($lookup->month_to == '6'){ echo "selected";} ?>>June</option>
                        <option value="7" <?php if($lookup->month_to == '7'){ echo "selected";} ?>>July</option>
                        <option value="8" <?php if($lookup->month_to == '8'){ echo "selected";} ?>>August</option>
                        <option value="9" <?php if($lookup->month_to == '9'){ echo "selected";} ?>>September</option>
                        <option value="10" <?php if($lookup->month_to == '10'){ echo "selected";} ?>>October</option>
                        <option value="11" <?php if($lookup->month_to == '11'){ echo "selected";} ?>>November</option>
                        <option value="12" <?php if($lookup->month_to == '12'){ echo "selected";} ?>>December</option>
                    </select>
                </div>
                <div class="form-field">
                    <label>Lease To Date (Day)</label>
                    <select name="day_to" id='day_to'>
                        <option value="0" disabled selected>Select one</option>
                        <option value="1" <?php if($lookup->day_to == '1'){ echo "selected";} ?>>01</option>
                        <option value="2" <?php if($lookup->day_to == '2'){ echo "selected";} ?>>02</option>
                        <option value="3" <?php if($lookup->day_to == '3'){ echo "selected";} ?>>03</option>
                        <option value="4" <?php if($lookup->day_to == '4'){ echo "selected";} ?>>04</option>
                        <option value="5" <?php if($lookup->day_to == '5'){ echo "selected";} ?>>05</option>
                        <option value="6" <?php if($lookup->day_to == '6'){ echo "selected";} ?>>06</option>
                        <option value="7" <?php if($lookup->day_to == '7'){ echo "selected";} ?>>07</option>
                        <option value="8" <?php if($lookup->day_to == '8'){ echo "selected";} ?>>08</option>
                        <option value="9" <?php if($lookup->day_to == '9'){ echo "selected";} ?>>09</option>
                        <option value="10" <?php if($lookup->day_to == '10'){ echo "selected";} ?>>10</option>
                        <option value="11" <?php if($lookup->day_to == '11'){ echo "selected";} ?>>11</option>
                        <option value="12" <?php if($lookup->day_to == '12'){ echo "selected";} ?>>12</option>
                        <option value="13" <?php if($lookup->day_to == '13'){ echo "selected";} ?>>13</option>
                        <option value="14" <?php if($lookup->day_to == '14'){ echo "selected";} ?>>14</option>
                        <option value="15" <?php if($lookup->day_to == '15'){ echo "selected";} ?>>15</option>
                        <option value="16" <?php if($lookup->day_to == '16'){ echo "selected";} ?>>16</option>
                        <option value="17" <?php if($lookup->day_to == '17'){ echo "selected";} ?>>17</option>
                        <option value="18" <?php if($lookup->day_to == '18'){ echo "selected";} ?>>18</option>
                        <option value="19" <?php if($lookup->day_to == '19'){ echo "selected";} ?>>19</option>
                        <option value="20" <?php if($lookup->day_to == '20'){ echo "selected";} ?>>20</option>
                        <option value="21" <?php if($lookup->day_to == '21'){ echo "selected";} ?>>21</option>
                        <option value="22" <?php if($lookup->day_to == '22'){ echo "selected";} ?>>22</option>
                        <option value="23" <?php if($lookup->day_to == '23'){ echo "selected";} ?>>23</option>
                        <option value="24" <?php if($lookup->day_to == '24'){ echo "selected";} ?>>24</option>
                        <option value="25" <?php if($lookup->day_to == '25'){ echo "selected";} ?>>25</option>
                        <option value="26" <?php if($lookup->day_to == '26'){ echo "selected";} ?>>26</option>
                        <option value="27" <?php if($lookup->day_to == '27'){ echo "selected";} ?>>27</option>
                        <option value="28" <?php if($lookup->day_to == '28'){ echo "selected";} ?>>28</option>
                        <option value="29" <?php if($lookup->day_to == '29'){ echo "selected";} ?>>29</option>
                        <option value="30" <?php if($lookup->day_to == '30'){ echo "selected";} ?>>30</option>
                        <option value="31" <?php if($lookup->day_to == '31'){ echo "selected";} ?>>31</option>
                    </select>
                </div>
            </div>
            <div class="form-field">
                <label>Lease To Time</label>
                <input type="time" id="lease_to_time" name="lease_to_time" value="{{$lookup->lease_to_time}}" >
            </div>
        @endif

        <div class="form-field <?php if($lookup->group_id !== "7"){echo "hidden";} ?>" id="waiverValue">
            <label>Value</label>
            <select name="booking_waiver" id="booking_waiver">
                <option value="0" disabled selected>- Select One -</option>
                <option value="1" <?php if($lookup->lookup_name == "1"){echo "selected";}?>>Yes</option>
                <option value="2" <?php if($lookup->lookup_name == "2"){echo "selected";}?>>No</option>
            </select>
        </div>

        <div class="form-field <?php if($lookup->group_id !== "9"){echo "hidden";} ?>" id="onlineAppValue">
            <label>Value</label>
            <select name="onlineApp_value" id="onlineApp_value">
                <option value="0" disabled selected>- Select One -</option>
                <option value="1" <?php if($lookup->lookup_name == "1"){echo "selected";}?>>Online</option>
                <option value="2" <?php if($lookup->lookup_name == "2"){echo "selected";}?>>Offline</option>
            </select>
        </div>

        <div class="form-field <?php if($lookup->group_id !== "1"){echo "hidden";} ?>" id="nsfas_value">
            <label>NSFAS Rate</label>
            <input type="number" id="nsfas_amount" name="nsfas_amount" value="{{$lookup->nsfas_amount}}" >
        </div>

        <!-- <div class="form-field <?php if($lookup->group_id !== "7"){echo "hidden";} ?>" id="waiverDateFrom">
            <label>Waiver Date From</label>
            <input type="date" name="date_from" id="date_from" value="<?php echo $lookup->waiver_date_from;?>">
        </div>

        <div class="form-field <?php if($lookup->group_id !== "7"){echo "hidden";} ?>" id="waiverDateTo">
            <label>Waiver Date To</label>
            <input type="date" name="date_to" id="date_to" value="<?php echo $lookup->waiver_date_to;?>">
        </div> -->

        <!-- <input type="submit" value="Update" class="profileSubmit" style="opacity:1"/> -->
        <button type="button" class="profileSubmitBtn" id="submitLookup" style="opacity:1">Update</button>
    </form>
</div>

<script>
    $("#group_id").on('change', function(){
        if($("#group_id").val() == "7"){
            $("#normalVlaue").addClass('hidden');
            $("#waiverValue").removeClass('hidden');
            $("#onlineAppValue").addClass('hidden');
            $("#mailWording").addClass('hidden');
            $("#mailCCWording").addClass('hidden');
            $("#nsfas_value").addClass('hidden');
            // $("#waiverDateFrom").removeClass('hidden');
            // $("#waiverDateTo").removeClass('hidden');
        }else if($("#group_id").val() == "9"){
            $("#onlineAppValue").removeClass('hidden');
            $("#normalVlaue").addClass('hidden');
            $("#waiverValue").addClass('hidden');
            $("#mailWording").addClass('hidden');
            $("#nsfas_value").addClass('hidden');
            $("#mailCCWording").addClass('hidden');
        }else if($("#group_id").val() == "10"){
            $("#mailWording").removeClass('hidden');
            $("#onlineAppValue").addClass('hidden');
            $("#normalVlaue").removeClass('hidden');
            $("#waiverValue").addClass('hidden');

            $("#mailCCWording").addClass('hidden');
        }else if($("#group_id").val() == "11"){
            $("#mailWording").addClass('hidden');
            $("#onlineAppValue").addClass('hidden');
            $("#normalVlaue").removeClass('hidden');
            $("#waiverValue").addClass('hidden');

            $("#mailCCWording").removeClass('hidden');
            $("#nsfas_value").addClass('hidden');
        }else if($("#group_id").val() == "1" ){
            $("#nsfas_value").removeClass('hidden');
            $("#normalVlaue").removeClass('hidden');
            $("#waiverValue").addClass('hidden');
            $("#mailWording").addClass('hidden');
            $("#onlineAppValue").addClass('hidden');
        }else{
            $("#normalVlaue").removeClass('hidden');
            $("#waiverValue").addClass('hidden');
            $("#onlineAppValue").addClass('hidden');
            $("#mailWording").addClass('hidden');
            $("#mailCCWording").addClass('hidden');
            $("#nsfas_value").addClass('hidden');
            // $("#waiverDateFrom").addClass('hidden');
            // $("#waiverDateTo").addClass('hidden');
        }
    })

    $("#submitLookup").on('click', function(){

        if($("#group_id").val() == "1"){
            if("{{$lookup->nsfas_amount}}" !== $("#nsfas_amount").val()){
                Swal.fire({
                    title: 'Update NSFAS Rate?',
                    text: "This will update the NSFAS Rate for all applications from this institution",
                    icon: 'warning',
                    showDenyButton: true,
                    denyButtonText: 'Cancel',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Continue',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#submitLookup").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                        $.ajax({
                            type: "post",
                            url: "/update/nsfas-amount",
                            data: {
                                lookup_id: "{{$lookup->id}}",
                                nsfas_amount: $("#nsfas_amount").val(),
                            },

                            success: function (data) {
                                $('#lookupForm').submit();
                            },
                        });
                    } else if (result.isDenied) {
                        $("#nsfas_amount").val('').val("{{$lookup->nsfas_amount}}")
                        $('#lookupForm').submit();
                    }
                });
            }else{
                $('#lookupForm').submit();
            }
        }else if($("#group_id").val() == "4"){

            let yearInfo = <?php echo $year_lookup; ?>;
            console.log(yearInfo)

            if(yearInfo.lookup_name == $("#value").val() && (yearInfo.month_from !== $("#month_from").val() || yearInfo.day_from !== $("#day_from").val() || yearInfo.lease_from_time !== $("#lease_from_time").val() || yearInfo.month_to !== $("#month_to").val() || yearInfo.day_to !== $("#day_to").val() || yearInfo.lease_to_time !== $("#lease_to_time").val() )){
                Swal.fire({
                    title: 'Update All Lease Dates',
                    text: "Apply this lease period to all existing (unapproved) applications as well?",
                    icon: 'warning',
                    showDenyButton: true,
                    denyButtonText: 'No',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#submitLookup").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                        $.ajax({
                            type: "post",
                            url: "/update/lease-dates",
                            data: {
                                month_from: $("#month_from").val(),
                                day_from: $("#day_from").val(),
                                lease_from_time: $("#lease_from_time").val(),
                                month_to: $("#month_to").val(),
                                day_to: $("#day_to").val(),
                                lease_to_time: $("#lease_to_time").val(),
                                value: $("#value").val(),
                            },

                            success: function (data) {
                                $('#lookupForm').submit();
                            },
                        });
                    } else if (result.isDenied) {
                        $('#lookupForm').submit();
                    }
                });
            }else{
                $('#lookupForm').submit();
            }
            
        }else{
            $('#lookupForm').submit();
        }
    })

</script>

@endsection