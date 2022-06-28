@extends('layouts.dash')

@section('content')
<div class="container">
    <div class="cards">
        <div class="whiteYellowCard roomImgBack smallerCard" style="margin-right:6%">
            <h2 class="cardHeader greyCardHeader" style="line-height:65px;">Your Room</h2>

            <div class="overlay">
                <div class="cardBody">
                    <h4 class="cardSubHeaderGrey">{{$propertyData->residence}}</h4>
                    <h2 class="cardHeaderText">{{$roomData->name}}</h2>
                    <h4 class="cardSubHeaderGrey">
                      @if($booking->floor !== null) 
                        {{$booking->floor}} Floor 
                      @endif 
                      
                      @if($booking->unit !== null) 
                        , Unit {{$booking->unit}}
                      @endif

                      @if($booking->bed_code !== null) 
                        , Bed Code: {{$booking->bed_code}}
                      @endif
                      
                      </h4>

                    <br/><br/><br/>
                    @if(Auth::user()->rob_60_cancelled != '1')
                      <button type="button" class="roomContactBtn" onClick="changeRoom()">Want to change your residence?</button>
                    @endif
                    @if(Auth::user()->rob_55_lease != '2')
                      @if(Auth::user()->rob_60_cancelled != '1')
                        <button type="button" class="roomContactBtn" onClick="cancelBooking()">Want to cancel your booking?</button>
                      @else
                        <button type="button" class="roomContactBtn" onClick="restoreBooking()">Want to restore your booking?</button>
                      @endif
                    @endif
                </div>
            </div>
            
            
        </div>

        @if($bookHeader != '0')
          @include('user.documents.bookingDocs')
        @else
          @include('user.documents.applicationDocs')
        @endif

        
    </div>

    <div class="cards" id="bookingCardDash">

        <div class="whiteYellowCard smallerCard" style="margin-right:6%">
            <h2 class="cardHeader greyCardHeader" style="line-height:65px;">Booking Summary</h2>
            <div class="cardBody">
              <h4 class="cardSubHeaderGreyTxt">Student Type</h4>
              <h2 class="cardHeaderBlck" >
                <?php if(Auth::user()->studentType == "Cash / Direct"){echo "Cash";}else{echo Auth::user()->studentType;}?> 
                @if(Auth::user()->rob_55_lease != '2')
                  <br/> 
                  <a href="/change-student-type"><button type="button" class="detailsChange" id="studentChangeType" style="margin-top:-40px;">Change Student Type</button></a></h2>
                @else
                  <br/>
                @endif
              <br/>
              <h4 class="cardSubHeaderGreyTxt">Booking Reference</h4>
              <h2 class="cardHeaderBlck" >#{{$booking->booking_code}}</h2>
              <br/>
              <h4 class="cardSubHeaderGreyTxt">Year of Study</h4>
              <h2 class="cardHeaderBlck" >{{$booking->year}}</h2>
            </div> 
        </div>

        @if($bookHeader == '0')
          @include('user.documents.bookingDocs')
        @else
          @include('user.documents.applicationDocs')
        @endif
    </div>
</div>

<!-- <div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div> -->

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
                <a href="https://campusafrica.whitehart.co.za/residences/49-jorissen/" target="_blank"><img src="/images/rooms/Group_6067.png" width="100%"/></a>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <a href="https://campusafrica.whitehart.co.za/residences/49-jorissen/" target="_blank"><img src="/images/rooms/Group_5241.png" width="100%"/></a>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <a href="https://campusafrica.whitehart.co.za/residences/80-jorissen/" target="_blank"><img src="/images/rooms/Group_6073.png" width="100%"/></a>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <a href="https://campusafrica.whitehart.co.za/residences/braamlofts/" target="_blank"><img src="/images/rooms/Group_6072.png" width="100%"/></a>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <a href="https://campusafrica.whitehart.co.za/residences/126-siemert/" target="_blank"><img src="/images/rooms/Group_6076.png" width="100%"/></a>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <a href="https://campusafrica.whitehart.co.za/residences/ymca/" taget="_blank"><img src="/images/rooms/Group_6077.png" width="100%"/></a>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <a href="https://campusafrica.whitehart.co.za/residences/wynton-joy/" target="_blank"><img src="/images/rooms/Group_6078.png" width="100%"/></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
