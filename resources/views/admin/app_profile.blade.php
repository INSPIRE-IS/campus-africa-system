@extends('layouts.admin')

@section('content')

<div class="greyAppSection">

    <div class="container">

        <div class="col-3">
            <p class="listHeader">Next of Kin</p>
            <br/>
            <p class="list">
                {{$user['kinFirstName']}} {{$user['kinLastName']}}<br/>
                ?dob<br/>
                {{$user['kinCellNo']}}<br/>
                {{$user['kinEmail']}}<br/>
                {{$user['kinId']}}<br/>
                ?nationality<br/>
                ?passport<br/>
                ?institution of study<br/>
                ?student no<br/>
                ?institute Campus<br/>
                ?faculty<br/>
                ?year<br/>
            </p>
        </div>
        <div class="col-3">
        <p class="listHeader">Sponsor</p>
            <br/>
            <p class="list">
                {{$user['sponFirstName']}} {{$user['sponLastName']}}<br/>
                ?dob<br/>
                {{$user['sponCellNo']}}<br/>
                {{$user['sponEmail']}}<br/>
                {{$user['sponId']}}<br/>
                ?nationality<br/>
                ?passport<br/>
                ?institution of study<br/>
                ?student no<br/>
                ?institute Campus<br/>
                ?faculty<br/>
                ?year<br/>
            </p>
        </div>
        <div class="col-3">
        <p class="listHeader">Bursar</p>
            <br/>
            <p class="list">
                {{$user['burFirstName']}} {{$user['burLastName']}}<br/>
                ?dob<br/>
                {{$user['burCellNo']}}<br/>
                {{$user['burEmail']}}<br/>
                {{$user['burId']}}<br/>
                ?nationality<br/>
                ?passport<br/>
                ?institution of study<br/>
                ?student no<br/>
                ?institute Campus<br/>
                ?faculty<br/>
                ?year<br/>
            </p>
        </div>
        
    </div>
</div>

