@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/save-property" method="post" autocomplete="off">
        <br/><br/>

        <div class="form-field">
            <label>Residence *</label>
            <select name="residence" id='residence' class="required" onChange="changeResidence()">
                <option value="" selected disabled>Select One</option>
                @foreach($Residence as $residence)
                    <option value="{{strtoupper($residence->id)}}" <?php if(!empty($propertyData) && $propertyData->residence == $residence->id){echo "selected";}?>>{{$residence->residence}}</option>
                @endforeach
            </select>
            <input type="hidden" name="propertyId" id="propertyId" value="<?php if(!empty($propertyData)){echo $propertyData->id;}else{echo "0";}?>"/>
        </div>

        <div class="form-field">
            <label>Room Type *</label>
            <select name="roomType" id='roomType' class="required">
                <option value="" selected disabled>Select a Room Type</option>
            </select>

        </div>

        <div class="form-field">
            <label>Floor Prefix</label>
            <input type="text" name="meta_info_1" id="meta_info_1" value="<?php if(!empty($propertyData)){echo $propertyData->meta_info_1;}?>">
        </div>

        <div class="form-field">
            <label>Bed Code Prefix</label>
            <input type="text" name="meta_info_2" id="meta_info_2" value="<?php if(!empty($propertyData)){echo $propertyData->meta_info_2;}?>">
        </div>

        <div class="form-field">
            <label>Floor *</label>
            <select name="floor" id='floor' class="required">
                <option value="" selected disabled>Select One</option>
                <option value="BASEMENT" <?php if(!empty($propertyData) && ($propertyData->floor == "BASEMENT" || $propertyData->floor == "Basement")){echo "selected";}?>> <?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>Basement</option>
                <option value="GROUND" <?php if(!empty($propertyData) && ($propertyData->floor == "GROUND" || $propertyData->floor == "Ground")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>Ground</option>
                <option value="1ST" <?php if(!empty($propertyData) && ($propertyData->floor == "1ST" || $propertyData->floor == "1st")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>1st</option>
                <option value="2ND" <?php if(!empty($propertyData) && ($propertyData->floor == "2ND" || $propertyData->floor == "2nd")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>2nd</option>
                <option value="3RD" <?php if(!empty($propertyData) && ($propertyData->floor == "3RD" || $propertyData->floor == "3rd")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>3rd</option>
                <option value="4TH" <?php if(!empty($propertyData) && ($propertyData->floor == "4TH" || $propertyData->floor == "4th")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>4th</option>
                <option value="5TH" <?php if(!empty($propertyData) && ($propertyData->floor == "5TH" || $propertyData->floor == "5th")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>5th</option>
                <option value="6TH" <?php if(!empty($propertyData) && ($propertyData->floor == "6TH" || $propertyData->floor == "6th")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>6th</option>
                <option value="7TH" <?php if(!empty($propertyData) && ($propertyData->floor == "7TH" || $propertyData->floor == "7th")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>7th</option>
                <option value="8TH" <?php if(!empty($propertyData) && ($propertyData->floor == "8TH" || $propertyData->floor == "8th")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>8th</option>
                <option value="9TH" <?php if(!empty($propertyData) && ($propertyData->floor == "9TH" || $propertyData->floor == "9th")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>9th</option>
                <option value="10TH" <?php if(!empty($propertyData) && ($propertyData->floor == "10TH" || $propertyData->floor == "10th")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>10th</option>
                <option value="11TH" <?php if(!empty($propertyData) && ($propertyData->floor == "11TH" || $propertyData->floor == "11th")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>11th</option>
                <option value="12TH" <?php if(!empty($propertyData) && ($propertyData->floor == "12TH" || $propertyData->floor == "12th")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>12th</option>
                <option value="13TH" <?php if(!empty($propertyData) && ($propertyData->floor == "13TH" || $propertyData->floor == "13th")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>13th</option>
                <option value="14TH" <?php if(!empty($propertyData) && ($propertyData->floor == "14TH" || $propertyData->floor == "14th")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>14th</option>
                <option value="15TH" <?php if(!empty($propertyData) && ($propertyData->floor == "15TH" || $propertyData->floor == "15th")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>15th</option>
                <option value="16TH" <?php if(!empty($propertyData) && ($propertyData->floor == "16TH" || $propertyData->floor == "16th")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>16th</option>
                <option value="17TH" <?php if(!empty($propertyData) && ($propertyData->floor == "17TH" || $propertyData->floor == "17th")){echo "selected";}?>><?php if(!empty($propertyData) && ($propertyData->meta_info_1 !== "" && $propertyData->meta_info_1 !== null)){echo  $propertyData->meta_info_1." | ";}?>17th</option>
            </select>
        </div>

        <div class="form-field">
            <label>Unit *</label>
            <input type="text" name="unit" id="unit" value="<?php if(!empty($propertyData)){echo $propertyData->unit;}?>" class="required">
        </div>

        <div class="form-field">
            <label>Room</label>
            <select name="room" id="room">
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
            <input type="text" name="bed_code" id="bed_code" value="<?php if(!empty($propertyData)){echo $propertyData->bed_code;}?>" readonly>
        </div>

        <div class="form-field">
            <label>Inventory *</label>
            <select name="inventory" id='inventory' class="required">                
                <option value="1" <?php if(empty($propertyData) || $propertyData->inventory == "1"){echo "selected";}?>>Yes</option>
                <option value="0" <?php if(!empty($propertyData) && ($propertyData->inventory == "0" || $propertyData->inventory == null)){echo "selected";}?>>No</option>
            </select>
        </div>

        <!-- <div class="form-field">
            <label>2022 Rate</label>
            <input type="text" name="room_rate" id="room_rate" value="<?php if(!empty($propertyData)){echo $propertyData->room_rate;}?>">
        </div> -->

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

    <a style="width: 95% !important;height: 36px;border-radius: 5px;border-radius: 5px !important;border: none;padding: 10px;display: block;text-align: center; text-decoration: none;line-height: 16px;" href="/manage-properties" class="profileSubmit">Cancel</a>

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

    document.addEventListener("DOMContentLoaded", function(){
        changeResidence();
    })

    function changeResidence(){

        if($("#residence").val() !== null){
            $("#roomType").empty().append('<option value="" selected disabled>Loading</option>')

            $.ajax({
                type: "post",
                url: "/get-room-types-id",
                data: {residence: $("#residence").val()},
    
                success: function (data) {          
                    $("#roomType").empty().append('<option value="" selected disabled>Select a Room Type</option>')
                    data.forEach(function(index){
                        let selected = "";
                        if("<?php if($propertyData && $propertyData->room_type){echo $propertyData->room_type;}else{echo "";}?>" == index.id){
                            selected = "selected"
                        }
                        $("#roomType").append('<option value="'+index.id+'" '+selected+'>'+index.name+'</option>')
                    })
                },
            });
        }
    };

    $('#btn_submit_frm').on('click', function(e) {

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

        if(req == 0){
            Swal.fire(
                'Blank Fields',
                'Please fill in all Required Fields!',
                'error'
            )

            return false;
        }else{
            //
            let btn = $(this);
            $(btn).val('Saving...');
    
            Swal.fire({
                text: "Will this change apply to bookings in the current Academic Year (<?php echo $this_year; ?>) or the next Academic Year (<?php echo ($this_year+1); ?>) going forward?",
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
                } else {
                    $(btn).val('Update Bed');
                    return false;
                }
    
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
        }

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

    $("#unit").keyup(function(){
        $("#bed_code").val('').val($("#meta_info_2").val()+""+$("#unit").val()+""+$("#room").val()+""+$("#bed").val());
    })
    $("#room").on('change', function(){
        $("#bed_code").val('').val($("#meta_info_2").val()+""+$("#unit").val()+""+$("#room").val()+""+$("#bed").val());
    })
    $("#bed").on('change', function(){
        $("#bed_code").val('').val($("#meta_info_2").val()+""+$("#unit").val()+""+$("#room").val()+""+$("#bed").val());
    })
    $("#meta_info_2").on('change', function(){
        $("#bed_code").val('').val($("#meta_info_2").val()+""+$("#unit").val()+""+$("#room").val()+""+$("#bed").val());
    })

    $("#meta_info_1").on('change', function(){
        $("#floor").empty()
        $('#floor').append('<option value="" selected disabled>Select One</option>')
        $('#floor').append('<option value="BASEMENT">'+$("#meta_info_1").val()+' | BASEMENT</option>')
        $('#floor').append('<option value="GROUND">'+$("#meta_info_1").val()+' | Ground</option>')
        $('#floor').append('<option value="1ST">'+$("#meta_info_1").val()+' | 1st</option>')
        $('#floor').append('<option value="2ND">'+$("#meta_info_1").val()+' | 2nd</option>')
        $('#floor').append('<option value="3RD">'+$("#meta_info_1").val()+' | 3rd</option>')
        $('#floor').append('<option value="4TH">'+$("#meta_info_1").val()+' | 4th</option>')
        $('#floor').append('<option value="5TH">'+$("#meta_info_1").val()+' | 5th</option>')
        $('#floor').append('<option value="6TH">'+$("#meta_info_1").val()+' | 6th</option>')
        $('#floor').append('<option value="7TH">'+$("#meta_info_1").val()+' | 7th</option>')
        $('#floor').append('<option value="8TH">'+$("#meta_info_1").val()+' | 8th</option>')
        $('#floor').append('<option value="9TH">'+$("#meta_info_1").val()+' | 9th</option>')
        $('#floor').append('<option value="10TH">'+$("#meta_info_1").val()+' | 10th</option>')
        $('#floor').append('<option value="11TH">'+$("#meta_info_1").val()+' | 11th</option>')
        $('#floor').append('<option value="12TH">'+$("#meta_info_1").val()+' | 12th</option>')
        $('#floor').append('<option value="13TH">'+$("#meta_info_1").val()+' | 13th</option>')
        $('#floor').append('<option value="14TH">'+$("#meta_info_1").val()+' | 14th</option>')
        $('#floor').append('<option value="15TH">'+$("#meta_info_1").val()+' | 15th</option>')
        $('#floor').append('<option value="16TH">'+$("#meta_info_1").val()+' | 16th</option>')
        $('#floor').append('<option value="17TH">'+$("#meta_info_1").val()+' | 17th</option>')
    })
</script>

@endsection