@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <br/>
    <a href="/residence/edit/0"><button type="button" class="detailsChange" >Add New</button></a>
    <br/>
    <table id="properties_table" class="display">
        <thead>
            <tr>
                <th>Residence</th>
                <!-- <th>Booking Fee (NSFAS)</th>
                <th>Booking Fee (Bursary)</th>
                <th>Booking Fee (Cash)</th> -->
                <th>Owner</th>
                <th>Address</th>
                <th>Building Prefix</th>
                <th></th>
            </tr> 
        </thead>
        <tbody>

        @foreach($residences as $residence)
            <tr>
                <td>{{$residence->residence}}</td>
                <!-- <td>{{$residence->nsfas_booking_rate}}</td>
                <td>{{$residence->bursary_booking_rate}}</td>
                <td>{{$residence->booking_rate}}</td> -->
                <td>{{$residence->owner}}</td>
                <td>{{$residence->address}}</td>
                <td>{{$residence->building_prefix}}</td>
                <td>
                    <a href="/residence/edit/{{$residence->id}}"><button type="button" style="margin:5px;" id="tableBtn" class="detailsChange">Edit</button></a>
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

</script>


@endsection