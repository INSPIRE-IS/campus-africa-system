@extends('layouts.dash')

@section('content')
<div class="container">
    <a href="/student-create-new-booking"><button type="button" class="newBookingBtn">
        Apply for {{$this_year}} now!
    </button></a>
    <br/>
    <hr/>
    <br/>
    @foreach($bookingList as $booking)
    @if(!$agent->isMobile())
      <div style="border:1px solid #ccc; margin:10px; height:250px; padding:15px;">
    @else
      <div style="border:1px solid #ccc; margin:10px; height:500px; padding:15px;">
    @endif
        <div class="appRow">
            <h1><u>{{$booking->year}}: {{$booking->booking_code}} @if($booking->cancelled == 1)  - CANCELLED @endif</u></h1>
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
    <br/><hr/><br/>
</div>


<div class="container">
    <div class="otherProperties">
        <h2 class="header">Want to explore our other properties?</h2>
        <br/><br/>

        <div class='demo-container'>
      <div class='carousel'>
        <input checked='checked' class='carousel__activator' id='carousel-slide-activator-1' name='carousel' type='radio'>
        <input class='carousel__activator' id='carousel-slide-activator-2' name='carousel' type='radio'>
        <input class='carousel__activator' id='carousel-slide-activator-3' name='carousel' type='radio'>
        <div class='carousel__controls'>
          <label class='carousel__control carousel__control--forward' for='carousel-slide-activator-2'>
          <i class="fas fa-chevron-right"></i>
          </label>
        </div>
        <div class='carousel__controls'>
          <label class='carousel__control carousel__control--backward' for='carousel-slide-activator-1'>
          <i class="fas fa-chevron-left"></i>
          </label>
          <label class='carousel__control carousel__control--forward' for='carousel-slide-activator-3'>
          <i class="fas fa-chevron-right"></i>
          </label>
        </div>
        <div class='carousel__controls'>
          <label class='carousel__control carousel__control--backward' for='carousel-slide-activator-2'>
          <i class="fas fa-chevron-left"></i>
          </label>
        </div>
        <div class='carousel__screen'>
          <div class='carousel__track'>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <a href="https://campusafrica.co.za/residences/49-jorissen/" target="_blank"><img src="/images/rooms/Group_6067.png" width="100%"/></a>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <a href="https://campusafrica.co.za/residences/49-jorissen/" target="_blank"><img src="/images/rooms/Group_5241.png" width="100%"/></a>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <a href="https://campusafrica.co.za/residences/80-jorissen/" target="_blank"><img src="/images/rooms/Group_6073.png" width="100%"/></a>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <a href="https://campusafrica.co.za/residences/braamlofts/" target="_blank"><img src="/images/rooms/Group_6072.png" width="100%"/></a>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <a href="https://campusafrica.co.za/residences/126-siemert/" target="_blank"><img src="/images/rooms/Group_6076.png" width="100%"/></a>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <a href="https://campusafrica.co.za/residences/ymca/" taget="_blank"><img src="/images/rooms/Group_6077.png" width="100%"/></a>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <a href="https://campusafrica.co.za/residences/wynton-joy/" target="_blank"><img src="/images/rooms/Group_6078.png" width="100%"/></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
    <br/><br/>
</div>




@endsection
