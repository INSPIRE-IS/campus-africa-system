@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="cards" style="margin-top:50px;">
        <div class="whiteCard dashCard">
        <div class="cardForm" >

                <h4 class="adminHeader">Applications</h4>
                <!-- <h4 class="adminOutstanding">10 new Applications</h4> -->
                <!-- <br/><br/> -->
                    <br/>
                <a href="/applications"><button type="button" class="submitButton" value="Login">VIEW ALL APPLICATIONS </button></a>
            </div>
        </div>

        <div class="whiteCard dashCard" style="margin-left:11px;">
            <div class="cardForm" >

                <h4 class="adminHeader">Leases</h4>
                <!-- <h4 class="adminOutstanding">6 new Leases for approval</h4> -->
                <!-- <br/><br/> -->
                <br/>
                <a href="/leases"><button type="button" class="submitButton" value="Login">View All Leases</button></a>
            </div>
        </div>

        <div class="whiteCard dashCard" style="margin-left:11px;">
        <div class="cardForm" >

                <h4 class="adminHeader">Invoices</h4>
                <!-- <h4 class="adminOutstanding">10 new Invoices</h4> -->
                <!-- <br/><br/> -->
                    <br/>
                <a href="invoices"><button type="button" class="submitButton" value="Login">VIEW ALL INVOICES </button></a>
            </div>
        </div>

        <div class="whiteCard dashCard" style="margin-left:11px; ">
            <div class="cardForm" >

                <h4 class="adminHeader">Mailing</h4>
                <br/>
                <!-- <br/><br/> -->
                <a href="/mailing"><button type="button" class="submitButton" value="Login">Edit mailing templates</button></a>
            </div>
        </div>
    </div>
</div>

<div class="clearSpaceLogin"></div>
<!-- <div class="clearSpaceLogin"></div> -->
<!-- <div class="clearSpaceLoginPage"></div> -->




@endsection
