@extends('layouts.admin')

@section('content')


<div class="container">
    <a href="/create-new-booking/{{$user->id}}"><button type="button" class="newBookingBtn">
        Create a new booking for {{$this_year}}
    </button></a>
    <br/>
    <hr/>
    <br/>
    @foreach($bookingList as $booking)
    <div style="border:1px solid #ccc; margin:10px; height:250px; padding:15px;">
        <div class="appRow">
            <h1><u>{{$booking->year}}: {{$booking->booking_code}}</u></h1>
            <p><b>Property:</b> @foreach($buildingList as $building) @if($booking->property == $building->id) {{$building->residence}} @endif @endforeach</p>
            <p><b>Room:</b> @foreach($roomList as $room) @if($booking->roomType == $room->id) {{$room->name}} @endif @endforeach</p>
            <p><b>Floor:</b> {{$booking->floor}}</p>
            <p><b>Unit:</b> {{$booking->unit}}</p>
            <p><b>Bed Code:</b> {{$booking->bed_code}}</p>
            <p><b>Booking Fee:</b> {{$booking->room_rate}}</p>
            <p><b>Lease From:</b> {{$booking->lease_from_date}} {{$booking->lease_from_time}}</p>
            <p><b>Lease To:</b> {{$booking->lease_to_date}} {{$booking->lease_to_time}}</p>
        </div>
        <div class="appRow">
            <br/><br/>
            @foreach($filesList as $file)
                @if($file->year == $booking->year)
                    <p><b>{{$file->fileId}}</b> : {{$file->status}}</p>
                @endif
            @endforeach
        </div>
    </div>
    @endforeach
</div>

@endsection
