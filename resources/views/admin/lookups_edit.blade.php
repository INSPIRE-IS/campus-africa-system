@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/save-lookup" method="post">
    @csrf
        <br/>        <br/>

        <div class="form-field">
            <label>Title</label>
            <select name="group_id" id='group_id'>
                <option value="0" disabled selected>Select one</option>
                <option value="1" <?php if($lookup->group_id == "1"){echo "selected";}?>>Institution of Study</option>
                <!-- <option value="2" <?php if($lookup->group_id == "2"){echo "selected";}?>>Insitution Campus</option> -->
                <option value="3" <?php if($lookup->group_id == "3"){echo "selected";}?>>Faculty</option>
                <option value="4" <?php if($lookup->group_id == "4"){echo "selected";}?>>Year</option>
                <option value="5" <?php if($lookup->group_id == "5"){echo "selected";}?>>Nationality</option>
                <option value="6" <?php if($lookup->group_id == "6"){echo "selected";}?>>Year of Study</option>
            </select>
            <input type="hidden" name="lookupId" id="lookupId" value="{{$lookup->id}}"/>
        </div>

        <div class="form-field">
            <label>Value</label>
            <input type="text" name="value" id="value" value="<?php echo $lookup->lookup_name;?>">
        </div>

        @if($lookup->group_id == "4")
            <div class="form-field">
                <label>Lease From Date</label>
                <input type="date" name="lease_from_date" id="lease_from_date" value="{{$lookup->lease_from_date}}">
            </div>
            <div class="form-field">
                <label>Lease From Time</label>
                <input type="time" id="lease_from_time" name="lease_from_time" value="{{$lookup->lease_from_time}}">
            </div>
            <div class="form-field">
                <label>Lease To Date</label>
                <input type="date" name="lease_to_date" id="lease_to_date" value="{{$lookup->lease_to_date}}">
            </div>
            <div class="form-field">
                <label>Lease To Time</label>
                <input type="time" id="lease_to_time" name="lease_to_time" value="{{$lookup->lease_to_time}}" >
            </div>
        @endif

        <input type="submit" value="Update" class="profileSubmit" style="opacity:1"/>
    </form>
</div>

@endsection