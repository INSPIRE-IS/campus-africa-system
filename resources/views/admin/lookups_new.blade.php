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
            </select>
        </div>

        <div class="form-field" id="normalVlaue">
            <label>Value</label>
            <input type="text" name="value" id="value"  >
        </div>

        <div class="form-field hidden" id="waiverValue">
            <label>Value</label>
            <select name="booking_waiver" id="booking_waiver">
                <option value="0" disabled selected>- Select One -</option>
                <option value="1">Yes</option>
                <option value="2">No</option>
            </select>
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
            // $("#waiverDateFrom").removeClass('hidden');
            // $("#waiverDateTo").removeClass('hidden');
        }else{
            $("#normalVlaue").removeClass('hidden');
            $("#waiverValue").addClass('hidden');
            // $("#waiverDateFrom").addClass('hidden');
            // $("#waiverDateTo").addClass('hidden');
        }
    })
</script>

@endsection