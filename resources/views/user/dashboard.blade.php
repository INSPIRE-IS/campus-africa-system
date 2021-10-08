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
                
                <div class="appAwait" id="studentCardSection">
                    <h3 class="uploadName">STUDENT CARD

                    <label for="studentCard" id="studentCardUpload" class="outstanding">
                        <span class="text">Outstanding</span>
                        <i class="fas fa-file-upload" ></i>
                        <i class="fas fa-exclamation-circle"></i>
                    </label>
                    </h3>
                    <input type="file" id="studentCard" hidden/>

                    <div class="invalid" id="studentCardInvalid">INVALID DOCUMENT - PLEASE REUPLOAD <i class="fas fa-exclamation-circle"></i></div>

                </div>

                <div class="appAwait" id="nsfasApprovalSection">
                    <h3 class="uploadName">NSFAS APPROVAL

                    <label for="nsfasApproval" id="nsfasApprovalUpload" class="outstanding">
                        <span class="text">Outstanding</span>
                        <i class="fas fa-file-upload" ></i>
                        <i class="fas fa-exclamation-circle"></i>
                    </label>
                    </h3>
                    <input type="file" id="nsfasApproval" hidden/>

                    <div class="invalid hidden" id="nsfasApprovalInvalid">INVALID DOCUMENT - PLEASE REUPLOAD <i class="fas fa-exclamation-circle"></i></div>

                </div>

                <div class="appAwait" id="registrationSection">
                    <h3 class="uploadName">REGISTRATION

                    <label for="registration" id="registrationUpload" class="outstanding hidden">
                        <span class="text">Outstanding</span>
                        <i class="fas fa-file-upload" ></i>
                        <i class="fas fa-exclamation-circle"></i>
                    </label>
                    </h3>
                    <input type="file" id="registration" hidden/>

                    <div class="invalid hidden" id="registrationInvalid">INVALID DOCUMENT - PLEASE REUPLOAD <i class="fas fa-exclamation-circle"></i></div>

                    <label id="registrationPending" class="pending">
                        <span class="text">Pending Approval</span>
                        <i class="fad fa-minus-circle"></i>
                    </label>

                </div>

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

                <div class="appAwait" id="idCopySection">
                    <h3 class="uploadName">I.D COPY
                        <label class="approved">
                            <span class="text">Approved</span>
                            <i class="fas fa-check-circle"></i>
                        </label>
                    </h3>
                </div>
                
                <div class="appAwait hidden" id="studentCardSection">
                    <h3 class="uploadName">STUDENT CARD
                        <label class="approved">
                            <span class="text">Approved</span>
                            <i class="fas fa-check-circle"></i>
                        </label>
                    </h3>
                </div>

                <div class="appAwait hidden" id="bankStatementSection">
                    <h3 class="uploadName">3 MONTHS BANK STATEMENT
                        <label class="approved">
                            <span class="text">Approved</span>
                            <i class="fas fa-check-circle"></i>
                        </label>
                    </h3>
                </div>

                <div class="appAwait" id="registrationSection">
                    <h3 class="uploadName">REGISTRATION
                        <label class="approved">
                            <span class="text">Approved</span>
                            <i class="fas fa-check-circle"></i>
                        </label>
                    </h3>
                </div>

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


@endsection
