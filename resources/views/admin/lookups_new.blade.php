@extends('layouts.admin')

@section('content')

@php
    $fundingTypes = $fundingTypes ?? \App\Models\Lookups::FUNDING_TYPES;
    $yearLookupData = isset($year_lookup)
        ? (is_array($year_lookup->data) ? $year_lookup->data : ($year_lookup->data ? json_decode($year_lookup->data, true) : []))
        : [];
    $defaultLeaseFees = is_array($yearLookupData) ? ($yearLookupData['lease_fees'] ?? []) : [];
    $defaultDepositApplies = is_array($yearLookupData) ? ($yearLookupData['deposit_applies'] ?? []) : [];

    $defaultMonthFrom = isset($year_lookup) && $year_lookup->month_from ? (string) $year_lookup->month_from : '1';
    $defaultDayFrom = isset($year_lookup) && $year_lookup->day_from ? (string) $year_lookup->day_from : '1';
    $defaultLeaseFromTime = isset($year_lookup) && $year_lookup->lease_from_time ? (string) $year_lookup->lease_from_time : '12:00';
    $defaultMonthTo = isset($year_lookup) && $year_lookup->month_to ? (string) $year_lookup->month_to : '12';
    $defaultDayTo = isset($year_lookup) && $year_lookup->day_to ? (string) $year_lookup->day_to : '31';
    $defaultLeaseToTime = isset($year_lookup) && $year_lookup->lease_to_time ? (string) $year_lookup->lease_to_time : '12:00';
@endphp