</div>

<br/><br/>

<div id="changeBuildingModal" class="modal">
    <div class="modal-content " id="changeBuildingHeight" style="height: 275px;">
        <span class="closeBuilding" style="float:right; font-size:25px; cursor:pointer">&times;</span>
        <div class="form-card">
            <h2>Restore this booking</h2>

            <input type="hidden" id="bookingId"/>
            <br/><br/>
            <div class="form-field">
                <label>Residence * <span id="propertyBuilding_error" class="hidden"><small style="color:red">This field is required</small></span></label>
                <select name="propertyBuilding" id='propertyBuilding' class="required">
                    <option value="" selected disabled>Select a Residence</option>
                </select>
            </div>

            <div class="form-field">
                <label>Room Type * <span id="roomTypeBuilding_error" class="hidden"><small style="color:red">This field is required</small></span></label>
                <select name="roomTypeBuilding" id='roomTypeBuilding' class="required">
                    <option value="" selected disabled>Select a Room Type</option>
                </select>
            </div>
            <div class="form-field-big">
                <button type="button" id="changeBuildingType" class="profileSubmit" style="width:98%; border:none; padding:12px; border-radius:10px;">Restore Booking</button>
            </div>
        </div>
    </div>
</div>

<div id="changeRoomModal" class="modal">
    <div class="modal-content " id="changeBuildingHeight" style="height: 275px;">
        <span class="closeRoom" style="float:right; font-size:25px; cursor:pointer">&times;</span>
        <div class="form-card">
            <h2>Change my Room</h2>

            <input type="hidden" id="bookingId"/>
            <br/><br/>
            <div class="form-field">
                <label>Residence * <span id="propertyRoom_error" class="hidden"><small style="color:red">This field is required</small></span></label>
                <select name="propertyRoom" id='propertyRoom' class="required">
                    <option value="" selected disabled>Select a Residence</option>
                </select>
            </div>

            <div class="form-field">
                <label>Room Type * <span id="roomTypeRoom_error" class="hidden"><small style="color:red">This field is required</small></span></label>
                <select name="roomTypeRoom" id='roomTypeRoom' class="required">
                    <option value="" selected disabled>Select a Room Type</option>
                </select>
            </div>
            <div class="form-field-big">
                <button type="button" id="changeRoomType" class="profileSubmit" style="width:98%; border:none; padding:12px; border-radius:10px;">Change Room</button>
            </div>
        </div>
    </div>
</div>

