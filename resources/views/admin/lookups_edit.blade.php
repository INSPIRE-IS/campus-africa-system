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
            </select>
            <input type="hidden" name="lookupId" id="lookupId" value="{{$lookup->id}}"/>
        </div>

        <div class="form-field <?php if($lookup->group_id == "7"){echo "hidden";} ?>">
            <label>Value</label>
            <input type="text" name="value" id="value" value="<?php echo $lookup->lookup_name;?>">
        </div>

        @if($lookup->group_id == "4")
            <div class="form-field">
                <label>Lease From Date</label>
                <input type="date" name="lease_from_date" id="lease_from_date" value="{{$lookup->lease_from_date}}">
            </div>
            <div class="form-field">
                <label>Lease From Time</label>
                <input type="time" id="lease_from_time" name="lease_from_time" value="{{$lookup->lease_from_time}}">
            </div>
            <div class="form-field">
                <label>Lease To Date</label>
                <input type="date" name="lease_to_date" id="lease_to_date" value="{{$lookup->lease_to_date}}">
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
            // $("#waiverDateFrom").removeClass('hidden');
            // $("#waiverDateTo").removeClass('hidden');
        }else{
            $("#normalVlaue").removeClass('hidden');
            $("#waiverValue").addClass('hidden');
            // $("#waiverDateFrom").addClass('hidden');
            // $("#waiverDateTo").addClass('hidden');
        }
    })

    $("#submitLookup").on('click', function(){
        if($("#group_id").val() == "4"){
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
                            lease_from_date: $("#lease_from_date").val(),
                            lease_from_time: $("#lease_from_time").val(),
                            lease_to_date: $("#lease_to_date").val(),
                            lease_to_time: $("#lease_to_time").val(),
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
    })

</script>

@endsection