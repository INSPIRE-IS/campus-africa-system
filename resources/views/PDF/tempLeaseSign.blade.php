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
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo{
            width: 50%;
            height:auto;
        }

        .owner{
            width: 50%;
            height: auto;
        }

        .center { text-align: center; }
        .left { text-align: left; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .section { margin-top: 20px; }
        .spacer { height: 20px; }
        .line { border-bottom: 1px solid #000; }

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

<!-- ================= HEADER ================= -->
<table>
    <tr>
        <td width="100%" class="right">
            <img class="logo" src="{{ $logo }}"  alt="logo">
            {{-- <img src="https://campusafrica.co.za/wp-content/uploads/2021/09/Site_Logo.svg'" height="70"> --}}
        </td>
    </tr>
    <tr>       
        <td width="100%" class="right">
            <div style="font-size: 11px; color:#ccc;">
                14th Floor, Rennie House, 19 Ameshoff Street, Braamfontein,
                Johannesburg, 2001 | Tel: 011 025 6526/7
            </div>
            <div style="margin-top: 5px; font-size: 11px; color:#ccc;">
                VAT: 4600283107 &nbsp;&nbsp;&nbsp; Reg: 2018/357298/07
            </div>
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
        {{ $pdfdata['tenantName'] ?? 'test' }}
    </span>
    the lessee with ID/Passport number 
    <span class="line" style="display: inline-block; width: 200px;">
        {{ $pdfdata['tenantId'] ?? 'test' }}
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
    Date:<?php echo date('Y-m-d');?> Signed at:  {{ $signed_at }}
</div>

<div class="section" style="margin-top: 40px;"></div>

<!-- ================= SIGNATURES ================= -->
<table>
    <tr>
        <td width="33%" class="center">
        <span class="line" style="display: inline-block; width: 200px;">
            <img src="{{ $signature }}" width="200" alt="signature" />
        </span>
            <div style="font-size: 11px;">(Tenant signature)</div>
        </td>

        <td width="33%" class="center">
        <span class="line" style="display: inline-block; width: 200px;">
            <img class="owner" src="{{ $owner }}"  alt="Juanita Cronje" />
        </span>
            <div style="font-size: 11px;">For: Braamby Trading (Pty) Ltd</div>
        </td>

        <td width="33%" class="center">
            <div  style="width: 90%; margin: auto;"></div>
            <div style="font-size: 11px;"></div>
        </td>
    </tr>
</table>

</body>
</html>