<script>

  $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var modalRestore = document.getElementById("changeBuildingModal");
    var spanRestore = document.getElementsByClassName("closeBuilding")[0];

    spanRestore.onclick = function() {
      modalRestore.style.display = "none";
    }

    var modalRoom = document.getElementById("changeRoomModal");
    var spanRoom = document.getElementsByClassName("closeRoom")[0];

    spanRoom.onclick = function() {
      modalRoom.style.display = "none";
    }

    function cancelBooking(bookingId, userId){

      Swal.fire({
        title: 'Why do you want to Cancel your booking?',
        input: 'text',
        inputAttributes: {
          autocapitalize: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Submit',
        showLoaderOnConfirm: true,
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
              type: "post",
              url: "/cancel/booking",
              data: {
                  bookingId: "{{Auth::user()->bookingRef}}", 
                  userId: "{{Auth::user()->id}}",
                  reason: result.value,
              },

              success: function (data) {
                  window.location.reload();
              },
          });
        }
      })

    }

    $("#changeBuildingType").on('click', function(){
        if($("#propertyBuilding").val() == null || $("#roomTypeBuilding").val() == null){
            if($("#propertyBuilding").val() == null){
                $("#propertyBuilding").addClass('fieldError')
                $("#propertyBuilding_error").removeClass('hidden')
            }
            if($("#roomTypeBuilding").val() == null){
                $("#roomTypeBuilding").addClass('fieldError')
                $("#roomTypeBuilding_error").removeClass('hidden')
            }
        }else{

            $("#changeBuildingType").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
            
            $.ajax({
                type: "post",
                url: "/restore-student-booking",
                data: {
                    userUid: "{{Auth::user()->id}}", 
                    property: $("#propertyBuilding").val(),
                    roomType: $("#roomTypeBuilding").val(), 
                },

                success: function (data) {
                    window.location.reload();
                }
            });
        }
    })

    
    $("#changeRoomType").on('click', function(){
        if($("#propertyRoom").val() == "" || $("#roomTypeRoom").val() == ""){
            if($("#propertyRoom").val() == ""){
                $("#propertyRoom").addClass('fieldError')
                $("#propertyRoom_error").removeClass('hidden')
            }
            if($("#roomTypeRoom").val() == ""){
                $("#roomTypeRoom").addClass('fieldError')
                $("#roomTypeRoom_error").removeClass('hidden')
            }
        }else{

          Swal.fire({
            title: 'Are you sure?',
            text: "You will have to pay a new booking fee if you switch residences",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes!'
          }).then((result) => {
            console.log(result)
              if (result.isConfirmed) {
                $("#changeRoomType").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
            
                $.ajax({
                    type: "post",
                    url: "/change-student-room",
                    data: {
                        userUid: "{{Auth::user()->id}}", 
                        property: $("#propertyRoom").val(),
                        roomType: $("#roomTypeRoom").val(), 
                    },

                    success: function (data) {
                        // 
                        Swal.fire(
                          'Your Booking Reference has changed',
                          'Your new Booking Reference is <b>'+data.booking_code+'<b>',
                          'success'
                        ).then((result) => {
                          if (result.isConfirmed) {
                            window.location.reload();
                          }
                        })
                    }
                });
              }else{
                var modal = document.getElementById("changeRoomModal");
                modal.style.display = "none";
              }
          });

            
        }
    })

    $("#propertyBuilding").on('change', function(){
        let propValue = $("#propertyBuilding").val();

        //here
        $("#roomTypeBuilding").empty().append('<option value="">Loading</option>');

        $.ajax({
            type: "get",
            url: "/check-all-rooms/{{Auth::user()->id}}",
            success: function (data) {
                //

                console.log(data)
                let roomData = data[3]

                $("#roomTypeBuilding").empty().append('<option value="">Select One</option>');
                Object.keys(roomData).forEach( function(key) {
                    if(propValue == roomData[key].residence){
                        var selected = "<?php echo $booking->roomType;?>" == roomData[key].room_type.id ? "selected" : "";
                        $("#roomTypeBuilding").append('<option value="'+roomData[key].room_type.id+'" '+selected+'>'+roomData[key].room_type.name+'</option>');
                    }
                });
            }
        })
    })

    function restoreBooking(bookingId, userId){

       
      Swal.fire('To restore your original booking', 'Please WhatsApp the team <a target="_blank" href="https://wa.me/27719991246?text=Ref%20<?php echo $booking->booking_code; ?>%20%7C%20<?php echo str_replace(" ", "%20", $propertyData->residence); ?>%20%7C%20<?php echo str_replace(" ", "%20", $roomData->name);?>%0A%0A%0A%0A">here</a>', 'question')

        // var modal = document.getElementById("changeBuildingModal");
        // modal.style.display = "block";

        // $.ajax({
        //     type: "get",
        //     url: "/check-all-rooms/{{Auth::user()->id}}",
        //     success: function (data) {
        //         let properties = data[0];

        //         console.log(data)

        //         let selProp = data[2].property;

        //         properties.sort();
        //         $('#propertyBuilding').empty()

        //         Object.keys(properties).forEach( function(key) {
        //             $('#propertyBuilding').append('<option value="'+properties[key].id+'">'+properties[key].residence+'</option>')
        //         });

        //         $('#propertyBuilding').val(selProp);

        //         $("#propertyBuilding").change();
        //     }
        // });
      }

      $("#propertyRoom").on('change', function(){
        let propValue = $("#propertyRoom").val();

        $("#roomTypeRoom").empty().append('<option value="">Loading</option>');

        $.ajax({
            type: "get",
            url: "/check-all-rooms/{{Auth::user()->id}}",
            success: function (data) {

                let roomData = data[3]

                $("#roomTypeRoom").empty().append('<option value="">Select One</option>');
                Object.keys(roomData).forEach( function(key) {
                    if(propValue == roomData[key].residence){
                        var selected = "<?php echo $booking->roomType;?>" == roomData[key].room_type.id ? "selected" : "";
                        $("#roomTypeRoom").append('<option value="'+roomData[key].room_type.id+'" '+selected+'>'+roomData[key].room_type.name+'</option>');
                    }
                });
            }
        })
      })

      function changeRoom(bookingId, userId){

        var modal = document.getElementById("changeRoomModal");
        modal.style.display = "block";

        $.ajax({
            type: "get",
            url: "/check-all-rooms/{{Auth::user()->id}}",
            success: function (data) {
                let properties = data[0];

                let selProp = data[2].property;

                properties.sort();
                $('#propertyRoom').empty()
                $('#propertyRoom').append('<option value="" selected disabled>Select a Residence</option>');

                Object.keys(properties).forEach( function(key) {
                  if(parseFloat("{{$booking->property}}") !== properties[key].id){
                    $('#propertyRoom').append('<option value="'+properties[key].id+'">'+properties[key].residence+'</option>');
                  }
                });

                // $('#propertyRoom').val(selProp);

                // $("#propertyRoom").change();
            }
        });
      }

        

        // Swal.fire({
        //   title: 'Select field validation',
        //   input: 'select',
        //   inputOptions: {
        //     'Fruits': {
        //       apples: 'Apples',
        //       bananas: 'Bananas',
        //       grapes: 'Grapes',
        //       oranges: 'Oranges'
        //     },
        //     'Vegetables': {
        //       potato: 'Potato',
        //       broccoli: 'Broccoli',
        //       carrot: 'Carrot'
        //     },
        //     'icecream': 'Ice cream'
        //   },
        //   inputPlaceholder: 'Select a fruit',
        //   showCancelButton: true,
        //   inputValidator: (value) => {
        //     return new Promise((resolve) => {
        //       if (value === 'oranges') {
        //         resolve()
        //       } else {
        //         resolve('You need to select oranges :)')
        //       }
        //     })
        //   }
        // })


        // Swal.fire({
        //     title: 'Are you sure?',
        //     text: "This booking will become active again!",
        //     icon: 'warning',
        //     showCancelButton: true,
        //     confirmButtonColor: '#3085d6',
        //     cancelButtonColor: '#d33',
        //     confirmButtonText: 'Yes, activate it!'
        // }).then((result) => {
        //     if (result.isConfirmed) {
        //         $.ajax({
        //             type: "post",
        //             url: "/restore/booking",
        //             data: {
        //                 bookingId: bookingId, 
        //                 userId: userId,
        //             },

        //             success: function (data) {
        //                 window.location = "/applications";
        //             },
        //         });
        //     }
        // })
    // }

    function UploadDocument(field){

        $("#" + field + "Section").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        let userId = "<?php echo Auth::user()->id;?>";

        var form_data = new FormData();
        form_data.append("file", document.getElementById(field).files[0]);
        form_data.append("userUid", userId);
        form_data.append("fileType", field);
        form_data.append("adminName", "{{Auth::user()->userFirstName}}");

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0]> 0) {
                    
                    window.location.reload();

                } else {
                    Swal.fire(
                        'Error',
                        data[0],
                        'error'
                    )
                    $("#" + field + "Section").empty().append('<span class="text">Upload</span>  <i class="fas fa-file-upload" ></i>');
                }
            },
        });
    }

 
</script>


@endsection
