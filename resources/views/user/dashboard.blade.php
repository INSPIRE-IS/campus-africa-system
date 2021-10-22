@extends('layouts.dash')

@section('content')
<div class="container">
    <div class="cards">
        <div class="whiteYellowCard roomImgBack smallerCard" style="margin-right:6%">
            <h2 class="cardHeader greyCardHeader" style="line-height:65px;">Your Room</h2>

            <div class="overlay">
                <div class="cardBody">
                    <h4 class="cardSubHeaderGrey">Braamlofts</h4>
                    <h2 class="cardHeaderText">Single Room</h2>
                    <h4 class="cardSubHeaderGrey">Room 23 Floor 2</h4>
                    <br/><br/><br/>
                    <a href="#"><button type="button" class="roomContactBtn">Want to change your room? Contact Us</button></a>
                    <a href="#"><button type="button" class="roomContactBtn">Want to cancel your booking? Contact Us</button></a>
                </div>
            </div>
            
            
        </div>

        <div class="whiteYellowCard smallerCard"> 
            
            <h2 class="cardHeader redCardHeader">Application
                <div class="small"><span class="text">Awaiting Documents </span>
                    <div class="circle">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </h2>
                
            <div class="cardBody">

            @foreach ($rejectedFiles as $file)

                <div class="appAwait">
                    <h3 class="uploadName">

                    @if($file->fileId == 'copyID')
                        I.D COPY
                      @elseif($file->fileId == 'studentCard')
                        STUDENT CARD
                      @elseif($file->fileId == 'bankStatement')
                        BANK STATEMENT
                      @elseif($file->fileId == 'proofRegistration')
                        REGISTRATION
                      @elseif($file->fileId == 'proof_of_payment')
                        PROOF OF PAYMENT
                      @elseif($file->fileId == 'moveIn')
                        LEASE
                      @endif

                    <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstanding">
                        <span class="text">Outstanding</span>
                        <i class="fas fa-file-upload" ></i>
                        <i class="fas fa-exclamation-circle"></i>
                    </label>
                    </h3>
                    <input type="file" id="{{$file->fileId}}" hidden/>

                    <div class="invalid" id="studentCardInvalid">INVALID DOCUMENT - PLEASE REUPLOAD <i class="fas fa-exclamation-circle"></i></div>

                </div>

            @endforeach

            @foreach($outstanding as $file)

              <div class="appAwait">
                    <h3 class="uploadName">
                    @if($file == 'copyID')
                        I.D COPY
                      @elseif($file == 'studentCard')
                        STUDENT CARD
                      @elseif($file == 'bankStatement')
                        BANK STATEMENT
                      @elseif($file == 'proofRegistration')
                        REGISTRATION
                      @elseif($file == 'proof_of_payment')
                        PROOF OF PAYMENT
                      @elseif($file == 'moveIn')
                        LEASE
                      @endif

                    <label for="{{$file}}" id="{{$file}}Section" class="outstanding">
                        <span class="text">Outstanding</span>
                        <i class="fas fa-file-upload" ></i>
                        <i class="fas fa-exclamation-circle"></i>
                    </label>
                    </h3>
                    <input type="file" id="{{$file}}" hidden/>

                </div>
            
            @endforeach
                
                

                

                @foreach($pendingFiles as $file)

                <div class="appAwait" id="registrationSection">
                    <h3 class="uploadName">

                    @if($file->fileId == 'copyID')
                        I.D COPY
                      @elseif($file->fileId == 'studentCard')
                        STUDENT CARD
                      @elseif($file->fileId == 'bankStatement')
                        BANK STATEMENT
                      @elseif($file->fileId == 'proofRegistration')
                        REGISTRATION
                      @elseif($file->fileId == 'proof_of_payment')
                        PROOF OF PAYMENT
                      @elseif($file->fileId == 'moveIn')
                        LEASE
                      @endif

                    <label id="registrationPending" class="pending">
                        <span class="text">Pending Approval</span>
                        <i class="fad fa-minus-circle"></i>
                    </label>

                </div>

                @endforeach

                

            </div>

        </div>
    </div>

    <div class="cards">
        <div class="whiteYellowCard smallerCard" style="margin-right:6%">
            <h2 class="cardHeader greyCardHeader" style="line-height:65px;">Booking Summary</h2>


            <div class="cardBody">
            
                <h4 class="cardSubHeaderGreyTxt">Student Type</h4>
                <h2 class="cardHeaderBlck" >NSFAS</h2>
                <br/>
                <h4 class="cardSubHeaderGreyTxt">Booking Reference</h4>
                <h2 class="cardHeaderBlck" >#0000000000</h2>
                <br/>
                <h4 class="cardSubHeaderGreyTxt">Year of Study</h4>
                <h2 class="cardHeaderBlck" >2022</h2>
            </div>
            
            
            
        </div>
        <div class="whiteYellowCard smallerCard">
            
            <h2 class="cardHeader greenCardHeader">Booking
                <div class="small"><span class="text">Confirmed </span>
                    <div class="circle">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </h2>
                
            <div class="cardBody">

            @foreach ($approvedFiles as $file)

            <div class="appAwait" id="idCopySection">
                    <h3 class="uploadName">
                      @if($file->fileId == 'copyID')
                        I.D COPY
                      @elseif($file->fileId == 'studentCard')
                        STUDENT CARD
                      @elseif($file->fileId == 'bankStatement')
                        BANK STATEMENT
                      @elseif($file->fileId == 'proofRegistration')
                        REGISTRATION
                      @elseif($file->fileId == 'proof_of_payment')
                        PROOF OF PAYMENT
                      @elseif($file->fileId == 'moveIn')
                        LEASE
                      @endif
                        <label class="approved">
                            <span class="text">Approved</span>
                            <i class="fas fa-check-circle"></i>
                        </label>
                    </h3>
                </div>

            @endforeach

            </div>
        </div>
    </div>
