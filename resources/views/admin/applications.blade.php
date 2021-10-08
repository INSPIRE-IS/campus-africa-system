@extends('layouts.admin')

@section('content')
<div class="container">


    <v class="appSearchForm">
        <div class="searchField">
            <br/>
            <select name="property">
                <option value="Property 1">Property 1</option>
                <option value="Property 2">Property 2</option>
                <option value="Property 3">Property 3</option>
            </select>
        </div>
        <div class="searchField">
            <br/>
            <select name="property">
                <option value="Property 1">Property 1</option>
                <option value="Property 2">Property 2</option>
                <option value="Property 3">Property 3</option>
            </select>
        </div>
        <div class="searchField">
            <label>Applicant</label><br/>
            <input type="text" name="applicant" id="applicant"/>
        </div>

        <div class="searchField">
            <br/>
            <select name="applicationStatus">
                <option value="Application Status 1">Application Status 1</option>
                <option value="Application Status 2">Application Status 2</option>
                <option value="Application Status 3">Application Status 3</option>
            </select>
        </div>
        <div class="searchField">
            <br/>
            <select name="bookingStatus">
                <option value="Booking Status 1">Booking Status 1</option>
                <option value="Booking Status 2">Booking Status 2</option>
                <option value="Booking Status 3">Booking Status 3</option>
            </select>
        </div>
        <div class="searchField">
            <label>Date</label><br/>
            <input type="date" name="date" id="date"/>
            <br/><br/><br/>
        </div>

        <div class="searchTab">
            <button type="button">Search</button>
        </div>
        <div class="indexTab flex">
            <label>Legend</label>
            <div class="keys">
                <div class="button approved"></div><span class="text">Approved</span>
                <div class="button action"></div><span class="text">Action Required</span>
                <div class="button rejected"></div><span class="text">Rejected</span>
                <div class="button na"></div><span class="text">Not Applicable</span>
            </div>
        </div>
        
    </div>

    <div class="clearSpaceLogin"></div>

    <div class="container">
        <div class="ApplicationResults">
            <table class="applicationTable">
                <thead>
                    <tr>
                        <th class="first">BOOKING REFERENCE</th>
                        <th>APPLICANT</th>
                        <th>STUDENT TYPE</th>
                        <th>BOOKING STATUS</th>
                        <th>PROOF OF PAYMENT</th>
                        <th>ID COPY</th>
                        <th>APPLICATION STATUS</th>
                        <th>STUDENT CARD COPY</th>
                        <th>PROOF OF REGISTRATION</th>
                        <th>NSFAS | BURSARY APPROVAL</th>
                        <th class="last">BANK STATEMENTS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="dark clickable-row" data-href='/application-profile/1'>
                        <td>#000000000</td>
                        <td><span class="bold">Name Surname</span> <br/> 9509251234804 | Braamlofts</td>
                        <td><span class="bold">NSFAS</span></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button approved"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button rejected"></div></td>
                        <td><div class="button na"></div></td>
                    </tr>
                    <tr class="light">
                        <td>#000000000</td>
                        <td><span class="bold">Name Surname</span> <br/> 9509251234804 | Braamlofts</td>
                        <td><span class="bold">NSFAS</span></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button approved"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button rejected"></div></td>
                        <td><div class="button na"></div></td>
                    </tr>
                    <tr class="dark">
                        <td>#000000000</td>
                        <td><span class="bold">Name Surname</span> <br/> 9509251234804 | Braamlofts</td>
                        <td><span class="bold">NSFAS</span></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button approved"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button rejected"></div></td>
                        <td><div class="button na"></div></td>
                    </tr>
                    <tr class="light">
                        <td>#000000000</td>
                        <td><span class="bold">Name Surname</span> <br/> 9509251234804 | Braamlofts</td>
                        <td><span class="bold">NSFAS</span></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button approved"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button rejected"></div></td>
                        <td><div class="button na"></div></td>
                    </tr>
                    <tr class="dark">
                        <td>#000000000</td>
                        <td><span class="bold">Name Surname</span> <br/> 9509251234804 | Braamlofts</td>
                        <td><span class="bold">NSFAS</span></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button approved"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button action"></div></td>
                        <td><div class="button rejected"></div></td>
                        <td><div class="button na"></div></td>
                    </tr>
                </tbody>
            </table>

        </div>
</div>

    
</div>

<div class="clearSpaceLoginPage"></div>


<script>
    jQuery(document).ready(function($) {
    $(".clickable-row").click(function() {
        window.location = $(this).data("href");
    });
});
</script>




@endsection
