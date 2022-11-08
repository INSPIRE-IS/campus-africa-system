@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/new-lookup" method="post" autocomplete="off">
    @csrf
        <br/>        <br/>

        <div class="form-field">
            <label>Title</label>
            <select name="group_id" id='group_id'>
                <option value="0" disabled selected>Select one</option>
                <option value="1">Institution of Study</option>
                <!-- <option value="2">Insitution Campus</option> -->
                <option value="3" >Faculty</option>
                <option value="4" >Year</option>
                <option value="5" >Nationality</option>
                <option value="6" >Year of Study</option>
                <option value="7" >Waive Booking Fee</option>
                <option value="8" >Lease Admin Fee</option>
                <option value="9" >Online Applications</option>
                <option value="10" >Email - Restrict to Test Domain(s)</option>
                <option value="11" >Email - BCC Email Addresses</option>
                <option value="12" >Email - Reminder Booking Fee</option>
                <option value="13" >Email - Reminder Outstanding Documents</option>
            </select>
        </div>

        <div class="form-field" id="normalVlaue">
            <label>Value</label>
            <input type="text" name="value" id="value"  >
            <small id="mailWording" class="hidden">
            <br/>- When this is set, emails are limited to the specified domains.<br/>
                - These should be domains only<br/>
                - When this is not set, no restrictions on email sending<br/>
                - Please seperate by semicolon</small>
            <small id="mailCCWording" class="hidden">Specify individual email addresses to BCC, Please seperate email addresses by semicolon</small>
        </div>

        <div class="form-field hidden" id="propertyListNsfas" style="height:<?php echo 48*count($allProperties);?>px !important;">
            <label>Student Types</label>
            <ul style="list-style-type:none; width:100%">
                @foreach($allProperties as $prop)
                <li style="display:inline-flex"><label for="{{$prop->id}}_prop" style="width:300px;">&nbsp; &nbsp;{{$prop->residence}}</label>

                    <ul style="list-style-type:none; width:100%">
                        <li style="display:inline-flex">
                            <input type="checkbox" id="{{$prop->id}}_nsfas" name="{{$prop->id}}_nsfas" style="width:27px !important" value='1'><label for="{{$prop->id}}_nsfas" style="width:100px;">&nbsp; &nbsp;NSFAS</label>
                        </li>
                        <li style="display:inline-flex">
                            <input type="checkbox" id="{{$prop->id}}_cash" name="{{$prop->id}}_cash" style="width:27px !important" value='1'><label for="{{$prop->id}}_cash" style="width:100px;">&nbsp; &nbsp;Cash</label>
                        </li>
                        <li style="display:inline-flex">
                            <input type="checkbox" id="{{$prop->id}}_bursary" name="{{$prop->id}}_bursary" style="width:27px !important" value='1'><label for="{{$prop->id}}_bursary" style="width:100px;">&nbsp; &nbsp;Bursary</label>
                        </li>
                        
                    </ul>

                    </li>
                
                <br/>
                @endforeach
            </ul>
        </div>

        <div class="form-field hidden" id="waiverValue">
            <label>Value</label>
            <select name="booking_waiver" id="booking_waiver">
                <option value="0" disabled selected>- Select One -</option>
                <option value="1">Yes</option>
                <option value="2">No</option>
            </select>
        </div>

        <div class="form-field hidden" id="onlineAppValue">
            <label>Value</label>
            <select name="onlineApp_value" id="onlineApp_value">
                <option value="0" disabled selected>- Select One -</option>
                <option value="1">Online</option>
                <option value="2">Offline</option>
            </select>
        </div>

        <div class="form-field hidden" id="nsfas_value">
            <label>NSFAS Rate</label>
            <input type="number" id="nsfas_amount" name="nsfas_amount" >
        </div>

        <!-- <div class="form-field hidden" id="waiverDateFrom">
            <label>Waiver Date From</label>
            <input type="date" name="date_from" id="date_from">
        </div>

        <div class="form-field hidden" id="waiverDateTo">
            <label>Waiver Date To</label>
            <input type="date" name="date_to" id="date_to">
        </div> -->

        <input type="submit" value="Save" class="profileSubmit" style="opacity:1"/>
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
            $("#propertyListNsfas").addClass('hidden');
            // $("#waiverDateFrom").removeClass('hidden');
            // $("#waiverDateTo").removeClass('hidden');
        }else if($("#group_id").val() == "9" || $("#group_id").val() == "12" || $("#group_id").val() == "13"){
            $("#onlineAppValue").removeClass('hidden');
            $("#normalVlaue").addClass('hidden');
            $("#waiverValue").addClass('hidden');
            $("#mailWording").addClass('hidden');
            $("#nsfas_value").addClass('hidden');
            $("#mailCCWording").addClass('hidden');
            $("#propertyListNsfas").addClass('hidden');
        }else if($("#group_id").val() == "10"){
            $("#mailWording").removeClass('hidden');
            $("#onlineAppValue").addClass('hidden');
            $("#normalVlaue").removeClass('hidden');
            $("#waiverValue").addClass('hidden');
            $("#propertyListNsfas").addClass('hidden');

            $("#mailCCWording").addClass('hidden');
        }else if($("#group_id").val() == "11"){
            $("#mailWording").addClass('hidden');
            $("#onlineAppValue").addClass('hidden');
            $("#normalVlaue").removeClass('hidden');
            $("#waiverValue").addClass('hidden');
            $("#propertyListNsfas").addClass('hidden');

            $("#mailCCWording").removeClass('hidden');
            $("#nsfas_value").addClass('hidden');
        }else if($("#group_id").val() == "1" ){
            $("#nsfas_value").removeClass('hidden');
            $("#normalVlaue").removeClass('hidden');
            $("#propertyListNsfas").removeClass('hidden');
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
            $("#propertyListNsfas").addClass('hidden');
            // $("#waiverDateFrom").addClass('hidden');
            // $("#waiverDateTo").addClass('hidden');
        }
    })
</script>

@endsection