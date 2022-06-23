@extends('layouts.admin')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<style>
    #level1Summary{
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    td{
        text-align:center;
        padding: 8px 10px;
        border-top: 1px solid #ccc;
    }
    .clickable{
        text-align:left; 
        cursor:pointer
    }
    i{
        padding-right:10px; 
        color:blue;
    }
</style>

<div class="container">
    <br/>
    <table id="level1Summary" class="display">
        <tr>
            <th style="text-align:left;">Residence</th>
            <th>Total Beds</th>
            <th>Total Bookings</th>
            <th>Booking Percent</th>
            <th>Allocated</th>
            <th>Waitlisted</th>
            <th>Online</th>
        </tr>
        @foreach($summaryData as $data)
            @if($data['total_beds'] > 0)
            
                <tr>
                    <td class="clickable" onClick="showData('<?php echo str_replace(" ", "_", $data['building_name']); ?>')">
                        <i class="fas fa-angle-down" id="<?php echo str_replace(" ", "_", $data['building_name']);?>_down"></i>
                            <i class="fas fa-angle-up hidden" id="<?php echo str_replace(" ", "_", $data['building_name']);?>_up"></i>
                        {{$data['building_name']}}
                    </td>
                    <td>{{$data['total_beds']}}</td>
                    <td>{{$data['total_bookings']}}</td>
                    <td style="background-color:<?php if($data['booking_percent'] <= 100){echo '#99f095';}elseif($data['booking_percent'] > 100 && $data['booking_percent'] <= 110){echo '#f7ff69';}else{echo '#f77b74';}?>">{{$data['booking_percent']}} %</td>
                    <td>{{$data['beds_allocated']}}</td>
                    <td>{{$data['beds_waitlisted']}}</td>
                    <td></td>
                </tr>
                    @foreach($data['room_type_data'] as $room)
                        @if($room['room_beds'] > 0)
                            <tr class="hidden <?php echo str_replace(" ", "_", $data['building_name']);?>" style="background-color: lightgray;">
                                <td style="text-align:left;">{{$room['room_type']}}</td>
                                <td>{{$room['room_beds']}}</td>
                                <td>{{$room['room_total_bookings']}}</td>
                                <td style="background-color:<?php if($room['room_booking_percent'] <= 100){echo '#99f095';}elseif($room['room_booking_percent'] > 100 && $room['room_booking_percent'] <= 110){echo '#f7ff69';}else{echo '#f77b74';}?>">{{$room['room_booking_percent']}} %</td>
                                <td>{{$room['room_allocated']}}</td>
                                <td>{{$room['room_waitlisted']}}</td>
                                
                                <td>
                                    <span id="{{$room['room_id']}}_text" class="{{$room['room_online']}}">@if($room['room_online'] == '1') Yes @else No @endif</span> 
                                    <?php if(Auth::user()->role_toggle_online_booking == '1'){?>
                                        <button type="button" id="{{$room['room_id']}}_button" onClick="changeRoom('{{$room['room_id']}}')" class='detailsChange' style="margin:0px;">Change</button>
                                    <?php } ?>
                                </td>
                                
                            </tr>
                        @endif
                    @endforeach
            @endif
           
        @endforeach
        <tr>
            <td style="text-align:left; border-top:2px solid #000"><strong>TOTAL</strong></td>
            <td style="border-top:2px solid #000"><strong>{{$total['beds']}}</strong></td>
            <td style="border-top:2px solid #000"><strong>{{$total['bookings']}}</strong></td>
            <td style="border-top:2px solid #000"><strong>{{$total['percent']}} %</strong></td>
            <td style="border-top:2px solid #000"><strong>{{$total['allocated']}}</strong></td>
            <td style="border-top:2px solid #000"><strong>{{$total['waitlisted']}}</strong></td>
            <?php if(Auth::user()->role_toggle_online_booking == '1'){?>
                <td style="border-top:2px solid #000"></td>
            <?php } ?>
        </tr>
    </table>

    <br/><br/>

</div>

<script>

    function showData(buildingName){

        let displayData = $('#'+buildingName+"_up").hasClass('hidden')

        $('.'+buildingName).each( function(){
            if(displayData == true){
                $('.'+buildingName).removeClass('hidden')
            }else{
                $('.'+buildingName).addClass('hidden')
            }
        });

        if(displayData == true){
            $('#'+buildingName+"_down").addClass('hidden')
            $('#'+buildingName+"_up").removeClass('hidden')
        }else{
            $('#'+buildingName+"_down").removeClass('hidden')
            $('#'+buildingName+"_up").addClass('hidden')
        }
    }

    function changeRoom(roomID){

        let roomAvailableText = $("#"+roomID+"_text").hasClass('1') == true ? "Yes" : "No";

        if(roomAvailableText == "Yes"){
            var text = "You are about to disable this room type";
        }else{
            var text = "You are about to activate this room type";
        }


        Swal.fire({
            title: 'Are you sure?',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#"+roomID+"_button").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                $.ajax({
                    type: "post",
                    url: "/update/room-activation",
                    data: {roomAvailableText: roomAvailableText, roomID: roomID},

                    success: function (data) {
                        $("#"+roomID+"_button").empty().append('Change');
                        let roomAvailableText = $("#"+roomID+"_text").hasClass('1') == true ? "Yes" : "No";

                        if(roomAvailableText == "Yes"){
                            $("#"+roomID+"_text").removeClass('1').addClass('0')
                            $("#"+roomID+"_text").empty().append("No")
                        }else{
                            $("#"+roomID+"_text").removeClass('0').addClass('1')
                            $("#"+roomID+"_text").empty().append("Yes")
                        }

                        Swal.fire(
                            'Success',
                            'Room Type Updated',
                            'success'
                        )
                    },
                });
            }
        })
    }

</script>



@endsection