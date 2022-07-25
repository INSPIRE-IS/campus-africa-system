@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/save-room-type" method="post" autocomplete="off" id="roomData">
    @csrf
        <br/><br/>

        <div class="form-field">
            <label>Residence * <span id="property_id_error" class="hidden" style="color:red"><small>This is a required field</small></span></label>
            <select name="property_id" id='property_id' class="required">
                <option value="" selected disabled>Select One</option>

                <?php foreach($residence as $r) { ?>
                    <option value="<?php echo $r->id; ?>" <?php if(!empty($roomTypeData) && $r->id == $roomTypeData->property_id){ echo "selected='selected'";}  ?>><?php echo $r->residence; ?></option>
                <?php } ?>
            </select>            
        </div>

        <div class="form-field">
            <label>Room Type * <span id="name_error" class="hidden" style="color:red"><small>This is a required field</small></span></label>
            <input type="text" name="name" id="name" value="<?php if(!empty($roomTypeData)){echo $roomTypeData->name;}?>" class="required">
        </div>

        <div class="form-field">
            <label>Website Room Map</label>
            <select name="room_map" id="room_map">
                <option value="">Please Select One</option>
            </select>
        </div>

        <div class="form-field">
            <br/>
        </div>

        <div class="form-field">
            <br/>
            <ul style="list-style-type:none;" id="pull_price_box">
                <li style="display:inline-flex">
                    <input type="checkbox" id="pull_price" name="pull_price" value="1" style="width:40px !important" <?php if(!empty($roomTypeData) && $roomTypeData->pull_price == '1'){echo "checked";}?>>
                    <label for="pull_price" style="width:500px; padding-left:10px;">Pull prices and booking fees from website</label>
                </li>
            </ul>
        </div>

        <div class="form-field">
            <br/>
        </div>

        <div class="form-field">
            <label>Booking Fee <span id="booking_price_error" class="hidden" style="color:red"><small>This is a required field</small></span></label>
            <input type="number" name="booking_price" id="booking_price" value="<?php if(!empty($roomTypeData)){echo $roomTypeData->booking_price;}?>">
        </div>

        <div class="form-field">
            <label>Room Price<span id="rent_price_error" class="hidden" style="color:red"><small>This is a required field</small></span></label>
            <input type="number" name="rent_price" id="rent_price" value="<?php if(!empty($roomTypeData)){echo $roomTypeData->rent_price;}?>">
        </div>

        <div>
            <label>Booking Fee Applies to <span id="applies_to" class="hidden" style="color:red"><small>Please Select at least one</small></span></label>
            <br/>
            <ul style="list-style-type:none;">
                <li style="display:inline-flex">
                    <input type="checkbox" id="apply_nsfas" name="apply_nsfas" value="1" style="width:40px !important" <?php if(!empty($roomTypeData) && $roomTypeData->rent_price == '1'){echo "checked";}?>>
                    <label for="apply_nsfas" style="width:200px; padding-left:10px;">NSFAS</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="apply_bursary" name="apply_bursary" value="1" style="width:40px !important" <?php if(!empty($roomTypeData) && $roomTypeData->apply_bursary == '1'){echo "checked";}?>>
                    <label for="apply_bursary" style="width:200px; padding-left:10px;">Bursary</label>
                </li>
                <li style="display:inline-flex">
                    <input type="checkbox" id="apply_cash" name="apply_cash" value="1" style="width:40px !important" <?php if(!empty($roomTypeData) && $roomTypeData->apply_cash == '1'){echo "checked";}?>>
                    <label for="apply_cash" style="width:200px; padding-left:10px;">Cash</label>
                </li>
            </ul>
            <br/>
        </div>

        

        <!-- <div class="form-field">
            <label>Website Room Price</label>
            <select name="room_map_price" id="room_map_price">
                <option value="">Please Select One</option>
            </select>
        </div> -->

        <input type="hidden" name="id" id="id" value="<?php if(!empty($roomTypeData)){echo $roomTypeData->id;}else{echo "0";}?>"/>

        <input type="submit" value="Update" class="profileSubmit" style="opacity:1"/>
    </form>
