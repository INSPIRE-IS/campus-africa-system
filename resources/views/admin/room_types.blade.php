@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <br/>
    <a href="/manage-properties"><button type="button" class="detailsChange" >Manage Properties</button></a>
    <a href="/properties-info"><button type="button" class="detailsChange" >Residence Info</button></a>
    <a href="/room-type/edit/0"><button type="button" class="detailsChange" >Add New</button></a>
    <br/>
    <table id="properties_table" class="display">
        <thead>
            <tr>
                <th>Residence</th>
                <th>Room Type</th>
                <th>Website Room Map</th>
                <th>Price Source</th>
                <th>Price</th>
                <th>Booking Fee</th>
                <th>Applies To</th>
                <th></th>
            </tr> 
        </thead>
        <tbody>

        @foreach($room_types as $rt)
            <tr>
                <td>
                    @foreach($residences as $residence)
                        @if($residence->id == $rt->property_id)
                            {{$residence->residence}}
                        @endif
                    @endforeach
                </td>
                <td>{{$rt->name}}</td>
                <td>
                    @if($rt->pull_price == '1')
                        @foreach($roomDetails as $rd)
                            @if($rd->roomId == $rt->room_map_slug)
                                {!!$rd->title!!}
                            @endif
                        @endforeach
                    @endif
                </td>
                <td>@if($rt->pull_price == '1') Website @else @if($rt->booking_price !== null) Booking System @endif @endif</td>
                <td>
                    @if($rt->rent_price !== null) 
                        R {{$rt->rent_price}} 
                    @else 
                        @foreach($roomDetails as $rd)
                            @if($rd->roomId == $rt->room_map_slug)
                                R {{$rd->price_from}}
                            @endif
                        @endforeach
                    @endif 
                </td>
                <td>
                    @if($rt->booking_price !== null) 
                        R {{$rt->booking_price}}
                    @else 
                        @foreach($roomDetails as $rd)
                            @if($rd->roomId == $rt->room_map_slug)
                                R {{$rd->booking_fee}}
                            @endif
                        @endforeach
                    @endif 
                </td>
                <td>
                    @if($rt->rent_price !== null) 
                        @if($rt->apply_nsfas !== null)
                            NSFAS
                        @endif
                        
                        @if($rt->apply_bursary !== null)
                            @if($rt->apply_nsfas !== null)
                                 | 
                            @endif

                            Bursary
                        @endif

                        @if($rt->apply_cash !== null)

                            @if($rt->apply_bursary !== null || $rt->apply_nsfas !== null)
                                 | 
                            @endif

                            Cash
                        @endif
                    @else
                        @foreach($roomDetails as $rd)
                            @if($rd->roomId == $rt->room_map_slug)
                                {{str_replace("Students", "", implode(" | ", $rd->applies_to))}}
                            @endif
                        @endforeach
                    @endif
                </td>
                <td>
                    <a href="/room-type/edit/{{$rt->id}}" style="background-color:red; color:#fff; padding:6px; border-radius:9px; text-decoration:none;">Edit</a>
                    <?php
                    $i = 0;
                    foreach($property as $p){
                        if($p->residence == $rt->property_id){
                            if($p->room_type == $rt->id){
                                $i++;
                            }
                        }
                    }
                    
                    if($i == 0){?>
                        <button type="button" id="tableBtn" class="detailsChange" onClick="deleteRoom('{{$rt->id}}')" style="margin:5px;">Delete</button>
                    <?php } ?>
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


    function deleteRoom(roomId){
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
                    url: "/delete/room",
                    data: {
                        roomId: roomId, 
                    },

                    success: function (data) {
                        window.location.reload();
                    },
                });
            }
        })
    }

    // room_map_slug

    
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