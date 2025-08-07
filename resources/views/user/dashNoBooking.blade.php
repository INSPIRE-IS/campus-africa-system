@extends('layouts.dash')

@section('content')
    <div class="container">
        <div style="display: flex; justify-content: flex-end; margin-bottom: 4px;margin-top: 12px;">
            <form method="GET" action="{{ route('home') }}" style="display: flex; align-items: center;">
                <label for="year" style="margin-right: 10px; font-weight: 600; font-size: 16px; color: #555;">
                    Select Year:
                </label>
                <select name="year" id="year" onchange="this.form.submit()"
                    style="padding: 10px 20px; border: 1px solid #ccc; border-radius: 8px; background-color: #eece5b; color: #333; font-size: 16px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);  width:240px;">
                    @foreach ($years as $year)
                        <option value="{{ $year->lookup_name }}"
                            style="font-size: 16px;font-weight: 400;line-height: 1.5;color: #000; text-align:center;"
                            {{ $selected_year == $year->lookup_name ? 'selected' : '' }}>
                            {{ $year->lookup_name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="contentSection">
            <a href="/student-create-new-booking">
                <button type="button" class="newBookingBtn">
                    Apply for {{ $this_year }} now!
                </button>
            </a>
            <br />
            <hr />
            <br />
            <div class="cards">
                @foreach ($bookingList as $booking)
                    <div class='customWhiteCard' style="float:none">
                        <div class="">
                            <h1>{{ $booking->year }} Booking</h1>
                            <p class="prevBookingTitle"><b>Booking:</b> {{ $booking->booking_code }}</p>
                            <p class="prevBookingTitle"><b>Property:</b>
                                @foreach ($buildingList as $building)
                                    @if ($booking->property == $building->id)
                                        {{ $building->residence }}
                                    @endif
                                    @endforeach - @foreach ($roomList as $room)
                                        @if ($booking->roomType == $room->id)
                                            {{ $room->name }}
                                        @endif
                                    @endforeach
                            </p>
                            <!-- <p class="prevBookingTitle"><b>Room:</b> </p> -->
                            <!-- <p class="prevBookingTitle"><b>Floor:</b> {{ $booking->floor }}</p> -->
                            <p class="prevBookingTitle"><b>Unit:</b> {{ $booking->bed_code }}</p>
                            <!-- <p class="prevBookingTitle"><b>Bed Code:</b> </p> -->
                            <p class="prevBookingTitle"><b>Booking Fee:</b> R {{ $booking->room_rate }}</p>
                            <p class="prevBookingTitle"><b>Lease From:</b> {{ $booking->lease_from_date }}</p>
                            <p class="prevBookingTitle"><b>Lease To:</b> {{ $booking->lease_to_date }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <br /><br />
        </div>
    </div>


    <div class="container">
        <div class="otherProperties">
            <h2 class="header">Want to explore our other properties?</h2>
            <br /><br />

            <div class='demo-container'>
                <div class='carousel'>
                    <input checked='checked' class='carousel__activator' id='carousel-slide-activator-1' name='carousel'
                        type='radio'>
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
                            <div
                                class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
                                <div class='demo-content'>
                                    <a href="https://campusafrica.co.za/residences/49-jorissen/" target="_blank"><img
                                            src="/images/rooms/Group_6067.png" width="100%" /></a>
                                </div>
                            </div>
                            <div
                                class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
                                <div class='demo-content'>
                                    <a href="https://campusafrica.co.za/residences/49-jorissen/" target="_blank"><img
                                            src="/images/rooms/Group_5241.png" width="100%" /></a>
                                </div>
                            </div>
                            <div
                                class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
                                <div class='demo-content'>
                                    <a href="https://campusafrica.co.za/residences/80-jorissen/" target="_blank"><img
                                            src="/images/rooms/Group_6073.png" width="100%" /></a>
                                </div>
                            </div>
                            <div
                                class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
                                <div class='demo-content'>
                                    <a href="https://campusafrica.co.za/residences/braamlofts/" target="_blank"><img
                                            src="/images/rooms/Group_6072.png" width="100%" /></a>
                                </div>
                            </div>
                            <div
                                class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
                                <div class='demo-content'>
                                    <a href="https://campusafrica.co.za/residences/126-siemert/" target="_blank"><img
                                            src="/images/rooms/Group_6076.png" width="100%" /></a>
                                </div>
                            </div>
                            <div
                                class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
                                <div class='demo-content'>
                                    <a href="https://campusafrica.co.za/residences/ymca/" taget="_blank"><img
                                            src="/images/rooms/Group_6077.png" width="100%" /></a>
                                </div>
                            </div>
                            <div
                                class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
                                <div class='demo-content'>
                                    <a href="https://campusafrica.co.za/residences/wynton-joy/" target="_blank"><img
                                            src="/images/rooms/Group_6078.png" width="100%" /></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br /><br />
    </div>
@endsection
