@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/save-property" method="post">
    @csrf
        <br/><br/>

        <div class="form-field">
            <label>Residence</label>
            <select name="residence" id='residence'>
                <option value="" selected disabled>Select One</option>
                <option value="Braamlofts" <?php if(!empty($propertyData) && $propertyData->residence == "Braamlofts"){echo "selected";}?>>Braamlofts</option>
                <option value="80 Jorissen" <?php if(!empty($propertyData) && $propertyData->residence == "80 Jorissen"){echo "selected";}?>>80 Jorissen</option>
                <option value="49 Jorissen" <?php if(!empty($propertyData) && $propertyData->residence == "49 Jorissen"){echo "selected";}?>>49 Jorissen</option>
                <option value="Dunvista Mansions" <?php if(!empty($propertyData) && $propertyData->residence == "Dunvista Mansions"){echo "selected";}?>>Dunvista Mansions</option>
                <option value="Wynton Joy" <?php if(!empty($propertyData) && $propertyData->residence == "Wynton Joy"){echo "selected";}?>>Wynton Joy</option>
                <option value="126 Siemert" <?php if(!empty($propertyData) && $propertyData->residence == "126 Siemert"){echo "selected";}?>>126 Siemert</option>
                <option value="YMCA" <?php if(!empty($propertyData) && $propertyData->residence == "YMCA"){echo "selected";}?>>YMCA</option>
            </select>
            <input type="hidden" name="propertyId" id="propertyId" value="<?php if(!empty($propertyData)){echo $propertyData->id;}else{echo "0";}?>"/>
        </div>

        <div class="form-field">
            <label>Room Type</label>
            <select name="roomType" id='roomType' class="required">
                <option value="" selected disabled>Select a Room Type</option>
                <?php if(!empty($propertyData) && $propertyData->residence == "Braamlofts"){?>
                    <option value="Single Room" <?php if($propertyData->room_type == "Single Room"){ echo "selected";}?>>Single Room</option>
                    <option value="2 Sharing" <?php if($propertyData->room_type == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>
                    <option value="3 Sharing" <?php if($propertyData->room_type == "3 Sharing"){ echo "selected";}?>>3 Sharing</option>
                <?php } ?>

                <?php if(!empty($propertyData) && $propertyData->residence == "80 Jorissen" || !empty($propertyData) && $propertyData->residence == "Dunvista Mansions" || !empty($propertyData) && $propertyData->residence == "Wynton Joy" || !empty($propertyData) && $propertyData->residence == "126 Siemert"){?>
                    <option value="Single Room" <?php if($propertyData->room_type == "Single Room"){ echo "selected";}?>>Single Room</option>
                    <option value="2 Sharing" <?php if($propertyData->room_type == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>
                <?php } ?>

                <?php if(!empty($propertyData) && $propertyData->residence == "49 Jorissen"){?>
                    <option value="2 Sharing" <?php if($propertyData->room_type == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>
                    <option value="single room 2 bedroom" <?php if($propertyData->room_type == "single room 2 bedroom"){ echo "selected";}?>>Single Room | 2 Bedroom</option>
                    <option value="single room 3 bedroom" <?php if($propertyData->room_type == "single room 3 bedroom"){ echo "selected";}?>>Single Room | 3 Bedroom</option>
                <?php } ?>

                <?php if(!empty($propertyData) && $propertyData->residence == "YMCA"){?>
                    <option value="2 Sharing" <?php if($propertyData->room_type == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>
                    <option value="3 Sharing" <?php if($propertyData->room_type == "3 Sharing"){ echo "selected";}?>>3 Sharing</option>
                    <option value="single room small" <?php if($propertyData->room_type == "single room small"){ echo "selected";}?>>Single Room | small</option>
                    <option value="single room medium" <?php if($propertyData->room_type == "single room medium"){ echo "selected";}?>>Single Room | Medium</option>
                    <option value="single room large" <?php if($propertyData->room_type == "single room large"){ echo "selected";}?>>Single Room | Large</option>
                <?php } ?>
            </select>

        </div>

        <div class="form-field">
            <label>Meta Info 1</label>
            <input type="text" name="meta_info_1" id="meta_info_1" value="<?php if(!empty($propertyData)){echo $propertyData->meta_info_1;}?>">
        </div>

        <div class="form-field">
            <label>Meta Info 2</label>
            <input type="text" name="meta_info_2" id="meta_info_2" value="<?php if(!empty($propertyData)){echo $propertyData->meta_info_2;}?>">
        </div>

        <div class="form-field">
            <label>Floor</label>
            <select name="floor" id='floor'>
                <option value="" selected disabled>Select One</option>
                <option value="Basement" <?php if(!empty($propertyData) && $propertyData->floor == "Basement"){echo "selected";}?>>Basement</option>
                <option value="Ground" <?php if(!empty($propertyData) && $propertyData->floor == "Ground"){echo "selected";}?>>Ground</option>
                <option value="1st" <?php if(!empty($propertyData) && $propertyData->floor == "1st"){echo "selected";}?>>1st</option>
                <option value="2nd" <?php if(!empty($propertyData) && $propertyData->floor == "2nd"){echo "selected";}?>>2nd</option>
                <option value="3rd" <?php if(!empty($propertyData) && $propertyData->floor == "3rd"){echo "selected";}?>>3rd</option>
                <option value="4th" <?php if(!empty($propertyData) && $propertyData->floor == "4th"){echo "selected";}?>>4th</option>
                <option value="5th" <?php if(!empty($propertyData) && $propertyData->floor == "5th"){echo "selected";}?>>5th</option>
                <option value="6th" <?php if(!empty($propertyData) && $propertyData->floor == "6th"){echo "selected";}?>>6th</option>
                <option value="7th" <?php if(!empty($propertyData) && $propertyData->floor == "7th"){echo "selected";}?>>7th</option>
                <option value="8th" <?php if(!empty($propertyData) && $propertyData->floor == "8th"){echo "selected";}?>>8th</option>
                <option value="9th" <?php if(!empty($propertyData) && $propertyData->floor == "9th"){echo "selected";}?>>9th</option>
                <option value="10th" <?php if(!empty($propertyData) && $propertyData->floor == "10th"){echo "selected";}?>>10th</option>
                <option value="11th" <?php if(!empty($propertyData) && $propertyData->floor == "11th"){echo "selected";}?>>11th</option>
                <option value="12th" <?php if(!empty($propertyData) && $propertyData->floor == "12th"){echo "selected";}?>>12th</option>
                <option value="13th" <?php if(!empty($propertyData) && $propertyData->floor == "13th"){echo "selected";}?>>13th</option>
                <option value="14th" <?php if(!empty($propertyData) && $propertyData->floor == "14th"){echo "selected";}?>>14th</option>
                <option value="15th" <?php if(!empty($propertyData) && $propertyData->floor == "15th"){echo "selected";}?>>15th</option>
                <option value="16th" <?php if(!empty($propertyData) && $propertyData->floor == "16th"){echo "selected";}?>>16th</option>
                <option value="17th" <?php if(!empty($propertyData) && $propertyData->floor == "17th"){echo "selected";}?>>17th</option>
            </select>
        </div>

        <div class="form-field">
            <label>Unit</label>
            <input type="text" name="unit" id="unit" value="<?php if(!empty($propertyData)){echo $propertyData->unit;}?>">
        </div>

        <div class="form-field">
            <label>Room</label>
            <select name="room" id='room'>
                <option value="" selected>Select One</option>
                <option value="A" <?php if(!empty($propertyData) && $propertyData->room == "A"){echo "selected";}?>>A</option>
                <option value="B" <?php if(!empty($propertyData) && $propertyData->room == "B"){echo "selected";}?>>B</option>
                <option value="C" <?php if(!empty($propertyData) && $propertyData->room == "C"){echo "selected";}?>>C</option>
                <option value="D" <?php if(!empty($propertyData) && $propertyData->room == "D"){echo "selected";}?>>D</option>
            </select>
        </div>

        <div class="form-field">
            <label>Bed</label>
            <select name="bed" id='bed'>
                <option value="" selected disabled>Select One</option>
                <option value="A" <?php if(!empty($propertyData) && $propertyData->bed == "A"){echo "selected";}?>>A</option>
                <option value="B" <?php if(!empty($propertyData) && $propertyData->bed == "B"){echo "selected";}?>>B</option>
                <option value="C" <?php if(!empty($propertyData) && $propertyData->bed == "C"){echo "selected";}?>>C</option>
                <option value="D" <?php if(!empty($propertyData) && $propertyData->bed == "D"){echo "selected";}?>>D</option>
            </select>
        </div>

        <div class="form-field">
            <label>Bed Code</label>
            <input type="text" name="bed_code" id="bed_code" value="<?php if(!empty($propertyData)){echo $propertyData->bed_code;}?>">
        </div>

        <div class="form-field">
            <label>Inventory</label>
            <select name="inventory" id='inventory'>
                <option value="" selected disabled>Select One</option>
                <option value="1" <?php if(!empty($propertyData) && $propertyData->inventory == "1"){echo "selected";}?>>Yes</option>
                <option value="0" <?php if(!empty($propertyData) && $propertyData->inventory == "0"){echo "selected";}?>>No</option>
            </select>
        </div>

        <div class="form-field">
            <label>2022 Rate</label>
            <input type="text" name="room_rate" id="room_rate" value="<?php if(!empty($propertyData)){echo $propertyData->room_rate;}?>">
        </div>


        <input type="submit" value="Update" class="profileSubmit" style="opacity:1"/>
    </form>
</div>

<script>
    $("#residence").on('change', function(){
        let propValue = $("#residence").val();

        if(propValue == "Braamlofts"){
            $("#roomType").empty().append('<option value="Single Room">Single Room</option><option value="2 Sharing">2 Sharing</option><option value="3 Sharing">3 Sharing</option>')
        }else if(propValue == "80 Jorissen"){
            $("#roomType").empty().append('<option value="Single Room">Single Room</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "49 Jorissen"){
            $("#roomType").empty().append('<option value="2 Sharing">2 Sharing</option><option value="single room 2 bedroom">Single Room | 2 Bedroom</option><option value="single room 3 bedroom">Single Room | 3 Bedroom</option>')
        }else if(propValue == "Dunvista Mansions"){
            $("#roomType").empty().append('<option value="Single Room">Single Room</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "Wynton Joy"){
            $("#roomType").empty().append('<option value="Single Room">Single Room</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "126 Siemert"){
            $("#roomType").empty().append('<option value="Single Room">Single Room</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "YMCA"){
            $("#roomType").empty().append('<option value="2 Sharing">2 Sharing</option><option value="3 Sharing">3 Sharing</option><option value="single room small">Single Room | small</option><option value="single room medium">Single Room | Medium</option><option value="single room large">Single Room | Large</option>')
        }
    })
</script>

@endsection