<div class="container form-card">
<form action="/new-lookup" method="post" autocomplete="off">
    @csrf
        <br/>        <br/>

        <div class="form-field">
            <label>Title</label>
            <select name="group_id" id='group_id'>
                <option value="0" disabled selected>Select one</option>
                <option value="1">Institution of Study</option>
                <!-- <option value="2">Insitution Campus</option> -->
                <option value="3" >Faculty</option>
                <option value="4" >Year</option>
                <option value="5" >Nationality</option>
                <option value="6" >Year of Study</option>
                <option value="7" >Waive Booking Fee</option>
                <option value="8" >Lease Admin Fee</option>
                <option value="9" >Online Applications</option>
                <option value="10" >Email - Restrict to Test Domain(s)</option>
                <option value="11" >Email - BCC Email Addresses</option>
                <option value="12" >Email - Reminder Booking Fee</option>
                <option value="13" >Email - Reminder Outstanding Documents</option>
            </select>
        </div>

        <div class="form-field" id="normalVlaue">
            <label>Value</label>
            <input type="text" name="value" id="value"  >
            <small id="mailWording" class="hidden">
            <br/>- When this is set, emails are limited to the specified domains.<br/>
                - These should be domains only<br/>
                - When this is not set, no restrictions on email sending<br/>
                - Please seperate by semicolon</small>
            <small id="mailCCWording" class="hidden">Specify individual email addresses to BCC, Please seperate email addresses by semicolon</small>
        </div>

        <div class="form-field hidden" id="yearLeaseFields" style="width:stretch">
            <div class="form-field">
                <label>Active Year</label>
                <div style="display:inline-flex; align-items:center;">
                    <input type="checkbox" id="is_active_year" name="is_active_year" style="width:27px !important" value="1">
                    <label for="is_active_year" style="width:auto;">&nbsp;&nbsp;Mark this year as the active application year</label>
                </div>
                <small>Only one Year lookup can be active. Leave unchecked when creating next-year configs early.</small>
            </div>
            <div class="form-field">
                <label>Lease From Date (Month)</label>
                <select name="month_from" id='month_from'>
                    <option value="0" disabled selected>Select one</option>
                    <option value="1" {{ $defaultMonthFrom === '1' ? 'selected' : '' }}>January</option>
                    <option value="2" {{ $defaultMonthFrom === '2' ? 'selected' : '' }}>February</option>
                    <option value="3" {{ $defaultMonthFrom === '3' ? 'selected' : '' }}>March</option>
                    <option value="4" {{ $defaultMonthFrom === '4' ? 'selected' : '' }}>April</option>
                    <option value="5" {{ $defaultMonthFrom === '5' ? 'selected' : '' }}>May</option>
                    <option value="6" {{ $defaultMonthFrom === '6' ? 'selected' : '' }}>June</option>
                    <option value="7" {{ $defaultMonthFrom === '7' ? 'selected' : '' }}>July</option>
                    <option value="8" {{ $defaultMonthFrom === '8' ? 'selected' : '' }}>August</option>
                    <option value="9" {{ $defaultMonthFrom === '9' ? 'selected' : '' }}>September</option>
                    <option value="10" {{ $defaultMonthFrom === '10' ? 'selected' : '' }}>October</option>
                    <option value="11" {{ $defaultMonthFrom === '11' ? 'selected' : '' }}>November</option>
                    <option value="12" {{ $defaultMonthFrom === '12' ? 'selected' : '' }}>December</option>
                </select>
            </div>
            <div class="form-field">
                <label>Lease From Date (Day)</label>
                <select name="day_from" id='day_from'>
                    <option value="0" disabled selected>Select one</option>
                    @for ($d = 1; $d <= 31; $d++)
                        @php $dv = (string) $d; @endphp
                        <option value="{{ $dv }}" {{ $defaultDayFrom === $dv ? 'selected' : '' }}>{{ str_pad((string) $d, 2, '0', STR_PAD_LEFT) }}</option>
                    @endfor
                </select>
            </div>
            <div class="form-field">
                <label>Lease From Time</label>
                <input type="time" id="lease_from_time" name="lease_from_time" value="{{ $defaultLeaseFromTime }}">
            </div>

            <div class="form-field">
                <div class="form-field">
                    <label>Lease To Date (Month)</label>
                    <select name="month_to" id='month_to'>
                        <option value="0" disabled selected>Select one</option>
                        <option value="1" {{ $defaultMonthTo === '1' ? 'selected' : '' }}>January</option>
                        <option value="2" {{ $defaultMonthTo === '2' ? 'selected' : '' }}>February</option>
                        <option value="3" {{ $defaultMonthTo === '3' ? 'selected' : '' }}>March</option>
                        <option value="4" {{ $defaultMonthTo === '4' ? 'selected' : '' }}>April</option>
                        <option value="5" {{ $defaultMonthTo === '5' ? 'selected' : '' }}>May</option>
                        <option value="6" {{ $defaultMonthTo === '6' ? 'selected' : '' }}>June</option>
                        <option value="7" {{ $defaultMonthTo === '7' ? 'selected' : '' }}>July</option>
                        <option value="8" {{ $defaultMonthTo === '8' ? 'selected' : '' }}>August</option>
                        <option value="9" {{ $defaultMonthTo === '9' ? 'selected' : '' }}>September</option>
                        <option value="10" {{ $defaultMonthTo === '10' ? 'selected' : '' }}>October</option>
                        <option value="11" {{ $defaultMonthTo === '11' ? 'selected' : '' }}>November</option>
                        <option value="12" {{ $defaultMonthTo === '12' ? 'selected' : '' }}>December</option>
                    </select>
                </div>
                <div class="form-field">
                    <label>Lease To Date (Day)</label>
                    <select name="day_to" id='day_to'>
                        <option value="0" disabled selected>Select one</option>
                        @for ($d = 1; $d <= 31; $d++)
                            @php $dv = (string) $d; @endphp
                            <option value="{{ $dv }}" {{ $defaultDayTo === $dv ? 'selected' : '' }}>{{ str_pad((string) $d, 2, '0', STR_PAD_LEFT) }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="form-field">
                <label>Lease To Time</label>
                <input type="time" id="lease_to_time" name="lease_to_time" value="{{ $defaultLeaseToTime }}">
            </div>
        </div>

        <div class="form-field hidden" id="propertyListNsfas" style="height:<?php echo 48*count($allProperties);?>px !important;">
            <label>Student Types</label>
            <ul style="list-style-type:none; width:100%">
                @foreach($allProperties as $prop)
                <li style="display:inline-flex"><label for="{{$prop->id}}_prop" style="width:300px;">&nbsp; &nbsp;{{$prop->residence}}</label>

                    <ul style="list-style-type:none; width:100%">
                        <li style="display:inline-flex">
                            <input type="checkbox" id="{{$prop->id}}_nsfas" name="{{$prop->id}}_nsfas" style="width:27px !important" value='1'><label for="{{$prop->id}}_nsfas" style="width:100px;">&nbsp; &nbsp;NSFAS</label>
                        </li>
                        <li style="display:inline-flex">
                            <input type="checkbox" id="{{$prop->id}}_cash" name="{{$prop->id}}_cash" style="width:27px !important" value='1'><label for="{{$prop->id}}_cash" style="width:100px;">&nbsp; &nbsp;Cash</label>
                        </li>
                        <li style="display:inline-flex">
                            <input type="checkbox" id="{{$prop->id}}_bursary" name="{{$prop->id}}_bursary" style="width:27px !important" value='1'><label for="{{$prop->id}}_bursary" style="width:100px;">&nbsp; &nbsp;Bursary</label>
                        </li>
                        
                    </ul>

                    </li>
                
                <br/>
                @endforeach
            </ul>
        </div>

        <div class="form-field hidden" id="waiverValue">
            <label>Value</label>
            <select name="booking_waiver" id="booking_waiver">
                <option value="0" disabled selected>- Select One -</option>
                <option value="1">Yes</option>
                <option value="2">No</option>
            </select>
        </div>

        <div class="form-field hidden" id="onlineAppValue">
            <label>Value</label>
            <select name="onlineApp_value" id="onlineApp_value">
                <option value="0" disabled selected>- Select One -</option>
                <option value="1">Online</option>
                <option value="2">Offline</option>
            </select>
        </div>

        <div class="form-field hidden" id="nsfas_value">
            <label>NSFAS Rate</label>
            <input type="number" id="nsfas_amount" name="nsfas_amount" >
        </div>
        <div style="height:400px !important; margin-top:400px; margin-bottom:10px;">
            <div class="form-field hidden" id="fundingFeesTable">
                <label>Funding Type Lease Fees</label>
                <table style="width:100%">
                    <thead>
                        <tr>
                            <th style="text-align:left;">Funding Type</th>
                            <th style="text-align:left;">Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fundingTypes as $typeId => $typeName)
                            <tr>
                                <td>{{ $typeName }}</td>
                                <td>
                                    <input type="text" name="lease_fees[{{ $typeId }}]" class="lease-fee-input" inputmode="decimal" value="{{ $defaultLeaseFees[$typeId] ?? '' }}" disabled>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <small>Saved per year. Used when creating/updating bookings and leases for this year.</small>
            </div>

            <div class="form-field hidden" id="fundingDepositsTable" >
                <label>Funding Type Deposit Applies</label>
                <table style="width:100%">
                    <thead>
                        <tr>
                            <th style="text-align:left;">Funding Type</th>
                            <th style="text-align:left;">Deposit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fundingTypes as $typeId => $typeName)
                            <tr>
                                <td>{{ $typeName }}</td>
                                <td>
                                    <input type="checkbox" name="deposit_applies[{{ $typeId }}]" value="1" {{ !empty($defaultDepositApplies[$typeId]) ? 'checked' : '' }} disabled>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <small>Saved per year. If checked, deposit is set to the monthly rental for this funding type (unless waived).</small>
            </div>
        </div>

        <!-- <div class="form-field hidden" id="waiverDateFrom">
            <label>Waiver Date From</label>
            <input type="date" name="date_from" id="date_from">
        </div>

        <div class="form-field hidden" id="waiverDateTo">
            <label>Waiver Date To</label>
            <input type="date" name="date_to" id="date_to">
        </div> -->

        <input type="submit" value="Save" class="profileSubmit" style="opacity:1"/>
    </form>
