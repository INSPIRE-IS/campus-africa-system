@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                <td>@if($lookup->group_id == "1") Institution of Study @elseif($lookup->group_id == "2")Insitution Campus @elseif($lookup->group_id == "3") Faculty @elseif($lookup->group_id == "4") Year @elseif($lookup->group_id == "5") Nationality @elseif($lookup->group_id == "6") Year of study @endif</td>
                <td>{{$lookup->lookup_name}}</td>
                <td>
                    <button type="button" id="tableBtn" class="detailsChange" onClick="deleteLookup('{{$lookup->id}}')" style="margin:5px;">Delete</button>
                    <a href="/lookups/edit/{{$lookup->id}}"><button type="button" style="margin:5px;" id="tableBtn" onClick="openModal('{{$lookup->id}}')" class="detailsChange">Edit</button></a>
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

</script>


@endsection