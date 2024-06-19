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
        <img src='https://campusafrica.co.za/wp-content/uploads/2021/09/Site_Logo.svg' alt="Logo" style="width:70px; margin-left:44%;">
        <h2 style="text-align:center">TEMPORARY LODGING AGREEMENT</h2>
        <p style="text-align:center; font-size:13px; font-weight:bold; margin-top:-10px;">BETWEEN</p>
        <h4 style="text-align:center; margin-top:-10px;">CONSOLIDATED URBAN MANAGEMENT CORPORATION (PTY) LTD</h4>
        <h4 style="text-align:center; margin-top:-20px;">(ON BEHALF OF {{$residence->owner}})</h4>
        <p style="text-align:center;font-weight:bold; font-size:13px;">(“the Owner”)</p>
        <p style="text-align:center; font-weight:bold; font-size:13px;">AND</p>
        <br/>
        <table style="width:100%; margin-top:-20px; margin-top:-15px;">
            <tr>
                <td style="text-align:right; padding-right:70px 20px;">
                    <span style="font-size:20px;"><b>{{$data->userFirstName}} {{$data->userLastname}}</b></span>
                </td>
                <td style="text-align:left; padding-left:70px 20px;">
                    <span style="font-size:20px;"><b>@if($data->userNationality == "South Africa") {{$data->userId}} @else {{$data->userPassport}} @endif</b></span>
                </td>
            </tr>
            <tr>
                <td style="text-align:right; padding-right:70px 20px;">
                    Full Name
                </td>
                <td style="text-align:left; padding-left:70px 20px;">
                    @if($data->userNationality == "South Africa") {{"ID Number"}} @else {{"Passport Number"}} @endif
                </td>
            </tr>
        </table>
        <p style="text-align:center; font-weight:bold; font-size:15px;">(“the Student”)</p>
        <br/>
        <hr/>
        <br/>

            <p style="font-size:14px;"><strong>1.	Definitions </strong>
            <br/>

            In this agreement, unless clearly inconsistent with or otherwise indicated by the context, the following words and phrases shall have the corresponding meanings assigned to them and cognate expressions shall have similar meanings: </p>

            <ol type="a" style="text-align:left; font-size:14px;">

                <li><strong> “Accommodation”</strong> means: the provision of temporary student lodging through a single or shared room within an apartment at the student accommodation residence {{$residence->residence}} situated at {{$residence->address}}, Johannesburg, or any other room of a similar quality and standard which the Owner may allocate to the Student and may change from time to time. </li>

                <br/>

                <li><strong>“Accommodation Period”</strong> means: the period commencing on <b>{{$booking->lease_from_date}} at {{$booking->lease_from_time}}</b> and will terminate on <b>{{$booking->lease_to_date}} at {{$booking->lease_to_time}}</b>, which period shall in all instances not exceed a maximum of 10 months (i.e. a student academic year).</li>

                <br/>

                <li><strong>“Contents”</strong> means: the furniture, appliances and electrical goods as listed in Annexure “A” hereto; </li>
                <br/>


                <li><strong>“Day”</strong> means: any day of the week, excluding Saturdays, Sundays and gazetted public holidays; </li>
                <br/>

                <li><strong>“the Student”</strong> means: any person who occupies the provided accommodation in terms of this agreement, and who has mere use thereof without however enjoying exclusive possession thereof; </li>
                <br/>


                <li><strong>“Month”</strong> means: a calendar month, and more specifically: </li>

                <p style="padding-left:10px"><span style="margin-left:-20px;">i. </span>	With reference to a number of months from a specific date, a calendar month commencing on that date or the same date of any subsequent month; </p>

                <p style="padding-left:10px"><span style="margin-left:-20px;">ii. </span>	In any other context, a month of the calendar, that is, one of the 12 (twelve) months of the calendar and, “monthly” has the corresponding meaning; </p>

                <li><strong>“the Owner”</strong> means Consolidated Urban Management Corporation (Pty) Ltd or any one of the five companies listed in Annexure “B” it manages and leases properties for and on behalf of, a company duly registered in accordance with the laws of The Republic of South Africa, or any of its duly authorised agents or assigns. </li>
                <br/>

                <li><strong>“Student Meals”</strong> means: the provision of meals by the Owner to the Student on a daily basis, which meals shall be consumed on-site at the Accomodation</li>
            </ol>



        <footer>
            Page | 1
        </footer>

        <div class="page_break"></div>


        <img src='https://campusafrica.co.za/wp-content/uploads/2021/09/Site_Logo.svg' alt="Logo" style="width:70px; margin-left:44%;">

            <ol style="list-style-type:none; text-laign:left; font-size:14px;">
                <li><span style="margin-left:-20px;">f. </span><strong>“Swipe Card”</strong> means: a payment mechanism provided to Students of tertiary institutions who receive bursary, loan, sponsorship or related allowances, for the consumption of student necessities, by suppliers, which suppliers for the purposes of this agreement include but are not limited to, ‘NSFAS’, ‘Intellicard’, ‘Eduloan’ and ‘Wizzard’; </li>
                <br/>

                <li><span style="margin-left:-20px;">g. </span><strong>“Shared Rooms”</strong> means: a room within the building consisting of two bedrooms to be shared by a maximum of 2 occupants. The occupants of shared rooms are permitted to use the common areas within the building consisting of the bathrooms, kitchenette and living area. The occupants of shared rooms are also permitted to use the other residence facilities, including but not limited to the canteen, the TV room, the study centre, the gym and the computer centre; </li>
                <br/>

                <li><span style="margin-left:-20px;">h. </span><strong>“Single”</strong> means: a room within the building consisting of one bed. The occupants of Singles are permitted to use the common areas within the building consisting of the bathrooms, kitchenette and living area. The occupants of Singles are also permitted to use the other residence facilities, including but not limited to the canteen, the TV room, the study centre, the gym and the computer centre; </li>
                <br/>

                <li><span style="margin-left:-20px;">i. </span><strong>“This Agreement”</strong> means: this temporary lodging agreement together with any and/or all Annexes hereto; </li>
                <br/>

                <li><span style="margin-left:-20px;">j. </span><strong>“Utilities”</strong> means: services provided by The Owner to The Student, including but not limited to the use of water, electricity, sewage and refusal removal; </li>
                <br/>

                <li><span style="margin-left:-20px;">k. </span>Expressions defined in this agreement shall bear the same meanings in schedules or annexes to this agreement that do not themselves contain their own conflicting definitions; </li>
                <br/>

                <li><span style="margin-left:-20px;">l. </span>The expiration or termination of this agreement shall not affect such of the provisions of this agreement as expressly provide that they will operate after any such expiration or termination or that of necessity must continue to have effect after such expiration or termination, notwithstanding that the clauses themselves do not expressly provide for this;  </li>
                <br/>

                <li><span style="margin-left:-20px;">m. </span><strong>"POPIA”</strong> means the Protection of Personal Information Act, No 4 of 2013; </li>
                <br/>

            </ol>

            <span style="font-weight:bold; font-size:14px;">2. Nature of Agreement</span>

            <ol type="a" style="text-align:left; font-size:14px;">
                <li>Under this Temporary Lodging Agreement including all annexures hereto, the Owner grants a personal right to the Student to use and occupy the Accommodation on a non-exclusive basis for the Accommodation Period. </li>
                <br/>

                <li>Nothing contained in this Temporary Lodging Agreement is intended to or will be constructed as setting up the relationship of Landlord and Tenant between the parties. </li>
                <br/>

                <li>The parties agree and confirm that no tenancy or right of exclusive occupation, or exclusive possession is created by this Temporary Lodging Agreement.</li>
                <br/>

                <li>The Owner reserves the right, at its sole discretion, to assign an alternative room as accommodation within the complex, and to require the Student to relocate from any room to another room within the complex, provided that any alternative room provided to the Student for purposes of accommodation may not materially differ in standard or quality from the accommodation agreed to in this Temporary Lodging Agreement.</li>
                <br/>

                <li>The Student will not impede the Owner (or any agent of the Owner) in their exercise of the Owner’s right of possession and control of the Accommodation. </li>
                <br/>

                <li>The Student must give all reasonable assistance to any person authorised by the Owner to enter the Accommodation. Wherever possible, the Student will be told in advance of such visits. </li>
            </ol>

            <footer>
                Page | 2
            </footer>

            <div class="page_break"></div>


            <img src='https://campusafrica.co.za/wp-content/uploads/2021/09/Site_Logo.svg' alt="Logo" style="width:70px; margin-left:44%;">
            <br/>
            <span style="font-weight:bold; font-size:14px;">3. Payment of Accommodation and Student Meal Fees </span>

                <ol type="a" style="text-laign:left; font-size:14px;">
                    <li>The Student agrees to pay the Owner, the sum of <b>R{{$paymentBooking->monthly_rental}}</b> as a monthly fee for the non-exclusive right to make use of the facilities at the residence and Single / Shared Room.
                         <!-- and a further R ??? as an optional monthly fee for the provision of Student Meals on a daily basis -->
                        </li>
                    <br/>

                    <li>The Accommodation and Student Meal fees (if requested) are payable in advance on or before the second day of each month. Where the lodger moves in during a month, a pro-rata amount based on the number of days that the lodger occupies a room for that month will be due and payable by the lodger in the first month. Thereafter, clause 3a applies. </li>
                    <br/>

                    <li>The total monthly accommodation fee includes an amount payable towards the use of a room per student and also includes the cost of utilities. To the extent that the consumption incurred in respect of the utilities exceeds the amount provided for within the accommodation fee, the Owner reserves the right to increase the accommodation fee to recover such differential amount from the Student. </li>
                    <br/>

                    <li>d.	The Accommodation and Student Meal fees (if requested) shall be payable in cash or by bank transfer or by credit card or by swipe card or by debit order</li>
                    <br/>

                    <li>For each payment which the Student makes after the due date, the Student must pay the Owner interest at the minimum of Prime + 2% or the maximum of rate allowed by law. Interest will accrue daily.</li>
                    <br/>

                    <li>The Student shall commit to be liable for his/her rental by providing an acknowledgment of obligation stating such, attached hereto as Annexure C. Failure to make timeous payments by the Student shall be breach of contract and section 11 shall apply.</li>
                    <br/>

                    <li>If the Student is a bursary holder he/she will be responsible for the full ten (10) month lease regardless if the Student passes of fails his/her semester/year.</li>
                </ol>

            <span style="font-weight:bold; font-size:14px;">4.	Administration Fee</span>

            <ol type="a" style=" text-laign:left; font-size:14px;">
                <li>On signature of this Agreement, the Student agrees to pay a non-refundable, once-off fee to the Owner in the amount of <b>R
                @if($paymentBooking->rob_loan_waived == '2') 0 @else @if(($data->studentType == "Cash / Direct") || ($data->studentType == "Bursary" && $data->nsfas_bursar !== 1)) {{$paymentBooking->loan_admin_fee}} @else 0 @endif @endif
                </b>. </li>
                <br/>
            </ol>

            <span style="font-weight:bold; font-size:14px;">5.	The Security Deposit </span>

            <ol type="a" style="text-laign:left; font-size:14px;">
                <li>The Student agrees to deposit with the Owner the amount of <b>R @if($paymentBooking->rob_deposit_waived == '2') 0 @else @if(($data->studentType == "Cash / Direct") || ($data->studentType == "Bursary" && $data->nsfas_bursar !== 1)) {{$paymentBooking->deposit}} @else 0 @endif @endif </b>month fee as a security deposit, payable to the Owner subsequent to the signature date hereto but prior to the commencement of the accommodation period, which security deposit shall be held by the Owner against any breach or non-performance of this agreement or any part hereof by the Student, including but not limited to the application of the security deposit by the Owner, in whole or part, in meeting any payment due by the Student to the Owner during the Accommodation Period or subsequent to the termination of this agreement, or against any damage to the Accommodation or any part of the Owner’s property which has been caused by the Student or by anyone who the Student allows to access to the Owner’s property. </li>
                <br/>

                <li>The Student shall not deduct the security deposit or any part thereof from the accommodation fees payable during any month of the agreement. </li>
                <br/>

                <li>At any time during the accommodation period, and subsequent to the security deposit or any part thereof having been applied by the Owner in terms of this clause 5 (five), the Student shall upon demand by the Owner reinstate the security deposit to its original amount.</li>
                <br/>

                <li>Subsequent to the termination of this agreement in accordance with clause 10 (ten) infra, the Owner shall refund to the Student, free of interest, so much of the security deposit as has not been applied by the Owner in terms of this clause 5 (five). </li>
            

            <footer>
                Page | 3
            </footer>

            <div class="page_break"></div>


            <img src='https://campusafrica.co.za/wp-content/uploads/2021/09/Site_Logo.svg' alt="Logo" style="width:70px; margin-left:44%;">
            <br/>

                <li>If the deposit is insufficient to cover the amount required to cover such repairs or remedies as are permitted deductions under this Temporary Lodging Agreement, the Student shall pay on demand such further sum as the Owner reasonably requires in order to cover the amount which the deposit would have otherwise covered. </li>
                <br/>

                <li>In the event of the Student failing to provide the Owner with the security deposit as contemplated in this clause 5 (five) subsequent to signature hereto but prior to the commencement of the accommodation period, the Owner shall not be compelled to grant the Student occupation of the accommodation; </li>
                <br/>
            </ol>
            <br/>

            <span style="font-weight:bold; font-size:14px;">6.	The Student’s Obligations </span>

            <ol type="a" style=" text-laign:left; font-size:14px;">
                <li>The Student must: </li>
                <br/>
                <ol type="i">
                    <li>Pay the Accommodation Fees at the time and in the manner agreed upon free of deduction; </li>
                    <br/>
                    <li>Sign and abide by the house rules; </li>
                    <br/>
                    <li>Use the Accommodation solely in accordance with the terms and conditions of this agreement; </li>
                    <br/>
                    <li>At all times maintain the Accommodation in a clean, tidy and sanitary state. </li>
                    <br/>
                    <li>Leave the Accommodation in a clean, tidy and sanitary state after each use. </li>
                    <br/>
                    <li>Lock the Accommodation when going out to protect the security of the Owner’s property and other Students within the property.</li>
                    <br/>
                    <li>Permit the Owner (or the Owner’s agents) to enter the Accommodation at any reasonable hour for purposes of providing services, such as cleaning of the Accommodation</li>
                    <br/>
                    <li>Ensure that any accounts set up in his/her name will be addressed to him/her personally and that all such accounts will be paid promptly and will be settled in full prior to the Student moving out of the Accommodation. </li>
                    <li>Vacate the Accommodation at the end of the Accommodation Period and return any keys and access cards to the Owner. </li>
                </ol>
                <br/>
                <li>The Student shall not: </li>
                <br/>
                <ol type="i">
                    <li>Sell, hire out or remove any of the Contents. </li>
                    <br/>
                    <li>Deface or damage the Accommodation, the Contents or any part of the Owner’s property</li>
                    <br/>
                    <li>Play any musical instrument, live or electronic music, radio or television or engage in any other noisy or disruptive activity between such hours as the Owner may stipulate. </li>
                    <br/>
                    <li>Keep anything dangerous, explosive, combustible or flammable in the Accommodation. </li>
                    <br/>
                    <li>Store any of his possessions in or on any part of the Owner’s property other than in the Accommodation provided unless the Owner gives him/her permission to do so. </li>
                    <br/>
                    <li>Keep any animals in the Accommodation without the Owner’s prior written permission. </li>
                    <br/>

                    <footer>
                        Page | 4
                    </footer>

                    <div class="page_break"></div>


                    <img src='https://campusafrica.co.za/wp-content/uploads/2021/09/Site_Logo.svg' alt="Logo" style="width:70px; margin-left:44%;">
                    <br/>

                    <li>Permit any third party to use the Accommodation, otherwise than explicitly provided for by this Temporary Lodging Agreement. </li>
                    <br/>
                    <li>Do or permit or omit to be done anything which might endanger any persons present at the accommodation at any time. </li>
                    <br/>
                    <li>Cede or assign to any persons, any rights or obligations of the Student in terms of this agreement;</li>
                </ol>
            </ol>

            <span style="font-weight:bold; font-size:14px;">7.	The Owner’s Obligations </span>

            <ol type="a" style=" text-laign:left; font-size:14px;">
                <li>The Owner shall: </li>
                <br/>
                <ol type="i">
                    <li>Provide the agreed upon Accommodation to the Student for purposes of the non-exclusive use of such Accommodation by the Student, in such part of the Owner’s property as the Owner may determine.</li>
                    <br/>
                    <li>Ensure that the Accommodation is in a good clean condition immediately before the Student is given access thereto and that the Shared / Singles are in such condition for the duration of the Temporary Lodging Agreement.</li>
                </ol>
                <br/>
                <li>In addition, the following services and facilities are provided to the Student: </li>
                <br/>
                <ol type="i">
                    <li>The provision of optional Student Meals on a daily basis.</li>
                    <br/>
                    <li>Introduction to the Accommodation and a key hand-over. </li>
                    <br/>
                    <li>Cleaning of the Accommodation before and during the stay</li>
                    <br/>
                    <li>Housekeeping (cleaning of the wash up areas, showers and toilets) and maintenance (upkeep of the accommodation). </li>
                    <br/>
                    <li>Check-out and collection of keys.</li>
                    <br/>
                    <li>Uncapped Wi–Fi Internet Access.</li>
                    <br/>
                    <li>A Laundry Facility which shall be made available to the Student for use at its sole election, risk and cost, </li>
                    <br/>
                    <li>A 24 (twenty four) hour, guarded security service of the accommodation and common areas, to be operational 7 (seven) days a week. </li>
                    <br/>
                    <li>Access to an on or offsite Residence Manager and/or Residence Team. </li>
                    <br/>
                    <li>Any other services requested by the Student and which the Owner has agreed, in writing, to provide. </li>
                </ol>
            </ol>
            <br/>

            <span style="font-weight:bold; font-size:14px;">8.	Liability of the Owner</span>
            <br/>
            <p style="font-size:14px;">The Student hereby indemnifies and holds harmless the Owner from and against any and/or all claims, losses, costs, charges, expenses, damages and liabilities of whatsoever nature howsoever arising which the Student may incur, pay or sustain in connection with the Student’s occupation of the Shared Room or Singles, including, all costs and expenses brought about in connection with any loss or damage.</p>

            <footer>
                Page | 5
            </footer>

            <div class="page_break"></div>


            <img src='https://campusafrica.co.za/wp-content/uploads/2021/09/Site_Logo.svg' alt="Logo" style="width:70px; margin-left:44%;">
            <br/>

            <span style="font-weight:bold; font-size:14px;">9.	Guests of the Student </span>

            <ol type="a" style=" text-laign:left; font-size:14px;">
                <li>The Student may invite any person (“guests”) other than any who have been barred by the Owner from visiting the property into the Accommodation, provided that such guests must comply with any signing in and out procedures, which may be prescribed from time to time by the Owner.</li>
                <br/>
                <li>All Guests are required to leave by 11pm unless prior permission has been given by the Owner for them to remain longer. Permission is given at the sole discretion of the Owner and its decision is final. </li>
                <br/>
                <li>A guest may only stay in the Accommodation overnight if the Owner gives permission for this to occur. Permission is given at the sole discretion of the Owner and its decision is final. </li>
                <br/>
                <li>Students may not have overnight guests.</li>
                <br/>
                <li>If the Student has unauthorized guests, he or she will be deemed to be in breach, which may result in termination of this Temporary Lodging Agreement. </li>
                <br>
                <li>All guests shall be subject to the duties and obligations applicable to the Student in terms of this agreement. The Student must take responsibility for the good conduct and behaviour of all guests and must repay to the Owner for any costs which the Owner incurs in repairing any damage caused by guests. In this regard the provisions of clause 5 (five) are applicable to this clause 9e.</li>
                <br/>
            </ol>

            <span style="font-weight:bold; font-size:14px;">10.	Termination  </span>

            <ol type="a" style=" text-laign:left; font-size:14px;">
                <li>This Temporary Lodging Agreement may be terminated by either party giving 1 (one) month’s written notice to the other party of their intention to terminate.</li>
                <br/>
                <li>The parties agree that the Notice referred to in 10a supra may only be furnished by either party to the other party on the last day of any particular month during which this agreement remains of force and effect. </li>
                <br/>
                <li>Upon termination of this Temporary Lodging Agreement, the Student must immediately leave the Accommodation and remove his or her belongings from within the Accommodation or anywhere else on the Owner’s property. </li>
                <br/>
                <li>If the Student fails to comply with the above clause the Owner may take all steps necessary to immediately remove the Student and his or her belongings.</li>
                <br/>
                <li>In the event of the Student vacating the accommodation prior to furnishing the Owner with the requisite notice period in terms of 10a supra, the provisions of clause 5 shall at the Owners election become applicable, this however shall not preclude the Owner from pursuing any other remedies available to it in law for the recovery of any monies payable to the Owner by the Student in terms of this agreement or in the pursuance of any claims for damages against the Student.</li>
                <br/>
            </ol>

            <span style="font-weight:bold; font-size:14px;">11.	Special Remedy for Breach:   </span>

            <ol type="a" style=" text-laign:left; font-size:14px;">
                <li>a.	Notwithstanding clause 10b supra, in the event that the Student defaults in any payment due under this accommodation agreement or breaches any other terms, or breaches the house rules in Annexure C attached hereto, and fails to remedy such default or breach within 3 (three) Days after receiving a written demand that it be remedied, the Owner may without further notice: </li>
                <ol type="i" style="font-size:14px;">
                    <li>cancel this agreement with immediate effect</li>
                    <br/>
                    <li>take back possession of the accommodation, </li>
                    

                    <footer>
                        Page | 6
                    </footer>

                    <div class="page_break"></div>


                    <img src='https://campusafrica.co.za/wp-content/uploads/2021/09/Site_Logo.svg' alt="Logo" style="width:70px; margin-left:44%;">
                    <br/>
                    <li>have a common law right to retain the Student’s movables to ensure payment of his or her Accommodation Fees, and </li>
                    <br/>

                    <li>recover damages from the Student for the default or breach and the cancellation of this agreement </li>

                </ol>
                <br/>
                <li>This will not affect any other rights or remedies the Owner might have in terms of any other provision of this agreement or in law. </li>
                <br/>
                <li>This Clause 11 does not exclude the ordinary lawful consequences of breach (except those that are expressly excluded by any of the other provisions of this agreement) and in particular any right of cancellation of this agreement on the ground of a material breach going to the root of this agreement</li>
            </ol>
            <br/>
            <span style="font-weight:bold; font-size:14px;">12.	Domicilia Citandi Et Executandi </span>

            <ol type="a" style=" text-laign:left; font-size:14px;">
                <li>The parties choose as their domicilia citandi et executandi (“domicilium”) for all purposes under this agreement, whether in respect of court process, notices or other documents or communications of whatsoever nature, their respective addresses as appear below: </li>
                <ol type="i" style="font-size:14px;">
                    <li>The Owner: 
                        <br/><br/>
                        <span style="font-weight:bold">Physical Address:</span> 14th Floor, 19AM, 19 Ameshoff Street, Braamfontein, Johannesburg, 2001
                        <br/>
                        <span style="font-weight:bold">Fax Number:</span> 086 558 7632
                        <br/>
                        <span style="font-weight:bold">Email:</span> students@conurban.co.za
                    </li>
                    <br/>
                    <li>The Student:
                        <br/><br/>
                        <span style="font-weight:bold">The unit occupied under this agreement</span>
                        <br/><br/>
                        <span style="font-weight:bold">Accommodation centre:</span> {{$propertyData->residence}}
                        <br/>
                        <span style="font-weight:bold">Physical Address:</span> {{$residence->address}}
                        <br/>
                        <span style="font-weight:bold">Unit No:</span> {{$propertyData->bed_code}} (or such other number that the student may be allocated in terms of clause 2d, which unit number shall be evidenced by reference to the owner's tenancy schedule report)
                        <br/>
                        <span style="font-weight:bold">Email:</span> {{$data->userEmail}}
                        <br/>
                        <span style="font-weight:bold">Cell Number:</span> {{$data->userCell}}
                        <br/><br/>
                        <span style="font-weight:bold">The student’s normal residential address (outside of the academic year) </span>
                        <br/><br/>
                        <span style="font-weight:bold">Physical Address:</span> {{$data->userAddress1}}, {{$data->userAddress2}}, {{$data->userAddressCity}}, {{$data->userAddressPostal}}
                        <br/>
                        <!-- <span style="font-weight:bold">Unit No:</span> ???
                        <br/> -->
                        <span style="font-weight:bold">Email:</span> {{$data->userEmail}}
                    </li>
                </ol>
                <br/>
                <li>Any notice or communication required or permitted to be given in terms of this agreement shall be valid and effective only if in writing, but it shall be competent to give notice by facsimile or e-mail. </li>
                <br/>
                <li>Any party may by notice to any other party change the physical address chosen as its domicilium vis-à-vis that party to another physical address where postal delivery occurs or its facsimile number or e-mail address, provided that the change shall become effective vis-à-vis that addressee on the seventh business day from the receipt of the notice by the addressee</li>
                <br/>
                <li>Any notice to a party: </li>
                <ol type="i" style="font-size:14px;">
                    <li>sent by prepaid registered post in a correctly addressed envelope to it at an address chosen as its domicilium to which post is delivered shall be deemed to have been received on the seventh business day after posting (unless the contrary is proved); </li>
                    

                    <footer>
                        Page | 7
                    </footer>

                    <div class="page_break"></div>


                    <img src='https://campusafrica.co.za/wp-content/uploads/2021/09/Site_Logo.svg' alt="Logo" style="width:70px; margin-left:44%;">
                    <br/>
                    <li>delivered by hand to a responsible person during ordinary business hours at the physical address chosen as its domicilium shall be deemed to have been received on the day of delivery; or </li>
                    <br/>
                    <li>sent by facsimile to its chosen fax number shall be deemed to have been received on the date of despatch (unless the contrary is proved); or </li>
                    <br/>
                    <li>sent by e-mail to its chosen e-mail address shall be deemed to have been received on the date of despatch (unless the contrary is proved). </li>
                    <br/>
                    <li>Notwithstanding anything to the contrary herein contained a written notice or communication actually received by a party shall be an adequate written notice or communication to it notwithstanding that it was not sent to or delivered at its chosen domicilium. </li>

                </ol>
            </ol>
            <br/>
            <p style="font-size:14px;">The provisions of the Electronic Communications and Transactions Act, No. 25 of 2002, are expressly excluded from this agreement in this regard, and data messages (as defined in that Act) are excluded as a valid form of notice in terms hereof.</p>
            <br/>
            <span style="font-weight:bold; font-size:14px;">13.	Whole agreement </span>
            <br/>
            <ol type="a" style=" text-laign:left; font-size:14px;">
                <li>This is the entire agreement between the parties pertaining to the subject matter hereto; </li>
                <br/>
                <li>No variation or consensual cancellation of this agreement will be of any force unless reduced to writing and signed by both parties. </li>
            </ol>
            <br/>
            <span style="font-weight:bold; font-size:14px;">14.	Non-waiver </span>
            <br/>
            <ol type="a" style=" text-laign:left; font-size:14px;">
                <li>No extension of time or indulgence must be interpreted as a waiver of any right in terms of this agreement that one party may have against the other. </li>
                <br/>
                <li>The failure of either party to comply with any non-material provision of this lease will not excuse the other party from performing its obligations fully and on time. </li>
            </ol>
            <br/>
            <span style="font-weight:bold; font-size:14px;">15. Protection of Personal Information Act </span>
            <br/>
            <ol type="a" style=" text-laign:left; font-size:14px;">
                <li>The Tenant:</li>
                <br/>
                <ol type="i" style="font-size:14px;">
                    <li>confirms that the Landlord will, in the course of this Agreement, hold “personal information” of the Tenant’s as defined in section 1 of POPIA;</li>
                    <br/>
                    <li>grants their express consent under section 27 of POPIA, that the Landlord may have access to the Tenant’s personal information held for the express purpose of:</li>
                    <ol type="a" style=" text-laign:left; font-size:14px;">
                        <li>processing their application for student accommodation;</li>
                        <li>conducting any credit searches against the Tenant with any recognised credit bureau.</li>
                    </ol>
                    <br/>
                    <li>on the expiry of this Agreement, the Tenant expressly requests that:</li>
                    <ol type="a" style=" text-laign:left; font-size:14px;">
                        <li>the Landlord releases any personal information pertaining to the Tenant to the Tenant;</li>
                        <li>stop using, processing or accessing the Tenant’s personal information;</li>
                        <li>destroy any personal information regarding the Tenant and held by the Landlord, save and to the extent that such information may be required to be held by the Landlord in terms of any law.</li>
                    </ol>
                </ol>
                <br/>
            </ol>

            <footer>
                Page | 8
            </footer>

            <div class="page_break"></div>


            <img src='https://campusafrica.co.za/wp-content/uploads/2021/09/Site_Logo.svg' alt="Logo" style="width:70px; margin-left:44%;">
            <br/>
            <table style="width:100%; border:1px solid #000;">
                <tr>
                    <td style="width:50%; vertical-align: top; border-right:1px solid #000">
                        <p style="font-size:12px; font-weight:bold">I {{$data->userFirstName}} {{$data->userLastname}}<br/>

                            THE  LODGER  IN  THIS AGREEMENT, CONFIRM AND ACCEPT THAT I FULLY UNDERSTAND THE  CONTENT  OF  THIS  AGREEMENT AND   THE   ANNEXES   HERETO   AND   THAT   THIS AGREEMENT IS DRAFTED IN A LANGUAGE IN WHICH I CAN FULLY COMPREHEND;
                        </p>
                    </td>
                    <td style="vertical-align: top;">
                        <p style="font-size:12px;"><i>SIGNATURE OF LODGER:</i>
                            <br/>
                            <img src="<?php echo "https://$_SERVER[HTTP_HOST]"; ?>/<?php echo $signature; ?>" style='width:200px;'/>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; border-right:1px solid #000">
                        <p style="font-size:12px; font-weight:bold">I {{$data->userFirstName}} {{$data->userLastname}}<br/>

                            THE  LODGER  IN  THIS AGREEMENT,  CONFIRM  THAT  I  HAVE  READ  AND UNDERSTOOD THE CONTENT  OF THIS  AGREEMENT AND THE ANNEXES HERETO;
                        </p>
                    </td>
                    <td style="vertical-align: top;">
                        <p style="font-size:12px;"><i>SIGNATURE OF LODGER:</i>
                            <br/>
                            <img src="<?php echo "https://$_SERVER[HTTP_HOST]"; ?>/<?php echo $signature; ?>" style='width:200px;'/>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; border-right:1px solid #000">
                        <p style="font-size:12px; font-weight:bold">I {{$data->userFirstName}} {{$data->userLastname}}<br/>

                            THE LODGER IN THIS AGREEMENT, CONFIRM THAT I HAVE ENTERED INTO THIS  AGREEMENT  VOLUNTARILY,  FREE  OF  UNDUE INFLUENCE AND AS A RESULT OF MY OWN FREEWILL AND DESIRE;
                        </p>
                    </td>
                    <td style="vertical-align: top;">
                        <p style="font-size:12px;"><i>SIGNATURE OF LODGER:</i>
                            <br/>
                            <img src="<?php echo "https://$_SERVER[HTTP_HOST]"; ?>/<?php echo $signature; ?>" style='width:200px;'/>
                        </p>
                    </td>
                </tr>
            </table>
            <br/>
            <p style="font-size:14px;">SIGNED BY PARTIES AS FOLLOWS:</p>
            <br/><br/>
            <table style="width:100%">
                <tr>
                    <td style="width:50%; vertical-align: top;">
                        <p style="font-size:14px;">OWNER Signature: <img src="{{url('/images/lease/juanita-cronje.jpg')}}" alt="Kim Degenaar" style='width:200px;'/><br/>(Who if signing in a representative capacity<br/>warrants that he is duly authorized thereto)</p>
                    </td>
                    <td style="vertical-align: top;">
                        <p style="font-size:14px;">Place: Braamfontein
                            <br/><br/>
                            Date: <?php echo date('Y-m-d');?>
                        </p>
                    </td>
                </tr>
            </table>
            <br/><br/>
            <br/><br/>
            <table style="width:100%">
                <tr>
                    <td style="width:50%; vertical-align: top;">
                        <p style="font-size:14px;">LODGER's Signature: <img src="<?php echo "https://$_SERVER[HTTP_HOST]"; ?>/<?php echo $signature; ?>" style='width:200px;'/><br/>(Who if signing in a representative capacity<br/>warrants that he is duly authorized thereto)</p>
                    </td>
                    <td style="vertical-align: top;">
                        <p style="font-size:14px;">Place: {{$signed_at}}
                            <br/><br/>
                            Date: <?php echo date('Y-m-d');?>
                        </p>
                    </td>
                </tr>
            </table>

            <footer>
                Page | 9
            </footer>

            <div class="page_break"></div>


            <img src='https://campusafrica.co.za/wp-content/uploads/2021/09/Site_Logo.svg' alt="Logo" style="width:70px; margin-left:44%;">
            <br/><br/>
            <span style="font-weight:bold; font-size:14px;"> ANNEXURE A: Inventory of contents of Room – UNIT: {{$propertyData->residence}}, {{$propertyData->bed_code}}, {{$data->userFirstName}}, {{$data->userLastname}}, {{$data->year}}</span>
            <br/><br/>
            <p style="font-weight:bold; font-size:14px; text-align:center">ACCOMMODATION & RESIDENCE LIFE FORM</p>
            <p style="font-size:14px; text-align:center">For Office Use Only</p>
            <br/>
            <table style="border:1px solid #000; border-collapse: collapse; width:80%">
                <tr style="border:1px solid #000; border-collapse: collapse;">
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse; width:50%">
                        <span style="font-weight:bold; font-size:14px;">WINDOWS</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">MICROWAVE OVEN</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">WALLS</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">WARDROBE DOOR</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">CARPET</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">WARDROBE SHELVES</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">STOVE / OVEN</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">BED (S)</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">LIGHTS</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">MATTRESS(ES)</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">BLINDS</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">PEDESTAL(S)</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">CURTAINS</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">STUDY DESK(S)</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">CUPBOARDS</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">CHAIR(S)</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">SKIRTINGS</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">WALL SOCKETS</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">ELECTRICS</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">DUSTBIN(S)</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">PLUGS</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">CEILING</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">FIXTURES</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">GLOBES</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">ROOM KEYS</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">WALLS</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">ROOM DOOR</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">CURTAINS/BLINDS</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">DOOR LOCK</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">CURTAIN RAIL & HOOKS</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">LIGHT SWITCHES</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">FLOOR/CARPET</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">KITCHEN UNIT</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-weight:bold; font-size:14px;">LIVING AREA</span>
                    </td>
                </tr>
            </table>
            <br/>
            <br/>
            <span style="font-size:14px;">Sign (Lodger) <img src="<?php echo "https://$_SERVER[HTTP_HOST]"; ?>/<?php echo $signature; ?>" style='width:200px;'/></span>
            <br/><br/><br/>
            <span style="font-size:14px;">Sign (Owner) <img src="{{url('/images/lease/juanita-cronje.jpg')}}" alt="Kim Degenaar" style='width:200px;'/></span>

            <footer>
                Page | 10
            </footer>

            <div class="page_break"></div>


            <img src='https://campusafrica.co.za/wp-content/uploads/2021/09/Site_Logo.svg' alt="Logo" style="width:70px; margin-left:44%;">
            <br/>
            <span style="font-weight:bold; font-size:14px;">ANNEXURE B</span>
            <br/><br/>
            <span style="font-weight:bold; font-size:14px;">COMPANIES MANAGED BY CONSOLIDATED URBAN CORPORATION (PTY) LTD</span>
            <br/>
            <ul>
                <li>Consolidated Urban Acquisitions 3 (Pty) Ltd</li>
                <li>Little Swift Investments 450 (Pty) Ltd</li>
                <li>Braamby Trading (Pty) Ltd & The Truzen 130 Trust</li>
                <li>126 Siemert Rd CC</li>
                <li>80 Jorissen Student investments</li>
                <li>Braamlofts Student Investments</li>
                <li>Ymca student Investments</li>
            </ul>
            <br/><br/><br/>

            <!-- <table style="border:1px solid #000; border-collapse: collapse; width:100%">
                <tr style="border:1px solid #000; border-collapse: collapse;">
                    <th style="padding:5px; border:1px solid #000; border-collapse: collapse; width:33.3%; background-color:#ccc">
                        <span style="font-weight:bold; font-size:14px;">RESIDENCE</span>
                    </th>
                    <th style="padding:5px; border:1px solid #000; border-collapse: collapse; background-color:#ccc">
                        <span style="font-weight:bold; font-size:14px;">RESIDENCE ADDRESS</span>
                    </th>
                    <th style="padding:5px; border:1px solid #000; border-collapse: collapse; background-color:#ccc">
                        <span style="font-weight:bold; font-size:14px;">RESIDENCE OWNER</span>
                    </th>
                </tr>
                <tr style="border:1px solid #000; border-collapse: collapse;">
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">126 SIEMERT</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">126 JOE SLOVE ROAD, NEW DOORNFONTEIN, JOHANNESBURG, 2001</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">126 SIEMERT RD CC</span>
                    </td>
                </tr>
                <tr style="border:1px solid #000; border-collapse: collapse;">
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">49 JORISSEN</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">49 JORISSEN STREET, BRAAMFONTEIN, JOHANNESBURG, 2001</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">BRAAMBY TRADING (PTY) LTD</span>
                    </td>
                </tr>
                <tr style="border:1px solid #000; border-collapse: collapse;">
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">80 JORISSEN</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">80 JORISSEN STREET, BRAAMFONTEIN, JOHANNESBURG, 2001</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">K201406883 (SOUTH AFRICA) (PTY) LTD</span>
                    </td>
                </tr>
                <tr style="border:1px solid #000; border-collapse: collapse;">
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">BRAAMLOFTS</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">26 BICCARD STREET, BRAAMFONTEIN, JOHANNESBURG, 2001</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">PONTEI INVESTMENTS (PTY) LTD</span>
                    </td>
                </tr>
                <tr style="border:1px solid #000; border-collapse: collapse;">
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">DUNVISTA MANSIONS</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">70 BANKET STREET, HILLBROW, JOHANNESBURG, 2001</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">CONSOLIDATED URBAN ACQUISITIONS 3 (PTY) LTD</span>
                    </td>
                </tr>
                <tr style="border:1px solid #000; border-collapse: collapse;">
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">WYNTON JOY</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">3 NUGGET STREET, HILLBROW, JOHANNESBURG, 2001</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">LITTLE SWIFT INVESTMENTS 450 (PTY) LTD</span>
                    </td>
                </tr>
                <tr style="border:1px solid #000; border-collapse: collapse;">
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">YMCA</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">104 RISSIK STREET, BRAAMFONTEIN, JOHANNESBURG, 2001</span>
                    </td>
                    <td style="padding:5px; border:1px solid #000; border-collapse: collapse;">
                        <span style="font-size:14px;">JIRAC PROJECTS (PTY) LTD</span>
                    </td>
                </tr>
            </table> -->

            <footer>
                Page | 11
            </footer>

            <div class="page_break"></div>


            <img src='https://campusafrica.co.za/wp-content/uploads/2021/09/Site_Logo.svg' alt="Logo" style="width:70px; margin-left:44%;">
            <br/>
            <br/>
            <span style="font-weight:bold; font-size:14px;">ANNEXURE C: Acknowledgement of obligation for accommodation payments</span>
            <br/><br/>
            <p style="font-weight:bold; font-size:14px;">I {{$data->userFirstName}} {{$data->userLastname}} With
                <br/><br/>
                @if($data->userNationality == "South Africa") {{"ID " . $data->userId}} @else {{"Passport Number " . $data->userPassport}} @endif
                <br/><br/>
                Hereby acknowledge that I am fully liable for any rental/accommodation payments due on demand to the Owner Ltd for accommodation supplied at the student accommodation centre as indicated in clause 12.a.ii of this lease. 
            </p>
            @if($data->studentType == "Bursary")
                <p style="font-weight:bold; font-size:14px;">I am an approved bursary holder from {{$data->burCompanyName}}, whose agreement is to pay all accommodation on my behalf. Should it arise that the above-mentioned bursary does not pay the accommodation I will be held liable in my private capacity for all outstanding monies due even if I have vacated the premises.</p>
            @endif
            <br/>
            <!-- <br/> -->
            <p style="font-weight:bold; font-size:14px;">Signed at {{$signed_at}} on this <?php echo date('d'); ?> day of <?php echo date('F'); ?> <?php echo date('Y'); ?></p>
            <br/>
            <p style="font-weight:bold; font-size:14px;"><img src="<?php echo "https://$_SERVER[HTTP_HOST]"; ?>/<?php echo $signature; ?>" style='width:200px;'/><br/>Student</p>
            <!-- <br/> -->
            <p style="font-weight:bold; font-size:14px;"><img src="{{url('/images/lease/juanita-cronje.jpg')}}" alt="Kim Degenaar" style='width:200px;'/><br/>For and on behalf of<br/>Owner</p>
            <br/>
            <hr/>
            <br/>
            <span style="font-weight:bold; font-size:14px;">Acknowledgement by parent/guardian of the abovementioned student:</span>
            <br/><br/>
            <p style="font-weight:bold; font-size:14px;">I @if($data->sponFirstName !== ""){{$data->sponFirstName}} {{$data->sponLastName}} @else ________________________ @endif With
                <br/><br/>
                ID {{$data->sponId}}
                <br/><br/>
                Hereby acknowledge that I am jointly and severally liable for any rental/accommodation payments due on demand to the Owner for accommodation supplied to the student mentioned in this lease at the student accommodation centre as indicated in clause 12.a.ii of this lease. 
            </p>
            <br/>
            <p style="font-weight:bold; font-size:14px;">Signed at __________________________________ on this ____ day of ____________ 20__.</p>
            <br/>
            <br/>
            <table style="width:100%">
                <tr>
                    <td style="width:75%"><p>_______________<br/>Parent/Guardian</p></td>
                    <td><p>_______________<br/>Witness</p></td>
                </tr>
            </table>

            <footer>
                Page | 12
            </footer>
            
                
            

           



    </div>

</body>

</html>