</div>

<script>
    function toggleFundingFees() {
        const groupId = $("#group_id").val();
        const shouldShowFees = groupId === "4";
        const shouldShowDeposits = groupId === "4";
        const shouldShowYearFields = groupId === "4";

        $("#fundingFeesTable").toggleClass('hidden', !shouldShowFees);
        $("#fundingFeesTable").find('input').prop('disabled', !shouldShowFees);

        $("#fundingDepositsTable").toggleClass('hidden', !shouldShowDeposits);
        $("#fundingDepositsTable").find('input').prop('disabled', !shouldShowDeposits);

        $("#yearLeaseFields").toggleClass('hidden', !shouldShowYearFields);
    }

    $("#group_id").on('change', function(){
        if($("#group_id").val() == "7"){
            $("#normalVlaue").addClass('hidden');
            $("#waiverValue").removeClass('hidden');
            $("#onlineAppValue").addClass('hidden');
            $("#mailWording").addClass('hidden');
            $("#mailCCWording").addClass('hidden');
            $("#nsfas_value").addClass('hidden');
            $("#propertyListNsfas").addClass('hidden');
            // $("#waiverDateFrom").removeClass('hidden');
            // $("#waiverDateTo").removeClass('hidden');
        }else if($("#group_id").val() == "9" || $("#group_id").val() == "12" || $("#group_id").val() == "13"){
            $("#onlineAppValue").removeClass('hidden');
            $("#normalVlaue").addClass('hidden');
            $("#waiverValue").addClass('hidden');
            $("#mailWording").addClass('hidden');
            $("#nsfas_value").addClass('hidden');
            $("#mailCCWording").addClass('hidden');
            $("#propertyListNsfas").addClass('hidden');
        }else if($("#group_id").val() == "10"){
            $("#mailWording").removeClass('hidden');
            $("#onlineAppValue").addClass('hidden');
            $("#normalVlaue").removeClass('hidden');
            $("#waiverValue").addClass('hidden');
            $("#propertyListNsfas").addClass('hidden');

            $("#mailCCWording").addClass('hidden');
        }else if($("#group_id").val() == "11"){
            $("#mailWording").addClass('hidden');
            $("#onlineAppValue").addClass('hidden');
            $("#normalVlaue").removeClass('hidden');
            $("#waiverValue").addClass('hidden');
            $("#propertyListNsfas").addClass('hidden');

            $("#mailCCWording").removeClass('hidden');
            $("#nsfas_value").addClass('hidden');
        }else if($("#group_id").val() == "1" ){
            $("#nsfas_value").removeClass('hidden');
            $("#normalVlaue").removeClass('hidden');
            $("#propertyListNsfas").removeClass('hidden');
            $("#waiverValue").addClass('hidden');
            $("#mailWording").addClass('hidden');
            $("#onlineAppValue").addClass('hidden');
        }else{
            $("#normalVlaue").removeClass('hidden');
            $("#waiverValue").addClass('hidden');
            $("#onlineAppValue").addClass('hidden');
            $("#mailWording").addClass('hidden');
            $("#mailCCWording").addClass('hidden');
            $("#nsfas_value").addClass('hidden');
            $("#propertyListNsfas").addClass('hidden');
            // $("#waiverDateFrom").addClass('hidden');
            // $("#waiverDateTo").addClass('hidden');
        }

        toggleFundingFees();
    })

    $("#value").on('input', function() {
        toggleFundingFees();
    });

    function isValidNonNegativeNumber(value) {
        if (value === '') {
            return true;
        }
        if (!/^\d*(\.\d+)?$/.test(value)) {
            return false;
        }
        return parseFloat(value) >= 0;
    }

    $(document).on('keydown', '.lease-fee-input', function(e) {
        const key = e.key;
        const isControl = e.ctrlKey || e.metaKey || e.altKey;
        if (isControl || key === 'Backspace' || key === 'Delete' || key === 'Tab' || key === 'ArrowLeft' || key === 'ArrowRight' || key === 'Home' || key === 'End') {
            return;
        }
        if (key === '.') {
            if ($(this).val().includes('.')) {
                e.preventDefault();
            }
            return;
        }
        if (!/^\d$/.test(key)) {
            e.preventDefault();
        }
    });

    $(document).on('blur', '.lease-fee-input', function() {
        const value = $(this).val().trim();
        if (!isValidNonNegativeNumber(value)) {
            this.setCustomValidity('Enter a number 0 or greater.');
            this.reportValidity();
        } else {
            this.setCustomValidity('');
        }
    });

    toggleFundingFees();
</script>

@endsection