</div>

<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>

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
                <img src="/images/rooms/Group_6067.png" width="100%"/>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <img src="/images/rooms/Group_5241.png" width="100%"/>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <img src="/images/rooms/Group_6073.png" width="100%"/>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <img src="/images/rooms/Group_6072.png" width="100%"/>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <img src="/images/rooms/Group_6076.png" width="100%"/>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <img src="/images/rooms/Group_6077.png" width="100%"/>
              </div>
            </div>
            <div class='carousel__item carousel__item--mobile-in-1 carousel__item--tablet-in-2 carousel__item--desktop-in-3'>
              <div class='demo-content'>
                <img src="/images/rooms/Group_6078.png" width="100%"/>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
</div>

<br/><br/>

<script>

$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

  $("#copyID").on('change', function(){
        $("#copyIDSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('copyID').files[0]);
        form_data.append("userUid", {{Auth::user()->id}});
        form_data.append("fileType", "copyID");

        $.ajax({
            type: "post",
            url: "/move-in-form",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                    window.location.reload();
                }
            },
        });
    });

    $("#studentCard").on('change', function(){
        $("#studentCardSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('studentCard').files[0]);
        form_data.append("userUid", {{Auth::user()->id}});
        form_data.append("fileType", "studentCard");

        $.ajax({
            type: "post",
            url: "/move-in-form",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                  window.location.reload();
                }
            },
        });  
    })

    $("#bankStatement").on('change', function(){
        $("#bankStatementSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('bankStatement').files[0]);
        form_data.append("userUid", {{Auth::user()->id}});
        form_data.append("fileType", "bankStatement");

        $.ajax({
            type: "post",
            url: "/move-in-form",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                  window.location.reload();
                }
            },
        });
    })

    $("#proofRegistration").on('change', function(){

        $("#proofRegistrationSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('proofRegistration').files[0]);
        form_data.append("userUid", {{Auth::user()->id}});
        form_data.append("fileType", "proofRegistration");

        $.ajax({
            type: "post",
            url: "/move-in-form",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                  window.location.reload();
                }
            },
        });
    })


    $("#proof_of_payment").on('change', function(){
        $("#proofRegistrationSection").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

        var form_data = new FormData();
        form_data.append("file", document.getElementById('proof_of_payment').files[0]);
        form_data.append("userUid", {{Auth::user()->id}});
        form_data.append("fileType", "proof_of_payment");

        $.ajax({
            type: "post",
            url: "/move-in-form",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data == "done") {
                  window.location.reload();
                }
            },
        });
    })
    </script>


@endsection
