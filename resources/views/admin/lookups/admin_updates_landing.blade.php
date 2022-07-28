@extends('layouts.admin')

@section('content')

<div class="container">    
    @if(Auth::user()->id == 1 || Auth::user()->email == "roger@whitehart.co.za")
        <h1>New</h1>
        <div>
            <button style="float: none;" type="button" id="updateDataNewRobots" class="detailsChange" >New Robots - Data Update</button>

            <button style="float: none;" type="button" id="updateProperties" class="detailsChange" >Properties Update</button>

            <button style="float: none;" type="button" id="updatePropIds" class="detailsChange" >Properties ID Update</button>

            <button style="float: none;" type="button" id="newBookingCode" class="detailsChange" >New Booking Code</button>

            <button style="float: none;" type="button" id="newCancelled" class="detailsChange" >Cancelled on bookings</button>
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

    $("#updateProperties").on('click', function(){
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
                $("#updateProperties").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                $.ajax({
                    type: "get",
                    url: "/admin-updates/update-properties",

                    success: function (data) {
                        $("#updateProperties").empty().append('Properties Update');

                        Swal.fire(
                            'Success',
                            'Data update complete',
                            'success'
                        )
                    },
                });
            }
        })
    })

    $("#updatePropIds").on('click', function(){
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
                $("#updatePropIds").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                $.ajax({
                    type: "get",
                    url: "/admin-updates/update-property-id",

                    success: function (data) {
                        $("#updatePropIds").empty().append('Properties ID Update');

                        Swal.fire(
                            'Success',
                            'Data update complete',
                            'success'
                        )
                    },
                });
            }
        })
    })

    $("#newCancelled").on('click', function(){
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
                $("#newCancelled").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                $.ajax({
                    type: "get",
                    url: "/admin-updates/update-cancelled",

                    success: function (data) {
                        $("#newCancelled").empty().append('Cancelled on bookings');

                        Swal.fire(
                            'Success',
                            'Data update complete',
                            'success'
                        )
                    },
                });
            }
        })
    })

    $("#newBookingCode").on('click', function(){
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
                $("#newBookingCode").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                $.ajax({
                    type: "get",
                    url: "/admin-updates/update-booking-code",

                    success: function (data) {
                        $("#newBookingCode").empty().append('New Booking Code');

                        Swal.fire(
                            'Success',
                            'Data update complete',
                            'success'
                        )
                    },
                });
            }
        })
    })
</script>

@endsection