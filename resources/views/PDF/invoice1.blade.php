<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Braamby Tax Invoice</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .border-bottom {
            border-bottom: 1px solid #000;
        }

        .mt {
            margin-top: 20px;
        }

        .items th {
            border-bottom: 1px solid #000;
            padding: 6px 0;
        }

        .items td {
            padding: 6px 0;
        }

        footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>

<body>

<!-- LOGO + HEADER -->
<table>
    <tr>
        <td>
            <img src="{{ public_path('images/braamby-logo.png') }}" height="70">
        </td>
        <td class="right">
            <span class="bold">TAX INVOICE</span><br>
            Invoice #: {{ $invoiceNumber }}<br>
            Invoice Date: {{ $invoiceDate }}
        </td>
    </tr>
</table>

<hr>

<!-- COMPANY INFO -->
<table>
    <tr>
        <td>
            <strong>Braamby Trading (Pty) Ltd</strong><br>
            VAT No: 4600283107<br>
            Reg No: 2018/357298/07<br><br>
            Managing Agent:<br>
            Consolidated Urban Management (Pty) Ltd
        </td>
    </tr>
</table>

<!-- BILL TO -->
<table class="mt">
    <tr>
        <td class="bold">Bill To:</td>
    </tr>
    <tr>
        <td>
            University of the Witwatersrand, Johannesburg<br>
            C/o Financial Aid and Scholarship Office<br><br>
            <span class="bold">Address:</span><br>
            Ground Floor – Solomon Mahlangu House<br>
            1 Jorissen Street, Braamfontein<br>
            2017<br><br>
            <span class="bold">VAT No:</span> 439-012-8942
        </td>
    </tr>
</table>

<!-- STUDENT INFO -->
<table class="mt">
    <tr>
        <td><strong>Student Name:</strong> {{ $user->userFirstName }} {{ $user->userLastname }}</td>
        <td class="right"><strong>Student Number:</strong> {{ $user->studentNo }}</td>
    </tr>
</table>

<!-- ITEMS -->
<table class="items mt">
    <thead>
        <tr>
            <th align="left">Details</th>
            <th align="center">Student No</th>
            <th align="right">Amount (Incl VAT)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item['description'] }}</td>
                <td class="center">{{ $user->studentNo }}</td>
                <td class="right">
                    {{ number_format($item['amount'], 2, ',', ' ') }}R
                </td>
            </tr>
        @endforeach

        <tr>
            <td colspan="3" class="border-bottom"></td>
        </tr>

        <tr>
            <td class="bold">TOTAL</td>
            <td></td>
            <td class="right bold">
                {{ number_format($totalAmount, 2, ',', ' ') }}R
            </td>
        </tr>
    </tbody>
</table>

<!-- AGING -->
<table class="mt">
    <tr class="bold">
        <td>60 DAYS+</td>
        <td>30 DAYS</td>
        <td>CURRENT</td>
        <td class="right">TOTAL</td>
    </tr>
    <tr>
        <td>-R</td>
        <td>-R</td>
        <td class="right">{{ number_format($totalAmount, 2, ',', ' ') }}R</td>
        <td class="right">{{ number_format($totalAmount, 2, ',', ' ') }}R</td>
    </tr>
</table>

<!-- BANKING -->
<table class="mt">
    <tr>
        <td class="bold">BANKING DETAILS</td>
    </tr>
    <tr>
        <td>
            Braamby Trading (Pty) Ltd & The Truzen 130 Trust<br>
            Nedbank<br>
            Account No: 1191709582<br>
            Branch: 198765<br>
            Reference: {{ $user->studentNo }} - {{ $user->userFirstName }} {{ $user->userLastname }}
        </td>
    </tr>
</table>

<footer>
    Accommodation for Bursary Student Invoice
</footer>

</body>
</html>
