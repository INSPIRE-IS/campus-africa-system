@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/save-room-type" method="post" autocomplete="off">
    @csrf
        <br/><br/>

        <div class="form-field">
            <label>Residence</label>
            <select name="property_id" id='property_id'>
                <option value="" selected disabled>Select One</option>

                <?php foreach($residence as $r) { ?>
                    <option value="<?php echo $r->id; ?>" @if(!empty($roomTypeData) && $r->id == $roomTypeData->property_id) {{"selected='selected'"}} @endif><?php echo $r->residence; ?></option>
                <?php } ?>
            </select>            
        </div>

        <div class="form-field">
            <label>Room Type</label>
            <input type="text" name="name" id="name" value="<?php if(!empty($roomTypeData)){echo $roomTypeData->name;}?>">
        </div>

        <input type="hidden" name="id" id="id" value="<?php if(!empty($roomTypeData)){echo $roomTypeData->id;}else{echo "0";}?>"/>

        <input type="submit" value="Update" class="profileSubmit" style="opacity:1"/>
    </form>
</div>

@endsection