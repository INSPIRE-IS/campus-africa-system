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
            </select>
            <input type="hidden" name="lookupId" id="lookupId" value="{{$lookup->id}}"/>
        </div>

        <div class="form-field">
            <label>Value</label>
            <input type="text" name="value" id="value" value="<?php echo $lookup->lookup_name;?>">
        </div>

        <input type="submit" value="Update" class="profileSubmit" style="opacity:1"/>
    </form>
</div>

@endsection