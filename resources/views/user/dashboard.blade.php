@extends('layouts.dash')

@section('content')
<div class="container">
    <div class="cards">
        <div class="whiteYellowCard roomImgBack smallerCard" style="margin-right:6%">
            <h2 class="cardHeader greyCardHeader" style="line-height:65px;">Your Room</h2>

            <div class="overlay">
                <div class="cardBody">
                    <h4 class="cardSubHeaderGrey">{{$booking->property}}</h4>
                    <h2 class="cardHeaderText">{{$booking->roomType}}</h2>
                    <!-- <h4 class="cardSubHeaderGrey">Room 23 Floor 2</h4> -->
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

            @foreach ($allFiles as $file)

            

                @if($file->fileId == 'bankStatement' || $file->fileId == 'studentCard' || $file->fileId == 'proofRegistration' || $file->fileId == 'bursaryApproval')

                <div class="appAwait">
                  <h3 class="uploadName">

                    @if($file->fileId == 'copyID')
                    COPY OF ID DOCUMENT
                      @elseif($file->fileId == 'studentCard')
                        STUDENT CARD
                      @elseif($file->fileId == 'bankStatement')
                        BANK STATEMENT
                      @elseif($file->fileId == 'proofRegistration')
                      PROOF OF REGISTRATION
                      @elseif($file->fileId == 'proof_of_payment')
                        BOOKING FEE PROOF OF PAYMENT
                      @elseif($file->fileId == 'bursaryApproval')
                        @if(Auth::user()->studentType == "NSFAS")
                          NSFAS APPROVAL
                        @else
                          BURSARY APPROVAL
                        @endif
                      @elseif($file->fileId == 'moveIn')
                        LEASE
                    @endif

                    @if($file->status == "Rejected")

                        <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstanding">
                          <span class="text">Outstanding</span>
                          <i class="fas fa-file-upload" ></i>
                          <i class="fas fa-exclamation-circle"></i>
                      </label>
                      </h3>
                      <input type="file" id="{{$file->fileId}}" hidden onChange="UploadDocument('{{$file->fileId}}')"/>

                      <div class="invalid" id="studentCardInvalid">INVALID DOCUMENT - PLEASE REUPLOAD <i class="fas fa-exclamation-circle"></i></div>
                      
                    @elseif($file->status == "None")
                    <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstanding">
                          <span class="text">Outstanding</span>
                          <i class="fas fa-file-upload" ></i>
                          <i class="fas fa-exclamation-circle"></i>
                      </label>
                      </h3>
                      <input type="file" id="{{$file->fileId}}" hidden onChange="UploadDocument('{{$file->fileId}}')"/>
                    @elseif($file->status == "Uploaded")
                      <label id="registrationPending" class="pending">
                          <span class="text">Pending Approval</span>
                          <i class="fad fa-minus-circle"></i>
                      </label>
                    @elseif($file->status == "Approved")
                      <label class="approved">
                            <span class="text">Approved</span>
                            <i class="fas fa-check-circle"></i>
                        </label>
                    @endif

                    </div>

                @endif

                
                
            @endforeach 


        </div>
    </div>

    <div class="cards">
        <div class="whiteYellowCard smallerCard" style="margin-right:6%">
            <h2 class="cardHeader greyCardHeader" style="line-height:65px;">Booking Summary</h2>


            <div class="cardBody">
            
                <h4 class="cardSubHeaderGreyTxt">Student Type</h4>
                <h2 class="cardHeaderBlck" ><?php if(Auth::user()->studentType == "Cash / Direct"){echo "Cash";}else{echo Auth::user()->studentType;}?></h2>
                <br/>
                <h4 class="cardSubHeaderGreyTxt">Booking Reference</h4>
                <h2 class="cardHeaderBlck" >#{{$booking->id}}</h2>
                <br/>
                <h4 class="cardSubHeaderGreyTxt">Year of Study</h4>
                <h2 class="cardHeaderBlck" >{{$booking->year}}</h2>
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

            @foreach ($allFiles as $file)

                @if($file->fileId == 'proof_of_payment' || $file->fileId == 'moveIn' || $file->fileId == 'copyID' )

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
                            BOOKING FEE PROOF OF PAYMENT
                          @elseif($file->fileId == 'moveIn')
                            LEASE
                          @endif
                            

                          @if($file->status == "Rejected")

                          <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstanding">
                            <span class="text">Outstanding</span>
                            <i class="fas fa-file-upload" ></i>
                            <i class="fas fa-exclamation-circle"></i>
                          </label>
                          </h3>
                          <input type="file" id="{{$file->fileId}}" hidden onChange="UploadDocument('{{$file->fileId}}')"/>

                          <div class="invalid" id="studentCardInvalid">INVALID DOCUMENT - PLEASE REUPLOAD <i class="fas fa-exclamation-circle"></i></div>

                          @elseif($file->status == "None")
                          <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstanding">
                            <span class="text">Outstanding</span>
                            <i class="fas fa-file-upload" ></i>
                            <i class="fas fa-exclamation-circle"></i>
                          </label>
                          </h3>
                          <input type="file" id="{{$file->fileId}}" hidden onChange="UploadDocument('{{$file->fileId}}')"/>
                          @elseif($file->status == "Uploaded")
                          <label id="registrationPending" class="pending">
                            <span class="text">Pending Approval</span>
                            <i class="fad fa-minus-circle"></i>
                          </label>
                          @elseif($file->status == "Approved")
                          <label class="approved">
                              <span class="text">Approved</span>
                              <i class="fas fa-check-circle"></i>
                          </label>
                          @endif
                        </h3>
                    </div>
                @endif

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

<script>

  $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

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
