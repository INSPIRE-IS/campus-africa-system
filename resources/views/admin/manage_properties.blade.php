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
                <th>Year</th>
                <th>Available</th>
                <th></th>
            </tr> 
        </thead>
        <tbody>

        @foreach($properties as $property)
            <tr>
                <td>{{$property->residence}} |{{$property->room_available}}|</td>
                <td>{{$property->floor}}</td>
                <td>{{$property->unit}}</td>
                <td>{{$property->room}}</td>
                <td>{{$property->bed}}</td>
                <td>{{$property->bed_code}}</td>
                <td>@if($property->inventory == "1") Yes @else No @endif</td>
                <td>{{$property->room_type}}</td>
                <td>{{$property->room_rate}}</td>
                <td>{{$property->year == 0 ? "All" : $property->year}}</td>
                <td>@if($property->room_available == "1" && $property->inventory != "1") &#128308; &nbsp; No @else &#128994; &nbsp; Yes @endif</td>
                <td>
                    <!-- <button type="button" id="tableBtn" class="detailsChange" onClick="deleteProperty('{{$property->id}}')" style="margin:5px;">Delete</button> -->
                    <a href="/property/edit/{{$property->id}}"><button type="button" style="margin:5px;" id="tableBtn" onClick="openModal('{{$property->id}}')" class="detailsChange">Edit</button></a>
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