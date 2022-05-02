@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/save-property" method="post" autocomplete="off">
        <br/><br/>

        <div class="form-field">
            <label>Residence</label>
            <select name="residence" id='residence'>
                <option value="" selected disabled>Select One</option>
                <option value="BRAAMLOFTS" <?php if(!empty($propertyData) && ($propertyData->residence == "BRAAMLOFTS" || $propertyData->residence == "Braamlofts")){echo "selected";}?>>Braamlofts</option>
                <option value="80 JORISSEN" <?php if(!empty($propertyData) && ($propertyData->residence == "80 JORISSEN" || $propertyData->residence == "80 Jorrisen")){echo "selected";}?>>80 Jorissen</option>
                <option value="49 JORISSEN" <?php if(!empty($propertyData) && ($propertyData->residence == "49 JORISSEN" || $propertyData->residence == "49 Jorrisen")){echo "selected";}?>>49 Jorissen</option>
                <option value="DUNVISTA MANSIONS" <?php if(!empty($propertyData) && ($propertyData->residence == "DUNVISTA MANSIONS" || $propertyData->residence == "Dunvista Mansions")){echo "selected";}?>>Dunvista Mansions</option>
                <option value="WYNTON JOY" <?php if(!empty($propertyData) && ($propertyData->residence == "WYNTON JOY" || $propertyData->residence == "Wynton Joy")){echo "selected";}?>>Wynton Joy</option>
                <option value="126 SIEMERT" <?php if(!empty($propertyData) && ($propertyData->residence == "126 SIEMERT" || $propertyData->residence == "126 Siemert")){echo "selected";}?>>126 Siemert</option>
                <option value="YMCA" <?php if(!empty($propertyData) && $propertyData->residence == "YMCA"){echo "selected";}?>>YMCA</option>
            </select>
            <input type="hidden" name="propertyId" id="propertyId" value="<?php if(!empty($propertyData)){echo $propertyData->id;}else{echo "0";}?>"/>
        </div>

        <div class="form-field">
            <label>Room Type</label>
            <select name="roomType" id='roomType' class="required">
                <option value="" selected disabled>Select a Room Type</option>
                <?php if(!empty($propertyData) && ($propertyData->residence == "BRAAMLOFTS" || $propertyData->residence == "Braamlofts")){?>
                    <option value="Single" <?php if($propertyData->room_type == "Single"){ echo "selected";}?>>Single</option>
                    <option value="2 Sharing" <?php if($propertyData->room_type == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>
                    <option value="3 Sharing" <?php if($propertyData->room_type == "3 Sharing"){ echo "selected";}?>>3 Sharing</option>
                <?php } ?>

                <?php if(!empty($propertyData) && ($propertyData->residence == "80 JORISSEN" || $propertyData->residence == "80 Jorrisen") || !empty($propertyData) && ($propertyData->residence == "DUNVISTA MANSIONS" || $propertyData->residence == "Dunvista Mansions") || !empty($propertyData) && ($propertyData->residence == "WYNTON JOY" || $propertyData->residence == "Wynton Joy") || !empty($propertyData) && ($propertyData->residence == "126 SIEMERT" || $propertyData->residence == "126 Siemert")){?>
                    <option value="Single" <?php if($propertyData->room_type == "Single"){ echo "selected";}?>>Single</option>
                    <option value="2 Sharing" <?php if($propertyData->room_type == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>
                <?php } ?>

                <?php if(!empty($propertyData) && ($propertyData->residence == "49 JORISSEN" || $propertyData->residence == "49 Jorrisen")){?>
                    <option value="2 Sharing" <?php if($propertyData->room_type == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>
                    <option value="Single 2 bedroom" <?php if($propertyData->room_type == "Single 2 bedroom"){ echo "selected";}?>>Single | 2 Bedroom</option>
                    <option value="Single 3 bedroom" <?php if($propertyData->room_type == "Single 3 bedroom"){ echo "selected";}?>>Single | 3 Bedroom</option>
                <?php } ?>

                <?php if(!empty($propertyData) && $propertyData->residence == "YMCA"){?>
                    <option value="2 Sharing" <?php if($propertyData->room_type == "2 Sharing"){ echo "selected";}?>>2 Sharing</option>
                    <option value="3 Sharing" <?php if($propertyData->room_type == "3 Sharing"){ echo "selected";}?>>3 Sharing</option>
                    <option value="Single small" <?php if($propertyData->room_type == "Single small"){ echo "selected";}?>>Single | small</option>
                    <option value="Single medium" <?php if($propertyData->room_type == "Single medium"){ echo "selected";}?>>Single | Medium</option>
                    <option value="Single large" <?php if($propertyData->room_type == "Single large"){ echo "selected";}?>>Single | Large</option>
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
                <option value="BASEMENT" <?php if(!empty($propertyData) && ($propertyData->floor == "BASEMENT" || $propertyData->floor == "Basement")){echo "selected";}?>>Basement</option>
                <option value="GROUND" <?php if(!empty($propertyData) && ($propertyData->floor == "GROUND" || $propertyData->floor == "Ground")){echo "selected";}?>>Ground</option>
                <option value="1ST" <?php if(!empty($propertyData) && ($propertyData->floor == "1ST" || $propertyData->floor == "1st")){echo "selected";}?>>1st</option>
                <option value="2ND" <?php if(!empty($propertyData) && ($propertyData->floor == "2ND" || $propertyData->floor == "2nd")){echo "selected";}?>>2nd</option>
                <option value="3RD" <?php if(!empty($propertyData) && ($propertyData->floor == "3RD" || $propertyData->floor == "3rd")){echo "selected";}?>>3rd</option>
                <option value="4TH" <?php if(!empty($propertyData) && ($propertyData->floor == "4TH" || $propertyData->floor == "4th")){echo "selected";}?>>4th</option>
                <option value="5TH" <?php if(!empty($propertyData) && ($propertyData->floor == "5TH" || $propertyData->floor == "5th")){echo "selected";}?>>5th</option>
                <option value="6TH" <?php if(!empty($propertyData) && ($propertyData->floor == "6TH" || $propertyData->floor == "6th")){echo "selected";}?>>6th</option>
                <option value="7TH" <?php if(!empty($propertyData) && ($propertyData->floor == "7TH" || $propertyData->floor == "7th")){echo "selected";}?>>7th</option>
                <option value="8TH" <?php if(!empty($propertyData) && ($propertyData->floor == "8TH" || $propertyData->floor == "8th")){echo "selected";}?>>8th</option>
                <option value="9TH" <?php if(!empty($propertyData) && ($propertyData->floor == "9TH" || $propertyData->floor == "9th")){echo "selected";}?>>9th</option>
                <option value="10TH" <?php if(!empty($propertyData) && ($propertyData->floor == "10TH" || $propertyData->floor == "10th")){echo "selected";}?>>10th</option>
                <option value="11TH" <?php if(!empty($propertyData) && ($propertyData->floor == "11TH" || $propertyData->floor == "11th")){echo "selected";}?>>11th</option>
                <option value="12TH" <?php if(!empty($propertyData) && ($propertyData->floor == "12TH" || $propertyData->floor == "12th")){echo "selected";}?>>12th</option>
                <option value="13TH" <?php if(!empty($propertyData) && ($propertyData->floor == "13TH" || $propertyData->floor == "13th")){echo "selected";}?>>13th</option>
                <option value="14TH" <?php if(!empty($propertyData) && ($propertyData->floor == "14TH" || $propertyData->floor == "14th")){echo "selected";}?>>14th</option>
                <option value="15TH" <?php if(!empty($propertyData) && ($propertyData->floor == "15TH" || $propertyData->floor == "15th")){echo "selected";}?>>15th</option>
                <option value="16TH" <?php if(!empty($propertyData) && ($propertyData->floor == "16TH" || $propertyData->floor == "16th")){echo "selected";}?>>16th</option>
                <option value="17TH" <?php if(!empty($propertyData) && ($propertyData->floor == "17TH" || $propertyData->floor == "17th")){echo "selected";}?>>17th</option>
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
                <option value="" selected>Select One</option>
                <option value="a" <?php if(!empty($propertyData) && $propertyData->bed == "a"){echo "selected";}?>>a</option>
                <option value="b" <?php if(!empty($propertyData) && $propertyData->bed == "b"){echo "selected";}?>>b</option>
                <option value="c" <?php if(!empty($propertyData) && $propertyData->bed == "c"){echo "selected";}?>>c</option>
                <option value="d" <?php if(!empty($propertyData) && $propertyData->bed == "d"){echo "selected";}?>>d</option>
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
                <option value="0" <?php if(!empty($propertyData) && ($propertyData->inventory == "0" || $propertyData->inventory == null)){echo "selected";}?>>No</option>
            </select>
        </div>

        <div class="form-field">
            <label>2022 Rate</label>
            <input type="text" name="room_rate" id="room_rate" value="<?php if(!empty($propertyData)){echo $propertyData->room_rate;}?>">
        </div>

        <?php if (!empty($propertyData)) { ?>
            <div class="form-field">
                <label>Academic Year</label>
                <div>
                    <?php
                    if ($propertyData->year_end == 0) {
                        echo $propertyData->year . " to ~";
                    } else if ($propertyData->year == 0) {
                        echo "~ to " . $propertyData->year_end;
                    } else {
                        echo $propertyData->year . " to " . $propertyData->year_end;
                    }
                    ?>
                </div>
            </div>
        <?php } ?>

        <input type="hidden" name="submit_type" id="submit_type" value="">

        <!-- <input type="submit" value="Update" class="profileSubmit" style="opacity:1"/>         -->
    </form>

    <input type="button" id="btn_submit_frm" class="profileSubmit" style="opacity:1" value="Update Bed" />

    @if(!empty($propertyData))
        @if (count($bookingData) == 0)
            <input type="button" id="btn_delete_bed" class="profileSubmit" style="opacity:1" value="Delete Bed" />
        @else
            <p style="text-align: center">This bed cannot be deleted with an active booking. Please Unallocate the bed from the booking first. The booking is listed below and clickable.</p>
        @endif
    @endif

    <h2>Bookings</h2>
    @if (count($bookingData) > 0)
        <div>
            <?php foreach($bookingData as $bd) { ?>
                <a target="_blank" href="/application-profile/{{$bd->userId}}"><?php echo "#" . $bd->id . " - " . $bd->users->userFirstName . " " . $bd->users->userLastname . " (" . $bd->lease_from_date . " to " . $bd->lease_to_date . ")"; ?></a>
            <?php } ?>
        </div>
    @else
        <p>No Bookings</p>
    @endif
</div>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#residence").on('change', function(){
        let propValue = $("#residence").val();

        if(propValue == "BRAAMLOFTS"){
            $("#roomType").empty().append('<option value="Single">Single</option><option value="2 Sharing">2 Sharing</option><option value="3 Sharing">3 Sharing</option>')
        }else if(propValue == "80 JORISSEN"){
            $("#roomType").empty().append('<option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "49 JORISSEN"){
            $("#roomType").empty().append('<option value="2 Sharing">2 Sharing</option><option value="Single 2 bedroom">Single | 2 Bedroom</option><option value="Single 3 bedroom">Single | 3 Bedroom</option>')
        }else if(propValue == "DUNVISTA MANSIONS"){
            $("#roomType").empty().append('<option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "WYNTON JOY"){
            $("#roomType").empty().append('<option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "126 SIEMERT"){
            $("#roomType").empty().append('<option value="Single">Single</option><option value="2 Sharing">2 Sharing</option>')
        }else if(propValue == "YMCA"){
            $("#roomType").empty().append('<option value="2 Sharing">2 Sharing</option><option value="3 Sharing">3 Sharing</option><option value="Single small">Single | small</option><option value="Single medium">Single | Medium</option><option value="Single large">Single | Large</option>')
        }
    });

    $('#btn_submit_frm').on('click', function(e) {
        let btn = $(this);
        $(btn).val('Saving...');

        Swal.fire({
            text: "Will this change apply to bookings in the current Academic Year (2022) or the next Academic Year (2023) going forward?",
            icon: 'warning',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonColor: '#3085d6',
            denyButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Current Academic Year',
            denyButtonText: 'Next Academic Year'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#submit_type").val('current');
            } else if (result.isDenied) {
                $("#submit_type").val('next');
            }

            // $('form').submit();

            $.ajax({
                type: "post",
                url: "/save-property",
                data: $('form').serialize(),

                success: function (data) {          
                    if (data == "done") {
                        window.location = '/manage-properties';
                    } else {
                        Swal.fire(
                            'Error',
                            data,
                            'error'
                        );

                        $(btn).val('Update Bed');
                    }
                },
            });
            
        })

        e.preventDefault();
    });

    <?php if(!empty($propertyData)) { ?>
        $(document).ready( function($) {
            $('#btn_delete_bed').on("click", function(e) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to delete this Bed, this cannot be undone",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "post",
                            url: "/delete-bed",
                            data: {
                                propertyId: "{{$propertyData->id}}",
                            },

                            success: function (data) {                        
                                window.location = "/manage-properties";                
                            },
                        });
                    }
                });

                e.preventDefault();
            });
        });
    <?php } ?>    
</script>

@endsection