</div>

<script>
    $(document).ready( function () {

        if($("#property_id").val() !== ""){

            let properties = <?php echo json_encode($propertyDetails); ?>;
            let rooms = <?php echo json_encode($roomDetails); ?>;

            $("#room_map").empty().append('<option value="">Please Select One</option>')
            // $("#room_map_price").empty().append('<option value="">Please Select One</option>')
            properties.forEach(function (arrayItem){
                if(arrayItem['propId'] == $("#property_id").val()){
                    rooms.forEach(function(roomItem){
                        if(roomItem['id'] == arrayItem['id']){

                            let preselected = "<?php if(!empty($roomTypeData) && $roomTypeData->room_map_slug != "" && $roomTypeData->room_map_slug != null){echo $roomTypeData->room_map_slug;}else {echo "";}?>";
                            let selected = preselected == roomItem['roomId'] ? "selected" : "";
                            $("#room_map").append('<option value="'+roomItem['roomId']+'" '+selected+'>'+roomItem['title']+'</option>')

                            // let preselectedPrice = "<?php if(!empty($roomTypeData) && $roomTypeData->room_map_price != "" && $roomTypeData->room_map_price != null){echo $roomTypeData->room_map_price;}else {echo "";}?>";
                            // let selectedPrice = preselectedPrice == roomItem['roomId'] ? "selected" : "";
                            // $("#room_map_price").append('<option value="'+roomItem['roomId']+'" '+selectedPrice+'>'+roomItem['title']+'</option>')
                        }
                    })
                }
            })

            UpdatePricingIni();
        }else{
            document.getElementById('pull_price').checked = false;
            document.getElementById('pull_price').disabled = true;
        }

        function UpdatePricingIni(){
            if($("#room_map").val() !== ""){
                $("#pull_price_box").removeClass('hidden');
                if(window.location.href.split("/").pop() !== 0){
                    if(document.getElementById('pull_price').checked){
                        document.getElementById('booking_price').disabled = true;
                        document.getElementById('rent_price').disabled = true;
                        document.getElementById('apply_nsfas').disabled = true;
                        document.getElementById('apply_bursary').disabled = true;
                        document.getElementById('apply_cash').disabled = true;
                        
                        $('#booking_price').removeClass('required').removeClass('fieldError');
                        $('#rent_price').removeClass('required').removeClass('fieldError');
                    }
                }
            }else{
                $("#pull_price_box").addClass('hidden');
                document.getElementById('pull_price').checked = false;
                document.getElementById('pull_price').disabled = true;

                $('#booking_price').addClass('required');
                $('#rent_price').addClass('required');
            }
        }

        $("#room_map").on('change', function(){
            if($("#room_map").val() !== ""){
                $("#pull_price_box").removeClass('hidden');
                document.getElementById('pull_price').disabled = false;
                document.getElementById('pull_price').checked = true;
                document.getElementById('booking_price').disabled = true;
                document.getElementById('rent_price').disabled = true;
                document.getElementById('apply_nsfas').disabled = true;
                document.getElementById('apply_bursary').disabled = true;
                document.getElementById('apply_cash').disabled = true;

                $('#booking_price').removeClass('required').removeClass('fieldError');
                $('#rent_price').removeClass('required').removeClass('fieldError');


            }else{
                $("#pull_price_box").addClass('hidden');
                document.getElementById('pull_price').checked = false;
                document.getElementById('pull_price').disabled = true;
                document.getElementById('booking_price').disabled = false;
                document.getElementById('rent_price').disabled = false;
                document.getElementById('apply_nsfas').disabled = false;
                document.getElementById('apply_bursary').disabled = false;
                document.getElementById('apply_cash').disabled = false;

                $('#booking_price').addClass('required');
                $('#rent_price').addClass('required');

            }
        })

        $("#pull_price").on('change', function(){
            if(document.getElementById('pull_price').checked){
                document.getElementById('booking_price').disabled = true;
                document.getElementById('rent_price').disabled = true;
                document.getElementById('apply_nsfas').disabled = true;
                document.getElementById('apply_bursary').disabled = true;
                document.getElementById('apply_cash').disabled = true;

                $('#booking_price').removeClass('required').removeClass('fieldError');
                $('#rent_price').removeClass('required').removeClass('fieldError');

            }else{
                document.getElementById('booking_price').disabled = false;
                document.getElementById('rent_price').disabled = false;
                document.getElementById('apply_nsfas').disabled = false;
                document.getElementById('apply_bursary').disabled = false;
                document.getElementById('apply_cash').disabled = false;

                $('#booking_price').addClass('required');
                $('#rent_price').addClass('required');

            }
        })

        $("#room_map").on('change', function(){
            if($("#room_map").val() !== ""){
                document.getElementById('pull_price').false = true;
                document.getElementById('pull_price').checked = true;
                document.getElementById('booking_price').disabled = true;
                document.getElementById('rent_price').disabled = true;
                document.getElementById('apply_nsfas').disabled = true;
                document.getElementById('apply_bursary').disabled = true;
                document.getElementById('apply_cash').disabled = true;

                $('#booking_price').removeClass('required').removeClass('fieldError');
                $('#rent_price').removeClass('required').removeClass('fieldError');

            }else{
                document.getElementById('pull_price').checked = false;
                document.getElementById('pull_price').disabled = true;
                document.getElementById('booking_price').disabled = false;
                document.getElementById('rent_price').disabled = false;
                document.getElementById('apply_nsfas').disabled = false;
                document.getElementById('apply_bursary').disabled = false;
                document.getElementById('apply_cash').disabled = false;

                $('#booking_price').addClass('required');
                $('#rent_price').addClass('required');

            }
        })

        $("#property_id").on('change', function(){
            if($("#property_id").val() !== ""){

                let properties = <?php echo json_encode($propertyDetails); ?>;
                let rooms = <?php echo json_encode($roomDetails); ?>;
    
                $("#room_map").empty().append('<option value="">Please Select One</option>')
                properties.forEach(function (arrayItem){
                    if(arrayItem['propId'] == $("#property_id").val()){
                        rooms.forEach(function(roomItem){
                            if(roomItem['id'] == arrayItem['id']){
                                $("#room_map").append('<option value="'+roomItem['roomId']+'">'+roomItem['title']+'</option>')
                                // $("#room_map_price").append('<option value="'+roomItem['roomId']+'">'+roomItem['title']+'</option>')

                            }
                        })
                    }
                })
            }
            
        })

        $('#roomData').submit(function() {

            //Check if any hidden fields are required, remove required check
            $('.hidden .fieldError').removeClass('fieldError').val('');

            var req = 1;

            $('.required').each( function(){
                if($(this).val() === "" || $(this).val() === null){
                    req = 0

                    console.log($(this)[0]["id"])

                    $(this).addClass("fieldError")
                    id = $(this)[0]["id"]
                    $("#"+id+"_error").removeClass("hidden")
                }
            })

            if ($('.fieldDuplicate').length > 0) {
                req = 0;
            }

            if(document.getElementById('pull_price').checked == false && document.getElementById('apply_nsfas').checked == false && document.getElementById('apply_bursary').checked == false && document.getElementById('apply_cash').checked == false){
                $("#applies_to").removeClass("hidden")
                req = 0;
            }

            if(req == 0){
                Swal.fire(
                    'Blank Fields',
                    'Please fill in all Required Fields!',
                    'error'
                )

                return false;
            }else{
                let total = 1
                $('.fieldError').each( function(){
                    if($(this).val() === ""){
                        total = 0

                        $(this).addClass("fieldError")
                        id = $(this)[0]["id"]
                        $("#"+id+"_error").removeClass("hidden")
                    }
                })

                if(total == 0){
                    Swal.fire(
                        'Blank Fields',
                        'Please fill in all Required Fields!',
                        'error'
                    )

                    return false;
                }else{
                    return true;
                }
            }

            });

    })
</script>
@endsection