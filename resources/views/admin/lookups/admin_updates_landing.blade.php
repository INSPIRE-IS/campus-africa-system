@extends('layouts.admin')

@section('content')

<div class="container">    
    @if(Auth::user()->id == 1)
        <h1>New</h1>
        <div>
            <button style="float: none;" type="button" id="updateDataNewRobots" class="detailsChange" >New Robots - Data Update</button>
        </div>

        
        <h1>Legacy</h1>
        <div>
            <button style="float: none;" type="button" id="updateBookingFee" class="detailsChange" >Update All Booking Fees</button>
            <button style="float: none;" type="button" id="updateProcessingStatus" class="detailsChange" >Update Student Processing Status</button>
            <button style="float: none;" type="button" id="updateStudentsStatus" class="detailsChange" >Update Student Overall Status</button>
        </div>
    @endif
</div>

<script>
    $("#updateBookingFee").on('click', function(){
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#updateBookingFee").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                $.ajax({
                    type: "get",
                    url: "/update/booking-fees",

                    success: function (data) {
                        $("#updateBookingFee").empty().append('Update All Booking Fees');

                        Swal.fire(
                            'Success',
                            'All booking fees updated',
                            'success'
                        )
                    },
                });
            }
        })
    });

    $("#updateProcessingStatus").on('click', function(){
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#updateProcessingStatus").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                $.ajax({
                    type: "get",
                    url: "/update/processing-status",

                    success: function (data) {
                        $("#updateProcessingStatus").empty().append('Update Student processing Status');

                        Swal.fire(
                            'Success',
                            'All Student Processing Status updated',
                            'success'
                        )
                    },
                });
            }
        })
    });

    $("#updateStudentsStatus").on('click', function(){
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#updateStudentsStatus").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                $.ajax({
                    type: "get",
                    url: "/all_student_update_status",

                    success: function (data) {
                        $("#updateStudentsStatus").empty().append('Update Students Overall Status');

                        Swal.fire(
                            'Success',
                            'All Student Overall Status updated',
                            'success'
                        )
                    },
                });
            }
        })
    });
    
    $("#updateDataNewRobots").on('click', function(){
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#updateDataNewRobots").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                $.ajax({
                    type: "get",
                    url: "/admin-updates/fix-data",

                    success: function (data) {
                        $("#updateDataNewRobots").empty().append('New Robots - Data Update');

                        Swal.fire(
                            'Success',
                            'Data update complete',
                            'success'
                        )
                    },
                });
            }
        })
    });
</script>

@endsection