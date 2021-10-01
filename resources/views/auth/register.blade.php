@extends('layouts.app')

@section('content')

<div class="yellowGradientSmall">
    <div class="container">

        <nav class="navBar">
            <div class="leftMenu">
                <img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91">
            </div>

            <div class="rightMenu">
                
                <button type="button" class="contactButton">contact us</button>
                <button type="button" class="menuButton">Menu <i class="fa fa-bars"></i></button>
            </div>
        </nav>

        <div class="loginHeader">
            <h1 class="booking">Online Application</h1>
        </div>
    
    </div>
</div>

<div class="container">
    
        <!-- MultiStep Form -->
        <div class="container-fluid" id="grad1">
            <div class="row justify-content-center mt-0">
                <div class="col-11 col-sm-9 col-md-7 col-lg-6 text-center p-0 mt-3 mb-2">
                    <div class="card px-0 pt-4 pb-0 mt-3 mb-3">
                        <div class="row">
                            <div class="col-md-12 mx-0">
                                <form id="msform">
                                    <!-- progressbar -->
                                    <ul id="progressbar">
                                        <li class="active" id="applicant"></li>
                                        <li id="sponsor"></li>
                                        <li id="nextKin"></li>
                                        <li id="upload"></li>
                                        <li id="booking"></li>
                                    </ul> <!-- fieldsets -->
                                    <fieldset>
                                        <div class="form-card">
                                            <h2 class="fs-title">Applicant Details</h2>
                                            <br/>

                                            <div class="form-field">
                                                <label>First Name</label>
                                                <input type="text" name="firstName">
                                            </div>

                                            <div class="form-field">
                                                <label>Last Name</label>
                                                <input type="text" name="lastName">
                                            </div>

                                            <div class="form-field">
                                                <label>Title</label>
                                                <select name="title">
                                                    <option value="1">Mr</option>
                                                    <option value="2">Ms</option>
                                                    <option value="3">Mrs</option>
                                                    <option value="4">Miss</option>
                                                    <option value="5">Dr</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Date of Birth</label>
                                                <input type="date" name="dob">
                                            </div>

                                            <div class="form-field">
                                                <label>Cellphone Number</label>
                                                <input type="text" name="cell_no">
                                            </div>

                                            <div class="form-field">
                                                <label>Email Address</label>
                                                <input type="email" name="email">
                                            </div>

                                            <div class="form-field">
                                                <label>I.D Number</label>
                                                <input type="text" name="idNumber">
                                            </div>

                                            <div class="form-field">
                                                <label>Nationality</label>
                                                <select name="nationality">
                                                    <option value="1">South African</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Passport Number</label>
                                                <input type="text" name="passportNo">
                                            </div>

                                            <div class="form-field">
                                                <label>Institution of Study</label>
                                                <select name="institStudy">
                                                    <option value="1">Choose one</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Student Number</label>
                                                <input type="text" name="studNumber">
                                            </div>

                                            <div class="form-field">
                                                <label>Insitution Campus</label>
                                                <select name="institCampus">
                                                    <option value="1">Choose one</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Faculty</label>
                                                <select name="faculty">
                                                    <option value="1">Choose one</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Year</label>
                                                <select name="year">
                                                    <option value="1">2022</option>
                                                    <option value="2">2021</option>
                                                    <option value="3">2020</option>
                                                    <option value="4">2019</option>
                                                    <option value="5">2018</option>
                                                    <option value="6">2017</option>
                                                </select>
                                            </div>
                                            
                                            
                                        </div> 
                                        <input type="button" name="next" class="next action-button" value="proceed To Next Step" />
                                    </fieldset>
                                    <fieldset>
                                        <div class="form-card">
                                            <h2 class="fs-title">Sponsor / Burser Information</h2>
                                            <h4 class="fs-subtitle">Parent / guardians / sponsor details</h4>
                                            <br/>

                                            <div class="form-field">
                                                <label>First Name</label>
                                                <input type="text" name="pFirstName">
                                            </div>

                                            <div class="form-field">
                                                <label>Last Name</label>
                                                <input type="text" name="pLastName">
                                            </div>

                                            <div class="form-field">
                                                <label>Cellphone Number</label>
                                                <input type="text" name="pCell_no">
                                            </div>

                                            <div class="form-field">
                                                <label>Email Address</label>
                                                <input type="email" name="pEmail">
                                            </div>

                                            <div class="form-field">
                                                <label>I.D Number</label>
                                                <input type="text" name="pIdNumber">
                                            </div>

                                            <div class="form-field">
                                                <br/>
                                            </div>

                                            <h4 class="fs-subtitle">Bursar Details</h4>
                                            <br/>

                                            <div class="form-field">
                                                <label>Company Name</label>
                                                <input type="text" name="bCompanyName">
                                            </div>

                                            <div class="form-field">
                                                <label>Registration Number</label>
                                                <input type="text" name="bRegistrationNo">
                                            </div>

                                            <div class="form-field">
                                                <label>Contact Person</label>
                                                <input type="text" name="bContactPerson">
                                            </div>

                                            <div class="form-field">
                                                <label>Contact Number</label>
                                                <input type="text" name="bContactNumber">
                                            </div>

                                            <div class="form-field">
                                                <label>Email Address</label>
                                                <input type="email" name="bEmail">
                                            </div>

                                            <div class="form-field">
                                                <br/>
                                            </div>
                                            
                                        </div>
                                        
                                        <input type="button" name="previous" class="previous action-button-previous" value="Back to Previous Step" />
                                        <input type="button" name="next" class="next action-button" value="Proceed to Next Step" />
                                    </fieldset>
                                    <fieldset>
                                        <div class="form-card">
                                            <h2 class="fs-title">Next of Kin</h2>
                                            <br/>

                                            <div class="form-field">
                                                <label>First Name</label>
                                                <input type="text" name="kFirstName">
                                            </div>
                                            <div class="form-field">
                                                <label>Last Name</label>
                                                <input type="text" name="kLastName">
                                            </div>

                                            <div class="form-field">
                                                <label>Title</label>
                                                <select name="kTitle">
                                                    <option value="1">Mr</option>
                                                    <option value="2">Ms</option>
                                                    <option value="3">Mrs</option>
                                                    <option value="4">Miss</option>
                                                    <option value="5">Dr</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>I.D Number</label>
                                                <input type="text" name="kIdNumber">
                                            </div>

                                            <div class="form-field-big">
                                                <label>Physical Address</label>
                                                <input type="text" placeholder="Street No" name="kAddress1">
                                            </div>
                                            <div class="form-field-big">
                                                <input type="text" placeholder="Suburb" name="kAddress2">
                                            </div>
                                            <div class="form-field">
                                                <input type="text" placeholder="City" name="kAddressCity">
                                            </div>
                                            <div class="form-field">
                                                <input type="text" placeholder="postal Code" name="kAddressPostal">
                                            </div>

                                            <div class="form-field">
                                                <label>Relationship</label>
                                                <input type="text" name="kRelationship">
                                            </div>

                                            <div class="form-field">
                                                <label>Telephone Number</label>
                                                <input type="text" name="kTeleNo">
                                            </div>

                                            <div class="form-field">
                                                <label>Cellphone Number</label>
                                                <input type="text" name="kCellNo">
                                            </div>

                                            <div class="form-field">
                                                <label>Work Number</label>
                                                <input type="text" name="kWorkNo">
                                            </div>

                                            <div class="form-field">
                                                <label>Email Address</label>
                                                <input type="email" name="kEmail">
                                            </div>

                                            <div class="form-field">
                                                <br/>
                                            </div>
                                            
                                        </div> 
                                        
                                        <input type="button" name="previous" class="previous action-button-previous" value="Back to previous step" /> 
                                        <input type="button" name="next" class="next action-button" value="Proceed to Next Step" />
                                        <!-- <input type="button" name="make_payment" class="next action-button" value="Confirm" /> -->
                                    </fieldset>
                                    <fieldset>
                                        <div class="form-card">

                                            <div class="form-field-app" style="width:100%; text-align:center; display:flex; justify-content: center;">
                                                <label style="font-size:12px; line-height:38px;">Application Type &nbsp;&nbsp;&nbsp;&nbsp;</label>
                                                <select name="kTitle" style="width:30% !important">
                                                    <option value="1">Cash</option>
                                                </select>
                                            </div>

                                            <br/><br/>

                                            <div>
                                                <div class="cards">
                                                    <div class="whiteYellowCard" style="margin-right:30px">
                                                        <h2 class="cardHeader">Booking<br/>Documents</h2>

                                                        <div class="cardBody">
                                                            <div class="uploadSection">
                                                                <h3 class="uploadName">I.D COPY</h3>
                                                                <div class="uploadFileSection">
                                                                    <i class="fas fa-file-upload"></i>
                                                                    <input type="file" id="copyID" hidden/>
                                                                    <label for="copyID" class="uploadLabel">Choose file</label>
                                                                    
                                                                </div>
                                                            </div>

                                                            <br/>
                                                            
                                                            <div class="uploadSection">
                                                                <h3 class="uploadName">BOOKING FEE PROOF OF PAYMENT</h3>
                                                                <div class="uploadFileSection">
                                                                    <i class="fas fa-file-upload"></i>
                                                                    <input type="file" id="copyID" hidden/>
                                                                    <label for="copyID" class="uploadLabel">Choose file</label>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        
                                                    </div>

                                                    <div class="whiteYellowCard">
                                                        <h2 class="cardHeader" style="height:80px;">Application</h2>
                                                        
                                                        <div class="cardBody">
                                                            <div class="uploadSection">
                                                                <h3 class="uploadName">COPY OF STUDENT CARD</h3>
                                                                <div class="uploadFileSection">
                                                                    <i class="fas fa-file-upload"></i>
                                                                    <!-- <input type="file" id="copyID" hidden/>
                                                                    <label for="copyID" class="uploadLabel">Choose file</label> -->
                                                                    
                                                                </div>
                                                            </div>

                                                            <br/>
                                                            
                                                            <div class="uploadSection">
                                                                <h3 class="uploadName">3 MONTHS BANK STATEMENTS</h3>
                                                                <div class="uploadFileSection">
                                                                    <i class="fas fa-file-upload"></i>
                                                                    <!-- <input type="file" id="copyID" hidden/>
                                                                    <label for="copyID" class="uploadLabel">Choose file</label> -->
                                                                    
                                                                </div>
                                                            </div>

                                                            <br/>
                                                            
                                                            <div class="uploadSection">
                                                                <h3 class="uploadName">PROOF OF REGISTRATION</h3>
                                                                <div class="uploadFileSection">
                                                                    <i class="fas fa-file-upload"></i>
                                                                    <!-- <input type="file" id="copyID" hidden/>
                                                                    <label for="copyID" class="uploadLabel">Choose file</label> -->
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="button" name="previous" class="previous action-button-previous" value="Back to previous step" /> 
                                        <input type="button" name="next" class="next action-button" value="Pay booking fee" />

                                        <div class="clearSpaceLogin"></div>
                                        <div class="clearSpaceLogin"></div>

                                        <div class="redBannerNotice">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <p>Please note that you can upload these documents via your online profile at a later date, however delays may cause you to not get the room you want! Your booking fee is processed in the next step; and you can upload proof of payment immediately or via your profile later.</p>
                                        </div>
                                    </fieldset>
                                    <fieldset>
                                        <div class="form-card">
                                            
                                        <div class="cards">
                                            <div class="whiteCard">

                                                <div class="flex flex-rev">
                                                    <button type="button" class="editButton">EDIT</button>
                                                </div>
                                            
                                                <p class="cardList">First Name Surname<br/>
                                                    20 / 05 / 1991<br/>
                                                    Cellphone Number<br/>
                                                    Email Address<br/>
                                                    I.D Number<br/>
                                                    Nationality<br/>
                                                    Passport Number<br/>
                                                    Institution of Study<br/>
                                                    Student Number<br/>
                                                    Institute Campus<br/>
                                                    Faculty<br/>
                                                    Year</p>
                                                    
                                            </div>

                                            <div class="whiteCard" style="margin-left:11px;">
                                                <h4 class="cardSubHeaderGrey">Braamlofts</h4>
                                                <h2 class="cardHeader" >Single Room</h2>
                                                <h4 class="cardSubHeaderGrey">Room 23 Floor 2</h4>
                                            </div>
                                        </div>

                                        <br/>
                                        <div class="clearSpaceLogin"></div>
                                        <div class="footerClearfix"></div>

                                        <div class="paymentSection">

                                            <div class="form-field">
                                                <h1 class="header">Payment Information</h1>
                                            </div>

                                            <div class="form-field">
                                                <label >Payment Type &nbsp;&nbsp;&nbsp;&nbsp;</label>
                                                <select name="paymentType" >
                                                    <option value="1">Credit card</option>
                                                </select>
                                            </div>

                                            <div class="form-field">
                                                <label>Cardholder Name</label>
                                                <input type="text" name="cardholderName">

                                                <label>Card Number</label>
                                                <input type="text" name="cardNumber">

                                                <label>Exp. Date</label>
                                                <input type="date" name="expDate">

                                                <label>CVV</label>
                                                <input type="text" name="cvv" style="width:50%">
                                            </div>

                                            <div class="form-field">
                                                <div class="cards flex flex-col">
                                                    <div class="whiteCard halfCard">
                                                        <h4 class="cardSubHeaderGrey">Booking Deposit</h4>
                                                        <h2 class="cardHeader" >R5000</h2>
                                                        <h4 class="cardSubHeaderGrey">Your application deposit is deducted from the total of the room deposit required to make this booking.</h4>
                                                        
                                                    </div>
                                                    <div class="redBottom flex">
                                                            <i class="fas fa-clock"></i>
                                                            <p>Please note that your booking will only be reserved for 24hrs without payment</p>
                                                        </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="clearSpaceLogin"></div>
                                        <div class="footerClearfix"></div>

                                        <input type="button" name="previous" class="previous action-button-previous" value="Back to previous step" /> 
                                        <input type="button" name="next" class="next action-button" value="Pay R2500.00 booking fee" />

                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>



<!-- <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> -->
@endsection
