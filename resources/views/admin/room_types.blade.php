@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <br/>
    <a href="/properties-info"><button type="button" class="detailsChange" >Residence Info</button></a>
    <a href="/room-type/edit/0"><button type="button" class="detailsChange" >Add New</button></a>
    <br/>
    <table id="properties_table" class="display">
        <thead>
            <tr>
                <th>Residence</th>
                <th>Room Type</th>
                <th></th>
            </tr> 
        </thead>
        <tbody>

        @foreach($room_types as $rt)
            <tr>
                <td>{{$rt->property_id}}</td>
                <td>{{$rt->name}}</td>
                <td>
                    <a href="/room-type/edit/{{$rt->id}}">Edit</a>
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
    <br/>
</div>



<script>

    $(document).ready( function () {
        $('#properties_table').DataTable();
    } );

    // $.ajaxSetup({
    //     headers: {
    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //     }
    // });

    // function deleteProperty(propertyId){
    //     Swal.fire({
    //         title: 'Are you sure?',
    //         text: "You won't be able to revert this!",
    //         icon: 'warning',
    //         showCancelButton: true,
    //         confirmButtonColor: '#3085d6',
    //         cancelButtonColor: '#d33',
    //         confirmButtonText: 'Yes, delete it!'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             $.ajax({
    //                 type: "post",
    //                 url: "/delete/lookup",
    //                 data: {
    //                     propertyId: propertyId, 
    //                 },

    //                 success: function (data) {
    //                     window.location.reload();
    //                 },
    //             });
    //         }
    //     })
    // }

</script>


@endsection