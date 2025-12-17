<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Braamby Tax Invoice</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td {
            vertical-align: top;
        }

        .bold { font-weight: bold; }
        .left { text-align: left; }
        .right { text-align: right; }
        .center { text-align: center; }
        .text-xlarge { font-size: 24px; }
        .text-large { font-size: 18px; }
        .text-medium { font-size: 14px; }
        .text-small { font-size: 12px; }
        .text-xsmall { font-size: 10px; }

        .section {
            margin-top: 14px;
        }

        .line {
            border-bottom: 1px solid #000;
        }

        tr { page-break-inside: avoid; }

        footer {
            position: fixed;
            bottom: 25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>

<body>

@php
    /* Lock column widths ONCE */
    $colDetails = '55%';
    $colStudent = '20%';
    $colAmount  = '25%';
@endphp

<!-- ================= HEADER ================= -->
<table>
    <tr>
        <!-- LEFT BLOCK -->
        <td width="55%">
            <table>
                <tr>
                    <td>
                        <img src="{{ public_path('images/consolidated_urban.jpg') }}" height="65">
                    </td>
                </tr>
            </table>
        </td>

        <!-- RIGHT BLOCK -->
        <td width="45%">
            <table class="section">
                <tr>
                    <td width="90%" class="left bold text-xlarge">Tax Invoice</td>
                    <td width="10%"></td>
                </tr>                
                <tr>
                    <td width="30%" class="bold text-xsmall">Invoice #:</td>
                    <td width="70%">{{ $invoiceNumber }}</td>
                </tr>
                <tr>
                    <td width="30%" class="bold text-xsmall">Invoice date:</td>
                    <td width="70%">{{ $invoiceDate }}</td>
                </tr>
                <tr>
                    <td width="30%" class="bold text-xsmall">Student Number:</td>
                    <td width="70%">{{ $user->studentNo }}</td>
                </tr>
                <tr>
                    <td width="30%" class="bold text-xsmall">Student Name:</td>
                    <td width="70%">{{ $user->userFirstName }} {{ $user->userLastname }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- ================= BILL TO ================= -->
<table>
    <tr>
        <!-- LEFT BLOCK -->
        <td width="55%">
            <table class="section">
                <tr class="bold"><td >Braamby Trading (Pty) Ltd</td></tr>
                <tr><td>P O Box 55597 Northlands 2116</td></tr>               
            </table>

            <table class="section">
                <tr class="bold text-xsmall"><td>Campus Africa, 49 Jorrissen Street Johannesburg 2001</td></tr>
                <tr class="text-xsmall"><td>VAT No: 4600283107 & 4370280168</td></tr>
                <tr class="text-xsmall"><td>Company Registration Number: 2018/357298/07 & IT000290/2017(N)</td></tr>
                <tr class="text-xsmall"><td>Account Queries : Andrea Fourie</td></tr>
                <tr class="text-xsmall"><td>Email: andrea@conurban.co.za</td></tr>
            </table>
        </td>

        <!-- RIGHT BLOCK -->
        <td width="45%">
            <table class="section">
                <tr>
                    <td width="30%" class="bold">Bill to:</td>
                    <td width="70%">
                        @if(!empty($billTo['name'])){{ $billTo['name'] }}<br>@endif
                        @if(!empty($billTo['regNo'])){{ $billTo['regNo'] }}<br>@endif
                        @if(!empty($billTo['contactName'])){{ $billTo['contactName'] }}<br>@endif
                        @if(!empty($billTo['contactEmail'])){{ $billTo['contactEmail'] }}<br>@endif
                        @if(!empty($billTo['contactPhone'])){{ $billTo['contactPhone'] }}<br>@endif
                    </td>
                </tr>
            </table>

            <table class="section">
                <tr>
                    <td width="30%" class="bold">Address:</td>
                    <td width="70%">
                      {{ $billAddress['address1'] }}<br>
                      {{ $billAddress['address2'] }}<br>
                      {{ $billAddress['addressCity'] }}<br>
                      {{ $billAddress['addressPostal'] }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- ================= ITEMS ================= -->
<table class="section">

    <!-- HEADER -->
    <tr>
        <td width="{{ $colDetails }}" class="bold">Accommodation for Bursary student invoice</td>
        <td width="{{ $colDetails }}" class="bold">Details</td>
        <td width="{{ $colStudent }}" class="bold center">Number of Student</td>
        <td width="{{ $colAmount }}" class="bold right">Amount Incl VAT</td>
    </tr>

    <tr>
        <td colspan="3" class="line"></td>
    </tr>

    <!-- ROWS -->
    @foreach($items as $item)
        <tr>
            <td width="{{ $colDetails }}">{{ $item['date'] }}</td>
            <td width="{{ $colDetails }}">{{ $item['description'] }}</td>
            <td width="{{ $colStudent }}" class="center">{{ $user->studentNo }}</td>
            <td width="{{ $colAmount }}" class="right">
                R {{ number_format($item['amount'], 2, ',', ' ') }}
            </td>
        </tr>
    @endforeach

    <tr>
        <td colspan="4" class="line"></td>
    </tr>

    <!-- TOTAL -->
    <tr>
        <td></td>
        <td></td>
        <td class="bold">TOTAL</td>
        <td class="right bold">
            {{ number_format($totalAmount, 2, ',', ' ') }}R
        </td>
    </tr>
</table>

<!-- ================= AGING ================= -->
<table class="section">
    <tbody  width="70%">
    <tr class="bold">
        <td width="20%">60 DAYS+</td>
        <td width="20%">30 DAYS</td>
        <td width="20%">CURRENT</td>
        <td width="20%" class="left">TOTAL</td>
        <td width="20%"></td>
    </tr>
    <tr>
        <td>-R</td>
        <td>-R</td>
        <td class="left">R {{ number_format($totalAmount, 2, ',', ' ') }}</td>
        <td class="left">R {{ number_format($totalAmount, 2, ',', ' ') }}</td>
        <td></td>
    </tr>
    </tbody>
</table>

<!-- ================= BANKING ================= -->
<table class="section">
    <tr>
        <td class="bold">NB: Interest will be charged at the rate of 2% per month<br/>NSFAS rates will apply<br/>Subject to change</td>
    </tr>

    <tr>
        <td class="bold">BANKING DETAILS</td>
    </tr>
    <tr>
        <td>
            {{$property->account_name}}<br/>
            {{$property->bank_name}}<br>
            ACCOUNT NUMBER: {{$property->account_number}}<br>            
            BRANCH NUMBER: {{$property->branch_code}}
        </td>
    </tr>
</table>

<table class="section">
    <tr>
        <td class="bold">Managing Agent</td>
    </tr>
    <tr>
        <td>
            Consolidated Urban Management (Pty) Ltd is the Official Management Agent of the Company
        </td>
    </tr>
</table>

<footer>
    Consolidated Urban Management Corporation (PTY) Ltd
</footer>

</body>
</html>
