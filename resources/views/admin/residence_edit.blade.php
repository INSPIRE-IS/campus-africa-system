@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/save-residence" method="post">
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
            <label>Booking Rate</label>
            <input type="text" name="booking_rate" id="booking_rate" value="<?php if(!empty($propertyData)){echo $propertyData->booking_rate;}?>">
        </div>

        <div class="form-field">
            <label>Owner</label>
            <input type="text" name="owner" id="owner" value="<?php if(!empty($propertyData)){echo $propertyData->owner;}?>">
        </div>


        <div class="form-field">
            <label>Address</label>
            <input type="text" name="address" id="address" value="<?php if(!empty($propertyData)){echo $propertyData->address;}?>">
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