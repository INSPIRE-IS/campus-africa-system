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
                    <option value="<?php echo $r->id; ?>" <?php if(!empty($roomTypeData) && $r->id == $roomTypeData->property_id){ echo "selected='selected'";}  ?>><?php echo $r->residence; ?></option>
                <?php } ?>
            </select>            
        </div>

        <div class="form-field">
            <label>Room Type</label>
            <input type="text" name="name" id="name" value="<?php if(!empty($roomTypeData)){echo $roomTypeData->name;}?>">
        </div>

        <div class="form-field">
            <label>Website Room Map</label>
            <select name="room_map" id="room_map">
                <option value="">Please Select One</option>
            </select>
        </div>

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
            properties.forEach(function (arrayItem){
                if(arrayItem['propId'] == $("#property_id").val()){
                    rooms.forEach(function(roomItem){
                        if(roomItem['id'] == arrayItem['id']){
                            let preselected = "<?php if(!empty($roomTypeData) && $roomTypeData->room_map_slug != "" && $roomTypeData->room_map_slug != null){echo $roomTypeData->room_map_slug;}else {echo "";}?>";
                            let selected = preselected == roomItem['roomId'] ? "selected" : "";
                            $("#room_map").append('<option value="'+roomItem['roomId']+'" '+selected+'>'+roomItem['title']+'</option>')
                        }
                    })
                }
            })
        }

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
                            }
                        })
                    }
                })
            }
            
        })

    })
</script>
@endsection