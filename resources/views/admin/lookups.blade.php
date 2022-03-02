@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <br/>
    @if(Auth::user()->role_lookup == "1")
        <a href="/lookups/new"><button type="button" class="detailsChange" >Add New Lookup</button></a>
        <a href="/manage-properties"><button type="button" class="detailsChange" >Manage properties</button></a>
        <!-- <button type="button" id="updateLeaseDates" class="detailsChange" >Update Lease Dates</button> -->
        <!-- <button type="button" id="updateNsfasStudents" class="detailsChange" >Update NSFAS Students</button> -->
    @endif
    @if(Auth::user()->id == 1)
        <button type="button" id="updateBookingFee" class="detailsChange" >Update All Booking Fees</button>
        <button type="button" id="updateProcessingStatus" class="detailsChange" >Update Student Processing Status</button>
        <button type="button" id="updateStudentsStatus" class="detailsChange" >Update Student Overall Status</button>
    @endif
    <br/>
    <table id="lookups_table" class="display">
        <thead>
            <tr>
                <th>Lookup</th>
                <th>Value</th>
                <th>Updated At</th>
                <th>Updated By</th>
                <th></th>
            </tr> 
        </thead>
        <tbody>

        @foreach($lookups as $lookup)
            <tr>
                <td>@if($lookup->group_id == "1") Institution of Study @elseif($lookup->group_id == "2")Insitution Campus @elseif($lookup->group_id == "3") Faculty @elseif($lookup->group_id == "4") Year @elseif($lookup->group_id == "5") Nationality @elseif($lookup->group_id == "6") Year of study @elseif($lookup->group_id == "7") Waive Booking Fee @elseif($lookup->group_id == "8") Lease Admin Fee @endif</td>
                <td>@if($lookup->lookup_name == '1') Yes @elseif($lookup->lookup_name == "2") No @else {{$lookup->lookup_name}} @endif</td>
                <td>{{$lookup->updated_at}}</td>
                <td>@if($lookup->users && $lookup->users->userFirstName) {{$lookup->users->userFirstName}} {{$lookup->users->userLastname}} @endif</td>
                <td>
                    @if(Auth::user()->role_lookup == "1")
                        <button type="button" id="tableBtn" class="detailsChange" onClick="deleteLookup('{{$lookup->id}}')" style="margin:5px;">Delete</button>
                        <a href="/lookups/edit/{{$lookup->id}}"><button type="button" style="margin:5px;" id="tableBtn" class="detailsChange">Edit</button></a>
                    @endif
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
    <br/>
</div>



<script>

    $(document).ready( function () {
        $('#lookups_table').DataTable();
    } );

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function deleteLookup(lookupId){
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "/delete/lookup",
                    data: {
                        lookupId: lookupId, 
                    },

                    success: function (data) {
                        window.location.reload();
                    },
                });
            }
        })
    }

    // $("#updateLeaseDates").on('click', function(){
    //     Swal.fire({
    //         title: 'Are you sure?',
    //         text: "You won't be able to revert this!",
    //         icon: 'warning',
    //         showCancelButton: true,
    //         confirmButtonColor: '#3085d6',
    //         cancelButtonColor: '#d33',
    //         confirmButtonText: 'Yes, Update it!'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             $("#updateLeaseDates").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
    //             $.ajax({
    //                 type: "get",
    //                 url: "/update/lease-dates",

    //                 success: function (data) {
    //                     $("#updateLeaseDates").empty().append('Update Lease Dates');

    //                     Swal.fire(
    //                         'Success',
    //                         'All Lease dates updated',
    //                         'success'
    //                     )
    //                 },
    //             });
    //         }
    //     })
    // })

    $("#updateNsfasStudents").on('click', function(){
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
                $("#updateNsfasStudents").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                $.ajax({
                    type: "get",
                    url: "/update/nsfas",

                    success: function (data) {
                        $("#updateNsfasStudents").empty().append('Update NSFAS Students');

                        Swal.fire(
                            'Success',
                            'All students updated',
                            'success'
                        )
                    },
                });
            }
        })
    })

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
    })

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
    })

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
    })

</script>


@endsection