<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Early Lease Termination</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
            line-height: 1.5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo{
            width: 80%;
            height:auto;
        }

        .owner{
            width: 50%;
            height: auto;
        }
        
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .section { margin-top: 20px; }
        .spacer { height: 20px; }
        .line { border-bottom: 1px solid #000; }

        footer {
            bottom: 25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>

<body>

<!-- ================= HEADER ================= -->
{{-- <table>
    <tr>
        <td width="100%" class="center">
            <div style="font-size: 11px;">
                14th Floor, Rennie House, 19 Ameshoff Street, Braamfontein,<br>
                Johannesburg, 2001 | Tel: 011 025 6526/7
            </div>
            <div style="margin-top: 5px; font-size: 11px;">
                VAT: 4600283107 &nbsp;&nbsp;&nbsp; Reg: 2018/357298/07
            </div>
        </td>
    </tr>
</table> --}}
<table>
    <tr>
        <td width="20%">
            <img class="logo" src="{{ $logo }}"  alt="logo">
        </td>

        <td width="80%" class="center" style="font-size: 11px;">
            14th Floor, Rennie House, 19 Ameshoff Street, Braamfontein,<br>
            Johannesburg, 2001 | Tel: 011 025 6526/7<br>
            VAT: 4600283107 &nbsp;&nbsp;&nbsp; Reg: 2018/357298/07
        </td>
    </tr>
</table>
<div class="section"></div>

<!-- ================= TITLE ================= -->
<div class="section bold underline center" style="font-size: 14px;">
    RE: EARLY LEASE TERMINATION
</div>

<div class="section"></div>

<!-- ================= BODY TEXT ================= -->
<div style="text-align: left; font-size: 12px;">
    In the event of the early termination of the written Temporary Lodging Agreement between,
    I 
    <span class="line" style="display: inline-block; width: 250px;">
        {{ $tenantName ?? '' }}
    </span>
    the lessee with ID/Passport number 
    <span class="line" style="display: inline-block; width: 200px;">
        {{ $tenantId ?? '' }}
    </span>
    and Braamby Trading (Pty) Ltd, the lessor.
</div>

<br>

<div style="text-align: left; font-size: 12px;">
    I, the lessee, will be liable for an early termination fee which is the full month’s rental.
</div>

<div class="section"></div>

<div style="text-align: left; font-size: 12px;">
    Your cooperation will be highly appreciated.
</div>

<div class="section"></div>

<!-- ================= DATE ================= -->
<div style="font-size: 12px;">
    Date:
    <span class="line" style="display: inline-block; width: 80px;"></span> /
    <span class="line" style="display: inline-block; width: 80px;"></span> /
    <span class="line" style="display: inline-block; width: 80px;"></span>
</div>

<div class="section" style="margin-top: 40px;"></div>

<!-- ================= SIGNATURES ================= -->
<table>
    <tr>
        <td width="33%" class="center">
            <div class="line" style="width: 90%; margin: auto;"></div>
            <div style="font-size: 11px;">(Tenant signature)</div>
        </td>

        <td width="33%" class="center">
            <div class="line" style="width: 90%; margin: auto;"></div>
            <div style="font-size: 11px;">For: Braamby Trading (Pty) Ltd</div>
        </td>

        <td width="33%" class="center">
            <div style="width: 90%; margin: auto;"></div>
            <div style="font-size: 11px;"></div>
        </td>
    </tr>
</table>

</body>
</html>

