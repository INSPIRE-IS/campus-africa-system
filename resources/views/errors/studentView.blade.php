@extends('layouts.admin')

@section('content')

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<div class="container">
    
    <form id="appOops" class="form onlineForm" autocomplete="off">
        <br/>
        <h1 class="formHeading">You are unavailable to view active bookings in {{$buildingName}}.</h1>
        <br/>
        <a href="/applications"><button type="button" class="detailsChange" style="text-transform:uppercase; background-color:#e74f26; float:none; margin-left:0px;">Back to Applications</button></a>
        <br/>
    </form>

</div>




@endsection
