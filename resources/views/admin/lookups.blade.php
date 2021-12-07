@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>

<div class="container">
    <br/>
    <a href="/lookups/new"><button type="button" class="detailsChange" >Add New</button></a>
    <br/>
    <table id="lookups_table" class="display">
        <thead>
            <tr>
                <th>Lookup</th>
                <th>Value</th>
                <th></th>
            </tr> 
        </thead>
        <tbody>

        @foreach($lookups as $lookup)
            <tr>
                <td>@if($lookup->group_id == "1") Institution of Study @elseif($lookup->group_id == "2")Insitution Campus @elseif($lookup->group_id == "3") Faculty @endif</td>
                <td>{{$lookup->lookup_name}}</td>
                <td><a href="/lookups/edit/{{$lookup->id}}"><button type="button" id="tableBtn" onClick="openModal('{{$lookup->id}}')" class="detailsChange">Edit</button></a></td>
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

</script>


@endsection