<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Braamby Tax Invoice</title>
</head>

<body style="font-family: DejaVu Sans, sans-serif; font-size:11px; color:#000; margin:0; padding:0;">

@php
    $colDetails = '55%';
    $colStudent = '20%';
    $colAmount  = '25%';
@endphp

<!-- ================= HEADER ================= -->
<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
    <tr>
        <td width="55%" style="vertical-align:top; padding:5px;">
            <img src="{{ public_path('images/consolidated_urban.jpg') }}" height="65">
        </td>

        <td width="45%" style="vertical-align:top; padding:5px;">
            <div style="font-size:24px; font-weight:bold; margin-bottom:10px;">
                Tax Invoice
            </div>

            <table width="100%" cellpadding="2" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                    <td style="font-size:10px; font-weight:bold; width:40%;">Invoice #:</td>
                    <td>{{ $invoiceNumber }}</td>
                </tr>
                <tr>
                    <td style="font-size:10px; font-weight:bold;">Invoice date:</td>
                    <td>{{ $invoiceDate }}</td>
                </tr>
                <tr>
                    <td style="font-size:10px; font-weight:bold;">Student Number:</td>
                    <td>{{ $user->studentNo }}</td>
                </tr>
                <tr>
                    <td style="font-size:10px; font-weight:bold;">Student Name:</td>
                    <td>{{ $user->userFirstName }} {{ $user->userLastname }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- ================= BILL TO ================= -->
<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin-top:14px;">
    <tr>
        <td width="55%" style="vertical-align:top; padding:5px;">
            <div style="font-weight:bold;">{{$property->account_name}}</div>
            <div>{{$property->address}}</div>

            <div style="margin-top:12px; font-size:10px;">
                <div style="font-weight:bold;">
                    <span style="text-transform:uppercase">{{$property->residence}}</span> - CAMPUS AFRICA,
                    <span style="text-transform:uppercase">{{$property->address}}</span>
                </div>
                <div>VAT No: {{$property->company_vat}}</div>
                <div>Company Registration Number: {{$property->company_reg}}</div>
                <div>Account Queries : Andrea Fourie</div>
                <div>Email: andrea@conurban.co.za</div>
            </div>
        </td>

        <td width="45%" style="vertical-align:top; padding:5px;">
            <div style="margin-bottom:10px;">
                <span style="font-weight:bold;">Bill to:</span><br>
                @if(!empty($billTo['name'])){{ $billTo['name'] }}<br>@endif
                @if(!empty($billTo['regNo'])){{ $billTo['regNo'] }}<br>@endif
                @if(!empty($billTo['contactName'])){{ $billTo['contactName'] }}<br>@endif
                @if(!empty($billTo['contactEmail'])){{ $billTo['contactEmail'] }}<br>@endif
                @if(!empty($billTo['contactPhone'])){{ $billTo['contactPhone'] }}<br>@endif
            </div>

            <div>
                <span style="font-weight:bold;">Address:</span><br>
                {{ $billAddress['address1'] }}<br>
                {{ $billAddress['address2'] }}<br>
                {{ $billAddress['addressCity'] }}<br>
                {{ $billAddress['addressPostal'] }}
            </div>
        </td>
    </tr>
</table>

<!-- ================= ITEMS ================= -->
<table width="100%" cellpadding="4" cellspacing="0"
       style="border-collapse:collapse; margin-top:16px;">

    <tr style="font-weight:bold; border-bottom:1px solid #000;">
        <td width="{{ $colDetails }}">Accommodation for Bursary student invoice</td>
        <td width="{{ $colDetails }}">Details</td>
        <td width="{{ $colStudent }}" style="text-align:center;">Number of Student</td>
        <td width="{{ $colAmount }}" style="text-align:right;">Amount Incl VAT</td>
    </tr>

    @foreach($items as $item)
        <tr style="page-break-inside:avoid;">
            <td>{{ $item['date'] }}</td>
            <td>{{ $item['description'] }}</td>
            <td style="text-align:center;">{{ $user->studentNo }}</td>
            <td style="text-align:right;">
                R {{ number_format($item['amount'], 2, ',', ' ') }}
            </td>
        </tr>
    @endforeach

    <tr>
        <td colspan="4" style="border-top:1px solid #000;"></td>
    </tr>

    <tr>
        <td></td>
        <td></td>
        <td style="font-weight:bold;">TOTAL</td>
        <td style="text-align:right; font-weight:bold;">
            R {{ number_format($totalAmount, 2, ',', ' ') }}
        </td>
    </tr>
</table>

<!-- ================= AGING ================= -->
<table width="70%" cellpadding="4" cellspacing="0"
       style="border-collapse:collapse; margin-top:16px;">

    <tr style="font-weight:bold;">
        <td width="20%">60 DAYS+</td>
        <td width="20%">30 DAYS</td>
        <td width="20%">CURRENT</td>
        <td width="20%">TOTAL</td>
        <td width="20%"></td>
    </tr>

    <tr>
        <td>-R</td>
        <td>-R</td>
        <td>R {{ number_format($totalAmount, 2, ',', ' ') }}</td>
        <td>R {{ number_format($totalAmount, 2, ',', ' ') }}</td>
        <td></td>
    </tr>
</table>

<!-- ================= BANKING ================= -->
<div style="margin-top:16px;">
    <div style="font-weight:bold;">
        NB: Interest will be charged at the rate of 2% per month<br>
        NSFAS rates will apply<br>
        Subject to change
    </div>

    <div style="font-weight:bold; margin-top:10px;">
        BANKING DETAILS
    </div>

    <div>
        {{ $property->account_name }}<br>
        {{ $property->bank_name }}<br>
        ACCOUNT NUMBER: {{ $property->account_number }}<br>
        BRANCH NUMBER: {{ $property->branch_code }}
    </div>
</div>

<div style="margin-top:16px; margin-bottom:16px;">
    <div style="font-weight:bold;">Managing Agent</div>
    <div>
        Consolidated Urban Management (Pty) Ltd is the Official Management Agent of the Company
    </div>
</div>

<!-- ================= FOOTER ================= -->
<div style="position:fixed; bottom:10px; left:0; right:0; text-align:center; font-size:10px;">
    Consolidated Urban Management Corporation (PTY) Ltd
</div>

</body>
</html>
