<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <style>
        footer {
            position: fixed; 
            bottom: 0px; 
            left: 0px; 
            right: 0px;
            height: 50px; 

            /** Extra personal styles **/
            color: #ccc;
            text-align: center;
            line-height: 35px;
        }
        .page_break { page-break-before: always; }
    </style>
</head>


<body>
    <div class="container mt-5">
        <h2 style="text-align: center"><i>Consolidated Urban Management Corporation (PTY) Ltd</i></h2>
        <br/>
        <table style="width:100%">
            <tr>
                <td>VAT NO: {{$property->company_vat}}</td>
                <td style="text-align:right">Owner Reg No: {{$property->company_reg}}</td>
            </tr>
        </table>
        <p style="text-align: center">{{$property->account_name}}<br/>
        <span style="text-transform:uppercase">{{$property->residence}}</span> - CAMPUS AFRICA<br/>
        TEL: 011 0256257 | Email: accounts@conurban.co.za | Fax: 0865587632</p>
        <br/>
        <table style="width:100%">
            <tr>
                <td style="text-transform:uppercase">Performa Invoice</td>
                <td style="text-align:right">No: 072/2022</td>
            </tr>
        </table>
        <br/>
        <table style="width:100%">
            <tr>
                <td style="border-top: 1px solid #000; border-left: 1px solid #000;">Student name</td>
                <td style="border-top: 1px solid #000; border-right: 1px solid #000;"></td>
                <td style="border-top: 1px solid #000; border-left: 1px solid #000;"></td>
                <td style="border-top: 1px solid #000; border-right: 1px solid #000;"></td>
            </tr>
            <tr>
                <td style="border-left: 1px solid #000;">{{$user->userFirstName}}</td>
                <td style="border-right: 1px solid #000;">{{$user->userLastname}}</td>
                <td style="border-left: 1px solid #000;">Date</td>
                <td style="border-right: 1px solid #000;"><?php echo date('Y-m-d');?></td>
            </tr>
            <tr>
                <td style="border-bottom: 1px solid #000; border-left: 1px solid #000;"></td>
                <td style="border-bottom: 1px solid #000; border-right: 1px solid #000;"></td>
                <td style="border-bottom: 1px solid #000; border-left: 1px solid #000;">Student No</td>
                <td style="border-bottom: 1px solid #000; border-right: 1px solid #000;">{{$user->studentNo}}</td>
            </tr>
        </table>
        <br/>
        <table style="width:100%">
            <tr>
                <td style="text-transform:uppercase; border-bottom:1px solid #000">Date</td>
                <td style="text-transform:uppercase; border-bottom:1px solid #000">Reference</td>
                <td style="text-transform:uppercase; border-bottom:1px solid #000"></td>
                <td style="text-transform:uppercase; border-bottom:1px solid #000">Description</td>
                <td style="text-transform:uppercase; border-bottom:1px solid #000">Debit</td>
                <td style="text-transform:uppercase; border-bottom:1px solid #000">Credit</td>
                <td style="text-transform:uppercase; border-bottom:1px solid #000">Balance</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>Lease Admin Fee</td>
                <td></td>
                <td></td>
                <td>R {{number_format($bookingPayment->loan_admin_fee, 2, '.', ' ')}}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>Deposit</td>
                <td></td>
                <td></td>
                <td>R {{number_format($bookingPayment->deposit, 2, '.', ' ')}}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <?php for($i = new DateTime($booking->lease_from_date); $i <= new DateTime($booking->lease_to_date); $i->modify('+1 month')){ ?>

                <tr>
                    <td><?php echo $i->format("Y-m-d");?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $i->format("F");?> Rent</td>
                    <td></td>
                    <td></td>
                    <td>R {{number_format($bookingPayment->monthly_rental, 2, '.', ' ')}}</td>
                </tr>

            <?php }?>
            <tr>
                <td style="border-bottom:1px solid #000"></td>
                <td style="border-bottom:1px solid #000"></td>
                <td style="border-bottom:1px solid #000"></td>
                <td style="border-bottom:1px solid #000"></td>
                <td style="border-bottom:1px solid #000"></td>
                <td style="border-bottom:1px solid #000"></td>
                <td style="border-bottom:1px solid #000"></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>Total Due</td>
                <td></td>
                <td>R</td>
                <td>R {{number_format(($bookingPayment->monthly_rental_total + $bookingPayment->deposit + $bookingPayment->loan_admin_fee), 2, '.', ' ')}}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>60 DAYS +</td>
                <td>30 DAYS</td>
                <td>CURRENT</td>
                <td>TOTAL</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="border-bottom:1px solid #000">0.0</td>
                <td style="border-bottom:1px solid #000">0.0</td>
                <td style="border-bottom:1px solid #000">0.0</td>
                <td style="border-bottom:1px solid #000">R {{number_format(($bookingPayment->monthly_rental_total + $bookingPayment->deposit + $bookingPayment->loan_admin_fee), 2, '.', ' ')}}</td>
                <td style="border-bottom:1px solid #000"></td>
                <td style="border-bottom:1px solid #000"></td>
                <td style="border-bottom:1px solid #000"></td>
            </tr>
        </table>
        <br/>
        <p>NB: Interest will be charged at the rate of 2% per month<br/>
        NSFAS rates will apply<br/>
        Subject to change</p>
        <br/>
        <p><b>BANKING DETAILS</b></p>
        <p><b>{{$property->account_name}}<br/>
        {{$property->bank_name}}<br/>
        ACCOUNT NUMBER: {{$property->account_number}}<br/>
        BRANCH NAME: {{$property->branch}}</p>
    </div>
</body>

</html>