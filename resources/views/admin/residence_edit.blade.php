@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/save-residence" method="post" autocomplete="off" id="residence">
    @csrf
        <br/><br/>

        <div class="form-field">
            <label>Residence</label>
            <input type="text" name="residence" id="residenceName" value="<?php if(!empty($propertyData)){echo $propertyData->residence;}?>">
            <!-- <select name="residence" id='residence'>
                <option value="" selected disabled>Select One</option>
                <option value="Braamlofts" <?php if(!empty($propertyData) && $propertyData->residence == "Braamlofts"){echo "selected";}?>>Braamlofts</option>
                <option value="80 Jorissen" <?php if(!empty($propertyData) && $propertyData->residence == "80 Jorissen"){echo "selected";}?>>80 Jorissen</option>
                <option value="49 Jorissen" <?php if(!empty($propertyData) && $propertyData->residence == "49 Jorissen"){echo "selected";}?>>49 Jorissen</option>
                <option value="Dunvista Mansions" <?php if(!empty($propertyData) && $propertyData->residence == "Dunvista Mansions"){echo "selected";}?>>Dunvista Mansions</option>
                <option value="Wynton Joy" <?php if(!empty($propertyData) && $propertyData->residence == "Wynton Joy"){echo "selected";}?>>Wynton Joy</option>
                <option value="126 Siemert" <?php if(!empty($propertyData) && $propertyData->residence == "126 Siemert"){echo "selected";}?>>126 Siemert</option>
                <option value="YMCA" <?php if(!empty($propertyData) && $propertyData->residence == "YMCA"){echo "selected";}?>>YMCA</option>
            </select> -->
            <input type="hidden" name="propertyId" id="propertyId" value="<?php if(!empty($propertyData)){echo $propertyData->id;}else{echo "0";}?>"/>
        </div>

        <div class="form-field">
            <label>Owner</label>
            <input type="text" name="owner" id="owner" value="<?php if(!empty($propertyData)){echo $propertyData->owner;}?>">
        </div>


        <div class="form-field">
            <label>Address</label>
            <input type="text" name="address" id="address" value="<?php if(!empty($propertyData)){echo $propertyData->address;}?>">
        </div>

        <div class="form-field">
            <label>MDA Property Code <span id="building_prefix_error" class="hidden" style="color:red"><small>MDA Property Code already in use</small></span></label>
            <input type="text" name="building_prefix" id="building_prefix" value="<?php if(!empty($propertyData)){echo $propertyData->building_prefix;}?>" onKeyUp="updatePrefix()" maxLength="2">
        </div>


        <!-- <div class="form-field" style="width:33%">
            <label>Booking Fee (NSFAS)</label>
            <input type="text" name="nsfas_booking_rate" id="nsfas_booking_rate" value="<?php if(!empty($propertyData)){echo $propertyData->nsfas_booking_rate;}?>">
        </div>

        <div class="form-field" style="width:33%">
            <label>Booking Fee (Bursary)</label>
            <input type="text" name="bursary_booking_rate" id="bursary_booking_rate" value="<?php if(!empty($propertyData)){echo $propertyData->bursary_booking_rate;}?>">
        </div>

        <div class="form-field" style="width:33%">
            <label>Booking Fee (Cash)</label>
            <input type="text" name="booking_rate" id="booking_rate" value="<?php if(!empty($propertyData)){echo $propertyData->booking_rate;}?>">
        </div> -->


        <input type="submit" value="Update" class="profileSubmit" style="opacity:1"/>
    </form>
</div>

<script>

    function updatePrefix(){
        $.ajax({
            type: "post",
            url: "/check-building-prefix",
            data: {building: $("#building_prefix").val(), residence: $("#residenceName").val()},

            success: function (data) {
                if(data == "false"){
                    $("#building_prefix").addClass('fieldError')
                    $("#building_prefix_error").removeClass('hidden')
                }else{
                    $("#building_prefix").removeClass('fieldError')
                    $("#building_prefix_error").addClass('hidden')
                }
            },
        });
    }
    

    $('#residence').submit(function() {

        if($("#building_prefix").hasClass('fieldError')){
            return false;
        }else{
            return true;
        }

    });

</script>

@endsection