<div class="container">
    <div class="appRow">
        <div class="cards">

            <div class="customWhiteCard" >
                <h2 class="appHeader">Applicant Details</h2>
                <p class="InfoList">
                    {{Auth::user()->userFirstName}} {{Auth::user()->userLastname}}<br/>
                    {{Auth::user()->userDob}}<br/>
                    {{Auth::user()->userCell}}<br/>
                    {{Auth::user()->userEmail}}<br/>
                    {{Auth::user()->userId}}<br/>
                    {{Auth::user()->userNationality}}<br/>
                    {{Auth::user()->userPassport}}<br/>
                    {{Auth::user()->institutionStudy}}<br/>
                    {{Auth::user()->studentNo}}<br/>
                    {{Auth::user()->institutionCampus}}<br/>
                    {{Auth::user()->faculty}}<br/>
                    {{Auth::user()->year}}
                </p>
            </div>

            <div class="customWhiteCard" style="margin-top:20px;">
                <h2 class="appHeader">Application made for</h2>
                <h4 class="cardSubHeaderGrey">Braamlofts</h4>
                <h2 class="cardHeader" >Single Room</h2>
                <h4 class="cardSubHeaderGrey">Room 23 Floor 2</h4>
                <br/>
                <h4 class="cardSubHeaderGrey">BOOKING FEE PAID</h4>
                <h2 class="cardHeader" >R2500</h2>
                <br/>
                <div class="buttons">
                    <button type="button" class="appBooking">Change this booking</button>
                    <button type="button" class="appBooking">Delete this booking</button>
                </div>
            </div>

            <div class="customWhiteCard" style="margin-top:20px;">
                <div class="bigHeader">
                    <h2 class="appHeader">Action List</h2>
                </div>

                <div class="infoBlock">
                    <div class="responsible">
                        <p>Username</p>
                        <button type="button" class="admin">Admin</button>
                        <p class="datetime"> 00 | 00 | 0000 | 00:00 </p>
                    </div>
                    <div class="information">
                        <p>Uploaded document for review</p>
                    </div>
                </div>

                <div class="infoBlock">
                    <div class="responsible">
                        <p>Username</p>
                        <button type="button" class="applicant">Applicant</button>
                        <p class="datetime"> 00 | 00 | 0000 | 00:00 </p>
                    </div>
                    <div class="information">
                        <p>Uploaded document for review</p>
                    </div>
                </div>

                <div class="infoBlock">
                    <div class="responsible">
                        <p>Username</p>
                        <button type="button" class="admin">Admin</button>
                        <p class="datetime"> 00 | 00 | 0000 | 00:00 </p>
                    </div>
                    <div class="information">
                        <p>Uploaded document for review</p>
                    </div>
                </div>

                <!-- <div class="infoBlock">
                    <div class="responsible">
                        <p>Username</p>
                        <button type="button" class="applicant">Applicant</button>
                        <p class="datetime"> 00 | 00 | 0000 | 00:00 </p>
                    </div>
                    <div class="information">
                        <p>Uploaded document for review</p>
                    </div>
                </div> -->

            </div>


        </div>
    </div>
    <div class="appRow">
        <div class="cards">

            <div class="customWhiteCard">
                <button type="button" class="reminder">Send reminder</button>
                <h2 class="appSubHeader bold">PAYMENT TYPE </h2>
                <p class="appSubHeader">
                    NSFAS
                </p>
                <br/>

                <h2 class="appSubHeader bold">DEPOSIT</h2>
                <p class="appSubHeader">
                    CREDIT CARD
                </p>
                <br/>

                <h2 class="appSubHeader bold">PAID</h2>
                <p class="appSubHeader">
                    NO
                </p>
            </div>

            <div class="customWhiteCard" style="margin-top:20px;">
                <div class="bigHeader appSearchForm" style="margin-top:0px;">
                    <h2 class="appHeader">Documents</h2>
                    <div class="indexTab flex ">
                        <div class="keys">
                            <div class="button approved"></div><span class="text">Approved</span>
                            <div class="button action"></div><span class="text">Action Required</span>
                            <div class="button rejected"></div><span class="text">Rejected</span>
                        </div>
                    </div>
                </div>

                <br/><br/><br/><br/>

                <div class="section">
                    <div class="label">STUDENT CARD / PROOF OF STUDENT NUMBER</div>
                    <div class="banner"><button type="button">View Document</button></div>
                    <div class="status"><div class="button action"></div></div>
                </div>

                <div class="section">
                    <div class="label">NSFAS APPROVAL</div>
                    <div class="banner"><button type="button">View Document</button></div>
                    <div class="status"><div class="button action"></div></div>
                </div>

                <div class="section">
                    <div class="label">PROOF OF REGISTRATION</div>
                    <div class="banner"><button type="button">View Document</button></div>
                    <div class="status"><div class="button action"></div></div>
                </div>

                <div class="section">
                    <div class="label">STUDENTS ID DOCUMENT</div>
                    <div class="banner"><button type="button">View Document</button></div>
                    <div class="status"><div class="button approved"></div></div>
                </div>

                <div class="section">
                    <div class="label">PROOF OF BOOKING FEE</div>
                    <div class="banner"><button type="button">View Document</button></div>
                    <div class="status"><div class="button approved"></div></div>
                </div>

                <div class="section">
                    <div class="label">BANK STATEMENTS</div>
                    <div class="banner"><button type="button" class="error">Send reminder</button></div>
                    <div class="status"><div class="button rejected"></div></div>
                </div>

                <div class="section">
                    <div class="label">BANK STATEMENTS</div>
                    <div class="banner"><button type="button" class="error">Send reminder</button></div>
                    <div class="status"><div class="button rejected"></div></div>
                </div>

                <div class="section">
                    <div class="label">BANK STATEMENTS</div>
                    <div class="banner"><button type="button" class="error">Send reminder</button></div>
                    <div class="status"><div class="button rejected"></div></div>
                </div>

                <div class="section">
                    <div class="label">BANK STATEMENTS</div>
                    <div class="banner"><button type="button" class="error">Send reminder</button></div>
                    <div class="status"><div class="button rejected"></div></div>
                </div>

            </div>

        </div>
    </div>
</div>

<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>
<div class="clearSpaceLogin"></div>






@endsection
