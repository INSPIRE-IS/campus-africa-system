@extends('layouts.admin')

@section('content')

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /*
         * Documents section compact buttons.
         * Intentionally scoped to `#documentsCard` so we can revert easily if any layout breaks.
         * Excludes the three header actions (Add Document / Download All / Email Reminder).
         */
        #documentsCard button:not(#addOtherDocument):not(#downloadAllDocs):not(#sendMailReminder),
        #documentsCard .outstandingUpload {
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 11px;
            line-height: 1.1;
        }

        #documentsCard .outstandingUpload {
            margin: 0;
        }

        @media (max-width: 420px) {
            #documentsCard button:not(#addOtherDocument):not(#downloadAllDocs):not(#sendMailReminder),
            #documentsCard .outstandingUpload {
                font-size: 11px;
                padding: 6px 8px;
            }
        }

        #documentsCard .docs-help {
            margin-left: 8px;
            font-size: 14px;
            color: #666;
            cursor: help;
        }

        .paymentSummaryActions {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-top: 12px;
        }

        .paymentSummaryActions .paymentSummaryStatus {
            min-width: 160px;
        }

        .paymentSummaryActions #nsfas_wording {
            margin: 0;
            padding-top: 8px;
        }

        .paymentSummaryActions .paymentSummaryButtons {
            display: flex;
            flex: 1;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        .paymentSummaryActions .profileSubmit,
        .paymentSummaryActions .generatePdf,
        .paymentSummaryActions .nsfasQuoteBtn {
            float: none !important;
            margin: 0 !important;
            min-height: 38px;
            padding: 10px;
            border: none;
            border-radius: 10px;
            color: #fff;
            line-height: 18px;
            white-space: normal;
        }

        .paymentSummaryActions .profileSubmit {
            background-color: #ffbb3c;
        }

        .paymentSummaryActions .nsfasQuoteBtn {
            background-color: #f1cc3e;
        }

        @media (max-width: 850px) {
            .paymentSummaryActions {
                flex-direction: column;
            }

            .paymentSummaryActions .paymentSummaryStatus,
            .paymentSummaryActions .paymentSummaryButtons {
                width: 100%;
            }

            .paymentSummaryActions .paymentSummaryButtons {
                flex-direction: column;
            }

            .paymentSummaryActions .profileSubmit,
            .paymentSummaryActions .generatePdf,
            .paymentSummaryActions .nsfasQuoteBtn {
                width: 100%;
            }
        }
    </style>

    <div class="greyAppSection  @if ($user[0]['userInfo']['studentType'] == 'Cash / Direct') greyAppCash @endif">
        @if ($bookingNext && (empty($userAccount) || empty($userAccount->banned_at)))
            @if (
                (int) Auth::user()->role_create_app === 1 ||
                    (int) Auth::user()->role_edit_app === 1 ||
                    (int) Auth::user()->role_sys_admin === 1 ||
                    (int) Auth::user()->role_super === 1)
                <div style="width: 100%;padding: 0 15px;margin: 0 auto;">
                    <div style="display: flex;justify-content: flex-end;padding-top: 5px;">
                        <a href="/create-new-booking/{{ $user[0]['userInfo']['userUid'] }}?year={{ $bookingNextYear }}"
                            class="btn btn-warning mt-4"
                            style="padding: 10px 20px;background-color: #e74f26;color: #fff;border: none;border-radius: 10px;text-decoration: none;font-weight: 500;transition: background-color 0.3s ease;">
                            Create a new booking for {{ $bookingNextYear }}
                        </a>
                    </div>
                </div>
            </div>
            @endif
        @endif

        @if (!empty($userAccount) && !empty($userAccount->banned_at))
            <div style="width: 100%;padding: 0 15px;margin: 10px auto 0 auto;">
                <div
                    style="padding: 12px 14px;border: 1px solid #dc3545;background: #f8d7da;color: #842029;border-radius: 10px;">
                    <strong>Restricted:</strong> This student is banned from portal access and re-application.<br />
                    <strong>Banned at:</strong> {{ $userAccount->banned_at }}<br />
                    @if (!empty($userAccount->banned_reason))
                        <strong>Reason:</strong> {{ $userAccount->banned_reason }}
                    @endif
                </div>
            </div>
        @endif
{{-- Physical Address Next of Kin Student Type --}}
        <div class="container">

            <div class="col-3" id="col1">
                <div class="flex">
                    <span id="approvedAddr" class="hidden">&#128994;</span><span id="rejectedAddr">&#128993;</span>
                    <p class="listHeader">Physical Address</p>
                </div>
                <div class="form" id="inline_edit_address">
                    {{ $user[0]['userInfo']['userAddress1'] }}<br />
                    {{ $user[0]['userInfo']['userAddress2'] }}<br />
                    {{ $user[0]['userInfo']['userAddressCity'] }}<br />
                    {{ $user[0]['userInfo']['userAddressPostal'] }}<br />

                    @if (in_array($booking->property, $allowedResidences))
                        <div class="address_btn"></div>
                    @endif

                </div>
            </div>
            <div class="col-3" id="col2">

                <div class="flex">
                    <span id="approvedKin" class="hidden">&#128994;</span>
                    <span id="rejectedKin">&#128993;</span>
                    <span id="notLoadKin" class="hidden">&#128308;</span>
                    <p class="listHeader">Next of Kin</p>
                </div>
                <div class="form" id="inline_edit_kin">
                    {{ $user[0]['userInfo']['kinTitle'] }} {{ $user[0]['userInfo']['kinFirstName'] }}
                    {{ $user[0]['userInfo']['kinLastName'] }}<br />
                    {{ $user[0]['userInfo']['kinRelationship'] }}<br />
                    {{-- {{$user[0]['userInfo']['kinNationality']}}<br/>
                @if ($user[0]['userInfo']['kinId']){{$user[0]['userInfo']['kinId']}}<br/>@endif
                @if ($user[0]['userInfo']['kinPassportNo']){{$user[0]['userInfo']['kinPassportNo']}}<br/>@endif --}}
                    {{ $user[0]['userInfo']['kinCellCode'] }}{{ $user[0]['userInfo']['kinCellNo'] }}<br />
                    {{ $user[0]['userInfo']['kinEmail'] }}<br />
                    {{ $user[0]['userInfo']['kinAddressStr'] }}, {{ $user[0]['userInfo']['kinAddressSuburb'] }}, <br />
                    {{ $user[0]['userInfo']['kinAddressCity'] }}, {{ $user[0]['userInfo']['kinAddressPost'] }}<br />

                    @if (in_array($booking->property, $allowedResidences))
                        <div class="kin_btn"></div>
                    @endif

                </div>

            </div>

            @if ($user[0]['userInfo']['studentType'] == 'Cash / Direct')
                <div class="col-3" id="col3">
                    <div class="flex">
                        <span id="approvedSpon" class="hidden">&#128994;</span>
                        <span id="rejectedSpon" class="hidden">&#128993;</span>
                        <span id="notLoadSpon" class="hidden">&#128308;</span>
                        <p class="listHeader">Sponsor/Parent </p>
                    </div>
                    <div class="form">
                        <div class="sponInfoSection"></div>
                    </div>
                </div>
            @endif

            @if ($user[0]['userInfo']['studentType'] == 'Bursary')
                <div class="col-3" id="col3">
                    <div class="flex">
                        <!-- <span id="approvedBur" class="<?php if ($user[0]['userInfo']['burser_approved'] != '1') {
                            echo 'hidden';
                        } ?>">&#128994;</span>
                                                                                                                                                                                                                                                        <span id="rejectedBur" class="<?php if ($user[0]['userInfo']['burser_approved'] == '1' && $user[0]['userInfo']['burCompanyName'] !== '') {
                                                                                                                                                                                                                                                            echo 'hidden';
                                                                                                                                                                                                                                                        } ?>">&#128993;</span>
                                                                                                                                                                                                                                                        <span id="notLoadBur" class="<?php if ($user[0]['userInfo']['burser_approved'] == '1' && $user[0]['userInfo']['burCompanyName'] == null) {
                                                                                                                                                                                                                                                            echo 'hidden';
                                                                                                                                                                                                                                                        } ?>">&#128308;</span> -->
                        <span id="approvedBur" class="hidden">&#128994;</span>
                        <span id="rejectedBur" class="hidden">&#128993;</span>
                        <span id="notLoadBur" class="hidden">&#128308;</span>
                        <p class="listHeader">Bursar </p>
                    </div>
                    <!-- <?php echo $user[0]['userInfo']['burCompanyName']; ?> -->
                    <div class="form">
                        <div class="burInfoSection"></div>
                    </div>
                </div>
            @endif



            <div class="col-3" id="col3">
                <div class="form" id="inline_edit_student_type">
                    <p class="listHeader">Student Type</p>
                    <span id="studentUserType" class="{{ $user[0]['userInfo']['studentType'] }}">
                        @if ($user[0]['userInfo']['studentType'] == 'Cash / Direct')
                            Cash
                        @else
                            {{ $user[0]['userInfo']['studentType'] }}
                        @endif
                    </span><br />

                    @if (
                        $user[0]['userInfo']['rob_60_cancelled'] != '1' &&
                            $user[0]['userInfo']['rob_55_lease'] != '2' &&
                            Auth::user()->role_change_student_type == '1')
                        @if (in_array($booking->property, $allowedResidences))
                            <div class="studentType_btn"></div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
{{-- Applicant Details --}}
    <div class="container">

        <div class="appRow">
            <div class="cards">

                <div class="customWhiteCard">
                    <div class="flex">
                        <span id="app_det_statYes" class="hidden" style="margin-top:10px;">&#128994;</span>
                        <span id="app_det_statNo" style="margin-top:10px;">&#128993;</span>
                        <h2 class="appHeader">Applicant Details</h2>
                    </div>

                    <p class="InfoList">
                        {{ $user[0]['userInfo']['userFirstName'] }} {{ $user[0]['userInfo']['userLastname'] }}<br />
                        {{ $user[0]['userInfo']['userDob'] }}<br />
                        {{ $user[0]['userInfo']['userNationality'] }}<br />
                        @if ($user[0]['userInfo']['userId'])
                            {{ $user[0]['userInfo']['userId'] }}<br />
                        @endif
                        @if ($user[0]['userInfo']['gender'] == 1)
                            Male <br />
                        @elseif($user[0]['userInfo']['gender'] == 2)
                            Female<br />
                        @endif
                        @if ($user[0]['userInfo']['userPassport'])
                            {{ $user[0]['userInfo']['userPassport'] }}<br />
                        @endif
                    </p>

                    <p class="InfoList">
                        @foreach ($lookups as $lookup)
                            @if ($lookup->id == $user[0]['userInfo']['institutionStudy'])
                                {{ $lookup->lookup_name }}
                            @endif
                        @endforeach
                        <br />
                        {{ $user[0]['userInfo']['studentNo'] }}<br />
                        @foreach ($lookups as $lookup)
                            @if ($lookup->id == $user[0]['userInfo']['faculty'])
                                {{ $lookup->lookup_name }}
                            @endif
                        @endforeach
                        <br />
                        {{ $user[0]['userInfo']['yearOfStudy'] }}
                    </p>

                    <div class="form" id="inline_app_details"
                        style="margin-top: 20px;font-size: 21px;font-weight: 300;line-height: 26px;">
                        <p class="InfoList">
                            <a
                                href="mailto:{{ $user[0]['userInfo']['userEmail'] }}?subject=Campus Africa Ref {{ $booking->booking_code }}">{{ $user[0]['userInfo']['userEmail'] }}</a>


                            @if ($user[0]['userInfo']['email_verified'] == '1')
                                <i class="fas fa-check" style="color:green"></i>
                                <input type="hidden" id="emailverifiedStatus"
                                    value="{{ $user[0]['userInfo']['email_verified'] }}" />
                            @else
                                &#10060;
                                &nbsp;&nbsp;
                                @if ($mailVerificationEmail->status == '1')
                                    <button type="button" class="unallocateBed" onClick="reVerifyEmail()"
                                        id="reVerify">Resend Email verification</button>
                                @endif
                                <input type="hidden" id="emailverifiedStatus"
                                    value="{{ $user[0]['userInfo']['email_verified'] }}" />
                            @endif
                            <br />

                            @if (!empty($user[0]['userInfo']['userEmailAlt']))
                                <a
                                    href="mailto:{{ $user[0]['userInfo']['userEmailAlt'] }}?subject=Campus Africa Ref {{ $booking->booking_code }}">{{ $user[0]['userInfo']['userEmailAlt'] }}</a>
                            @else
                                <span>No Alt Email</span>
                            @endif
                            <br />

                            <a href="https://wa.me/<?php echo substr($user[0]['userInfo']['userCellCode'], 1) . $user[0]['userInfo']['userCell']; ?>?text=Campus%20Africa%20Ref%20{{ $booking->booking_code }}%0A%0A%0A%0A"
                                target="_blank">{{ $user[0]['userInfo']['userCellCode'] }}{{ $user[0]['userInfo']['userCell'] }}</a><br />
                        </p>



                        @if (in_array($booking->property, $allowedResidences))
                            @if ($mailVerificationEmail->status == '1')
                                <div class="app_details_btn"></div>
                            @endif
                        @endif


                    </div>

                </div>
{{-- Application Summary --}}
                <div class="customWhiteCard" id="applicationCard" style="margin-top:20px;">
                    <div class="flex">
                        <div class="bed_code_status"></div>

                        <h2 class="appHeader">Application Summary</h2>
                    </div>

                    <div class="app_summary_details"></div>

                    <div class="buttons flex" id="buttonTop">

                        @if (in_array($booking->property, $allowedResidences))
                            @if ($leaseApproved == 'false' && $user[0]['userInfo']['rob_60_cancelled'] == 0)
                                @if (Auth::user()->role_change_bed == '1')
                                    @if ($user[0]['userInfo']['rob_50_approve'] > 0)
                                        <button type="button" class="appBooking" id="changeLeaseCancel"
                                            onClick="cancelLease('{{ $user[0]['userInfo']['userUid'] }}', 'generate')">Change
                                            Bed</button>
                                    @else
                                        <button type="button" class="appBooking" id="changeBedOnly"
                                            onClick="changeBooking('{{ $user[0]['userInfo']['userUid'] }}', 'generate')">Change
                                            Bed</button>
                                    @endif
                                @endif

                                @if (Auth::user()->role_change_date == '1' && $user[0]['userInfo']['rob_50_approve'] != 2)
                                    <button type="button" class="appBooking" id="changeDatesLease"
                                        onClick="changeLease('{{ $user[0]['userInfo']['userUid'] }}')">Change Lease
                                        Dates</button>
                                @endif
                            @endif

                            @if ($leaseApproved == 'true' && $user[0]['userInfo']['rob_60_cancelled'] == 0)
                                @if (Auth::user()->role_modify_approved == 1)
                                    <button type="button" class="appBooking" id="changeLeaseCancel"
                                        onClick="cancelLease('{{ $user[0]['userInfo']['userUid'] }}', 'generate')">Change
                                        Bed</button>
                                @endif
                            @endif
                        @endif


                        @if (Auth::user()->role_change_building == '1')
                            <button type="button" class="appBooking"
                                onClick="changeBuilding('{{ $booking->id }}', '{{ $user[0]['userInfo']['userUid'] }}')">Change
                                Building</button>
                        @endif
                    </div>
                    <div class="buttons flex" id="buttonBottom">
                        @if (in_array($booking->property, $allowedResidences))
                            @if ($user[0]['userInfo']['rob_60_cancelled'] == 0)
                                @if (Auth::user()->role_delete_booking == 1)
                                    <button type="button" class="appBooking"
                                        onClick="deleteBooking('{{ $booking->id }}', '{{ $user[0]['userInfo']['userUid'] }}')">Delete
                                        this booking</button>
                                @endif

                                @if (Auth::user()->role_cancel_booking == 1 && Auth::user()->role_super == 1)
                                    @if (
                                        $user[0]['userInfo']['rob_55_lease'] != 2 ||
                                            (Auth::user()->role_modify_approved == 1 && $user[0]['userInfo']['rob_55_lease'] == 2))
                                        <button type="button" class="appBooking"
                                            onClick="cancelBooking('{{ $booking->id }}', '{{ $user[0]['userInfo']['userUid'] }}')">Cancel
                                            booking</button>
                                    @endif
                                @endif
                            @elseif($user[0]['userInfo']['rob_60_cancelled'] == 1)
                                @if (Auth::user()->role_cancel_booking == 1)
                                    @if (
                                        $user[0]['userInfo']['rob_55_lease'] != 2 ||
                                            (Auth::user()->role_modify_approved == 1 && $user[0]['userInfo']['rob_55_lease'] == 2))
                                        @if (in_array($booking->property, $allowedResidences))
                                            <button type="button" class="appBooking"
                                                onClick="restoreBooking('{{ $booking->id }}', '{{ $user[0]['userInfo']['userUid'] }}')">Restore
                                                booking</button>
                                        @endif
                                    @endif
                                @endif
                            @endif
                        @endif

                        @if (Auth::user()->role_sys_admin == 1 && !empty($userAccount))
                            @if (empty($userAccount->banned_at))
                                <button type="button" class="appBooking" id="btn_ban_student">Ban student</button>
                            @else
                                <button type="button" class="appBooking" id="btn_unban_student">Remove ban</button>
                            @endif
                        @endif

                    </div>
                </div>
{{-- Action List --}}
                <div class="customWhiteCard" id="actionListCard" style="margin-top:20px;">
                    <div class="">
                        <h2 class="appHeader">Action List
                            <select id="actionListSort">
                                <option selected value="all">All Users</option>
                                <option value="admin">Admin Users</option>
                                <option value="applicant">Applicant</option>
                            </select>
                        </h2>
                    </div>

                    <div class="actionItemList">

                        @foreach ($actions as $action)
                            <div class="infoBlock">
                                <div class="responsible">

                                    @if ($action->actionUserRole !== 0)
                                        @if (!empty($action->users))
                                            <p>{{ $action->users->userFirstName }} {{ $action->users->userLastname }}</p>
                                        @else
                                            <p>{{ $action->actionUserDisplayName }}</p>
                                        @endif

                                        @if (!empty($action->users))
                                            @if ($action->users->user_role_name == '' || $action->users->user_role_name == null)
                                                <button type="button" class="admin">Admin</button>
                                            @else
                                                <button type="button"
                                                    class="admin">{{ $action->users->user_role_name }}</button>
                                            @endif
                                        @else
                                            <button type="button" class="admin">Admin</button>
                                        @endif
                                    @else
                                        @if (!empty($action->usersInfo))
                                            <p>{{ $action->usersInfo->userFirstName }}
                                                {{ $action->usersInfo->userLastname }}</p>
                                        @else
                                            <p>{{ $action->actionUserDisplayName }}</p>
                                        @endif

                                        <button type="button" class="applicant">Applicant</button>
                                    @endif
                                    <p class="datetime"> {{ substr($action->created_at, 8, 2) }} |
                                        {{ substr($action->created_at, 5, 2) }} | {{ substr($action->created_at, 0, 4) }}
                                        | {{ substr($action->created_at, 11, 5) }} </p>
                                </div>
                                <div class="information">
                                    <p>{{ $action->action }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
{{-- Previous Bookings --}}
                <div class="customWhiteCard" id="actionListCard" style="margin-top:20px; margin-bottom:20px;">
                    <div class="">
                        <h2 class="appHeader">Previous Bookings</h2>
                    </div>

                    <div class="actionItem">

                        @foreach ($previousBookings as $pBooking)
                            <div class="infoBlock">
                                <div class="responsible">
                                    @if (!empty($pBooking->year))
                                        <p>{{ $pBooking->year }}</p>
                                    @endif

                                </div>
                                <div class="information">
                                    <p><b>Application Reference:</b> {{ $pBooking->booking_code }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>


            </div>
        </div>
        <div class="appRow">
            <div class="cards">
{{-- Booking --}}
                <div class="customWhiteCard">
                    <!-- <button type="button" class="reminder">Send reminder</button> -->
                    <!-- <h2 class="appSubHeader bold">PAYMENT TYPE </h2> -->


                    @if ($paymentBooking)
                        <table style="width:100%; text-align:left">
                            <tbody>
                            <tr >
                                <th>
                                    <h2 class="appHeader bold" style="text-transform:uppercase;">
                                        @if ($user[0]['userInfo']['studentType'] == 'Cash / Direct')
                                            Cash
                                        @else
                                            {{ $user[0]['userInfo']['studentType'] }}
                                        @endif
                                    </h2>
                                </th>
                                <th style="width:35%">Upfront</th>
                                <th>Monthly</th>
                                <th>Total</th>
                            </tr>
                            <tr>
                                <td>Booking Fee</td>
                                <td class="flex">
                                    <div style="width:85px;" id="booking_info">
                                        @if ($paymentBooking->booking_fee_waived == '0')
                                            @if ($paymentBooking->booking_fee != '0')
                                                R {{ $paymentBooking->booking_fee }}
                                            @else
                                                -
                                            @endif
                                        @else
                                            @if ($paymentBooking->rob_b_waived == '2')
                                                &#128994;
                                            @else
                                                &#128993;
                                            @endif
                                            <span style="color:red; padding-right:5px;">Waived</span>
                                        @endif
                                    </div>

                                    <div id="booking_btns">
                                        @if ($paymentBooking->booking_fee_waived == '0')
                                            @if ($paymentBooking->booking_fee != '0')
                                                @if (Auth::user()->role_waive_fee == '1')
                                                    <button type="button" class="waiveBtn"
                                                        onClick="waive('rob_b_waived', '{{ $user[0]['userInfo']['userUid'] }}','{{ $paymentBooking->bookingId }}', 'booking_info', 'booking_btns', 'reason_booking_fee', 'booking_fee_waived', 'Booking fee')">Waive</button>
                                                @endif
                                            @endif
                                        @else
                                            @if (Auth::user()->role_funding == '1')
                                                @if ($paymentBooking->rob_b_waived == '2')
                                                    <button type="button" class="waiveBtn"
                                                        onClick="ActionWaiveRej('rob_b_waived', '{{ $paymentBooking->bookingId }}', 'booking_info', 'booking_btns', 'Booking fee', 'reason_booking_fee_reject', 'booking_fee_waived')">Change</button>
                                                @else
                                                    <button type="button" class="waiveAprv"
                                                        onClick="ActionWaiveAprv('rob_b_waived', '{{ $paymentBooking->bookingId }}', 'booking_info', 'booking_btns', 'Booking fee')">Approve</button>
                                                    <button type="button" class="waiveRej"
                                                        onClick="ActionWaiveRej('rob_b_waived', '{{ $paymentBooking->bookingId }}', 'booking_info', 'booking_btns', 'Booking fee', 'reason_booking_fee_reject', 'booking_fee_waived')">Reject</button>
                                                @endif
                                            @endif
                                        @endif
                                    </div>

                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Loan Admin Fee</td>
                                <td class="flex">
                                    <div style="width:85px;" id="loan_info">
                                        @if ($paymentBooking->loan_admin_fee_waived == '0')
                                            @if ($paymentBooking->loan_admin_fee != '0')
                                                R {{ $paymentBooking->loan_admin_fee }}
                                            @else
                                                -
                                            @endif
                                        @else
                                            @if ($paymentBooking->rob_loan_waived == '2')
                                                &#128994;
                                            @else
                                                &#128993;
                                            @endif
                                            <span style="color:red; padding-right:5px;">Waived</span>
                                        @endif
                                    </div>

                                    <div id="loan_btns">
                                        @if ($paymentBooking->loan_admin_fee_waived == '0')
                                            @if ($paymentBooking->loan_admin_fee != '0')
                                                @if (Auth::user()->role_waive_fee == '1')
                                                    <button type="button" class="waiveBtn"
                                                        onClick="waive('rob_loan_waived', '{{ $user[0]['userInfo']['userUid'] }}','{{ $paymentBooking->bookingId }}', 'loan_info', 'loan_btns', 'reason_admin_fee', 'loan_admin_fee_waived', 'Loan Admin fee')">Waive</button>
                                                @endif
                                            @endif
                                        @else
                                            @if (Auth::user()->role_funding == '1')
                                                @if ($paymentBooking->rob_loan_waived == '2')
                                                    <button type="button" class="waiveBtn"
                                                        onClick="ActionWaiveRej('rob_loan_waived', '{{ $paymentBooking->bookingId }}', 'loan_info', 'loan_btns', 'Loan Admin fee', 'reason_admin_fee_reject', 'loan_admin_fee_waived')">Change</button>
                                                @else
                                                    <button type="button" class="waiveAprv"
                                                        onClick="ActionWaiveAprv('rob_loan_waived', '{{ $paymentBooking->bookingId }}', 'loan_info', 'loan_btns', 'Loan Admin fee')">Approve</button>
                                                    <button type="button" class="waiveRej"
                                                        onClick="ActionWaiveRej('rob_loan_waived', '{{ $paymentBooking->bookingId }}', 'loan_info', 'loan_btns', 'Loan Admin fee', 'reason_admin_fee_reject', 'loan_admin_fee_waived')">Reject</button>
                                                @endif
                                            @endif
                                        @endif
                                    </div>

                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Deposit</td>
                                <td class="flex">
                                    <div style="width:85px;" id="deposit_info">
                                        @if ($paymentBooking->deposit_waived == '0')
                                            @if ($paymentBooking->deposit != '0')
                                                R {{ $paymentBooking->deposit }}
                                            @else
                                                -
                                            @endif
                                        @else
                                            @if ($paymentBooking->rob_deposit_waived == '2')
                                                &#128994;
                                            @else
                                                &#128993;
                                            @endif
                                            <span style="color:red; padding-right:5px;">Waived</span>
                                        @endif
                                    </div>

                                    <div id="deposit_btns">
                                        @if ($paymentBooking->deposit_waived == '0')
                                            @if ($paymentBooking->deposit != '0')
                                                @if (Auth::user()->role_waive_fee == '1')
                                                    <button type="button" class="waiveBtn"
                                                        onClick="waive('rob_deposit_waived', '{{ $user[0]['userInfo']['userUid'] }}','{{ $paymentBooking->bookingId }}', 'deposit_info', 'deposit_btns', 'reason_deposit', 'deposit_waived', 'Deposit')">Waive</button>
                                                @endif
                                            @endif
                                        @else
                                            @if (Auth::user()->role_funding == '1')
                                                @if ($paymentBooking->rob_deposit_waived == '2')
                                                    <button type="button" class="waiveBtn"
                                                        onClick="ActionWaiveRej('rob_deposit_waived', '{{ $paymentBooking->bookingId }}', 'deposit_info', 'deposit_btns', 'Deposit', 'reason_deposit_reject', 'deposit_waived')">Change</button>
                                                @else
                                                    <button type="button" class="waiveAprv"
                                                        onClick="ActionWaiveAprv('rob_deposit_waived', '{{ $paymentBooking->bookingId }}', 'deposit_info', 'deposit_btns', 'Deposit')">Approve</button>
                                                    <button type="button" class="waiveRej"
                                                        onClick="ActionWaiveRej('rob_deposit_waived', '{{ $paymentBooking->bookingId }}', 'deposit_info', 'deposit_btns', 'Deposit', 'reason_deposit_reject', 'deposit_waived')">Reject</button>
                                                @endif
                                            @endif
                                        @endif
                                    </div>

                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Monthly Rental</td>
                                <td></td>
                                <td id="monthlyRentalAmnt">R {{ $paymentBooking->monthly_rental }}</td>
                                <td id="monthlyRentalTotalAmnt">R {{ $paymentBooking->monthly_rental_total }}</td>
                            </tr>
                            <tr>
                                <td>Funded</td>
                                <td></td>
                                <td id="mthlyfunded">
                                    @if ($paymentBooking->funded_monthly != '0')
                                        R {{ $paymentBooking->funded_monthly }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td id="fundedAmnt">
                                    @if ($paymentBooking->total_funded != '0' && $paymentBooking->total_funded != null)
                                        R {{ $paymentBooking->total_funded }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Top-Up</td>
                                <td></td>
                                <td></td>
                                <td id="topupAmnt">
                                    @if ($paymentBooking->total_top_up != '0')
                                        R {{ $paymentBooking->total_top_up }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                        </table>
                        <br />
                        <div class="paymentSummaryActions">
                            <div class="paymentSummaryStatus">
                                <h2 id="nsfas_wording">
                                    @if ($paymentBooking->nsfas_rate)
                                        NSFAS Rate Applies
                                    @endif
                                </h2>
                            </div>
                            <div class="paymentSummaryButtons">
                                @if (Auth::user()->role_sys_admin == 1)
                                    <button type="button" id="openPaymentModal" class="profileSubmit"
                                        onClick="document.getElementById('paymentModal').style.display = 'block';">
                                        Update Rates
                                    </button>
                                    <button type="button" id="openMonthlyPopModal" class="profileSubmit"
                                        onClick="document.getElementById('monthlyPopModal').style.display = 'block';">
                                        Update Monthly POP
                                    </button>
                                @endif
                                @if (Auth::user()->role_allow_generate_invoice == 1)
                                    <button type="button" class="generatePdf"
                                        onClick="generateInvoice('{{ $paymentBooking->id }}', '{{ $booking->id }}', '{{ $user[0]['userInfo']['userUid'] }}')">
                                        Generate Pro Forma Invoice
                                    </button>                               
                                    <button type="button" class="nsfasQuoteBtn"
                                        onClick="generateNSFASQuote('{{ $paymentBooking->id }}', '{{ $booking->id }}', '{{ $user[0]['userInfo']['userUid'] }}')">
                                        Generate NSFAS Quote
                                    </button>
                                @endif
                            
                            </div>
                        </div>
                    @endif



                    <!-- <p class="appSubHeader">
                                                                                                                                                                                                                                                        <div class="burPaymentDetails"></div>
                                                                                                                                                                                                                                                    </p>
                                                                                                                                                                                                                                                    <br/> -->

                    <!-- <h2 class="appSubHeader bold">BOOKING FEE</h2> -->
                    <!-- <p class="appSubHeader"> -->
                    <!-- @if ($residence !== null && $user[0]['userInfo']['studentType'] == 'NSFAS')
                        R {{ $residence->nsfas_booking_rate }}
                        @endif
                                                                                                                                                                                                                                                                            @if ($residence !== null && $user[0]['userInfo']['studentType'] == 'Bursary')
                        R {{ $residence->bursary_booking_rate }}
                        @endif
                                                                                                                                                                                                                                                                            @if ($residence !== null && $user[0]['userInfo']['studentType'] == 'Cash / Direct')
                        R {{ $residence->booking_rate }}
                        @endif -->
                    <!-- R {{ $booking->room_rate }} -->
                    <!-- </p> -->
                    <!-- <br/> -->

                    <!-- <h2 class="appSubHeader bold">PAID</h2>
                                                                                                                                                                                                                                                    <p class="appSubHeader" id="paidOption">
                                                                                                                                                                                                                                                    <div class="waived_paid_wording"></div> -->



                    <!-- </p> -->
                </div>
{{-- Documents --}}
                <div class="customWhiteCard" id="documentsCard" style="margin-top:20px;">
                    <div class="bigHeader appSearchForm">
                        <h2 class="appHeader">Documents
                            <span class="docs-help"
                                title="Button guide: Upload = add a file; View = open in modal; Download = download the saved file; Generate = create/refresh the doc and email a signing link; Resend = email a new signing link without changing the PDF; Regen (lease) = rebuild the PDF and email a new link.">
                                <i class="fas fa-info-circle" aria-hidden="true"></i>
                            </span>
                            <select id="documentSortList">
                                <option selected value="all">All Documents</option>
                                <option value="review">Uploaded For Review</option>
                                <option value="approved">Approved</option>
                                <option value="noDoc">No Document</option>
                            </select>
                        </h2>
                        <div class="indexTab flex " style="width:100%">
                            <div class="keys">
                                <div id="indexOne">
                                    <div class="button approved"></div><span class="text">APPROVED</span>
                                </div>
                                <div id="indexOne">
                                    <div class="button action"></div><span class="text">ACTION REQUIRED</span>
                                </div>
                                <div id="indexOne">
                                    <div class="button rejected"></div><span class="text">NO DOCUMENT</span>
                                </div>
                            </div>
                        </div>
                        <!-- <a href="/download/docs/{{ $user[0]['userInfo']['userUid'] }}"><button type="button" class="appBooking" id="downloadAllDocs">Download All</button></a> -->
                        @if (
                            (int) Auth::user()->role_upload_docs === 1 ||
                                (int) Auth::user()->role_approve_docs === 1 ||
                                (int) Auth::user()->role_edit_app === 1 ||
                                (int) Auth::user()->role_comment === 1 ||
                                (int) Auth::user()->role_app_lease === 1 ||
                                (int) Auth::user()->role_sys_admin === 1 ||
                                (int) Auth::user()->role_super === 1)
                            <button type="button" class="appBooking" id="sendMailReminder"
                                onClick="reminderMail('{{ $user[0]['userInfo']['userUid'] }}')"
                                style="color:#fff; background-color:#e74f26; border:none">Email Reminder</button>
                        @endif
                        <!-- files_to_download -->
                        @if (
                            (int) Auth::user()->role_upload_docs === 1 ||
                                (int) Auth::user()->role_approve_docs === 1 ||
                                (int) Auth::user()->role_edit_app === 1 ||
                                (int) Auth::user()->role_app_lease === 1 ||
                                (int) Auth::user()->role_sys_admin === 1 ||
                                (int) Auth::user()->role_super === 1)
                            <button type="button" class="appBooking" id="downloadAllDocs"
                                onClick="downloadZipDocs('{{ $user[0]['userInfo']['userUid'] }}')">Download All</button>
                        @endif
                        <div class="waitve_btn"></div>
                        @if (in_array($booking->property, $allowedResidences))
                            @if (
                                (Auth::user()->role_modify_approved == 1 && $user[0]['userInfo']['rob_50_approve'] == 2) ||
                                    $user[0]['userInfo']['rob_50_approve'] == 0)
                                @if (Auth::user()->role_upload_docs == 1 && $user[0]['userInfo']['rob_60_cancelled'] == 0)
                                    <button type="button" class="appBooking" id="addOtherDocument"
                                        onClick="addOtherDoc('{{ $user[0]['userInfo']['userUid'] }}')">Add
                                        Document</button>
                                @endif
                            @endif
                        @endif
                    </div>

                    <br /><br /><br /><br />

                    <div id="documentsSectionDisplay">

                        <?php $i = 0; ?>

                        @foreach ($user[0]['files'] as $file)
                        {{-- <p>file: {{ $file['field'] }}</p> --}}
                            @if ($file['field'] !== 'moveIn' || ($file['field'] == 'moveIn' && $user[0]['userInfo']['rob_50_approve'] == '2'))
                                <div class="section">
                                    <div class="label">

                                        @if ($file['field'] === 'studentCard')
                                            STUDENT CARD
                                        @elseif($file['field'] === 'proof_of_payment')
                                            BOOKING FEE PROOF OF PAYMENT
                                        @elseif($file['field'] === 'copyID')
                                            COPY OF ID DOCUMENT
                                        @elseif($file['field'] === 'proofRegistration')
                                            PROOF OF REGISTRATION
                                        @elseif($file['field'] === 'bursaryApproval')
                                            @if ($user[0]['userInfo']['studentType'] == 'NSFAS')
                                                NSFAS APPROVAL
                                            @else
                                                BURSARY APPROVAL
                                            @endif
                                        @elseif($file['field'] === 'bankStatement')
                                            BANK STATEMENT
                                        @elseif($file['field'] === 'assurity_doc')
                                            DEBT ASSURITY
                                        @elseif($file['field'] === 'moveIn')
                                            LEASE
                                        @else
                                            <?php echo strtoupper(str_replace('_', ' ', str_replace('other', 'other |', $file['field']))); ?>
                                        @endif
                                    </div>
                                    <div class="status" id="{{ $file['field'] }}Status">

                                        <!--  -->

                                        @if (explode('_', $file['field'])[0] == 'other' && $file['field'] == $file['field'])
                                            @if ($file['status'] == 'XApproved' || $file['status'] == 'Approved')
                                                <div class="button approved"></div>
                                            @elseif($file['status'] == 'AUploaded')
                                                <div class="button action"></div>
                                            @else
                                                <div class="button rejected"></div>
                                            @endif
                                        @endif



                                        @if ($file['field'] === 'proof_of_payment')
                                            @if ($user[0]['userInfo']['rob_10_fee'] == 0)
                                                <div class="button rejected"></div>
                                            @elseif ($user[0]['userInfo']['rob_10_fee'] == 1)
                                                <div class="button action"></div>
                                            @elseif ($user[0]['userInfo']['rob_10_fee'] == 2)
                                                <div class="button approved"></div>
                                            @else
                                                <div class="button na"></div>
                                            @endif
                                        @endif

                                        @if ($file['field'] === 'copyID')
                                            @if ($user[0]['userInfo']['rob_15_id'] == 0)
                                                <div class="button rejected"></div>
                                            @elseif ($user[0]['userInfo']['rob_15_id'] == 1)
                                                <div class="button action"></div>
                                            @elseif ($user[0]['userInfo']['rob_15_id'] == 2)
                                                <div class="button approved"></div>
                                            @else
                                                <div class="button na"></div>
                                            @endif
                                        @endif

                                        {{-- @if ($file['field'] === 'MDA')
                                        
                                        @if ($file['status'] == 'XApproved' || $file['status'] == 'Approved')
                                            <div class="button approved"></div>
                                        @else
                                            <div class="button action"></div>
                                        @endif
                                        
                                    @endif --}}

                                        @if ($file['field'] === 'studentCard')
                                            @if ($user[0]['userInfo']['rob_20_card'] == 0)
                                                <div class="button rejected"></div>
                                            @elseif ($user[0]['userInfo']['rob_20_card'] == 1)
                                                <div class="button action"></div>
                                            @elseif ($user[0]['userInfo']['rob_20_card'] == 2)
                                                <div class="button approved"></div>
                                            @else
                                                <div class="button na"></div>
                                            @endif
                                        @endif

                                        @if ($file['field'] === 'proofRegistration')
                                            @if ($user[0]['userInfo']['rob_25_reg'] == 0)
                                                <div class="button rejected"></div>
                                            @elseif ($user[0]['userInfo']['rob_25_reg'] == 1)
                                                <div class="button action"></div>
                                            @elseif ($user[0]['userInfo']['rob_25_reg'] == 2)
                                                <div class="button approved"></div>
                                            @else
                                                <div class="button na"></div>
                                            @endif
                                        @endif

                                        @if ($file['field'] === 'bursaryApproval' || $file['field'] === 'bankStatement')
                                            @if ($user[0]['userInfo']['rob_30_funds'] == 0)
                                                <div class="button rejected"></div>
                                            @elseif ($user[0]['userInfo']['rob_30_funds'] == 1)
                                                <div class="button action"></div>
                                            @elseif ($user[0]['userInfo']['rob_30_funds'] == 2)
                                                <div class="button approved"></div>
                                            @else
                                                <div class="button na"></div>
                                            @endif
                                        @endif

                                        @if ($file['field'] === 'moveIn')
                                            @if ($user[0]['userInfo']['rob_55_lease'] == 0)
                                                <div class="button rejected"></div>
                                            @elseif ($user[0]['userInfo']['rob_55_lease'] == 1)
                                                <div class="button action"></div>
                                            @elseif ($user[0]['userInfo']['rob_55_lease'] == 2)
                                                <div class="button approved"></div>
                                            @else
                                                <div class="button na"></div>
                                            @endif
                                        @endif

                                        @if ($file['field'] === 'assurity_doc')
                                            @if ($user[0]['userInfo']['rob_65_debt'] == 0)
                                                <div class="button rejected"></div>
                                            @elseif ($user[0]['userInfo']['rob_65_debt'] == 1)
                                                <div class="button approved"></div>
                                            @elseif ($user[0]['userInfo']['rob_65_debt'] == 2)
                                                <div class="button action"></div>
                                            @else
                                                <div class="button na"></div>
                                            @endif
                                        @endif

                                        @if ($file['field'] === 'tempMoveIn')
                                            @if ($user[0]['userInfo']['rob_51_temp_lease_approve'] == 0)
                                                <div class="button rejected"></div>
                                            @elseif ($user[0]['userInfo']['rob_51_temp_lease_approve'] == 1)
                                                <div class="button approved"></div>
                                            @elseif ($user[0]['userInfo']['rob_51_temp_lease_approve'] == 2)
                                                <div class="button action"></div>
                                            @else
                                                <div class="button na"></div>
                                            @endif
                                        @endif

                                        {{-- @if ($file['field'] === 'moveIn')
                                        @if ($file['status'] === 'XApproved' || $file['status'] === 'Approved')
                                            <div class="button approved"></div>
                                        @elseif($file["status"] === "AUploaded")
                                            <div class="button action"></div>
                                        @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                            <div class="button rejected"></div>
                                        @endif
                                    @else
                                        @if ($file['status'] === 'AUploaded')
                                            <div class="button action"></div>
                                        @elseif($file["status"] === "XApproved")
                                            <div class="button approved"></div>
                                        @elseif($file["status"] === "Rejected" || $file["status"] === "")
                                            <div class="button rejected"></div>
                                        @elseif($file["status"] === "na")
                                            <div class="button na"></div>
                                        @endif
                                    @endif --}}

                                    </div>

                                    <div class="banner" id="{{ $file['field'] }}Banner">
                                        @if ($file['field'] === 'moveIn')
                                            @if (isset($file['url']) && isset($file['fileName']))
                                                <a href="/storage/{{ $file['url'] }}"
                                                    download="{{ $file['fileName'] }}">
                                                    <button type="button" id="myBtn_download">Download Lease</button>
                                                </a>
                                            @endif
                                        @endif

                                        @if ($file['field'] === 'moveIn' && $file['status'] !== 'XApproved')
                                            @if (Auth::user()->role_lease_regen == '1')
                                                <a href="#">
                                                    <button type="button" id="myBtn_regen_lease">Regen Lease</button>
                                                </a>
                                                @if ($file['status'] !== 'AUploaded')
                                                    <button type="button" id="myBtn_resend_lease"
                                                        onClick="resendLease()">Resend Lease</button>
                                                @endif
                                            @endif
                                        @endif

                                        @if ($file['field'] === 'assurity_doc')
                                            <div class="assurity-actions" style="display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
                                                @if (isset($file['url']) && isset($file['fileName']))
                                                    <a href="/storage/{{ $file['url'] }}" download="{{ $file['fileName'] }}">
                                                        <button type="button" id="myBtn_download_assurity" title="Download the latest saved Debt Assurity PDF.">Download</button>
                                                    </a>
                                                @endif

                                                @php
                                                    $rob65Debt = $user[0]['userInfo']['rob_65_debt'] ?? null;
                                                @endphp

                                                {{-- Only show generate/resend when not approved (Debt Assurity approved = 1). --}}
                                                @if (Auth::user()->role_sys_admin == 1 && (int) $rob65Debt !== 1)
                                                    <a href="#">
                                                        <button type="button" id="myBtn_regen_assurity" title="Create/refresh the Debt Assurity PDF, issue a new signing link, and email the sponsor (and notify the student).">Generate</button>
                                                    </a>
                                                    @if ($file['status'] !== 'AUploaded')
                                                        <button type="button" id="myBtn_resend_assurity" onClick="resendAssurity()" title="Email a new signing link without changing the current PDF content.">Resend</button>
                                                    @endif
                                                @endif

                                                @if (($file['status'] === 'AUploaded' || $file['status'] === 'XApproved') && isset($file['id']))
                                                    <button type="button" onClick="btn_view_document_ajax({{ $file['id'] }})" title="View the uploaded/signed document in the modal.">View</button>
                                                @endif

                                                {{-- Optional manual upload of a signed PDF (sys admin only). --}}
                                                @if (Auth::user()->role_sys_admin == 1 && $file['status'] !== 'XApproved')
                                                    <label for="assurity_doc" id="assurity_docSection" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;" title="Sys admin only: manually upload a signed Debt Assurity PDF.">
                                                        <span class="text">Upload Signed</span>
                                                        <i class="fas fa-file-upload"></i>
                                                    </label>
                                                    <input type="file" id="assurity_doc" onChange="UploadDocument('assurity_doc')" accept="image/*, application/pdf" hidden />
                                                @endif
                                            </div>
                                        @endif


                                        <!-- tempMoveIn -->
                                        @if ($file['field'] === 'tempMoveIn')
                                            @if (isset($file['url']) && isset($file['fileName']))
                                                <a href="/storage/{{ $file['url'] }}"
                                                    download="{{ $file['fileName'] }}">
                                                    <button type="button" id="myBtn_temp_download"><small >Download Temp Lease</small></button>
                                                </a>
                                            @endif
                                        @endif
                                        @if ($file['field'] === 'tempMoveIn' && $file['status'] !== 'XApproved')
                                            @if (Auth::user()->role_lease_regen == '1')
                                                <a href="#">
                                                    <button type="button" id="myBtn_regen_temp_lease"> <small >Regen Temp Lease</small></button>
                                                </a>
                                                @if ($file['status'] !== 'AUploaded')
                                                    <button type="button" id="myBtn_resend_temp_lease"
                                                        onClick="resendTempLease()"> <small >Resend Temp Lease</small></button>
                                                        
                                                @endif
                                            @endif
                                        @endif
                                        <!-- tempMoveIn -->

                                        @if (($file['status'] === 'AUploaded' || $file['status'] === 'XApproved') && $file['field'] !== 'assurity_doc')
                                            <button type="button"
                                                onClick="btn_view_document_ajax({{ $file['id'] }})">View
                                                Document</button>
                                        @else
                                            @if (in_array($booking->property, $allowedResidences))
                                                @if (Auth::user()->role_upload_docs == 1 && $user[0]['userInfo']['rob_60_cancelled'] == 0)
                                                    @if ($file['field'] === 'assurity_doc')
                                                        {{-- Assurity is sponsor-signed; manual upload is handled in the assurity block above (sys admin only). --}}
                                                    @else
                                                    <!-- booking_payment_ref -->
                                                    @if ($file['field'] === 'proof_of_payment')
                                                        <label onClick="paymentRef()" id="{{ $file['field'] }}Section"
                                                            class="outstandingUpload"
                                                            style="padding:8px 16px; border-radius:10px;">
                                                            <span class="text">Upload</span>
                                                            <i class="fas fa-file-upload"></i>
                                                        </label>
                                                    @else
                                                        <label for="{{ $file['field'] }}"
                                                            id="{{ $file['field'] }}Section" class="outstandingUpload"
                                                            style="padding:8px 16px; border-radius:10px;">
                                                            <span class="text">Upload</span>
                                                            <i class="fas fa-file-upload"></i>
                                                        </label>
                                                        <input type="file" id="{{ $file['field'] }}"
                                                            onChange="UploadDocument('{{ $file['field'] }}')"
                                                            accept="image/*, application/pdf" hidden />
                                                    @endif
                                                    @endif


                                                    @if (explode('_', $file['field'])[0] == 'other')
                                                        <button type="button" style="margin-left:5px;"
                                                            onClick="deleteFile('{{ $file['field'] }}')">Delete</button>
                                                    @endif
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <?php $i++; ?>
                            @endif
                        @endforeach

                    </div>


                </div>
                @if (Auth::user()->role_sys_admin == 1 || Auth::user()->role_bm_report == 1)
                    <div class="customWhiteCard" id="RobFixes" style="margin-top:20px; margin-bottom:20px;">
                        <h2 class="appHeader">Irregularity Report</h2>
                        <p class="formParagraph" style="margin-bottom:10px;">
                            Diagnostics and actions. Comment is required for any change (logged to Action List).
                        </p>
                        @php
                            $bookingFee = null;
                            if (isset($paymentBooking) && $paymentBooking) {
                                $bookingFee = $paymentBooking->booking_fee;
                            } elseif (isset($booking) && $booking) {
                                $bookingFee = $booking->room_rate;
                            }

                            $bookingFeeWaived = false;
                            if (isset($paymentBooking) && $paymentBooking) {
                                $bookingFeeWaived =
                                    $paymentBooking->booking_fee_waived == '1' ||
                                    $paymentBooking->rob_b_waived == '1';
                            }

                            $requiresBookingFeeDoc = (!$bookingFeeWaived) && (!is_null($bookingFee)) && ((float) $bookingFee > 0);

                            $rob10Fee = $user[0]['userInfo']['rob_10_fee'] ?? null;
                            $bookingFeeIssue = (!$requiresBookingFeeDoc) && ((int) $rob10Fee !== 2);

                            $popStatus = null;
                            $popStatusType = 'ok';
                            foreach ($user[0]['files'] as $file) {
                                if ($file['field'] === 'proof_of_payment') {
                                    $popStatus = $file['status'] ?? null;
                                    break;
                                }
                            }

                            if ($requiresBookingFeeDoc) {
                                if (is_null($popStatus)) {
                                    $popStatusType = 'missing';
                                } elseif ($popStatus === 'Approved' || $popStatus === 'XApproved') {
                                    $popStatusType = 'ok';
                                } elseif ($popStatus === 'AUploaded' || $popStatus === 'Uploaded') {
                                    $popStatusType = 'action';
                                } else {
                                    $popStatusType = 'missing';
                                }
                            }

                            $popIssue = $requiresBookingFeeDoc && $popStatusType !== 'ok';

                            $otherFiles = [];
                            $hasOtherUploaded = false;
                            $hasOtherRejected = false;
                            $hasOtherApproved = false;

                            foreach ($user[0]['files'] as $file) {
                                if (strpos($file['field'], 'other') === 0) {
                                    $otherFiles[] = $file;
                                    if ($file['status'] === 'AUploaded' || $file['status'] === 'Uploaded') {
                                        $hasOtherUploaded = true;
                                    } elseif ($file['status'] === 'Rejected') {
                                        $hasOtherRejected = true;
                                    } elseif ($file['status'] === 'XApproved' || $file['status'] === 'Approved') {
                                        $hasOtherApproved = true;
                                    }
                                }
                            }

                            $otherStatus = 3;
                            if (count($otherFiles) > 0) {
                                if ($hasOtherUploaded) {
                                    $otherStatus = 1;
                                } elseif ($hasOtherRejected) {
                                    $otherStatus = 0;
                                } elseif ($hasOtherApproved) {
                                    $otherStatus = 2;
                                }
                            }

                            $hasWaiver = false;
                            if (isset($paymentBooking) && $paymentBooking) {
                                $hasWaiver =
                                    $paymentBooking->rob_b_waived == '1' ||
                                    $paymentBooking->rob_loan_waived == '1' ||
                                    $paymentBooking->rob_deposit_waived == '1';
                            }

                            if ($hasWaiver || $otherStatus == 1) {
                                $expectedRob45 = 1;
                            } elseif ($otherStatus == 0) {
                                $expectedRob45 = 0;
                            } elseif ($otherStatus == 3) {
                                $expectedRob45 = 3;
                            } else {
                                $expectedRob45 = 2;
                            }

                            $actualRob45 = (int) ($user[0]['userInfo']['rob_45_other'] ?? -1);
                            $rob45Mismatch = $actualRob45 !== $expectedRob45;
                            $bedCode = $booking->bed_code ?? null;
                            $rob40Bed = $user[0]['userInfo']['rob_40_bed'] ?? null;
                            $bedAllocationMismatch = (!empty($bedCode)) && ((int) $rob40Bed !== 2);

                            $requiresDebtAssurity = ($user[0]['userInfo']['studentType'] ?? null) === 'Cash / Direct';
                            $rob65Debt = $user[0]['userInfo']['rob_65_debt'] ?? null;
                            $debtAssurityIssue = $requiresDebtAssurity && ((int) $rob65Debt !== 1);

                            $hasBlockingIssues = $bookingFeeIssue || $rob45Mismatch || $popIssue || $bedAllocationMismatch || $debtAssurityIssue;

                            $robRows = [
                                ['key' => 'rob_10_fee', 'label' => 'Booking Fee', 'naLabel' => 'Not Applicable'],
                                ['key' => 'rob_15_id', 'label' => 'Copy of ID', 'naLabel' => 'Not Applicable'],
                                ['key' => 'rob_20_card', 'label' => 'Student Card', 'naLabel' => 'Not Applicable'],
                                ['key' => 'rob_25_reg', 'label' => 'Registration', 'naLabel' => 'Not Applicable'],
                                ['key' => 'rob_30_funds', 'label' => 'Funding', 'naLabel' => 'Not Applicable'],
                                ['key' => 'rob_35_info', 'label' => 'Other Info', 'naLabel' => 'Not Applicable'],
                                ['key' => 'rob_40_bed', 'label' => 'Bed Allocation', 'naLabel' => 'Not Applicable'],
                                ['key' => 'rob_45_other', 'label' => 'Other Documents', 'naLabel' => 'Not Required'],
                                ['key' => 'rob_65_debt', 'label' => 'Debt Assurity', 'naLabel' => 'Not Required'],
                                ['key' => 'rob_50_approve', 'label' => 'Application Approval', 'naLabel' => 'Not Set'],
                                ['key' => 'rob_55_lease', 'label' => 'Lease', 'naLabel' => 'Not Set'],
                                ['key' => 'rob_51_temp_lease_approve', 'label' => 'Temp Lease', 'naLabel' => 'Not Set'],
                            ];

                            $robStatusLabels = [
                                0 => 'Rejected',
                                1 => 'Action Required',
                                2 => 'Approved',
                                3 => 'Not Required',
                            ];

                            $robStatusClasses = [
                                0 => 'rejected',
                                1 => 'action',
                                2 => 'approved',
                                3 => 'na',
                            ];
                        @endphp

                        @if ($hasBlockingIssues)
                            <p class="formParagraph" style="margin-bottom:10px; color:#e74f26;">
                                Blocking issues detected. A comment is required before applying any fix.
                            </p>
                        @endif

                        <div class="robFixLegend" style="display:flex; align-items:center; gap:16px; margin-bottom:10px;">
                            <div style="display:flex; align-items:center; gap:6px;">
                                <div class="status"><div class="button approved"></div></div>
                                <span>Approved</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <div class="status"><div class="button action"></div></div>
                                <span>Action Required</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <div class="status"><div class="button rejected"></div></div>
                                <span>No Document</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <div class="status"><div class="button na"></div></div>
                                <span>Not Required</span>
                            </div>
                        </div>

                        <div class="robFixTableWrap">
                        <table class="table table-sm robFixTable" style="margin-bottom:12px; table-layout:fixed; width:100%;">
                            <colgroup>
                                <col style="width:28%;">
                                <col style="width:10%;">
                                <col style="width:42%;">
                                <col style="width:20%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Issue</th>
                                    <th>Status</th>
                                    <th>Detail</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Booking Fee Requirement</td>
                                    <td>
                                        <div class="status" id="robFixBookingFeeStatus">
                                            <div class="button {{ $bookingFeeIssue ? 'action' : 'approved' }}"></div>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        <span
                                            title="{{ $bookingFeeIssue ? 'Action Required' : 'OK' }} | Booking fee: {{ is_null($bookingFee) ? 'null' : $bookingFee }} | booking_fee_waived: {{ $bookingFeeWaived ? '1' : '0' }} | rob_10_fee: {{ is_null($rob10Fee) ? 'null' : $rob10Fee }}">
                                            Info
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        @if ($bookingFeeIssue && Auth::user()->role_sys_admin == 1)
                                            <button type="button" class="appBooking robFixActionBtn"
                                                onClick="robFix('clear_booking_fee_requirement', '{{ $user[0]['userInfo']['userUid'] }}', '{{ $booking->id ?? '' }}')">
                                                Clear Requirement
                                            </button>
                                        @elseif ($bookingFeeIssue)
                                            <button type="button" class="appBooking robFixDisabledBtn" disabled
                                                title="Sys admin only">Restricted</button>
                                        @else
                                            <button type="button" class="appBooking robFixDisabledBtn" disabled>None</button>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Proof of Payment (Booking Fee)</td>
                                    <td>
                                        <div class="status" id="robFixProofOfPaymentStatus">
                                            @if ($popIssue)
                                                <div class="button {{ $popStatusType === 'action' ? 'action' : 'rejected' }}"></div>
                                            @else
                                                <div class="button approved"></div>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        <span
                                            title="{{ $requiresBookingFeeDoc ? 'Required' : 'Not Required' }} | proof_of_payment status: {{ is_null($popStatus) ? 'missing' : $popStatus }}">
                                            Info
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        <button type="button" class="appBooking robFixDisabledBtn" disabled>None</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Other Documents Requirement</td>
                                    <td>
                                        <div class="status" id="robFixOtherDocsStatus">
                                            <div class="button {{ $rob45Mismatch ? 'action' : 'approved' }}"></div>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        <span
                                            title="{{ $rob45Mismatch ? 'Action Required' : 'OK' }} | rob_45_other: {{ $actualRob45 }} | expected: {{ $expectedRob45 }}">
                                            Info
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        @if ($rob45Mismatch && Auth::user()->role_sys_admin == 1)
                                            <button type="button" class="appBooking robFixActionBtn"
                                                onClick="robFix('recompute_other_docs', '{{ $user[0]['userInfo']['userUid'] }}', '{{ $booking->id ?? '' }}')">
                                                Recompute Other Docs
                                            </button>
                                        @elseif ($rob45Mismatch)
                                            <button type="button" class="appBooking robFixDisabledBtn" disabled
                                                title="Sys admin only">Restricted</button>
                                        @else
                                            <button type="button" class="appBooking robFixDisabledBtn" disabled>None</button>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Bed Allocation</td>
                                    <td>
                                        <div class="status" id="robFixBedStatus">
                                            <div class="button {{ $bedAllocationMismatch ? 'action' : 'approved' }}"></div>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        <span
                                            title="{{ $bedAllocationMismatch ? 'Action Required' : 'OK' }} | bed_code: {{ $bedCode ?? 'null' }} | rob_40_bed: {{ is_null($rob40Bed) ? 'null' : $rob40Bed }}">
                                            Info
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        @if ($bedAllocationMismatch && Auth::user()->role_sys_admin == 1)
                                            <button type="button" class="appBooking robFixActionBtn"
                                                onClick="robFix('fix_bed_allocation', '{{ $user[0]['userInfo']['userUid'] }}', '{{ $booking->id ?? '' }}')">
                                                Approve Bed Allocation
                                            </button>
                                        @elseif ($bedAllocationMismatch)
                                            <button type="button" class="appBooking robFixDisabledBtn" disabled
                                                title="Sys admin only">Restricted</button>
                                        @else
                                            <button type="button" class="appBooking robFixDisabledBtn" disabled>None</button>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>

                        <div class="robFixTableWrap">
                        <table class="table table-sm robFixTable" style="margin-bottom:0; table-layout:fixed; width:100%;">
                            <colgroup>
                                <col style="width:32%;">
                                <col style="width:10%;">
                                <col style="width:58%;align:center;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Step</th>
                                    <th>Status</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($robRows as $robRow)
                                    @php
                                        $robValue = $user[0]['userInfo'][$robRow['key']] ?? null;
                                        $robValueInt = is_null($robValue) ? null : (int) $robValue;
                                        $robLabel = $robStatusLabels[$robValueInt] ?? 'Not Set';
                                        $robClass = $robStatusClasses[$robValueInt] ?? 'na';
                                        if ($robRow['key'] === 'rob_51_temp_lease_approve') {
                                            $tempLeaseLabels = [
                                                0 => 'Rejected',
                                                1 => 'Approved',
                                                2 => 'Pending',
                                                3 => 'Not Set',
                                            ];
                                            $tempLeaseClasses = [
                                                0 => 'rejected',
                                                1 => 'approved',
                                                2 => 'action',
                                                3 => 'na',
                                            ];
                                            $robLabel = $tempLeaseLabels[$robValueInt] ?? 'Not Set';
                                            $robClass = $tempLeaseClasses[$robValueInt] ?? 'na';
                                        }
                                        if ($robRow['key'] === 'rob_65_debt') {
                                            $assurityLabels = [
                                                0 => 'Rejected',
                                                1 => 'Approved',
                                                2 => 'Pending',
                                                3 => 'Not Set',
                                            ];
                                            $assurityClasses = [
                                                0 => 'rejected',
                                                1 => 'approved',
                                                2 => 'action',
                                                3 => 'na',
                                            ];
                                            $robLabel = $assurityLabels[$robValueInt] ?? 'Not Set';
                                            $robClass = $assurityClasses[$robValueInt] ?? 'na';
                                        }
                                        if ($robValueInt === 3) {
                                            if ($robRow['key'] === 'rob_65_debt' && $requiresDebtAssurity) {
                                                $robLabel = 'Not Set';
                                            } else {
                                                $robLabel = $robRow['naLabel'];
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <span title="{{ $robRow['key'] }}">{{ $robRow['label'] }}</span>
                                        </td>
                                        <td>
                                            <div class="status" id="{{ $robRow['key'] }}Status">
                                                <div class="button {{ $robClass }}"></div>
                                            </div>
                                        </td>
                                        <td style="text-align:center;">
                                            <span title="{{ $robLabel }} | {{ is_null($robValue) ? 'null' : $robValue }}">
                                                Info
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td><span title="rob_60_cancelled">Cancelled</span></td>
                                    <td>
                                        <div class="status" id="rob_60_cancelledStatus">
                                            @if ($user[0]['userInfo']['rob_60_cancelled'] == 1)
                                                <div class="button rejected"></div>
                                            @elseif ($user[0]['userInfo']['rob_60_cancelled'] == 0)
                                                <div class="button approved"></div>
                                            @else
                                                <div class="button na"></div>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        <span
                                            title="@if ($user[0]['userInfo']['rob_60_cancelled'] == 1) Yes @elseif ($user[0]['userInfo']['rob_60_cancelled'] == 0) No @else Not Set @endif | {{ $user[0]['userInfo']['rob_60_cancelled'] ?? 'null' }}">
                                            Info
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
                @endif
{{-- Comments --}}
                <div class="customWhiteCard" id="Comments" style="margin-top:20px; margin-bottom:20px; height:660px;">
                    <h2 class="appHeader">Comments</h2>
                    @if (in_array($booking->property, $allowedResidences))
                        @if (Auth::user()->role_comment == '1' && $user[0]['userInfo']['rob_60_cancelled'] == 0)
                            <textarea id="newComment" name="newComment" rows="6" cols="50" style="width:100%; border-radius:5px;"></textarea>
                            <br />
                            <button type="button" class='detailsChange' id="addComment"
                                style="background-color:#e74f26">Add Comment</button>
                        @endif
                    @endif

                    <!-- comments -->

                    <div class="commentItemList"
                        style="<?php if (Auth::user()->role_comment == '1') {
                            echo 'margin-top:73px;';
                        } ?> max-height: 350px; overflow: auto; width:100%">

                        <div class="comments_block"></div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <div id="myModal" class="modal" style="padding-top: 10px;"></div>

    <div id="changeBuildingModal" class="modal">
        <div class="modal-content " id="changeBuildingHeight" style="height: 275px;">
            <span class="closeBuilding" style="float:right; font-size:25px; cursor:pointer">&times;</span>
            <div class="form-card">
                <h2>Change this booking</h2>

                <input type="hidden" id="bookingId" />
                <br /><br />
                <div class="form-field">
                    <label>Residence * <span id="propertyBuilding_error" class="hidden"><small style="color:red">This
                                field is required</small></span></label>
                    <select name="propertyBuilding" id='propertyBuilding' class="required">
                        <option value="" selected disabled>Select a Residence</option>
                    </select>
                </div>

                <div class="form-field" id="buildingRooms">
                    <label>Room Type * <span id="roomTypeBuilding_error" class="hidden"><small style="color:red">This
                                field is required</small></span></label>
                    <select name="roomTypeBuilding" id='roomTypeBuilding' class="required">
                        <option value="" selected disabled>Select a Room Type</option>
                    </select>
                </div>
                <div class="form-field-big">
                    <button type="button" id="changeBuildingType" class="profileSubmit"
                        style="width:98%; border:none; padding:12px; border-radius:10px;">Change building</button>
                </div>
            </div>
        </div>
    </div>

    <div id="myModalEditApp" class="modal">

        <div class="modal-content " id="editModalHeight" style="height: 439px;">
            <span class="closeBooking" style="float:right; font-size:25px; cursor:pointer">&times;</span>
            <div class="form-card">
                <h2>Change this booking</h2>

                <input type="hidden" id="bookingId" />

                <br /><br />
                <div class="form-field">
                    <label>Residence * <span id="property_error" class="hidden"><small style="color:red">This field is
                                required</small></span></label>

                    <select name="property" id='property' class="required">
                        <option value="" selected disabled>Select a Residence</option>
                    </select>
                </div>

                <div class="form-field">
                    <label>Room Type * <span id="roomType_error" class="hidden"><small style="color:red">This field is
                                required</small></span></label>
                    <select name="roomType" id='roomType' class="required">
                        <option value="" selected disabled>Select a Room Type</option>
                    </select>
                </div>

                <div class="form-field">
                    <label>Floor</label>
                    <select name="floor" id='floor'>
                        <!--  -->
                    </select>
                </div>

                <div class="form-field">
                    <label>Unit</label>
                    <select name="unit" id='unit'>
                        <!--  -->
                    </select>
                </div>

                <div class="form-field" id="roomSection">
                    <label>Room</label>
                    <select name="room" id='room'>
                        <!--  -->
                    </select>
                </div>

                <div class="form-field">
                    <label>Bed Code * <span id="bed_code_error" class="hidden"><small style="color:red">This field is
                                required</small></span></label>
                    <select name="bed_code" id='bed_code'>
                        <!--  -->
                    </select>
                </div>

                <input type="hidden" name="room" id="room" value="" />
                <input type="hidden" name="bed" id="bed" value="" />

                <div class="form-field-big" id="ChangeBookingOnly">
                    <button type="button" id="changeBookingType" class="profileSubmit"
                        style="width:98%; border:none; padding:12px; border-radius:10px;">Change booking</button>
                </div>
            </div>


        </div>

    </div>

    <div id="myModalEditLease" class="modal">

        <div class="modal-content " id="leaseEditModal" style="height: 304px;">
            <span class="closeLease" style="float:right; font-size:25px; cursor:pointer">&times;</span>
            <div class="form-card">
                <h2>Change this Lease</h2>
                <input type="hidden" id="bookingId2" />
                <div id="LeaseInfo">
                    <div class="form-field">
                        <label>Lease From Date * <span id="lease_from_date_error" class="hidden"><small
                                    style="color:red">This field is required</small></span></label>
                        <input type="date" name="lease_from_date" id="lease_from_date" <?php if ($booking->lease_from_date == null) {
                            foreach ($lookups as $lookup) {
                                if ($lookup->group_id == '4') {
                                    echo "value='" . $lookup->lease_from_date . "'";
                                }
                            }
                        } else {
                            echo "value='" . $booking->lease_from_date . "'";
                        } ?>
                            max="<?php echo $this_year; ?>-12-31" min="<?php echo $this_year; ?>-01-01">
                    </div>
                    <div class="form-field">
                        <label>Lease From Time * <span id="lease_from_time_error" class="hidden"><small
                                    style="color:red">This field is required</small></span></label>
                        <input type="time" id="lease_from_time" name="lease_from_time" <?php if ($booking->lease_from_time == null) {
                            foreach ($lookups as $lookup) {
                                if ($lookup->group_id == '4') {
                                    echo "value='" . $lookup->lease_from_time . "'";
                                }
                            }
                        } else {
                            echo "value='" . $booking->lease_from_time . "'";
                        } ?>>
                    </div>
                    <div class="form-field">
                        <label>Lease To Date * <span id="lease_to_date_error" class="hidden"><small
                                    style="color:red">This field is required</small></span></label>
                        <input type="date" name="lease_to_date" id="lease_to_date" <?php if ($booking->lease_to_date == null) {
                            foreach ($lookups as $lookup) {
                                if ($lookup->group_id == '4') {
                                    echo "value='" . $lookup->lease_to_date . "'";
                                }
                            }
                        } else {
                            echo "value='" . $booking->lease_to_date . "'";
                        } ?>
                            max="<?php echo $this_year; ?>-12-31" min="<?php echo $this_year; ?>-01-01">
                    </div>
                    <div class="form-field">
                        <label>Lease To Time * <span id="lease_to_time_error" class="hidden"><small
                                    style="color:red">This field is required</small></span></label>
                        <input type="time" id="lease_to_time" name="lease_to_time" <?php if ($booking->lease_to_time == null) {
                            foreach ($lookups as $lookup) {
                                if ($lookup->group_id == '4') {
                                    echo "value='" . $lookup->lease_to_time . "'";
                                }
                            }
                        } else {
                            echo "value='" . $booking->lease_to_time . "'";
                        } ?>>
                    </div>
                </div>
                <div class="form-field-big" id="changeLeaseDatesOnly">
                    <button type="button" id="changeLeaseDates" class="profileSubmit"
                        style="width:98%; border:none; padding:12px; border-radius:10px;">Change Lease Dates</button>
                </div>
            </div>
        </div>
    </div>

    <div id="myModalSposorInformation" class="modal">
        <div class="modal-content " style="height: 690px;">
            <!-- <span class="closeSponsorinfo" style="float:right; font-size:25px; cursor:pointer">&times;</span> -->
            <div class="form-card">
                <h2>Sponsor Information</h2>
                <div class="form-field">
                    <label>First Name * <span id="pFirstName_error" class="hidden" style="color:red"><small>This field is
                                required</small></span></label>
                    <input type="text" name="pFirstName" id="pFirstName" class="required"
                        value="{{ $user[0]['userInfo']['sponFirstName'] }}">
                </div>

                <div class="form-field">
                    <label>Last Name * <span id="pLastName_error" class="hidden" style="color:red"><small>This field is
                                required</small></span></label>
                    <input type="text" name="pLastName" id="pLastName" class="required"
                        value="{{ $user[0]['userInfo']['sponLastName'] }}">
                </div>

                <div class="form-field">
                    <label>Cellphone Number * <span id="pCell_no_error" class="hidden" style="color:red"><small>Not a
                                valid cellphone number</small></span></label>
                    <input type="text" name="sponCellCode" id="sponCellCode" class="required"
                        value="{{ $user[0]['userInfo']['sponCellCode'] }}" style="width:8% !important; margin-right:2%"
                        value="+27">
                    <input type="text" name="pCell_no" id="pCell_no" class="required"
                        value="{{ $user[0]['userInfo']['sponCell'] }}" style="width:85% !important">
                </div>

                <div class="form-field">
                    <label>Email Address * <span id="pEmail_error" class="hidden" style="color:red"><small>Not a valid
                                email</small></span></label>
                    <input type="email" name="pEmail" id='pEmail' class="required"
                        value="{{ $user[0]['userInfo']['sponEmail'] }}">
                </div>

                <div class="form-field">
                    <label>Nationality * <span id="pNationality_error" class="hidden" style="color:red"><small>This field
                                is required</small></span></label>
                    <select name="pNationality" id='pNationality' class="required">
                        <option value="" selected disabled>Please select one</option>
                        @foreach ($lookups as $lookup)
                            @if ($lookup->group_id == '5')
                                <option value="{{ $lookup->lookup_name }}" <?php if ($user[0]['userInfo']['pNationality'] == $lookup->lookup_name) {
                                    echo 'selected';
                                } ?>>
                                    {{ $lookup->lookup_name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-field" id="pIdField">
                    <label>I.D Number * <span id="pIdNumber_error" class="hidden" style="color:red"><small>Not a valid ID
                                number</small></span></label>
                    <input type="text" name="pIdNumber" id="pIdNumber"
                        value="{{ $user[0]['userInfo']['sponId'] }}">
                </div>

                <div class="form-field hidden" id="pPassportField">
                    <label>Passport Number * <span id="pPassportNo_error" class="hidden" style="color:red"><small>This
                                field is required</small></span></label>
                    <input type="text" name="pPassportNo" id="pPassportNo"
                        value="{{ $user[0]['userInfo']['pPassportNo'] }}">
                </div>



                <!-- <div class="form-field-big">
                                                                                                                                                                                                                                                        <label>Physical Address* <span id="pAddress1_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                                                                                                                                                                                                                                                        <input type="text" placeholder="Street No" name="pAddress1" id="pAddress1" style="width:98% !important">
                                                                                                                                                                                                                                                    </div> -->
                <div class="form-field-big" style="height:50px !important">
                    <label>Physical Address * <span id="pAddress1_error" class="hidden" style="color:red"><small>This
                                field is required</small></span></label>
                </div>
                <div class="form-field-big" style="height:60px !important">
                    <li style="display:inline-flex">
                        <input type="checkbox" id="app_address_p" name="app_address_p" value="1"
                            style="width:50px !important;" <?php if ($user[0]['userInfo']['app_address_p'] == '1') {
                                echo 'checked';
                            } ?>>
                        <label for="app_address_p" style="width:400px">Same as Applicant's Home Address</label>
                    </li>
                </div>

                <div class="pAddress <?php if ($user[0]['userInfo']['app_address_p'] == '1') {
                    echo 'hidden';
                } ?>">
                    <div class="form-field-big" style="height:60px !important">
                        <input type="text" placeholder="Street No" name="pAddress1" id="pAddress1"
                            style="width:98% !important" class="required"
                            value="{{ $user[0]['userInfo']['pAddress1'] }}">
                    </div>
                    <div class="form-field-big" style="height:60px !important">
                        <span id="pAddress2_error" class="hidden" style="color:red"><small>This field is
                                required</small></span>
                        <input type="text" placeholder="Suburb" name="pAddress2" id="pAddress2"
                            style="width:98% !important" value="{{ $user[0]['userInfo']['pAddress2'] }}">
                    </div>
                    <div class="form-field" style="height:60px !important">
                        <span id="pAddressCity_error" class="hidden" style="color:red"><small>This field is
                                required</small></span>
                        <input type="text" placeholder="City" name="pAddressCity" id='pAddressCity'
                            value="{{ $user[0]['userInfo']['pAddressCity'] }}">
                    </div>
                    <div class="form-field" style="height:60px !important">
                        <span id="pAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal
                                code</small></span>
                        <input type="text" placeholder="Postal Code" name="pAddressPostal" id="pAddressPostal"
                            value="{{ $user[0]['userInfo']['pAddressPostal'] }}">
                    </div>
                </div>
                <div class="form-field-big" id="changeLeaseDatesOnly">
                    <button type="button" id="sponsorSubmit" class="profileSubmit"
                        style="width:98%; border:none; padding:12px; border-radius:10px;">Save Sponsor / Parent
                        Information</button>
                </div>
            </div>
        </div>
    </div>

    <div id="myModalBursarInformation" class="modal">
        <div class="modal-content " style="height: 860px;">
            <!-- <span class="closeBursarinfo" style="float:right; font-size:25px; cursor:pointer">&times;</span> -->
            <div class="form-card">
                <h2>Bursar Information</h2>
                <div class="form-field">
                    <label>Company Name * <span id="bCompanyName_error" class="hidden" style="color:red"><small>This
                                field is required</small></span></label>
                    <input type="text" name="bCompanyName" id='bCompanyName' class="required"
                        value="{{ $user[0]['userInfo']['burCompanyName'] }}">
                </div>

                <div class="form-field">
                    <label>Registration Number <span id="bRegistrationNo_error" class="hidden"
                            style="color:red"><small>This field is required</small></span></label>
                    <input type="text" name="bRegistrationNo" id="bRegistrationNo"
                        value="{{ $user[0]['userInfo']['burRegNo'] }}">
                </div>

                <div class="form-field">
                    <label>Contact Person * <span id="bContactperson_error" class="hidden" style="color:red"><small>This
                                field is required</small></span></label>
                    <input type="text" name="bContactPerson" id="bContactPerson" class="required"
                        value="{{ $user[0]['userInfo']['burContactPerson'] }}">
                </div>

                <div class="form-field">
                    <label>Contact Number * <span id="bContactNumber_error" class="hidden" style="color:red"><small>Not a
                                valid cellphone number</small></span></label>
                    <input type="text" name="burContactCode" id="burContactCode" class="required"
                        value="{{ $user[0]['userInfo']['burContactCode'] }}"
                        style="width:8% !important; margin-right:2%" value="+27">
                    <input type="text" name="bContactNumber" id="bContactNumber" class="required"
                        value="{{ $user[0]['userInfo']['burContactNo'] }}" style="width:85% !important">
                </div>

                <div class="form-field">
                    <label>Email Address * <span id="bEmail_error" class="hidden" style="color:red"><small>Not a valid
                                email</small></span></label>
                    <input type="email" name="bEmail" id="bEmail" class="required"
                        value="{{ $user[0]['userInfo']['burEmail'] }}">
                </div>

                <div class="form-field">
                    <br />
                </div>

                <div class="form-field-big">
                    <label>Physical Address * <span id="bAddress1_error" class="hidden" style="color:red"><small>This
                                field is required</small></span></label>
                    <input type="text" placeholder="Street No" name="bAddress1" id="bAddress1"
                        style="width:98% !important" class="required" value="{{ $user[0]['userInfo']['bAddress1'] }}">
                </div>
                <div class="form-field-big" style="height:60px !important">
                    <span id="bAddress2_error" class="hidden" style="color:red"><small>This field is
                            required</small></span>
                    <input type="text" placeholder="Suburb" name="bAddress2" id="bAddress2"
                        style="width:98% !important" class="required" value="{{ $user[0]['userInfo']['bAddress2'] }}">
                </div>
                <div class="form-field" style="height:60px !important">
                    <span id="bAddressCity_error" class="hidden" style="color:red"><small>This field is
                            required</small></span>
                    <input type="text" placeholder="City" name="bAddressCity" id='bAddressCity' class="required"
                        value="{{ $user[0]['userInfo']['bAddressCity'] }}">
                </div>
                <div class="form-field" style="height:60px !important">
                    <span id="bAddressPostal_error" class="hidden" style="color:red"><small>Not a valid postal
                            code</small></span>
                    <input type="text" placeholder="Postal Code" name="bAddressPostal" id="bAddressPostal"
                        class="required" value="{{ $user[0]['userInfo']['bAddressPostal'] }}">
                </div>

                <div class="form-field">
                    <label>How Is the Bursary Paid * <span id="bPayment_error" class="hidden"
                            style="color:red"><small>This field is required</small></span></label>
                    <select name="bPayment" id='bPayment' class="required">
                        <option value="" disabled selected>Please select one</option>
                        <option value="Directly to the RESIDENCE" <?php if ($user[0]['userInfo']['bPayment'] == 'Directly to the RESIDENCE') {
                            echo 'selected';
                        } ?>>Directly to the RESIDENCE</option>
                        <option value="Directly to the STUDENT" <?php if ($user[0]['userInfo']['bPayment'] == 'Directly to the STUDENT') {
                            echo 'selected';
                        } ?>>Directly to the STUDENT</option>
                        <option value="Via an INTERMEDIARY" <?php if ($user[0]['userInfo']['bPayment'] == 'Via an INTERMEDIARY') {
                            echo 'selected';
                        } ?>>Via an INTERMEDIARY</option>
                    </select>
                </div>

                <div class="form-field" id="placeholder">
                    <br />
                </div>

                <div id="viaInt" class="<?php if ($user[0]['userInfo']['bPayment'] != 'Via an INTERMEDIARY') {
                    echo 'hidden';
                } ?>">

                    <div class="form-field">
                        <label>Intermediary * <span id="bint_error" class="hidden" style="color:red"><small>This field is
                                    required</small></span></label>
                        <select name="bint" id='bint'>
                            <option value="" disabled selected>Please select one</option>
                            <option value="Fundi" <?php if ($user[0]['userInfo']['bint'] == 'Fundi') {
                                echo 'selected';
                            } ?>>Fundi</option>
                            <option value="Intellicard" <?php if ($user[0]['userInfo']['bint'] == 'Intellicard') {
                                echo 'selected';
                            } ?>>Intellicard</option>
                            <option value="Other" <?php if ($user[0]['userInfo']['bint'] == 'Other') {
                                echo 'selected';
                            } ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div id="spesificInt" class="<?php if ($user[0]['userInfo']['bint'] != 'Other') {
                    echo 'hidden';
                } ?>">

                    <div class="form-field">
                        <label>Specify Intermediary Name * <span id="bIntName_error" class="hidden"
                                style="color:red"><small>This field is required</small></span></label>
                        <input type="text" name="bIntName" id="bIntName"
                            value="{{ $user[0]['userInfo']['bIntName'] }}">
                    </div>

                    <div class="form-field">
                        <br />
                    </div>
                </div>
                <div class="form-field-big" id="changeLeaseDatesOnly">
                    <button type="button" id="saveBursarDetails" class="profileSubmit"
                        style="width:98%; border:none; padding:12px; border-radius:10px;">Save Bursar Information</button>
                </div>
            </div>
        </div>
    </div>


    <div id="paymentModal" class="modal">
        <div class="modal-content"
            style="height:400px; max-width: 500px; background-color: #fff; border-radius: 10px; padding: 20px; margin: 10px auto; box-shadow: 0 0 10px rgba(0, 0, 0, .2); text-align: center;">
            <span class="closePaymentModal" onClick="document.getElementById('paymentModal').style.display = 'none';"
                style="float:right; font-size:25px; cursor:pointer">&times;</span>
            <div class="form-card">
                <h2>Update Booking Payment {{ $paymentBooking->id }}</h2>

                <form method="POST" action="{{ route('update.booking.payment') }}">
                    @csrf
                    <input type="hidden" name="booking_payment_id" value="{{ $paymentBooking->id }}">
                    <input type="hidden" name="userId" value="{{ $user[0]['userInfo']['userUid'] }}">

                    <div
                        style="width: auto; flex-direction: row; display: flex; justify-content: space-between; font-size: 12px; font-weight: bold; color: #666; margin-top: 10px;">
                        <span>Booking Fee R {{ $paymentBooking->booking_fee ?? 0 }}</span>
                        <span>Admin Fee R {{ $paymentBooking->loan_admin_fee ?? 0 }}</span>
                        <span>NSFAS Rate {{ $paymentBooking->nsfas_rate ? 'Yes' : 'No' }}</span>
                    </div>
                    {{-- <p>Freska</p> 
                    <p style="width: 300px">{{ $paymentBooking ?? '-----Freska------' }}</p> --}}
                    <div class="form-field">
                        <label>Monthly Rental</label>
                        <input type="number" step="0.01" name="monthly_rental"
                            value="{{ $paymentBooking->monthly_rental ?? 0 }}">
                    </div>
                    {{-- <div class="form-field">
                        <label>Total Monthly</label>
                        <input type="number" step="0.01" name="monthly_rental_total"
                            value="{{ $paymentBooking->monthly_rental_total ?? 0 }}">
                    </div> --}}
                    <div class="form-field">
                        <label>Monthly Funded</label>
                        <input type="number" step="0.01" name="funded_monthly"
                            value="{{ $paymentBooking->funded_monthly ?? 0 }}">
                    </div>
                    {{-- <div class="form-field">
                        <label>Total Funded</label>
                        <input type="number" step="0.01" name="total_funded"
                            value="{{ $paymentBooking->total_funded ?? 0 }}">
                    </div> --}}
                    {{-- <div class="form-field">
                        <label>Deposit</label>
                        <input type="number" step="0.01" name="deposit"
                            value="{{ $paymentBooking->deposit ?? 0 }}">
                    </div> --}}
                    {{-- <div class="form-field">
                        <label>Top-up Total</label>
                        <input type="number" step="0.01" name="total_top_up"
                            value="{{ $paymentBooking->total_top_up ?? 0 }}">
                    </div> --}}



                    <div class="form-field-big">
                        <button type="submit" class="profileSubmit"
                            style="width:98%; border:none; padding:12px; border-radius:10px;">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="monthlyPopModal" class="modal">
        <div class="modal-content"
            style="height:450px; max-width: 500px; background-color: #fff; border-radius: 10px; padding: 20px; margin: 10px auto; box-shadow: 0 0 10px rgba(0, 0, 0, .2); text-align: center;">
            <span class="closeMonthlyPopModal" onClick="document.getElementById('monthlyPopModal').style.display = 'none';"
                style="float:right; font-size:25px; cursor:pointer">&times;</span>
            <div class="form-card">
                <h2>Update Monthly Proof of Payment</h2>
                <p>for {{ $user[0]['userInfo']['userLastname'] }} {{ $user[0]['userInfo']['userFirstName'] }}</p>

                <form method="POST" action="{{ route('update.monthly.pop') }}">
                    @csrf
                    <input type="hidden" name="booking_payment_id" value="{{ $paymentBooking->id }}">
                    <input type="hidden" name="userId" value="{{ $user[0]['userInfo']['userUid'] }}">
                    <div style="flex : 1; display:flex; flex-direction:column; align-items:start; width:100%;">
                        <label style="display:flex; font-weight:semibold; margin-bottom:6px; text-align:left; align-items:center;">
                            Schedule
                        </label>
                        <div style="font-size:12px; color:#666; margin-top:2px; text-align:left;">
                            Send: 25th of each month.
                        </div>
                        <div style="font-size:12px; color:#666; margin-top:2px; text-align:left;">
                            Due: 1st of the following month.
                        </div>

                        <label style="display:flex; font-weight:semibold; margin-bottom:6px; text-align:left;">Monthly Amount</label>
                        <div style="width: 50%;float: left;">                            
                            <input type="number" step="0.01" name="monthly_pop_amount"
                                value="{{ $paymentBooking->monthly_pop_amount ?? $paymentBooking->monthly_rental ?? '' }}">
                        </div>
                    </div>
                    <label style="display:block; font-weight:bold; margin-bottom:6px; text-align:left;">Send Email Notification to:</label>

                    <div style="flex : 1; display:flex; flex-direction:row; align-items:start; justify-content:start; ">
                        <label style="display:flex;">
                            <input type="checkbox" name="send_to_student" value="1"
                                {{ ($paymentBooking->send_to_student ?? 1) ? 'checked' : '' }}>
                            <span style="margin-left:5px;">Student</span>
                        </label>

                        <label style="display:flex; margin-left:15px;">
                            <input type="checkbox" name="send_to_sponsor" value="1"
                                {{ empty($user[0]['userInfo']['sponEmail']) ? 'disabled' : '' }}
                                {{ !empty($paymentBooking->send_to_sponsor) ? 'checked' : '' }}>
                            <span style=" margin-left:5px;">Sponsor</span>
                        </label>
                    </div>
                    <div style="flex : 1; display:flex; flex-direction:column; align-items:start; justify-content:start; ">
                        <div style="display:block;font-size:12px; color:#666; margin-top:6px; text-align:left;">
                            Student email: {{ $user[0]['userInfo']['userEmail'] ?? 'Not set' }}
                        </div>                    
                        

                        @if (empty($user[0]['userInfo']['sponEmail']))
                            <div style="display:block;font-size:12px; color:#a33; margin-bottom:6px; text-align:left;">
                                Sponsor email not set — sponsor notifications are disabled.
                            </div>
                        @else
                            <div style="display:block;font-size:12px; color:#666; margin-bottom:6px; text-align:left;">
                                Sponsor email: {{ $user[0]['userInfo']['sponEmail'] ?? 'Not set' }}
                            </div>                    
                        @endif
                    </div>
                    <div class="form-field-big">
                        <button type="submit" class="profileSubmit"
                            style="width:98%; border:none; padding:12px; border-radius:10px;">Save Monthly POP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script defer>
        const openPaymentModalBtn = document.getElementById('openPaymentModal');
        const paymentModal = document.getElementById('paymentModal');
        const closePaymentModalBtn = document.querySelector('.closePaymentModal');
        const openMonthlyPopModalBtn = document.getElementById('openMonthlyPopModal');
        const monthlyPopModal = document.getElementById('monthlyPopModal');
        const closeMonthlyPopModalBtn = document.querySelector('.closeMonthlyPopModal');

        if (openPaymentModalBtn && paymentModal) {
            openPaymentModalBtn.onclick = function() {
                paymentModal.style.display = 'block';
            };
        }

        if (closePaymentModalBtn && paymentModal) {
            closePaymentModalBtn.onclick = function() {
                paymentModal.style.display = 'none';
            };
        }

        if (openMonthlyPopModalBtn && monthlyPopModal) {
            openMonthlyPopModalBtn.onclick = function() {
                monthlyPopModal.style.display = 'block';
            };
        }

        if (closeMonthlyPopModalBtn && monthlyPopModal) {
            closeMonthlyPopModalBtn.onclick = function() {
                monthlyPopModal.style.display = 'none';
            };
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (paymentModal && event.target == paymentModal) {
                paymentModal.style.display = 'none';
            }
            if (monthlyPopModal && event.target == monthlyPopModal) {
                monthlyPopModal.style.display = 'none';
            }
        };
    </script>
    <div class="footerClearfix"></div>
    <div class="footerClearfix"></div>
    <div class="footerClearfix"></div>
    <div class="footerClearfix"></div>
    <div class="footerClearfix"></div>
    <div class="footerClearfix"></div>

    <div class="hidden"></hidden>

        <div class="clearSpaceLogin"></div>
        <div class="clearSpaceLogin"></div>
        <div class="clearSpaceLogin"></div>
        <div class="clearSpaceLogin"></div>

        <!-- <input type="hidden" id="id_data_approved" value="{{ $user[0]['userInfo']['id_data_approved'] }}"/> -->
        <!-- <input type="hidden" id="student_card_approved" value="{{ $user[0]['userInfo']['student_card_approved'] }}"/> -->
        <!-- <input type="hidden" id="registration_approved" value="{{ $user[0]['userInfo']['registration_approved'] }}"/> -->
        <!-- <input type="hidden" id="bursary_approved" value="{{ $user[0]['userInfo']['bursary_approved'] }}"/> -->
        <!-- <input type="hidden" id="bank_approved" value="{{ $user[0]['userInfo']['bank_approved'] }}"/> -->
        <input type="hidden" id="address_approved" value="{{ $user[0]['userInfo']['address_approved'] }}" />
        <input type="hidden" id="kin_approved" value="{{ $user[0]['userInfo']['kin_approved'] }}" />
        <!-- <input type="hidden" id="parent_approved" value="{{ $user[0]['userInfo']['parent_approved'] }}"/> -->
        <!-- <input type="hidden" id="burser_approved" value="{{ $user[0]['userInfo']['burser_approved'] }}"/> -->
        <!-- <input type="hidden" id="bed_allocated" value="{{ $booking->bed_code }}"/> -->
        <input type="hidden" id="files_to_download" value="{{ $files_to_download }}" />
        <input type="hidden" id="selectedRoom" value="" />
        <input type="hidden" id="email_data_approved" value="{{ $user[0]['userInfo']['email_data_approved'] }}" />

        <input type="hidden" id="booking_fee_paid" value="{{ $booking_paid }}" />
        <input type="hidden" id="booking_fee_waived" value="{{ $booking_waived }}" />

        <input type="hidden" id="change_bed_type" value="" />




        <script defer>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            async function paymentRef() {
                const steps = ["1", "2"];
                const firstStep = swal.mixin({
                    confirmButtonText: 'Next',
                    cancelButtonText: 'Cancel',
                    progressSteps: steps,
                    title: "Please enter the Payment Reference:",
                    input: 'text',
                    inputAttributes: {
                        required: true
                    },
                    reverseButtons: true,
                    validationMessage: 'This field is required'
                })
                const secondStep = swal.mixin({
                    confirmButtonText: 'Submit',
                    cancelButtonText: 'Back',
                    progressSteps: steps,
                    title: "Please upload your Proof of Payment (image or PDF)",
                    input: 'file',
                    inputAttributes: {
                        'accept': "image/*, application/pdf",
                        required: true
                    },
                    reverseButtons: true,
                    validationMessage: 'This field is required'
                })

                const values = []
                let currentStep
                const mixins = [firstStep, secondStep]

                for (currentStep = 0; currentStep < steps.length;) {
                    const result = await mixins[currentStep].fire({
                        inputValue: values[currentStep],
                        showCancelButton: true,
                        currentProgressStep: currentStep
                    })

                    if (result.value) {
                        values[currentStep] = result.value
                        currentStep++
                    } else if (result.dismiss === swal.DismissReason.cancel) {
                        currentStep--
                    } else {
                        break
                    }
                }

                if (currentStep === steps.length) {
                    console.log("Values", values)
                    $.ajax({
                        type: "post",
                        url: "/UpdatePaymentRef",
                        data: {
                            bookingId: "{{ $booking->id }}",
                            reason: values[0]
                        }
                    })

                    let userId = "{{ $user[0]['userInfo']['userUid'] }}";

                    var form_data = new FormData();
                    form_data.append("file", values[1]);
                    form_data.append("userUid", userId);
                    form_data.append("fileType", 'proof_of_payment');
                    form_data.append("adminName", "{{ Auth::user()->userFirstName }}");

                    $.ajax({
                        type: "post",
                        url: "/move-in-form-admin",
                        data: form_data,
                        cache: false,
                        contentType: false,
                        processData: false,

                        success: function(data) {
                            if (data[0] > 0) {

                                window.location.reload();

                            } else {
                                Swal.fire(
                                    'Error',
                                    data[0],
                                    'error'
                                )
                                $("#" + field + "Section").empty().append(
                                    '<span class="text">Upload</span>  <i class="fas fa-file-upload" ></i>');
                            }
                        },
                    });
                }
            }

            // function generateNSFASQuote(paymentId, bookingId, userId) {
            //     $.ajax({
            //         type: "post",
            //         url: "/download-quote",
            //         data: {
            //             paymentId: paymentId,
            //             bookingId: bookingId,
            //             userId: userId,
            //         },

            //         success: function(data) {
            //             const url = `/download-quote?paymentId=${paymentId}&bookingId=${bookingId}&userId=${userId}`;
            //             window.open(url, "_blank");
            //         },
            //     });
            // }
            function generateNSFASQuote(paymentId, bookingId, userId) {
                const url = `/download-quote?paymentId=${paymentId}&bookingId=${bookingId}&userId=${userId}`;
                window.open(url, "_blank");
            }
            // function generateInvoice(paymentId, bookingId, userId) {
            //     $.ajax({
            //         type: "post",
            //         url: "/download-invoice",
            //         data: {
            //             paymentId: paymentId,
            //             bookingId: bookingId,
            //             userId: userId,
            //         },

            //         success: function(data) {
            //             $("#downloadBtn").click();
            //         },
            //     });
            // }
            function generateInvoice(paymentId, bookingId, userId) {
                const url = `/download-invoice?paymentId=${paymentId}&bookingId=${bookingId}&userId=${userId}`;
                window.open(url, "_blank");
            }
            function changeNsfsBursar(userId, bursar) {

                if (bursar == 1) {
                    newBursar = "No"
                } else {
                    newBursar = "Yes"
                }
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to change the NSFAS bursar to " + newBursar,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "post",
                            url: "/change-nsfsbursar",
                            data: {
                                userId: "{{ $user[0]['userInfo']['userUid'] }}",
                            },

                            success: function(data) {

                                window.location.reload();

                            },
                        });
                    }
                });
            }

            // $("#updateProfileStatus").on('click', function(){
            //     $("#updateProfileStatus").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
            //     $.ajax({
            //         type: "get",
            //         url: "/update-user-status/{{ $user[0]['userInfo']['userUid'] }}",
            //         success: function (data) {

            //             window.location.reload();

            //         },
            //     });
            // })

            $("#addComment").on('click', function() {
                $("#addComment").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                $.ajax({
                    type: "post",
                    url: "/add-comment",
                    data: {
                        userId: "{{ $user[0]['userInfo']['userUid'] }}",
                        bookingId: "{{ $booking->id }}",
                        comment: $("#newComment").val(),
                    },

                    success: function(data) {

                        // window.location.reload();
                        $("#newComment").val('')
                        $("#addComment").empty().append('Add Comment')
                        getAllComments();

                    },
                });
            });

            getAllComments();

            function getAllComments() {
                $.ajax({
                    type: "post",
                    url: "/get-comments",
                    data: {
                        userId: "{{ $user[0]['userInfo']['userUid'] }}",
                        bookingId: "{{ $booking->id }}",
                    },

                    success: function(data) {

                        // console.log(data)
                        $(".comments_block").empty()

                        if (data.length == 0) {
                            $(".comments_block").append('<p style="text-align:center">There are no comments!</p>');
                        } else {

                            Object.keys(data).forEach(function(key) {
                                var commentEmail = (data[key].users && data[key].users.email) ? data[key].users.email : "";
                                var commentRoleName = (data[key].users && data[key].users.user_role_name) ? data[key].users.user_role_name : "";

                                $(".comments_block").append(
                                    '<div class="infoBlock"><div class="responsible" style="width:100%">' +
                                    (data[key].created_at).slice(8, 10) + '/' + (data[key].created_at)
                                    .slice(5, 7) + '/' + (data[key].created_at).slice(0, 4) + ' ' + (
                                        data[key].created_at).slice(11, 16) +
                                    ' | <span class="commentMail">' + commentEmail +
                                    '</span> <span class="commentName"> | ' + commentRoleName + '</span></p></div><div class="information"><p>' +
                                    data[key].comment + '</p></div></div>')
                            });
                        }

                    },
                });
            }




            checkUserStatus();

            // MainAppSection
            function checkUserStatus() {

                // $(".waitve_btn").empty();

                // if($("#booking_fee_paid").val() == 'false'){
                //     if(("{{ Auth::user()->role_modify_approved }}" == 1 && "{{ $user[0]['userInfo']['rob_50_approve'] }}" == 2 && "{{ Auth::user()->role_waive_fee }}" == 1) || ("{{ $user[0]['userInfo']['rob_60_cancelled'] }}" == 0 &&  "{{ $user[0]['userInfo']['rob_50_approve'] }}" != 2 && "{{ Auth::user()->role_waive_fee }}" == 1)){
                //         if($("#booking_fee_waived").val() == 'false'){
                //             $(".waitve_btn").append('<button type="button" class="appBooking" id="waiveThisBookingFee" onClick="waiveBookingFee(\'{{ $user[0]['userInfo']['userUid'] }}\')">Waive Booking Fee</button>')
                //         }else{
                //             $(".waitve_btn").append('<button type="button" class="appBooking" id="unWaiveThisBookingFee" onClick="unWaiveBookingFee(\'{{ $user[0]['userInfo']['userUid'] }}\')">Undo Booking Fee Waiver </button>')
                //         }
                //     }
                // }

                // $(".waived_paid_wording").empty();




                if ($("#kin_approved").val() == "1") {
                    $("#rejectedKin").addClass('hidden')
                    $("#approvedKin").removeClass('hidden')
                } else {
                    if ("{{ $user[0]['userInfo']['kinFirstName'] }}" == "" ||
                        "{{ $user[0]['userInfo']['kinFirstName'] }}" == null) {
                        $("#rejectedKin").addClass('hidden')
                        $("#notLoadKin").removeClass('hidden')
                    } else {
                        $("#rejectedKin").removeClass('hidden')
                        $("#approvedKin").addClass('hidden')
                    }
                }

                if ($("#address_approved").val() == "1") {
                    $("#rejectedAddr").addClass('hidden')
                    $("#approvedAddr").removeClass('hidden')
                } else {
                    $("#rejectedAddr").removeClass('hidden')
                    $("#approvedAddr").addClass('hidden')
                }

                if ($("#email_data_approved").val() == "1") {
                    $("#app_det_statNo").addClass('hidden')
                    $("#app_det_statYes").removeClass('hidden')
                } else {
                    $("#app_det_statNo").removeClass('hidden')
                    $("#app_det_statYes").addClass('hidden')
                }

                //
                $.ajax({
                    type: "get",
                    url: "/check-user-payment/{{ $user[0]['userInfo']['userUid'] }}",
                    success: function(data) {
                        if (data['nsfas_bursar'] == 1) {
                            var nsfas_bursar = "Yes";
                        } else {
                            var nsfas_bursar = "No"
                        }

                        $(".burInfoSection").empty().append(data['burCompanyName'] + "<br/>" + data['burRegNo'] +
                            "<br/>" + data['burContactPerson'] + " - " + data['burContactCode'] + "" + data[
                                'burContactNo'] + "<br/>" + data['burEmail'] + "<br/>" + data['bAddress1'] +
                            ", " + data['bAddress2'] + ", <br/>" + data['bAddressCity'] + ", " + data[
                                'bAddressPostal'] + "<br/><br/> NSFAS bursar? <strong>" + nsfas_bursar +
                            "</strong>")

                        if (data['studentType'] == "Cash / Direct") {
                            var studentType = "Cash"
                        } else {
                            var studentType = data['studentType']
                        }

                        if (data['bPayment'] !== null) {

                            var bPayment = "<span style='color:red;'>" + data['bPayment'] + "</span>";

                            if (data['bPayment'] == "Via an INTERMEDIARY" && data['bint'] !== "Other") {
                                var bint = "<span style='color:red;'> | " + data['bint'];
                            } else if (data['bPayment'] == "Via an INTERMEDIARY" && data['bint'] == "Other") {
                                var bint = "<span style='color:red;'> | " + data['bIntName'];
                            } else {
                                var bint = "";
                            }
                        } else {
                            var bPayment = "";
                            var bint = "";
                        }


                        if (data['sponId'] !== null) {
                            var IDspon = data['sponId']
                        } else {
                            var IDspon = data['pPassportNo']
                        }

                        if (studentType == "Bursary") {
                            $(".burPaymentDetails").empty().append(studentType + "<br/>" + bPayment + " " + bint)
                        } else {
                            $(".burPaymentDetails").empty().append(studentType)
                        }



                        $(".sponInfoSection").empty().append(data['sponFirstName'] + " " + data['sponLastName'] +
                            "<br/>" + data['sponCellCode'] + data['sponCell'] + "<br/>" + data['sponEmail'] +
                            "<br/>" + data['pNationality'] + "<br/>" + IDspon + "<br/>" + data['pAddress1'] +
                            ", " + data['pAddress2'] + "<br/>" + data['pAddressCity'] + ", " + data[
                                'pAddressPostal'])
                    }
                });


                //Check if we show/hide the approve user section
                $.ajax({
                    type: "get",
                    url: "/check-user-status/{{ $user[0]['userInfo']['userUid'] }}",

                    success: function(data) {
                        //console.log(data)

                        $(".address_btn").empty()


                        $(".studentType_btn").empty().append(
                            '<button type="button" class="amend button btn_edit_inline" formid="inline_edit_student_type" edittype="form">Edit</button>'
                        )


                        if (data.rob_50_approve != 2 && "<?php echo $user[0]['userInfo']['rob_60_cancelled']; ?>" == 0) {
                            if ($("#address_approved").val() != 1) {
                                if ("<?php echo Auth::user()->role_non_funding; ?>" == "1") {
                                    $(".address_btn").append(
                                        '<button type="button" class="approve button btn_approve_inline" formid="inline_edit_address" approvefield="address_approved" style="height:40px;">Approve</button>'
                                    )
                                }

                                if ("<?php echo Auth::user()->role_edit_app; ?>" == "1") {
                                    $(".address_btn").append(
                                        '<button type="button" class="amend button btn_edit_inline" formid="inline_edit_address" edittype="form">Edit</button>'
                                    )
                                }
                            } else {
                                if ("<?php echo Auth::user()->role_non_funding; ?>" == 1) {
                                    $(".address_btn").append(
                                        '<button type="button" class="rejected button btn_approve_inline" formid="inline_edit_address" approvefield="address_approved" style="height:40px;">Reject</button>'
                                    )
                                }
                            }

                        } else if (data.rob_50_approve == 2) {

                            if ($("#address_approved").val() != 1) {
                                if ("<?php echo Auth::user()->role_non_funding; ?>" == "1") {

                                    if ("<?php echo Auth::user()->role_non_funding; ?>" == "1") {
                                        $(".address_btn").append(
                                            '<button type="button" class="approve button btn_approve_inline" formid="inline_edit_address" approvefield="address_approved" style="height:40px;">Approve</button>'
                                        )
                                    }

                                    if ("<?php echo Auth::user()->role_edit_app; ?>" == "1") {
                                        $(".address_btn").append(
                                            '<button type="button" class="amend button btn_edit_inline" formid="inline_edit_address" edittype="form">Edit</button>'
                                        )
                                    }
                                }
                            } else {
                                if ("<?php echo Auth::user()->role_delete_uploaded; ?>" == '1') {
                                    $(".address_btn").append(
                                        '<button type="button" class="rejected button btn_approve_inline" formid="inline_edit_address" approvefield="address_approved" style="height:40px;">Reject</button>'
                                    )
                                }
                            }
                        }

                        $(".kin_btn").empty()

                        if (data.rob_50_approve != 2 && "<?php echo $user[0]['userInfo']['rob_60_cancelled']; ?>" == 0) {
                            if ($("#kin_approved").val() != 1) {
                                if (data.kinFirstName != "" && data.kinFirstName !== null) {
                                    if ("<?php echo Auth::user()->role_non_funding; ?>" == "1") {
                                        $(".kin_btn").append(
                                            '<button type="button" class="approve button btn_approve_inline" formid="inline_edit_kin" approvefield="kin_approved" style="height:40px;">Approve</button>'
                                        )
                                    }
                                }

                                if ("<?php echo Auth::user()->role_edit_app; ?>" == "1") {
                                    $(".kin_btn").append(
                                        '<button type="button" class="amend button btn_edit_inline" formid="inline_edit_kin" edittype="form">Edit</button>'
                                    )
                                }
                            } else {
                                if ("<?php echo Auth::user()->role_non_funding; ?>" == 1) {
                                    $(".kin_btn").append(
                                        '<button type="button" class="rejected button btn_approve_inline" formid="inline_edit_kin" approvefield="kin_approved" style="height:40px;">Reject</button>'
                                    )
                                }
                            }
                        } else if (data.rob_50_approve == 2) {
                            if ($("#kin_approved").val() != 1) {
                                if ("<?php echo Auth::user()->role_non_funding; ?>" == "1") {
                                    $(".kin_btn").append(
                                        '<button type="button" class="approve button btn_approve_inline" formid="inline_edit_kin" approvefield="kin_approved" style="height:40px;">Approve</button>'
                                    )
                                }

                                if ("<?php echo Auth::user()->role_edit_app; ?>" == "1") {
                                    $(".kin_btn").append(
                                        '<button type="button" class="amend button btn_edit_inline" formid="inline_edit_kin" edittype="form">Edit</button>'
                                    )
                                }
                            } else {
                                if ("<?php echo Auth::user()->role_delete_uploaded; ?>" == 1) {
                                    $(".kin_btn").append(
                                        '<button type="button" class="rejected button btn_approve_inline" formid="inline_edit_kin" approvefield="kin_approved" style="height:40px;">Reject</button>'
                                    )
                                }
                            }
                        }

                        $(".app_details_btn").empty()

                        if (data.rob_50_approve != 2 && "<?php echo $user[0]['userInfo']['rob_60_cancelled']; ?>" == 0) {
                            if ($("#email_data_approved").val() != 1) {
                                if ("<?php echo Auth::user()->role_non_funding; ?>" == "1") {
                                    $(".app_details_btn").append(
                                        '<button type="button" class="approve button btn_approve_inline" formid="inline_app_details" approvefield="email_data_approved" style="height:40px;">Approve</button>'
                                    )
                                }

                                if ("<?php echo Auth::user()->role_edit_app; ?>" == "1") {
                                    $(".app_details_btn").append(
                                        '<button type="button" class="amend button btn_edit_inline" formid="inline_app_details" edittype="form">Edit</button>'
                                    )
                                }
                            } else {
                                if ("<?php echo Auth::user()->role_non_funding; ?>" == 1) {
                                    $(".app_details_btn").append(
                                        '<button type="button" class="rejected button btn_approve_inline" formid="inline_app_details" approvefield="email_data_approved" style="height:40px;">Reject</button>'
                                    )
                                }
                            }
                        } else if (data.rob_50_approve == 2) {
                            if ($("#email_data_approved").val() != 1) {
                                if ("<?php echo Auth::user()->role_non_funding; ?>" == "1") {
                                    $(".app_details_btn").append(
                                        '<button type="button" class="approve button btn_approve_inline" formid="inline_app_details" approvefield="email_data_approved" style="height:40px;">Approve</button>'
                                    )
                                }

                                if ("<?php echo Auth::user()->role_edit_app; ?>" == "1") {
                                    $(".app_details_btn").append(
                                        '<button type="button" class="amend button btn_edit_inline" formid="inline_app_details" edittype="form">Edit</button>'
                                    )
                                }
                            } else {
                                if ("<?php echo Auth::user()->role_delete_uploaded; ?>" == 1) {
                                    $(".app_details_btn").append(
                                        '<button type="button" class="rejected button btn_approve_inline" formid="inline_app_details" approvefield="email_data_approved" style="height:40px;">Reject</button>'
                                    )
                                }
                            }
                        }

                        if (data['parent_approved'] == 1) {
                            $("#rejectedSpon").addClass('hidden')
                            $("#approvedSpon").removeClass('hidden')
                            $("#notLoadSpon").addClass('hidden')
                        } else {
                            if (data['sponFirstName'] == '' || data['sponFirstName'] == null) {
                                $("#rejectedSpon").addClass('hidden')
                                $("#approvedSpon").addClass('hidden')
                                $("#notLoadSpon").removeClass('hidden')
                            } else {
                                $("#rejectedSpon").removeClass('hidden')
                                $("#approvedSpon").addClass('hidden')
                                $("#notLoadSpon").addClass('hidden')
                            }
                        }

                        if (data['burser_approved'] == 1) {
                            $("#rejectedBur").addClass('hidden')
                            $("#approvedBur").removeClass('hidden')
                            $("#notLoadBur").addClass('hidden')
                        } else {

                            if (data['burCompanyName'] == "" || data['burCompanyName'] == null) {
                                $("#notLoadBur").removeClass('hidden')
                                $("#approvedBur").addClass('hidden')
                                $("#rejectedBur").addClass('hidden')
                            } else {
                                $("#rejectedBur").removeClass('hidden')
                                $("#approvedBur").addClass('hidden')
                                $("#notLoadBur").addClass('hidden')
                            }
                        }

                        if (data['rob_40_bed'] == 2) {
                            $(".bed_code_status").empty().append(
                                '<span id="bedAllocatedYes" style="margin-top:10px;">&#128994;</span>')
                        } else {
                            $(".bed_code_status").empty().append(
                                '<span id="bedAllocatedNo" style="margin-top:10px;">&#128993;</span>')
                        }

                        $(".appSection").empty()

                        if ((data['rob_50_approve'] == 0 || data['rob_50_approve'] == 1) && 
                            (data['rob_60_cancelled'] == 0 || data['rob_60_cancelled'] == null)) {
                            $(".appSection").append('<div id="MainAppSection"><div class="main_btn"></div></div>')

                            if ("<?php echo Auth::user()->role_app_lease; ?>" == 1 &&
                                data['rob_10_fee'] == 2 &&
                                data['rob_15_id'] == 2 &&
                                data['rob_20_card'] == 2 &&
                                data['rob_25_reg'] == 2 &&
                                data['rob_30_funds'] == 2 &&
                                data['rob_35_info'] == 2 &&
                                data['rob_40_bed'] == 2 &&
                                (data['rob_45_other'] == 3 || data['rob_45_other'] == 2)) {
                                $(".main_btn").empty().append(
                                    '<button type="button" class="appBtn" id="approveProfile" onClick="approveApplication(\'<?php echo $user[0]['userInfo']['userUid']; ?>\')">APPROVE APPLICATION AND SEND CONFIRMATION</button><br/><br/><br/><button type="button" class="appBtn" id="rejectApplication" onClick="rejectApplication(\'<?php echo $user[0]['userInfo']['userUid']; ?>\')">REJECT APPLICATION AND SEND NOTIFICATION</button><br/><br/><br/>'
                                )
                                $("#sendMailReminder").addClass('hidden');
                            }

                        }
                        console.log(data['generate_temp_lease'])
                        if (
                            data['generate_temp_lease'] &&
                            data['rob_51_temp_lease_approve'] == 3 &&
                            data['role_allow_generate_temp_lease'] == 1
                        ) {

                            if ($('#MainAppSection').length === 0) {

                                $(".appSection").append(
                                    '<div id="MainAppSection"><div class="main_btn"></div></div>'
                                );
                            }
                            if ($('#approveTemp').length === 0) {
                                $(".main_btn").append(
                                    '<button type="button" class="appBtn" id="approveTemp" ' +
                                    'onClick="approveTempLease(\'<?php echo $user[0]['userInfo']['userUid']; ?>\')">' +
                                    'GENERATE TEMP LEASE AND SEND FOR SIGNATURE</button><br/><br/><br/>'
                                );
                            }

                        }  
                                                       
                        

                        if (data['rob_10_fee'] == 2 &&
                            data['rob_15_id'] == 2 &&
                            data['rob_20_card'] == 2 &&
                            data['rob_25_reg'] == 2 &&
                            data['rob_30_funds'] == 2 &&
                            data['rob_35_info'] == 2 &&
                            data['rob_40_bed'] == 2 &&
                            (data['rob_45_other'] == 3 || data['rob_45_other'] == 2)) {
                            $("#sendMailReminder").addClass('hidden');
                        }

                        if (data['rob_60_cancelled'] == 1) {
                            if (data['rob_50_approve'] == 3) {
                                $(".appStatusText").empty().append(": Rejected")
                            } else {
                                $(".appStatusText").empty().append(": Cancelled")
                            }
                        } else {
                            if (data['rob_50_approve'] == 2) {
                                $(".appStatusText").empty().append(": Approved")
                            } else if (data['rob_50_approve'] == 3) {
                                $(".appStatusText").empty().append(": Rejected")
                            }
                        }

                        // appStatusText
                    }
                });

                //         if("{{ $user[0]['userInfo']['studentType'] }}" == "Cash / Direct"){

                //             if($("#id_data_approved").val() == "2" && $("#student_card_approved").val() == "2" && $("#registration_approved").val() == "2" && $("#bank_approved").val() == "2" && $("#parent_approved").val() == "2" && data.bookingStatus === "Approved" && data.applicationStatus === "Approved" && data.overall_status === "Pending" && $("#bed_allocated").val() !== ""){
                //                 $("#MainAppSection").removeClass('hidden')
                //                 // document.getElementById("col1").style.marginTop = "-35px";
                //                 // document.getElementById("col2").style.marginTop = "-35px";
                //                 // document.getElementById("col3").style.marginTop = "-35px";
                //             } else {
                //                 $("#MainAppSection").addClass('hidden')
                //             }

                //         }else if("{{ $user[0]['userInfo']['studentType'] }}" == "NSFAS"){

                //             if($("#id_data_approved").val() == "2" && $("#student_card_approved").val() == "2" && $("#registration_approved").val() == "2" && data.bookingStatus === "Approved" && data.applicationStatus === "Approved" && data.overall_status === "Pending" && $("#bed_allocated").val() !== ""){
                //                 $("#MainAppSection").removeClass('hidden')
                //                 // document.getElementById("col1").style.marginTop = "-35px";
                //                 // document.getElementById("col2").style.marginTop = "-35px";
                //                 // document.getElementById("col3").style.marginTop = "-35px";
                //             } else {
                //                 $("#MainAppSection").addClass('hidden')
                //             }
                //         }else if("{{ $user[0]['userInfo']['studentType'] }}" == "Bursary"){

                //             if($("#id_data_approved").val() == "2" && $("#student_card_approved").val() == "2" && $("#registration_approved").val() == "2" && $("#bursary_approved").val() == "2" && $("#burser_approved").val() == "2" && data.bookingStatus === "Approved" && data.applicationStatus === "Approved" && data.overall_status === "Pending" && $("#bed_allocated").val() !== ""){
                //                 $("#MainAppSection").removeClass('hidden')
                //                 // document.getElementById("col1").style.marginTop = "-35px";
                //                 // document.getElementById("col2").style.marginTop = "-35px";
                //                 // document.getElementById("col3").style.marginTop = "-35px";
                //             } else {
                //                 $("#MainAppSection").addClass('hidden')
                //             }
                //         }
                //     }
                // })
            }

            function changeBuilding(bookingId, userid) {
                var modal = document.getElementById("changeBuildingModal");
                modal.style.display = "block";

                $.ajax({
                    type: "get",
                    url: "/check-all-rooms/{{ $user[0]['userInfo']['userUid'] }}",
                    success: function(data) {
                        let properties = data[6];

                        // console.log(data)

                        let selProp = data[2].property;

                        let inSelectedCash = data[7];
                        let inSelectedBursary = data[8];
                        let inSelectedNsfas = data[9];

                        properties.sort();
                        $('#propertyBuilding').empty()
                        $('#propertyBuilding').append(
                            '<option value="" selected disabled>Select a Residence</option>')

                        let propArr = <?php echo json_encode($allowedResidences); ?>;
                        let propNsfasAllow = data[5];

                        Object.keys(properties).forEach(function(key) {
                            if (parseFloat("{{ $booking->property }}") !== properties[key].id) {
                                // STUDENT TYPE
                                if ("{{ $user[0]['userInfo']['rob_60_cancelled'] }}" == '1') {
                                    if (propArr.includes(properties[key].id)) {

                                        if (data[4].studentType == "Cash / Direct" && inSelectedCash
                                            .includes(properties[key].id)) {
                                            $("#propertyBuilding").append('<option value="' + properties[key].id + '">' + properties[key].residence + '</option>')
                                        } else if (data[4].studentType == "Bursary" && inSelectedBursary
                                            .includes(properties[key].id)) {
                                            $("#propertyBuilding").append('<option value="' + properties[key].id + '">' + properties[key].residence +'</option>')
                                        } else if ((data[4].studentType == "NSFAS" || data[4].studentType == "NRF" || data[4].studentType == "FUNZA" || data[4].studentType == "GCRA" || data[4].studentType == "MQA") && inSelectedNsfas.includes(properties[key].id)) {
                                            $("#propertyBuilding").append('<option value="' + properties[key].id + '">' + properties[key].residence +'</option>')
                                        } else {
                                            $('#propertyBuilding').append('<option value="' + properties[
                                                    key].id + '" disabled>' + properties[key]
                                                .residence + ' (Not Allowed)</option>');
                                        }

                                    }
                                } else {

                                    if (data[4].studentType == "Cash / Direct" && inSelectedCash.includes(
                                            properties[key].id)) {
                                        $("#propertyBuilding").append('<option value="' + properties[key]
                                            .id + '">' + properties[key].residence + '</option>')
                                    } else if (data[4].studentType == "Bursary" && inSelectedBursary
                                        .includes(properties[key].id)) {
                                        $("#propertyBuilding").append('<option value="' + properties[key]
                                            .id + '">' + properties[key].residence + '</option>')
                                    } else if ((data[4].studentType == "NSFAS" || data[4].studentType == "NRF" || data[4].studentType == "FUNZA" || data[4].studentType == "GCRA" || data[4].studentType == "MQA") && inSelectedNsfas.includes(
                                            properties[key].id)) {
                                        $("#propertyBuilding").append('<option value="' + properties[key]
                                            .id + '">' + properties[key].residence + '</option>')
                                    } else {
                                        $('#propertyBuilding').append('<option value="' + properties[key]
                                            .id + '" disabled>' + properties[key].residence +
                                            ' (Not Allowed)</option>');
                                    }


                                    // if(data[4].studentType == "NSFAS" && propNsfasAllow.includes(properties[key].id)){
                                    //     $('#propertyBuilding').append('<option value="'+properties[key].id+'">'+properties[key].residence+'</option>')
                                    // }else if(data[4].studentType != "NSFAS"){
                                    //     $('#propertyBuilding').append('<option value="'+properties[key].id+'">'+properties[key].residence+'</option>')
                                    // }else{
                                    //     $('#propertyBuilding').append('<option value="'+properties[key].id+'" disabled>'+properties[key].residence+' (Not Allowed)</option>')
                                    // }
                                }

                                // $('#propertyBuilding').append('<option value="'+properties[key].id+'">'+properties[key].residence+'</option>')
                            }
                        });

                        // $('#propertyBuilding').val(selProp);

                        // $("#propertyBuilding").change();
                    }
                });

                // callRoomDetails('floor','', '', '', '', '', 'open')
            }

            function changeBooking(bookingId, type) {

                // console.log(bookingId)
                // console.log(type)

                if (type === "generate" && $('#moveInStatus').length) {
                    // console.log('regen lease');
                    $("#change_bed_type").val('cancel')
                }

                $("#bookingId").val(bookingId)
                var modal = document.getElementById("myModalEditApp");
                modal.style.display = "block";

                $.ajax({
                    type: "get",
                    url: "/check-rooms/{{ $user[0]['userInfo']['userUid'] }}",
                    success: function(data) {
                        let properties = data[0];

                        // console.log(data)

                        let selProp = data[2].property;

                        let inSelectedCash = data[5];
                        let inSelectedBursary = data[6];
                        let inSelectedNsfas = data[7];

                        properties.sort();
                        $('#property').empty()

                        Object.keys(properties).forEach(function(key) {

                            if ((data[4].studentType == "NSFAS" || data[4].studentType == "NRF" || data[4].studentType == "FUNZA" || data[4].studentType == "GCRA" || data[4].studentType == "MQA" ) && inSelectedNsfas.includes(properties[key]
                                    .id)) {
                                $('#property').append('<option value="' + properties[key].id + '">' +
                                    properties[key].residence + '</option>')
                            } else if (data[4].studentType == "Bursary" && inSelectedBursary.includes(
                                    properties[key].id)) {
                                $('#property').append('<option value="' + properties[key].id + '">' +
                                    properties[key].residence + '</option>')
                            } else if (data[4].studentType == "Cash / Direct" && inSelectedCash.includes(
                                    properties[key].id)) {
                                $('#property').append('<option value="' + properties[key].id + '">' +
                                    properties[key].residence + '</option>')
                            } else {
                                $('#property').append('<option value="' + properties[key].id +
                                    '" disabled>' + properties[key].residence +
                                    ' (Not Allowed)</option>')
                            }
                            // if(data[4].studentType == "NSFAS" && propNsfasAllow.includes(properties[key].id)){
                            //     $('#property').append('<option value="'+properties[key].id+'">'+properties[key].residence+'</option>')
                            // }else if(data[4].studentType != "NSFAS"){
                            //     $('#property').append('<option value="'+properties[key].id+'">'+properties[key].residence+'</option>')
                            // }else{
                            //     $('#property').append('<option value="'+properties[key].id+'" disabled>'+properties[key].residence+' (Not Allowed)</option>')
                            // }
                        });

                        $('#property').val(selProp);

                        $("#property").change();
                    }
                });

                callRoomDetails('floor', '', '', '', '', '', 'open')

            }

            function changeLease(bookingId, type) {

                $("#bookingId2").val(bookingId)
                var modal = document.getElementById("myModalEditLease");
                modal.style.display = "block";

            }

            function cancelLease(bookingId, type) {

                @if ($user[0]['userInfo']['rob_55_lease'] == 2)
                    let msg = "The current Approved Lease will be rejected.";
                @else
                    let msg = "The current Lease will be regenerated.";
                @endif

                // Swal.fire({
                //     title: 'Are you sure?',
                //     text: "The current Approved Lease will be rejected.",
                //     icon: 'warning',
                //     showCancelButton: true,
                //     confirmButtonColor: '#3085d6',
                //     cancelButtonColor: '#d33',
                //     confirmButtonText: 'Yes!'
                // }).then((result) => {
                //     if (result.isConfirmed) {

                //         $.ajax({
                //             type: "post",
                //             url: "/cancel-lease",
                //             data: {
                //                 userId: "{{ $user[0]['userInfo']['userUid'] }}",
                //             },
                //         });

                $("#change_bed_type").val('cancel')
                changeBooking(bookingId, type);

                //     }
                // });
            }

            $("#generateLease").on('click', function() {

                if ($("#property").val() == "" || $("#roomType").val() == "" || $("#bed_code").val() == null || $(
                        "#lease_from_date").val() == "" || $("#lease_from_time").val() == "" || $("#lease_to_date")
                    .val() == "" || $("#lease_to_time").val() == "") {
                    if ($("#property").val() == "") {
                        $("#property").addClass("fieldError")
                        $("#property_error").removeClass("hidden")
                    }

                    if ($("#roomType").val() == "") {
                        $("#roomType").addClass("fieldError")
                        $("#roomType_error").removeClass("hidden")
                    }

                    if ($("#bed_code").val() == null) {
                        $("#bed_code").addClass('fieldError')
                        $("#bed_code_error").removeClass('hidden')
                    }

                    if ($("#lease_from_date").val() == "") {
                        $("#lease_from_date").addClass("fieldError")
                        $("#lease_from_date_error").removeClass("hidden")
                    }
                    if ($("#lease_from_time").val() == "") {
                        $("#lease_from_time").addClass("fieldError")
                        $("#lease_from_time_error").removeClass("hidden")
                    }
                    if ($("#lease_to_date").val() == "") {
                        $("#lease_to_date").addClass("fieldError")
                        $("#lease_to_date_error").removeClass("hidden")
                    }
                    if ($("#lease_to_time").val() == "") {
                        $("#lease_to_time").addClass("fieldError")
                        $("#lease_to_time_error").removeClass("hidden")
                    }
                } else {

                    $("#property").removeClass("fieldError")
                    $("#property_error").addClass("hidden")
                    $("#roomType").removeClass("fieldError")
                    $("#roomType_error").addClass("hidden")
                    $("#bed_code").removeClass('fieldError')
                    $("#bed_code_error").addClass('hidden')
                    $("#lease_from_date").removeClass("fieldError")
                    $("#lease_from_date_error").addClass("hidden")
                    $("#lease_from_time").removeClass("fieldError")
                    $("#lease_from_time_error").addClass("hidden")
                    $("#lease_to_date").removeClass("fieldError")
                    $("#lease_to_date_error").addClass("hidden")
                    $("#lease_to_time").removeClass("fieldError")
                    $("#lease_to_time_error").addClass("hidden")

                    $("#generateLease").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');


                    $.ajax({
                        type: "post",
                        url: "/createLease/" + $("#bookingId").val(),
                        data: {
                            bookingId: $("#bookingId").val(),
                            property: $("#property").val(),
                            roomType: $("#roomType").val(),
                            floor: $("#floor").val(),
                            unit: $("#unit").val(),
                            room: $("#room").val(),
                            bed: $("#bed").val(),
                            bed_code: $("#bed_code").val(),
                            lease_from_date: $("#lease_from_date").val(),
                            lease_from_time: $("#lease_from_time").val(),
                            lease_to_date: $("#lease_to_date").val(),
                            lease_to_time: $("#lease_to_time").val(),
                        },

                        success: function(data) {

                            window.location.reload();
                        },
                        error: function(xhr) {
                            showLeaseError(xhr, 'Lease generation blocked', 'Generate Lease', $("#generateLease"));
                        },
                    });

                }
            })

            function callRoomDetails(callId) {

                if (callId == "floor") {
                    if ($("#property").val() !== "" && $("#roomType").val() !== "") {
                        $('#floor').html('<option>Loading</option>');
                        $('#unit').html('<option>Choose a Unit</option>');
                        $('#room').html('<option>All Rooms</option>');
                        $('#bed_code').html('<option>Choose a Bed</option>');

                        $('#room').val('');
                        $('#bed').val('');

                        $.ajax({
                            type: "post",
                            url: "/get-booking-info",
                            data: {
                                bookingId: $("#bookingId").val(),
                                property: $("#property").val(),
                                roomType: $("#roomType").val(),
                                getType: callId
                            },

                            success: function(data) {
                                let roomData = data[2];

                                $('#floor').html('<option>Choose a Floor</option>');

                                // console.log("roomData")
                                // console.log(roomData)

                                Object.keys(roomData).forEach(function(key) {

                                    let floor_name = "";
                                    let spacer = "";

                                    if (roomData[key].meta_info_1 !== "" && roomData[key].meta_info_1 !==
                                        null) {
                                        floor_name = roomData[key].meta_info_1;
                                        spacer = " | ";
                                    }

                                    /*if (roomData[key].meta_info_2 !== "" && roomData[key].meta_info_2 !== null) {
                                        floor_name += spacer + roomData[key].meta_info_2;
                                        spacer = " - ";
                                    }*/

                                    floor_name += spacer + roomData[key].floor;

                                    selected = "";
                                    /*if (data[1].floor == roomData[key].floor) {
                                        selected = "selected='selected'";
                                    }*/

                                    // console.log(floor_name)

                                    let meta = roomData[key].meta_info_1 !== null ? roomData[key]
                                        .meta_info_1 : "";

                                    $('#floor').append("<option " + selected + " value='" + roomData[key]
                                        .floor + "' meta='" + meta + "'>" + floor_name + "</option>")
                                });
                            }
                        });
                    }

                } else if (callId == "unit") {
                    $('#unit').html('<option>Loading</option>');
                    $('#bed_code').html('<option>Choose a Bed</option>');

                    $('#room').val('');
                    $('#bed').val('');

                    $.ajax({
                        type: "post",
                        url: "/get-booking-info",
                        data: {
                            bookingId: $("#bookingId").val(),
                            property: $("#property").val(),
                            roomType: $("#roomType").val(),
                            floor: $('#floor').val(),
                            meta: $('#floor option:selected').attr('meta'),
                            getType: callId
                        },

                        success: function(data) {
                            let roomData = data[2];

                            $('#unit').html('<option>Choose a Unit</option>');

                            Object.keys(roomData).forEach(function(key) {
                                $('#unit').append("<option value='" + roomData[key].unit + "'>" + roomData[
                                    key].unit + "</option>")
                            });
                        }
                    });

                } else if (callId == "bed_code") {
                    $('#bed_code').html('<option>Loading</option>');

                    $('#room').val('');
                    $('#bed').val('');

                    $.ajax({
                        type: "post",
                        url: "/get-booking-info",
                        data: {
                            bookingId: $("#bookingId").val(),
                            property: $("#property").val(),
                            roomType: $("#roomType").val(),
                            floor: $('#floor').val(),
                            unit: $('#unit').val(),
                            getType: callId
                        },

                        success: function(data) {
                            let roomData = data[2];
                            let bookedRoomData = data[3];

                            let RoomSelected = $("#selectedRoom").val()

                            $('#bed_code').html('<option>Choose a Bed</option>');
                            $('#room').empty()
                            $('#room').html('<option value="all">All Rooms</option>');

                            let roomCodes = [];



                            Object.keys(roomData).forEach(function(key) {

                                if (!roomCodes.includes(roomData[key].room)) {
                                    roomCodes.push(roomData[key].room)
                                }

                                if (roomData[key].room == RoomSelected) {
                                    $('#bed_code').append("<option value='" + roomData[key].bed_code +
                                        "' room='" + roomData[key].room + "' bed='" + roomData[key]
                                        .bed + "'>" + roomData[key].bed_code + "</option>")
                                } else if (RoomSelected == "all" || RoomSelected == "" || RoomSelected ==
                                    "All Rooms") {
                                    $('#bed_code').append("<option value='" + roomData[key].bed_code +
                                        "' room='" + roomData[key].room + "' bed='" + roomData[key]
                                        .bed + "'>" + roomData[key].bed_code + "</option>")
                                }
                            });

                            Object.keys(roomCodes).forEach(function(key) {
                                // console.log(roomCodes[key])
                                if (RoomSelected == roomCodes[key]) {
                                    $('#room').append("<option value='" + roomCodes[key] + "' selected>" +
                                        roomCodes[key] + "</option>")
                                } else {
                                    if (roomCodes[key] !== null) {
                                        $('#room').append("<option value='" + roomCodes[key] + "'>" +
                                            roomCodes[key] + "</option>")
                                    }
                                }
                            })



                            Object.keys(bookedRoomData).forEach(function(key) {
                                $('#bed_code').append("<option disabled value='" + bookedRoomData[key]
                                    .bed_code + "' room='" + bookedRoomData[key].room + "' bed='" +
                                    bookedRoomData[key].bed + "'>" + bookedRoomData[key].bed_code +
                                    " (Booked)</option>")
                            });
                        }
                    });
                }

                /*
                if (floor === "") {
                    $("#floor").html('<option>Loading...</option>');
                    $("#unit").html('<option>Loading...</option>');
                    $("#room").html('<option>Loading...</option>');
                    $("#bed").html('<option>Loading...</option>');
                    $("#bed_code").html('<option>Loading...</option>');
                } else if (unit === "") {
                    $("#unit").html('<option>Loading...</option>');
                    $("#room").html('<option>Loading...</option>');
                    $("#bed").html('<option>Loading...</option>');
                    $("#bed_code").html('<option>Loading...</option>');
                } else if (room === "") {
                    $("#room").html('<option>Loading...</option>');
                    $("#bed").html('<option>Loading...</option>');
                    $("#bed_code").html('<option>Loading...</option>');
                } else if (bed === "") {
                    $("#bed").html('<option>Loading...</option>');
                    $("#bed_code").html('<option>Loading...</option>');
                } else if (bed_code === "") {
                    $("#bed_code").html('<option>Loading...</option>');
                }

                $.ajax({
                    type: "post",
                    url: "/get-booking-info",
                    data: {
                        bookingId: $("#bookingId").val(), 
                        property: $("#property").val(),
                        roomType: $("#roomType").val(), 
                        floor:floor,
                        unit:unit,
                        room:room,
                        bed:bed,
                        bed_code:bed_code,
                    },

                    success: function (data) {
                        
                        //No Room data been selected, just Property & room Type
                        roomData = data[2]; 
                        type = []

                        console.log(callId, floor, unit, room, bed, bed_code, action);

                        console.log(roomData);
                        
                        roomData.forEach(function (arrayItem) {
                            let new_key = arrayItem[''+callId+''];
                            let meta1 = "";
                            let meta2 = "";
                            
                            if (arrayItem['meta_info_1']) {
                                meta1 = arrayItem['meta_info_1'] + " - ";
                            }
                            
                            if (arrayItem['meta_info_2']) {
                                meta2 = arrayItem['meta_info_2'] + " - ";
                            }

                            //type.push(meta1 + new_key);
                            type.push(new_key);
                        })
                        

                        var unique = [...new Set(type)];
                        $("#"+callId).empty();

                        if(unit == ""){
                            $("#unit").empty();
                        }
                        if(room == ""){
                            $("#room").empty();
                        }
                        if(bed == ""){
                            $("#bed").empty();
                        }
                        if(bed_code == ""){
                            $("#bed_code").empty();
                        }

                        console.log(unique)
                        $("#"+callId).append('<option value="">Select One</option>');

                        unique.forEach(function (arrayItem) {
                            if(action == "open" && data[1][callId] == arrayItem){
                                value = '<option value="'+arrayItem+'" selected>'+arrayItem+'</option>';
                            }else{
                                value = '<option value="'+arrayItem+'">'+arrayItem+'</option>';
                            }
                            
                            $("#"+callId).append(value);
                        })

                        //If room selected & open

                        if(action == "open"){
                            units = [];
                            rooms = [];
                            beds = [];
                            bed_codes = [];

                            roomData.forEach(function (arrayItem) {
                                if(arrayItem['floor'] == data[1]['floor']){
                                    units.push(arrayItem['unit'])
                                }
                                if(arrayItem['floor'] == data[1]['floor'] && arrayItem['unit'] == data[1]['unit']){
                                    rooms.push(arrayItem['room'])
                                }
                                if(arrayItem['floor'] == data[1]['floor'] && arrayItem['unit'] == data[1]['unit'] && arrayItem['room'] == data[1]['room']){
                                    beds.push(arrayItem['bed'])
                                }
                                if(arrayItem['floor'] == data[1]['floor'] && arrayItem['unit'] == data[1]['unit'] && arrayItem['room'] == data[1]['room'] && arrayItem['bed'] == data[1]['bed']){
                                    bed_codes.push(arrayItem['bed_code'])
                                }
                            })

                            var uniqueUnits = [...new Set(units)];
                            var uniqueRooms = [...new Set(rooms)];
                            var uniqueBeds = [...new Set(beds)];
                            var uniqueBedCodes = [...new Set(bed_codes)];

                            uniqueUnits.forEach(function (arrayItem) {
                                if(data[1]['unit'] == arrayItem){
                                    value = '<option value="'+arrayItem+'" selected>'+arrayItem+'</option>';
                                }else{
                                    value = '<option value="'+arrayItem+'">'+arrayItem+'</option>';
                                }
                                $("#unit").append(value);
                            })
                            uniqueRooms.forEach(function (arrayItem) {
                                if(data[1]['room'] == arrayItem){
                                    value = '<option value="'+arrayItem+'" selected>'+arrayItem+'</option>';
                                }else{
                                    value = '<option value="'+arrayItem+'">'+arrayItem+'</option>';
                                }
                                $("#room").append(value);
                            })
                            uniqueBeds.forEach(function (arrayItem) {
                                if(data[1]['bed'] == arrayItem){
                                    value = '<option value="'+arrayItem+'" selected>'+arrayItem+'</option>';
                                }else{
                                    value = '<option value="'+arrayItem+'">'+arrayItem+'</option>';
                                }
                                $("#bed").append(value);
                            })
                            uniqueBedCodes.forEach(function (arrayItem) {
                                if(data[1]['bed_code'] == arrayItem){
                                    value = '<option value="'+arrayItem+'" selected>'+arrayItem+'</option>';
                                }else{
                                    value = '<option value="'+arrayItem+'">'+arrayItem+'</option>';
                                }
                                $("#bed_code").append(value);
                            })
                        }
                    },
                });
                */
            }

            $("#floor").on('change', function() {
                callRoomDetails('unit');
            });

            $("#unit").on('change', function() {
                callRoomDetails('bed_code');
                $("#selectedRoom").val('all')
            });

            $("#room").on('change', function() {
                $("#selectedRoom").val($("#room").val())
                callRoomDetails('bed_code');
            });

            $('#bed_code').on('change', function() {
                if ($('#bed_code option:selected').attr('room') !== "null") {
                    $('#room').val($('#bed_code option:selected').attr('room'));
                }

                if ($('#bed_code option:selected').attr('bed') !== "null") {
                    $('#bed').val($('#bed_code option:selected').attr('bed'));
                }
            });

            /*
            $("#room").on('change', function(){
                callRoomDetails('bed', $("#floor").val(), $("#unit").val(), $("#room").val(), '', '', '')
            })

            $("#bed").on('change', function(){
                callRoomDetails('bed_code', $("#floor").val(), $("#unit").val(), $("#room").val(), $("#bed").val(), '', '')
            })
            */





            var modalBooking = document.getElementById("myModalEditApp");
            var spanBooking = document.getElementsByClassName("closeBooking")[0];

            var modalBuilding = document.getElementById("changeBuildingModal");
            var closeBuildingButtons = document.getElementsByClassName("closeBuilding");

            spanBooking.onclick = function() {
                modalBooking.style.display = "none";
            }

            if (modalBuilding && closeBuildingButtons.length) {
                Array.from(closeBuildingButtons).forEach(function(btn) {
                    btn.onclick = function() {
                        modalBuilding.style.display = "none";
                    }
                });
            }

            var modalLease = document.getElementById("myModalEditLease");
            var modalBursarInfo = document.getElementById("myModalBursarInformation");
            var modalSponInfo = document.getElementById("myModalSposorInformation");
            var spanLease = document.getElementsByClassName("closeLease")[0];
            // var spanBurInfo = document.getElementsByClassName("closeBursarinfo")[0];
            // var spanSponInfo = document.getElementsByClassName("closeSponsorinfo")[0];







            spanLease.onclick = function() {
                modalLease.style.display = "none";
            }

            // spanBurInfo.onclick = function() {
            //     modalBursarInfo.style.display = "none";
            // }
            // spanSponInfo.onclick = function() {
            //     modalSponInfo.style.display = "none";
            // }

            bookingTypeDetails();

            function bookingTypeDetails() {
                $.ajax({
                    type: "post",
                    url: "/get-booking-details",
                    data: {
                        userId: "{{ $user[0]['userInfo']['userUid'] }}",
                    },

                    success: function(data) {

                        var metaInfo1 = "";
                        var floor = "";
                        var unit = "";
                        if (data[1]?.meta_info_1 && data[1].meta_info_1 !== "" && data[1].meta_info_1 !== null) {
                            metaInfo1 = data[1].meta_info_1 + ", ";
                        }
                        if (data[0]?.floor !== "" && data[0].floor !== null) {
                            floor = data[0].floor + " Floor";
                        }
                        if (data[0]?.unit !== "" && data[0].unit !== null) {
                            unit = ", Unit " + data[0].unit;
                        }

                        var bed_code = "";
                        var unallocated_bed = "";
                        if (data[0].bed_code !== "" && data[0].bed_code !== null) {
                            bed_code = "Bed Code: " + data[0].bed_code;

                            var hasResidenceAccess = "<?php echo in_array($booking->property, $allowedResidences); ?>" == "1";
                            var isSysAdmin = "<?php echo Auth::user()->role_sys_admin; ?>" == "1";
                            var canChangeBed = "<?php echo Auth::user()->role_change_bed; ?>" == "1";
                            var leaseRob = data[5] && typeof data[5].rob_55_lease !== "undefined" ? parseInt(data[5].rob_55_lease, 10) : null;

                            if (hasResidenceAccess || isSysAdmin) {
                                // Normal staff: allow only when lease isn't approved. Sys admin can override.
                                if ((canChangeBed && leaseRob !== 2) || isSysAdmin) {
                                    var unallocated_bed = '<button data-lease="' + data[2] +
                                        '" data-lease-status="' + leaseRob +
                                        '" data-sysadmin="' + (isSysAdmin ? 1 : 0) +
                                        '" class="unallocateBed" type="button" id="btn_clear_bed">Unallocate Bed</button>';
                                }
                            }
                        }

                        var lease_from = "";
                        if (data[0].lease_from_date !== "") {
                            var lease_from = 'Lease From: ' + data[0].lease_from_date + ' at ' + data[0]
                                .lease_from_time + '</br/>';
                        }
                        var lease_to = "";
                        if (data[0].lease_to_date !== "") {
                            var lease_to = 'Lease To: ' + data[0].lease_to_date + ' at ' + data[0].lease_to_time +
                                '</br/>';
                        }

                        let date1 = new Date(data[0].lease_from_date);
                        let date2 = new Date(data[0].lease_to_date);
                        var diff = new Date(date2.getTime() - date1.getTime());
                        let years = diff.getUTCFullYear() - 1970;
                        let months = diff.getUTCMonth();
                        let days = diff.getUTCDate() - 3;
                        var lease_duration = "";
                        if (data[0].lease_from_date !== "" && data[0].lease_to_date !== "") {
                            var lease_duration = "Lease Duration: " + years + " years, " + months + " months, " +
                                days + " days ";
                        }

                        var booking_fee = "";
                        if (data[3] !== 'false' && data[0].room_rate !== null) {
                            var booking_fee =
                                '<h2 class="cardSubHeaderGrey">BOOKING FEE PAID</h2><h4 class="cardHeader" > R ' +
                                data[0].room_rate + '</h4><br/></div>';
                        }

                        var room_type = "";
                        if (data[0].room_type !== null && data[0].room_type !== undefined) {
                            var room_type = '<h4 class="cardHeader" >' + data[0].room_type.name + '</h4>';
                        }

                        let app_summary_details = '<h4 class="cardSubHeaderGrey">' + data[0].residence.residence +
                            '</h4>' +
                            room_type +
                            '<h4 class="cardHeader" id="prop_room_row1" >' + metaInfo1 + floor + unit + '</h4>' +
                            '<h4 class="cardHeader" id="prop_room_row2" >' +
                            bed_code +
                            unallocated_bed +
                            '</h4>' +
                            '<h4 class="cardHeader">' +
                            lease_from +
                            lease_to +
                            lease_duration +
                            '</h4><br/>' +
                            booking_fee;

                        $(".app_summary_details").html(app_summary_details);

                        checkUserStatus();

                    },
                });
            }

            $("input").blur(function() {
                let fieldId = $(this)[0]["id"]

                // var required = document.getElementById(fieldId).required
                var required = $("#" + fieldId).hasClass('required')

                if ($("#" + fieldId).val() == "" && required == true) {
                    $("#" + $(this)[0]["id"]).addClass('fieldError')
                    $("#" + $(this)[0]["id"] + "_error").removeClass('hidden')
                } else {
                    $("#" + $(this)[0]["id"]).removeClass('fieldError')
                    $("#" + $(this)[0]["id"] + "_error").addClass('hidden')
                }

            });

            $("select").blur(function() {
                let fieldId = $(this)[0]["id"]


                // var required = document.getElementById(fieldId).required
                var required = $("#" + fieldId).hasClass('required')

                if ($("#" + fieldId).val() == null && required == true) {
                    $("#" + $(this)[0]["id"]).addClass('fieldError')
                    $("#" + $(this)[0]["id"] + "_error").removeClass('hidden')
                } else {
                    $("#" + $(this)[0]["id"]).removeClass('fieldError')
                    $("#" + $(this)[0]["id"] + "_error").addClass('hidden')
                }
            });

            $("#bPayment").on('change', function() {

                if ($("#bPayment").val() == "Via an INTERMEDIARY") {
                    $("#viaInt").removeClass("hidden")
                    $("#bint").addClass("required")
                    $("#placeholder").addClass("hidden")
                    $("#spesificInt").addClass("hidden")
                    $("#bIntName").removeClass("required")
                } else {
                    $("#viaInt").addClass("hidden")
                    $("#bint").removeClass("required")
                    $("#bint").removeClass("fieldError")
                    $("#placeholder").removeClass("hidden")
                    $("#spesificInt").addClass("hidden")
                    $("#bIntName").removeClass("required")
                    $("#bIntName").removeClass("fieldError")
                }
            })

            $("#bint").on('change', function() {

                if ($("#bint").val() == "Other") {
                    $("#spesificInt").removeClass("hidden")
                    $("#bIntName").addClass("required")
                } else {
                    $("#spesificInt").addClass("hidden")
                    $("#bIntName").removeClass("required")
                }
            })
            $("#pIdNumber").on('keyup blur', function() {
                let idNumber = $("#pIdNumber").val();

                if (idNumber.length == 13) {
                    ValidID(idNumber, "pIdNumber", "pIdNumber_error")
                } else {
                    $("#pIdNumber").addClass("fieldError")
                    $("#pIdNumber_error").removeClass('hidden')
                }

            })

            function ValidCell(cell, fieldId, fieldError, code) {
                // if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
                //     $("#"+fieldId).removeClass("fieldError")
                //     $("#"+fieldError).addClass('hidden')
                // }else{
                //     $("#"+fieldId).addClass("fieldError")
                //     $("#"+fieldError).removeClass('hidden')
                // }

                // console.log('2')

                if (code === "+27") {
                    if (/^[1-9][0-9]{8}$/.test(cell)) {
                        $("#" + fieldId).removeClass("fieldError")
                        $("#" + fieldError).addClass('hidden')
                    } else {
                        $("#" + fieldId).addClass("fieldError")
                        $("#" + fieldError).removeClass('hidden')
                    }
                } else {
                    if (/^[+][0-9]{1,3}$/.test(code)) {
                        if (/^(0|[1-9][0-9]*)$/.test(cell)) {
                            $("#" + fieldId).removeClass("fieldError")
                            $("#" + fieldError).addClass('hidden')
                        } else {
                            $("#" + fieldId).addClass("fieldError")
                            $("#" + fieldError).removeClass('hidden')
                        }
                    } else {
                        $("#" + fieldId).addClass("fieldError")
                        $("#" + fieldError).removeClass('hidden')
                    }
                }
            }
            $("#pCell_no").on('keyup blur', function() {
                cell = $("#pCell_no").val()
                ValidCell(cell, "pCell_no", "pCell_no_error", $("#sponCellCode").val())
            })
            $("#sponCellCode").on('keyup blur', function() {
                cell = $("#pCell_no").val()
                ValidCell(cell, "pCell_no", "pCell_no_error", $("#sponCellCode").val())
            })

            $("#bContactNumber").on('keyup blur', function() {
                cell = $("#bContactNumber").val()
                ValidCell(cell, "bContactNumber", "bContactNumber_error", $("#burContactCode").val())
            })
            $("#burContactCode").on('keyup blur', function() {
                cell = $("#bContactNumber").val()
                ValidCell(cell, "bContactNumber", "bContactNumber_error", $("#burContactCode").val())
            })
            $("#bAddressPostal").on('keyup blur', function() {
                postal = $("#bAddressPostal").val()

                if (/\b\d{4}\b/g.test(postal)) {
                    $("#bAddressPostal").removeClass("fieldError")
                    $("#bAddressPostal_error").addClass('hidden')
                } else {
                    $("#bAddressPostal").addClass("fieldError")
                    $("#bAddressPostal_error").removeClass('hidden')
                }

            })

            $("#pAddressPostal").on('keyup blur', function() {
                postal = $("#pAddressPostal").val()

                if (/\b\d{4}\b/g.test(postal)) {
                    $("#pAddressPostal").removeClass("fieldError")
                    $("#pAddressPostal_error").addClass('hidden')
                } else {
                    $("#pAddressPostal").addClass("fieldError")
                    $("#pAddressPostal_error").removeClass('hidden')
                }

            })

            function isEmail(email) {
                var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                return regex.test(email);
            }
            $("#pEmail").on('keyup blur', function() {
                if (isEmail($("#pEmail").val())) {
                    $("#pEmail").removeClass("fieldError")
                    $("#pEmail_error").addClass('hidden')
                } else {
                    $("#pEmail").addClass("fieldError")
                    $("#pEmail_error").removeClass('hidden')
                }
            })

            $("#bEmail").on('keyup blur', function() {
                if (isEmail($("#bEmail").val())) {
                    $("#bEmail").removeClass("fieldError")
                    $("#bEmail_error").addClass('hidden')
                } else {
                    $("#bEmail").addClass("fieldError")
                    $("#bEmail_error").removeClass('hidden')
                }
            })
            $("#pNationality").on('change', function() {
                // console.log($("#pNationality").val())
                if ($("#pNationality").val() == "South Africa") {
                    $("#pPassportField").addClass('hidden')
                    $("#pIdField").removeClass('hidden')

                    $("#pPassportNo").removeClass("required")
                    $("#pIdNumber").addClass("required")
                    $("#pPassportNo").removeClass("fieldError")
                } else {
                    $("#pIdField").addClass('hidden')
                    $("#pPassportField").removeClass('hidden')

                    $("#pPassportNo").addClass("required")
                    $("#pIdNumber").removeClass("required")
                    $("#pIdNumber").removeClass("fieldError")
                }
            })

            $("#changeBuildingType").on('click', function() {

                if ($("#propertyBuilding").val() == null || $("#propertyBuilding").val() == "" || $("#roomTypeBuilding")
                    .val() == null || $("#roomTypeBuilding").val() == "") {
                    if ($("#propertyBuilding").val() == "" || $("#propertyBuilding").val() == null) {
                        $("#propertyBuilding").addClass('fieldError')
                        $("#propertyBuilding_error").removeClass('hidden')
                    }

                    if ($("#roomTypeBuilding").val() == "" || $("#roomTypeBuilding").val() == null) {
                        $("#roomTypeBuilding").addClass('fieldError')
                        $("#roomTypeBuilding_error").removeClass('hidden')
                    }
                } else {

                    $("#changeBuildingType").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')

                    $.ajax({
                        type: "post",
                        url: "/change-booking",
                        data: {
                            bookingId: "{{ $user[0]['userInfo']['userUid'] }}",
                            property: $("#propertyBuilding").val(),
                            roomType: $("#roomTypeBuilding").val(),
                            floor: "Choose a Floor",
                        },

                        success: function(data) {
                            window.location.replace('/applications');
                        }
                    });
                }
            })

            $("#changeBookingType").on('click', function() {


                if ($("#floor").val() !== "Choose a Floor" && $("#bed_code").val() == "Choose a Bed") {
                    // if($("#property").val() == ""){
                    //     $("#property").addClass("fieldError")
                    //     $("#property_error").removeClass("hidden")
                    // }

                    // if($("#roomType").val() == ""){
                    //     $("#roomType").addClass("fieldError")
                    //     $("#roomType_error").removeClass("hidden")
                    // }

                    // if($("#bed_code").val() == null){
                    $("#bed_code").addClass('fieldError')
                    $("#bed_code_error").removeClass('hidden')
                    // }
                } else {
                    $("#changeBookingType").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "post",
                        url: "/change-booking",
                        data: {
                            bookingId: $("#bookingId").val(),
                            property: $("#property").val(),
                            roomType: $("#roomType").val(),
                            floor: $("#floor").val(),
                            unit: $("#unit").val(),
                            room: $("#room").val(),
                            bed: $("#bed").val(),
                            bed_code: $("#bed_code").val(),
                            change_bed_type: $("#change_bed_type").val(),
                        },

                        success: function(data) {

                            // console.log(data)

                            // window.location.reload();

                            if ($("#change_bed_type").val() == "cancel") {
                                $.ajax({
                                    type: "post",
                                    url: "/createLease/{{ $user[0]['userInfo']['userUid'] }}",
                                    data: {
                                        type: "auto",
                                    },

                                    success: function(data) {

                                        let uploadbtn = '<a href="/storage/' + data[0][
                                                'fileUrl'
                                            ] + '" download="' + data[0][
                                                'fileName'
                                            ] +
                                            '" style="margin-right:5px;"><button type="button" id="myBtn_download">Download Lease</button></a> '

                                        if ("<?php echo Auth::user()->role_lease_regen; ?>" == "1") {
                                            uploadbtn +=
                                                '<button type="button" id="myBtn_regen_lease" style="margin-right:5px;">Regen Lease</button> '
                                        }

                                        uploadbtn +=
                                            '<label for="moveIn" id="moveInSection" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="moveIn" onChange="UploadDocument(\'moveIn\')" accept="image/*, application/pdf" hidden/>';
                                        $("#moveInBanner").empty().append(uploadbtn);
                                        $("#moveInStatus").empty().append(
                                            '<div class="button rejected"></div>');

                                        $("#buttonTop").empty();

                                        @if (Auth::user()->role_change_bed == '1')
                                            $("#buttonTop").append(
                                                '<button type="button" class="appBooking" id="changeBedOnly" onClick="changeBooking(\'{{ $user[0]['userInfo']['userUid'] }}\', \'generate\')">Change Bed</button>'
                                            );
                                        @endif

                                        @if (Auth::user()->role_change_date == '1' && $user[0]['userInfo']['rob_50_approve'] != 2)
                                            $("#buttonTop").append(
                                                '<button type="button" class="appBooking" id="changeDatesLease" onClick="changeLease(\'{{ $user[0]['userInfo']['userUid'] }}\')">Change Lease Dates</button>'
                                            );
                                        @endif

                                        var pDoc = document.getElementById("MDAStatus");
                                        if (pDoc && pDoc.parentNode) {
                                            pDoc.parentNode.style.display = 'none';
                                        }
                                    },
                                    error: function(xhr) {
                                        showLeaseError(xhr, 'Lease generation blocked');
                                    },
                                });
                            }

                            if (data[1] !== null) {
                                if (data[1].status == "Approved") {
                                    $("#proof_of_paymentStatus").empty().append(
                                        '<div class="button approved"></div>');
                                    var button = '<button type="button" onClick="btn_view_document_ajax(' +
                                        data[1]['id'] + ')">View Document</button>';
                                    $("#proof_of_paymentBanner").empty().append(button);

                                } else if (data[1].status == "Uploaded") {
                                    $("#proof_of_paymentStatus").empty().append(
                                        '<div class="button action"></div>');
                                    var button = '<button type="button" onClick="btn_view_document_ajax(' +
                                        data[1]['id'] + ')">View Document</button>';
                                    $("#proof_of_paymentBanner").empty().append(button);
                                } else {
                                    $("#proof_of_paymentStatus").empty().append(
                                        '<div class="button rejected"></div>');
                                    let uploadbtn =
                                        '<label for="proof_of_payment" id="proof_of_paymentSection" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="proof_of_payment" onChange="UploadDocument(\'proof_of_payment\')" accept="image/*, application/pdf" hidden/>';
                                    $("#proof_of_paymentBanner").empty().append(uploadbtn);
                                }
                            }


                            $("#change_bed_type").val();
                            bookingTypeDetails();
                            refreshActions();
                            getBookingPaymentInfo();

                            // $(".waived_paid_wording").empty();

                            // if(data[4] == 'true'){
                            //     $(".waived_paid_wording").append('Waived')
                            // }else if(data[4] == '0'){
                            //     $(".waived_paid_wording").append('Yes, no booking fee')
                            // }else{
                            //     if(data[3].status == "Approved"){
                            //         $(".waived_paid_wording").append('Yes')
                            //     }else{
                            //         $(".waived_paid_wording").append('No')
                            //     }
                            // }

                            $("#changeBookingType").empty().append('Change booking')

                            // $("#myModalEditApp").html("");
                            $("#myModalEditApp").css("display", "none");
                        },
                    });
                }
            })

            function reVerifyEmail() {
                $("#reVerify").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

                $.ajax({
                    type: "post",
                    url: "/re-verify-email",
                    data: {
                        userUid: "{{ $user[0]['userInfo']['userUid'] }}",
                    },

                    success: function(data) {

                        $("#reVerify").empty().append('resend email verification');

                    },
                });

            }

            $("#lease_to_date").on('blur', function() {
                let date = new Date($("#lease_to_date").val());

                if (Object.prototype.toString.call(date) === "[object Date]") {
                    if (!isNaN(date.getTime())) {
                        $("#lease_to_date").removeClass("fieldError")
                    } else {
                        $("#lease_to_date").addClass("fieldError")
                    }
                } else {
                    $("#lease_to_date").addClass("fieldError")
                }
            })

            $("#lease_from_date").on('blur', function() {
                let date = new Date($("#lease_from_date").val());

                if (Object.prototype.toString.call(date) === "[object Date]") {
                    if (!isNaN(date.getTime())) {
                        $("#lease_from_date").removeClass("fieldError")
                    } else {
                        $("#lease_from_date").addClass("fieldError")
                    }
                } else {
                    $("#lease_from_date").addClass("fieldError")
                }
            })

            $("#changeLeaseDates").on('click', function() {

                if ($("#lease_to_date").hasClass('fieldError') === false && $("#lease_from_date").hasClass(
                        'fieldError') === false) {

                    $("#changeLeaseDates").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

                    $.ajax({
                        type: "post",
                        url: "/change-lease",
                        data: {
                            bookingId: $("#bookingId2").val(),
                            lease_from_date: $("#lease_from_date").val(),
                            lease_from_time: $("#lease_from_time").val(),
                            lease_to_date: $("#lease_to_date").val(),
                            lease_to_time: $("#lease_to_time").val(),
                        },

                        success: function(data) {

                            // window.location.reload();

                            getBookingPaymentInfo();
                            bookingTypeDetails();
                            refreshActions();
                            // $("#myModalEditLease").html("");
                            $("#myModalEditLease").css("display", "none");
                            $("#changeLeaseDates").empty().append('Change Lease Dates');
                        },
                    });
                }

            })

            $("#property").on('blur', function() {
                if ($("#property").val() == "") {
                    $("#property").addClass("fieldError")
                    $("#property_error").removeClass("hidden")
                } else {
                    $("#property").removeClass("fieldError")
                    $("#property_error").addClass("hidden")
                }
            })

            $("#roomType").on('blur', function() {
                if ($("#roomType").val() == "") {
                    $("#roomType").addClass("fieldError")
                    $("#roomType_error").removeClass("hidden")
                } else {
                    $("#roomType").removeClass("fieldError")
                    $("#roomType_error").addClass("hidden")
                }
            })

            $("#roomType").on('change', function() {
                callRoomDetails('floor', '', '', '', '', '', '')
            })

            $("#bed_code").on('blur, change', function() {
                if ($("#bed_code").val() == null) {
                    $("#bed_code").addClass("fieldError")
                    $("#bed_code_error").removeClass("hidden")
                } else {
                    $("#bed_code").removeClass("fieldError")
                    $("#bed_code_error").addClass("hidden")
                }
            });

            $("#propertyBuilding").on('change', function() {
                let propValue = $("#propertyBuilding").val();

                $("#roomTypeBuilding").empty().append('<option value="">Loading</option>');

                $.ajax({
                    type: "get",
                    url: "/check-all-rooms/{{ $user[0]['userInfo']['userUid'] }}",
                    success: function(data) {
                        let roomData = data[5]

                        // console.log(roomData)
                        // console.log("roomData")

                        $("#roomTypeBuilding").empty().append('<option value="">Select One</option>');
                        Object.keys(roomData).forEach(function(key) {
                            // if("{{ $booking->property }}" == propValue){
                            //     $("#buildingRooms").addClass('hidden')
                            // }else{
                            //     $("#buildingRooms").removeClass('hidden')
                            // }
                            if (propValue == roomData[key].residence) {
                                var selected = "<?php echo $booking->roomType; ?>" == roomData[key].room_type
                                    .id ? "selected" : "";
                                $("#roomTypeBuilding").append('<option value="' + roomData[key]
                                    .room_type.id + '" ' + selected + '>' + roomData[key]
                                    .room_type.name + '</option>');
                            }
                        });

                    }
                })
            })

            $("#property").on('change', function() {
                let propValue = $("#property").val();

                //here
                $("#roomType").empty().append('<option value="">Loading</option>');

                $.ajax({
                    type: "get",
                    url: "/check-rooms/{{ $user[0]['userInfo']['userUid'] }}",
                    success: function(data) {
                        //

                        // console.log(data)
                        let roomData = data[3]
                        // console.log(roomData)

                        $("#roomType").empty().append('<option value="">Select One</option>');
                        Object.keys(roomData).forEach(function(key) {
                            if (propValue == roomData[key].residence) {
                                var selected = "<?php echo $booking->roomType; ?>" == roomData[key].room_type
                                    .id ? "selected" : "";
                                $("#roomType").append('<option value="' + roomData[key].room_type
                                    .id + '" ' + selected + '>' + roomData[key].room_type.name +
                                    '</option>');
                            }
                        });

                        $("#floor").empty();
                        $("#unit").empty();
                        $("#room").empty();
                        $("#bed").empty();
                        $("#bed_code").empty();
                        $("#roomType").val('<?php echo $booking->roomType; ?>');
                        callRoomDetails('floor');
                    }
                })




            })

            // function openModal(field, url, uploaded_by, created_at, fileId, status){
            //     url = url.replace("%27", "'");

            //     if(field == "studentCard"){
            //         var title = "Student card";
            //         if($("#student_card_approved").val() != "1" && $("#student_card_approved").val() != "2"){
            //             var approved = 0;
            //         }else{
            //             var approved = 1;
            //         }
            //         var Data = "<span style='font-weight:bold'>Student Number:</span> {{ $user[0]['userInfo']['studentNo'] }}";
            //     }else if(field == "proof_of_payment"){
            //         var title = "Proof of payment";
            //         var approved = 1;
            //     }else if(field == "copyID"){
            //         var title = "Copy Of ID document";
            //         if($("#id_data_approved").val() != "1" && $("#id_data_approved").val() != "2"){
            //             var approved = 0;
            //         }else{
            //             var approved = 1;
            //         }
            //         if("{{ $user[0]['userInfo']['userTitle'] }}" == "Mr"){
            //             var gender = "<br/><span style='font-weight:bold'>Gender:</span> Male"
            //         }else{
            //             var gender = "<br/><span style='font-weight:bold'>Gender:</span> Female"
            //         }
            //         if("{{ $user[0]['userInfo']['userNationality'] }}" == "South Africa"){
            //             var IdData = "<br/><span style='font-weight:bold'>ID Number:</span>{{ $user[0]['userInfo']['userId'] }}";
            //         }else{
            //             var IdData = "<br/><span style='font-weight:bold'>Passport Number:</span>{{ $user[0]['userInfo']['userPassport'] }}";
            //         }
            //         var Data = "<span style='font-weight:bold'>Name:</span> {{ $user[0]['userInfo']['userFirstName'] }} {{ $user[0]['userInfo']['userLastname'] }}"+
            //             "<br/><span style='font-weight:bold'>Nationality:</span>{{ $user[0]['userInfo']['userNationality'] }}"+
            //             IdData+
            //             gender;
            //     }else if(field == "proofRegistration"){
            //         var title = "Proof of registration";
            //         if($("#registration_approved").val() != "1" && $("#registration_approved").val() != "2"){
            //             var approved = 0;
            //         }else{
            //             var approved = 1;
            //         }

            //         var Institution = "<?php foreach ($lookups as $lookup) {
                if ($lookup->id == $user[0]['userInfo']['institutionStudy']) {
                    echo $lookup->lookup_name;
                }
            } ?>";
            //         var Faculty = "<?php foreach ($lookups as $lookup) {
                if ($lookup->id == $user[0]['userInfo']['faculty']) {
                    echo $lookup->lookup_name;
                }
            } ?>";

            //         var Data = "<span style='font-weight:bold'>Institution:</span> "+Institution+
            //             "<br/><span style='font-weight:bold'>Faculty:</span> "+Faculty;
            //     }else if(field == "bursaryApproval"){
            //         var title = "NSFAS / Bursary Approval";
            //         if($("#bursary_approved").val() != "1" && $("#bursary_approved").val() != "2"){
            //             var approved = 0;
            //         }else{
            //             var approved = 1;
            //         }

            //         if("{{ $user[0]['userInfo']['bint'] }}"){
            //             bInt = "<span style='font-weight:bold'>Intermediary:</span> {{ $user[0]['userInfo']['bint'] }}<br/>"
            //         }else{
            //             bInt = ""
            //         }
            //         if("{{ $user[0]['userInfo']['bIntName'] }}"){
            //             bIntName = "<span style='font-weight:bold'>Specify Intermediary Name:</span> {{ $user[0]['userInfo']['bIntName'] }}<br/>"
            //         }else{
            //             bIntName = ""
            //         }
            //         if("{{ $user[0]['userInfo']['studentType'] }}" !== "NSFAS"){

            //             var Data = "<span style='font-weight:bold'>Company Name:</span> {{ $user[0]['userInfo']['burCompanyName'] }}<br/>"+
            //                 "<span style='font-weight:bold'>Registration Number:</span> {{ $user[0]['userInfo']['burRegNo'] }}<br/>"+
            //                 "<span style='font-weight:bold'>Contact Person:</span> {{ $user[0]['userInfo']['burContactPerson'] }}<br/>"+
            //                 "<span style='font-weight:bold'>Contact Number:</span> {{ $user[0]['userInfo']['burContactNo'] }}<br/>"+
            //                 "<span style='font-weight:bold'>Email Address:</span> {{ $user[0]['userInfo']['burEmail'] }}<br/>"+
            //                 "<span style='font-weight:bold'>Physical Address:</span> {{ $user[0]['userInfo']['bAddress1'] }}, {{ $user[0]['userInfo']['bAddress2'] }}, {{ $user[0]['userInfo']['bAddressCity'] }}, {{ $user[0]['userInfo']['bAddressPostal'] }}<br/>"+
            //                 "<span style='font-weight:bold'>How is the Bursary Paid:</span> {{ $user[0]['userInfo']['bPayment'] }}<br/>"+bInt+bIntName;
            //         }else{
            //             var Data = "";
            //         }

            //     }else if(field == "bankStatement"){
            //         var title = "Bank statement";
            //         if($("#bank_approved").val() != "1" && $("#bank_approved").val() != "2"){
            //             var approved = 0;
            //         }else{
            //             var approved = 1;
            //         }



            //         if("{{ $user[0]['userInfo']['pNationality'] }}" == "South Africa"){
            //             var IdData = "<span style='font-weight:bold'>ID Number:</span>{{ $user[0]['userInfo']['sponId'] }}<br/>";
            //         }else{
            //             var IdData = "<span style='font-weight:bold'>Passport Number:</span>{{ $user[0]['userInfo']['pPassportNo'] }}<br/>";
            //         }

            //         var Data = "<span style='font-weight:bold'>Name:</span> {{ $user[0]['userInfo']['sponFirstName'] }} {{ $user[0]['userInfo']['sponLastName'] }}<br/>"+
            //             "<span style='font-weight:bold'>Cellphone Number:</span> {{ $user[0]['userInfo']['sponCell'] }}<br/>"+
            //             "<span style='font-weight:bold'>Email Address:</span> {{ $user[0]['userInfo']['sponEmail'] }}<br/>"+
            //             "<span style='font-weight:bold'>Nationality:</span> {{ $user[0]['userInfo']['pNationality'] }}<br/>"+
            //             IdData+
            //             "<span style='font-weight:bold'>Physical Address:</span> {{ $user[0]['userInfo']['pAddress1'] }}, {{ $user[0]['userInfo']['pAddress2'] }}, {{ $user[0]['userInfo']['pAddressCity'] }}, {{ $user[0]['userInfo']['pAddressPostal'] }}<br/>";

            //     }else if(field == "moveIn"){
            //         var title = "Lease";
            //         var approved = 1;
            //     }

            //     $("#modalHeader").empty().append(title);
            //     $("#uploadedBy").empty().append(uploaded_by)
            //     $("#uploadedTime").empty().append(created_at.slice(11, 16))
            //     $("#uploadedDate").empty().append(created_at.slice(8, 10)+"|"+created_at.slice(5, 7)+"|"+created_at.slice(0, 4))
            //     $("#FileIdForApproval").val(fileId)
            //     $("#FileNameForApproval").val(field)

            //     if (Data !== "") {
            //         $("#infoToCheck").empty().append(Data);
            //     } else {
            //         approved = 1;
            //     }

            //     if(approved !== 1){
            //         /*var preview1 = document.getElementById("rejected");
            //         preview1.setAttribute("disabled", "disabled");*/
            //         @if (Auth::user()->role_approve_docs == 1)
            //             $("#approved").attr("disabled", "disabled");
            //         @endif
            //         /*var preview3 = document.getElementById("amend");
            //         preview3.setAttribute("disabled", "disabled");
            //         var preview4 = document.getElementById("rejectReason");
            //         preview4.setAttribute("readonly", "readonly");
            //         var preview5 = document.getElementById("actionUserName");
            //         preview5.setAttribute("readonly", "readonly");*/

            //         if (Data !== "") {
            //             $("#infoButtons").removeClass('hidden');
            //         }

            //         /*$("#infoChecker").removeClass('hidden')
            //         $("#infoToCheck").empty().append(Data);*/
            //     }else{
            //         @if (Auth::user()->role_approve_docs == 1)
            //             $("#rejected").removeAttr("disabled", "disabled");
            //             $("#approved").removeAttr("disabled", "disabled");
            //             $("#amend").removeAttr("disabled", "disabled");
            //             $("#rejectReason").removeAttr("readonly", "readonly");
            //             $("#actionUserName").removeAttr("readonly", "readonly");
            //         @endif

            //         $("#infoButtons").addClass('hidden');

            //         /*$("#infoChecker").addClass('hidden')
            //         $("#infoToCheck").empty();*/
            //     }

            //     if(status == "Approved" || status == "XApproved"){
            //         $("#approvedDocs").removeClass("hidden")
            //         $("#actionUserName").addClass("hidden")
            //     }else{
            //         $("#approvedDocs").addClass("hidden")
            //         $("#actionUserName").removeClass("hidden")
            //     }

            //     if(status == "Approved" || status == "Rejected" || status == "XApproved")
            //         $("#buttonSection").addClass('hidden')
            //     else
            //         $("#buttonSection").removeClass('hidden')


            //     if (url.search(".pdf") > 0) {
            //         $(".pdfSection").empty().append('<embed id="pdfView" src="/storage/'+url+'"  width="100%" height="600px" />')
            //     } else {
            //         $(".pdfSection").empty().append('<img id="pdfView" src="/storage/'+url+'" />')
            //     }  

            //     var modal = document.getElementById("myModal");
            //     modal.style.display = "block";
            // }

            // var modal = document.getElementById("myModal");


            // var span = document.getElementsByClassName("close")[0];

            // span.onclick = function() {
            //     modal.style.display = "none";
            //     $("#actionUserName").removeClass("errorBorder")
            //     $("#rejectReason").removeClass("errorBorder")
            //     $("#rejectReason_error").addClass("hidden")
            //     $("#actionUserName_error").addClass("hidden")

            //     $("#rejectReason").val("")
            //     $("#approvedDocs").addClass("hidden")
            //     document.getElementById("reasonBox").style.marginTop = "-21px";
            // }

            // window.onclick = function(event) {
            //     if (event.target == modal) {

            //         modal.style.display = "none";
            //         $("#actionUserName").removeClass("errorBorder")
            //         $("#rejectReason").removeClass("errorBorder")
            //         $("#rejectReason_error").addClass("hidden")
            //         $("#actionUserName_error").addClass("hidden")
            //         $("#rejectReason").val("")
            //         $("#approvedDocs").addClass("hidden")
            //         document.getElementById("reasonBox").style.marginTop = "-21px";
            //     }

            //     if (event.target == modalBooking) {
            //         //
            //         modalBooking.style.display = "none";
            //     }

            // }       

            function refreshActions() {

                $(".actionItemList").empty().append(
                    '<i class="fas fa-circle-notch fa-spin" style="font-size:41px; margin-left:41%;"></i>');

                $.ajax({
                    type: "get",
                    url: "/action-list/<?php echo $user[0]['userInfo']['userUid']; ?>/all",
                    success: function(data) {
                        $(".actionItemList").empty();

                        data.forEach(myFunction);

                        function myFunction(item, index) {
                            // console.log(item)
                            if (item['actionUserRole'] == "1") {
                                var userRoleName = "";
                                if (item['users'] && item['users']['user_role_name']) {
                                    userRoleName = item['users']['user_role_name'];
                                }

                                if (userRoleName == "") {
                                    var role = '<button type="button" class="admin">Admin</button>';
                                } else {
                                    var role = '<button type="button" class="admin">' + userRoleName + '</button>';
                                }

                                if (item['user']) {
                                    var usernames = '<p>' + item['user']['userFirstName'] + ' ' + item['user'][
                                        'userLastname'
                                    ] + '</p>';
                                } else {
                                    var usernames = '<p>' + item['actionUserDisplayName'] + '</p>';
                                }
                                // var usernames = '<p>'+item['user']['userFirstName']+' '+item['user']['userLastname']+'</p>';
                            } else {
                                var role = '<button type="button" class="applicant">Applicant</button>';
                                var usernames = '<p>' + item['actionUserDisplayName'] + '</p>';
                            }

                            $(".actionItemList").append(
                                '<div class="infoBlock">' +
                                '<div class="responsible">' +
                                usernames +
                                role +
                                '<p class="datetime">  ' + item["created_at"].slice(8, 10) + ' | ' + item[
                                    "created_at"].slice(5, 7) + ' | ' + item["created_at"].slice(0, 4) + ' | ' +
                                item["created_at"].slice(11, 16) + ' </p>' +
                                '</div>' +
                                '<div class="information">' +
                                '<p>' + item['action'] + '</p>' +
                                ' </div>' +
                                '</div>'
                            )
                        }
                    },
                });

                checkUserStatus();
            }

            $("#actionListSort").on("change", function() {
                $(".actionItemList").empty().append(
                    '<i class="fas fa-circle-notch fa-spin" style="font-size:41px; margin-left:41%;"></i>');

                $.ajax({
                    type: "get",
                    url: "/action-list/<?php echo $user[0]['userInfo']['userUid']; ?>/" + $("#actionListSort").val(),
                    success: function(data) {
                        $(".actionItemList").empty();

                        data.forEach(myFunction);

                        function myFunction(item, index) {
                            if (item['actionUserRole'] == "1") {
                                var role = '<button type="button" class="admin">Admin</button>';
                            } else {
                                var role = '<button type="button" class="applicant">Applicant</button>';
                            }

                            $(".actionItemList").append(
                                '<div class="infoBlock">' +
                                '<div class="responsible">' +
                                '<p>' + item['actionUserDisplayName'] + '</p>' +
                                role +
                                '<p class="datetime">  ' + item["created_at"].slice(8, 10) + ' | ' +
                                item["created_at"].slice(5, 7) + ' | ' + item["created_at"].slice(0,
                                    4) + ' | ' + item["created_at"].slice(11, 16) + ' </p>' +
                                '</div>' +
                                '<div class="information">' +
                                '<p>' + item['action'] + '</p>' +
                                ' </div>' +
                                '</div>'
                            )
                        }
                    },
                });
            })

            $("#documentSortList").on('change', function() {

                $("#documentsSectionDisplay").empty().append(
                    '<i class="fas fa-circle-notch fa-spin" style="font-size:41px; margin-left:41%;"></i>');

                $.ajax({
                    type: "get",
                    url: "/document-list/<?php echo $user[0]['userInfo']['userUid']; ?>/" + $("#documentSortList").val(),
                    success: function(data) {

                        $("#documentsSectionDisplay").empty();

                        data.forEach(myFunction);

                        function myFunction(item, index) {
                            if (item['field'] == "studentCard") {
                                var name = 'STUDENT CARD';
                            } else if (item['field'] == "proof_of_payment") {
                                var name = 'PROOF OF PAYMENT';
                            } else if (item['field'] == "copyID") {
                                var name = 'COPY OF ID DOCUMENT';
                            } else if (item['field'] == "proofRegistration") {
                                var name = 'PROOF OF REGISTRATION';
                            } else if (item['field'] == "bursaryApproval") {
                                var name = 'NSFAS APPROVAL';
                            } else if (item['field'] == "bankStatement") {
                                var name = 'BANK STATEMENT';
                            }

                            if (item['status'] == "Uploaded" || item['status'] == "Approved") {
                                var button = '<button type="button" onClick="btn_view_document_ajax(' +
                                    item['id'] + ')">View Document</button>';

                            } else {
                                // var button = '<button type="button" class="error">Send reminder</button>';
                            }

                            if (item['status'] == "Uploaded") {
                                var status = '<div class="button action"></div>';
                            } else if (item['status'] == "Approved") {
                                var status = '<div class="button approved"></div>';
                            } else if (item['status'] == "Rejected" || item['status'] == "") {
                                var status = '<div class="button rejected"></div>';
                            } else if (item['status'] == "na") {
                                var status = '<div class="button na"></div>';
                            }

                            $("#documentsSectionDisplay").append(
                                '<div class="section">' +
                                '<div class="label">' +
                                name +
                                '</div>' +
                                '<div class="banner">' +
                                button +
                                '</div>' +
                                '<div class="status">' +
                                status +
                                '</div>' +
                                '</div>'
                            )
                        }
                    },
                });
            })

            function UploadDocument(field) {
                // let field = $(this).attr('id');
                // console.log(field);

                if (document.getElementById(field).files[0].type == "application/pdf" || document.getElementById(field).files[0]
                    .type.split("/")[0] == "image") {
                    $("#" + field + "Section").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

                    let userId = <?php echo $user[0]['userInfo']['userUid']; ?>

                    var form_data = new FormData();
                    form_data.append("file", document.getElementById(field).files[0]);
                    form_data.append("userUid", userId);
                    form_data.append("fileType", field);
                    form_data.append("adminName", "{{ Auth::user()->userFirstName }}");

                    $.ajax({
                        type: "post",
                        url: "/move-in-form-admin",
                        data: form_data,
                        cache: false,
                        contentType: false,
                        processData: false,

                        success: function(data) {
                            if (data[0] > 0) {

                                $("#" + field + "Status").empty().append('<div class="button action"></div>');

                                var button = '<button type="button" onClick="btn_view_document_ajax(' + data[2][
                                    'id'
                                ] + ')">View Document</button>';

                                if (field == "moveIn") {
                                    var button = '<a href="/storage/' + data[2]['fileUrl'] + '" download="' + data[
                                            2]['fileName'] +
                                        '" style="margin-right:5px;"><button type="button" id="myBtn_download">Download Lease</button></a> '

                                    if ("<?php echo Auth::user()->role_lease_regen; ?>" == "1") {
                                        button +=
                                            '<button type="button" id="myBtn_regen_lease" style="margin-right:5px;">Regen Lease</button>'
                                        // button += '<button type="button" id="myBtn_resend_lease" onClick="resendLease()">Resend Lease</button>'
                                    }

                                    button += '<button type="button" onClick="btn_view_document_ajax(' + data[2][
                                        'id'
                                    ] + ')">View Document</button>'
                                }

                                $("#" + field + "Banner").empty().append(button);

                                refreshActions();

                            } else {
                                Swal.fire(
                                    'Error',
                                    data[0],
                                    'error'
                                )
                                $("#" + field + "Section").empty().append(
                                    '<span class="text">Upload</span>  <i class="fas fa-file-upload" ></i>');
                            }
                        },
                    });
                } else {
                    Swal.fire(
                        'Error',
                        'Only PDF and Images are allowed',
                        'error'
                    )
                }


            }


            // //File Upload
            // $(document).on('change', '#bursaryApproval, #moveIn, #copyID, #studentCard, #bankStatement, #proofRegistration, #proof_of_payment', function(){
            //     let field = $(this).attr('id');
            //     // console.log(field);

            //     $("#" + field + "Section").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

            //     let userId = <?php echo $user[0]['userInfo']['userUid']; ?>

            //     var form_data = new FormData();
            //     form_data.append("file", document.getElementById(field).files[0]);
            //     form_data.append("userUid", userId);
            //     form_data.append("fileType", field);
            //     form_data.append("adminName", "{{ Auth::user()->userFirstName }}");

            //     $.ajax({
            //         type: "post",
            //         url: "/move-in-form-admin",
            //         data: form_data,
            //         cache: false,
            //         contentType: false,
            //         processData: false,

            //         success: function (data) {
            //             if (data[0]> 0) {

            //                 $("#" + field + "Status").empty().append('<div class="button action"></div>');

            //                 var button = '<button type="button" onClick="btn_view_document_ajax('+data[2]['id']+')">View Document</button>';

            //                 if(field == "moveIn"){
            //                     var button = '<a href="/storage/'+data[2]['fileUrl']+'" download="'+data[1]['userFirstName']+'_'+data[1]['userLastname']+'_'+data[1]['userId']+'_Lease.pdf" style="margin-right:5px;"><button type="button" id="myBtn_download">Download Lease</button></a><button type="button" id="myBtn_regen_lease" style="margin-right:5px;">Regen Lease</button><button type="button" onClick="btn_view_document_ajax('+data[2]['id']+')">View Document</button>'
            //                 }

            //                 $("#" + field + "Banner").empty().append(button);

            //                 refreshActions();

            //             } else {
            //                 Swal.fire(
            //                     'Error',
            //                     data[0],
            //                     'error'
            //                 )
            //                 $("#" + field + "Section").empty().append('<span class="text">Upload</span>  <i class="fas fa-file-upload" ></i>');
            //             }
            //         },
            //     });
            // })    

            function unWaiveBookingFee(userId) {
                $("#unWaiveThisBookingFee").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to undo this booking fee waiver",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, undo it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "post",
                            url: "/undo-waive-booking-fee",
                            data: {
                                userId: userId,
                            },

                            success: function(data) {
                                // window.location.reload();

                                $("#proof_of_paymentStatus").empty().append(
                                    '<div class="button rejected"></div>');

                                <?php if(Auth::user()->role_upload_docs == 1){ ?>
                                let uploadbtn =
                                    '<label for="proof_of_payment" id="proof_of_paymentSection" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="proof_of_payment" onChange="UploadDocument(\'proof_of_payment\')" accept="image/*, application/pdf" hidden/>';
                                $("#proof_of_paymentBanner").empty().append(uploadbtn);
                                <?php } ?>

                                $("#booking_fee_waived").val('false');
                                refreshActions();
                                checkUserStatus();
                            },
                        });
                    } else {
                        $("#unWaiveThisBookingFee").empty().append('Undo Booking Fee Waiver')
                    }
                })
            }

            function getBookingPaymentInfo() {
                $.ajax({
                    type: "post",
                    url: "/getPaymentInfo",
                    data: {
                        bookingId: "{{ $user[0]['userInfo']['bookingId'] }}",
                    },

                    success: function(data) {
                        console.log(data)

                        var bookingPrice = data.booking_fee == '0' ? '-' : "R " + data.booking_fee
                        var adminPrice = data.loan_admin_fee == '0' ? '-' : "R " + data.loan_admin_fee
                        var depositPrice = data.deposit == '0' ? '-' : "R " + data.deposit

                        var MonthlyPrice = data.monthly_rental == '0' ? '-' : "R " + data.monthly_rental
                        var MonthlyTotal = data.monthly_rental_total == '0' ? '-' : "R " + data.monthly_rental_total
                        var TopupPrice = data.total_top_up == '0' ? '-' : "R " + data.total_top_up

                        var totalFunded = data.total_funded == '0' ? '-' : "R " + data.total_funded
                        var MonthlyTotalFunded = data.funded_monthly == '0' ? '-' : 'R ' + data.funded_monthly

                        $("#nsfas_wording").empty();
                        if (data.nsfas_rate == 1) {
                            $("#nsfas_wording").append("NSFAS Rate Applies")
                        }

                        $("#booking_info").empty().append(bookingPrice)
                        if (bookingPrice != '-') {
                            @if ($paymentBooking)
                                if (data.booking_fee_waived == '0') {
                                    @if (Auth::user()->role_waive_fee == '1')
                                        $("#booking_btns").empty().append(
                                            '<button type="button" class="waiveBtn" onClick="waive(\'rob_b_waived\', \'{{ $user[0]['userInfo']['userUid'] }}\',\'{{ $paymentBooking->bookingId }}\', \'booking_info\', \'booking_btns\', \'reason_booking_fee\', \'booking_fee_waived\', \'Booking Fee\')">Waive</button>'
                                        );
                                    @endif
                                } else {
                                    if (data.rob_b_waived == '1') {
                                        $("#booking_info").empty().append(
                                            '&#128993; <span style="color:red; padding-right:5px;">Waived</span>'
                                        )
                                    } else {
                                        $("#booking_info").empty().append(
                                            '&#128994; <span style="color:red; padding-right:5px;">Waived</span>'
                                        )
                                    }
                                    @if (Auth::user()->role_funding == '1')
                                        if (data.rob_b_waived == '2') {
                                            $("#booking_btns").empty().append(
                                                '<button type="button" class="waiveBtn" onClick="ActionWaiveRej(\'rob_b_waived\', \'{{ $paymentBooking->bookingId }}\', \'booking_info\', \'booking_btns\', \'Booking Fee\', \'reason_booking_fee_reject\', \'booking_fee_waived\')">Change</button>'
                                            )
                                        } else {
                                            $("#booking_btns").empty().append(
                                                '<button type="button" class="waiveAprv" onClick="ActionWaiveAprv(\'rob_b_waived\', \'{{ $paymentBooking->bookingId }}\', \'booking_info\', \'booking_btns\', \'Booking Fee\')">Approve</button><button type="button" class="waiveRej" onClick="ActionWaiveRej(\'rob_b_waived\', \'{{ $paymentBooking->bookingId }}\', \'booking_info\', \'booking_btns\', \'Booking Fee\', \'reason_booking_fee_reject\', \'booking_fee_waived\')">Reject</button>'
                                            )
                                        }
                                    @endif
                                }
                            @endif
                        }


                        $("#loan_info").empty().append(adminPrice)
                        if (adminPrice != '-') {
                            @if ($paymentBooking)
                                if (data.loan_admin_fee_waived == '0') {
                                    $("#loan_info").empty().append(adminPrice)
                                    @if (Auth::user()->role_waive_fee == '1')
                                        $("#loan_btns").empty().append(
                                            '<button type="button" class="waiveBtn" onClick="waive(\'rob_loan_waived\', \'{{ $user[0]['userInfo']['userUid'] }}\',\'{{ $paymentBooking->bookingId }}\', \'loan_info\', \'loan_btns\', \'reason_admin_fee\', \'loan_admin_fee_waived\', \'Loan Admin fee\')">Waive</button>'
                                        )
                                    @endif
                                } else {
                                    if (data.rob_loan_waived == '1') {
                                        $("#loan_info").empty().append(
                                            '&#128993; <span style="color:red; padding-right:5px;">Waived</span>'
                                        )
                                    } else {
                                        $("#loan_info").empty().append(
                                            '&#128994; <span style="color:red; padding-right:5px;">Waived</span>'
                                        )
                                    }
                                    @if (Auth::user()->role_funding == '1')
                                        if (data.rob_loan_waived == '2') {
                                            $("#loan_btns").empty().append(
                                                '<button type="button" class="waiveBtn" onClick="ActionWaiveRej(\'rob_loan_waived\', \'{{ $paymentBooking->bookingId }}\', \'loan_info\', \'loan_btns\', \'Loan Admin fee\', \'reason_admin_fee_reject\', \'loan_admin_fee_waived\')">Change</button>'
                                            )
                                        } else {
                                            $("#loan_btns").empty().append(
                                                '<button type="button" class="waiveAprv" onClick="ActionWaiveAprv(\'rob_loan_waived\', \'{{ $paymentBooking->bookingId }}\', \'loan_info\', \'loan_btns\', \'Loan Admin fee\')">Approve</button><button type="button" class="waiveRej" onClick="ActionWaiveRej(\'rob_loan_waived\', \'{{ $paymentBooking->bookingId }}\', \'loan_info\', \'loan_btns\', \'Loan Admin fee\', \'reason_admin_fee_reject\', \'loan_admin_fee_waived\')">Reject</button>'
                                            )
                                        }
                                    @endif
                                }
                            @endif
                        }

                        $("#deposit_info").empty().append(depositPrice)
                        if (depositPrice != '-') {
                            @if ($paymentBooking)
                                if (data.deposit_waived == '0') {
                                    $("#deposit_info").empty().append(depositPrice)
                                    @if (Auth::user()->role_waive_fee == '1')
                                        $("#deposit_btns").empty().append(
                                            '<button type="button" class="waiveBtn" onClick="waive(\'rob_deposit_waived\', \'{{ $user[0]['userInfo']['userUid'] }}\',\'{{ $paymentBooking->bookingId }}\', \'deposit_info\', \'deposit_btns\', \'reason_deposit\', \'deposit_waived\', \'Deposit\')">Waive</button>'
                                        )
                                    @endif
                                } else {
                                    if (data.rob_deposit_waived == '1') {
                                        $("#deposit_info").empty().append(
                                            '&#128993; <span style="color:red; padding-right:5px;">Waived</span>'
                                        )
                                    } else {
                                        $("#deposit_info").empty().append(
                                            '&#128994; <span style="color:red; padding-right:5px;">Waived</span>'
                                        )
                                    }
                                    @if (Auth::user()->role_funding == '1')
                                        if (data.rob_deposit_waived == '2') {
                                            $("#deposit_btns").empty().append(
                                                '<button type="button" class="waiveBtn" onClick="ActionWaiveRej(\'rob_deposit_waived\', \'{{ $paymentBooking->bookingId }}\', \'deposit_info\', \'deposit_btns\', \'Deposit\', \'reason_deposit_reject\', \'deposit_waived\')">Change</button>'
                                            )
                                        } else {
                                            $("#deposit_btns").empty().append(
                                                '<button type="button" class="waiveAprv" onClick="ActionWaiveAprv(\'rob_deposit_waived\', \'{{ $paymentBooking->bookingId }}\', \'deposit_info\', \'deposit_btns\', \'Deposit\')">Approve</button><button type="button" class="waiveRej" onClick="ActionWaiveRej(\'rob_deposit_waived\', \'{{ $paymentBooking->bookingId }}\', \'deposit_info\', \'deposit_btns\', \'Deposit\', \'reason_deposit_reject\', \'deposit_waived\')">Reject</button>'
                                            )
                                        }
                                    @endif
                                }
                            @endif
                        }

                        $("#monthlyRentalAmnt").empty().append(MonthlyPrice)
                        $("#monthlyRentalTotalAmnt").empty().append(MonthlyTotal)
                        $("#fundedAmnt").empty().append(totalFunded)
                        $("#topupAmnt").empty().append(TopupPrice)
                        $("#mthlyfunded").empty().append(MonthlyTotalFunded)





                        refreshActions();
                        checkUserStatus();
                    },
                });
            }

            function ActionWaiveRej(changeField, bookingId, updateField1, updateField2, textChange, rejField, field) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to reject this waiver",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, reject it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        swal.fire({
                            title: 'Reason for rejecting the waiver',
                            input: 'text',
                            inputAttributes: {
                                autocapitalize: 'off'
                            },
                            showCancelButton: true,
                            confirmButtonText: 'Submit',
                            showLoaderOnConfirm: true,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    type: "post",
                                    url: "/waive-info-reject",
                                    data: {
                                        changeField: changeField,
                                        bookingId: bookingId,
                                        textChange: textChange,
                                        userId: "{{ $user[0]['userInfo']['userUid'] }}",
                                        rejField: rejField,
                                        reason: result.value,
                                        field: field,
                                    },

                                    success: function(data) {
                                        if (changeField == "rob_b_waived") {
                                            var value = data.booking_fee == '0' ? '-' : "R " + data
                                                .booking_fee
                                            var reason = "reason_booking_fee"
                                            var waived = "booking_fee_waived"
                                            var wording = "Booking Fee"

                                            $("#proof_of_paymentStatus").empty().append(
                                                '<div class="button rejected"></div>');

                                            <?php if(Auth::user()->role_upload_docs == 1){ ?>

                                            let uploadbtn =
                                                '<label onClick="paymentRef()" id="{{ $file['field'] }}Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label>';
                                            $("#proof_of_paymentBanner").empty().append(uploadbtn);


                                            <?php } ?>

                                        } else if (changeField == "rob_loan_waived") {
                                            var value = data.loan_admin_fee == '0' ? '-' : "R " +
                                                data.loan_admin_fee
                                            var reason = "reason_admin_fee"
                                            var waived = "loan_admin_fee_waived"
                                            var wording = "Loan Admin Fee"
                                        } else {
                                            var value = data.deposit == '0' ? '-' : "R " + data
                                                .deposit
                                            var reason = "reason_deposit"
                                            var waived = "deposit_waived"
                                            var wording = "Deposit"
                                        }

                                        @if ($paymentBooking)
                                            $("#" + updateField1).empty().append(value)
                                            @if (Auth::user()->role_waive_fee == '1')
                                                $("#" + updateField2).empty().append(
                                                    '<button type="button" class="waiveBtn" onClick="waive(\'' +
                                                    changeField +
                                                    '\', \'{{ $user[0]['userInfo']['userUid'] }}\',\'{{ $paymentBooking->bookingId }}\', \'' +
                                                    updateField1 + '\', \'' + updateField2 +
                                                    '\', \'' + reason + '\', \'' + waived +
                                                    '\', \'' + wording + '\')">Waive</button>')
                                            @else
                                                $("#" + updateField2).empty()
                                            @endif
                                        @endif



                                        refreshActions();
                                        checkUserStatus();
                                    },
                                });
                            }
                        })
                    }
                })
            }

            function ActionWaiveAprv(changeField, bookingId, updateField1, updateField2, textChange) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to approve this waiver",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, waive it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "post",
                            url: "/waive-info",
                            data: {
                                changeField: changeField,
                                bookingId: bookingId,
                                textChange: textChange,
                                userId: "{{ $user[0]['userInfo']['userUid'] }}",
                            },

                            success: function(data) {

                                if (changeField == "rob_b_waived") {
                                    var rej = "reason_booking_fee_reject"
                                    var waived = "booking_fee_waived"
                                    var wording = "Booking Fee"

                                    $("#proof_of_paymentStatus").empty().append(
                                        '<div class="button approved"></div>');
                                    var button = '<button type="button" onClick="btn_view_document_ajax(' +
                                        data + ')">View Document</button>';
                                    $("#proof_of_paymentBanner").empty().append(button);

                                } else if (changeField == "rob_loan_waived") {
                                    var rej = "reason_admin_fee_reject"
                                    var waived = "loan_admin_fee_waived"
                                    var wording = "Loan Admin Fee"
                                } else {
                                    var waived = "deposit_waived"
                                    var wording = "Deposit"
                                    var rej = "reason_deposit_reject"
                                }

                                @if ($paymentBooking)
                                    $("#" + updateField1).empty().append(
                                        '&#128994; <span style="color:red; padding-right:5px;">Waived</span>'
                                    )
                                    @if (Auth::user()->role_funding == '1')
                                        $("#" + updateField2).empty().append(
                                            '<button type="button" class="waiveBtn" onClick="ActionWaiveRej(\'' +
                                            changeField +
                                            '\', \'{{ $paymentBooking->bookingId }}\', \'' +
                                            updateField1 + '\', \'' + updateField2 + '\', \'' +
                                            wording + '\', \'' + rej + '\', \'' + waived +
                                            '\')">Change</button>')
                                    @else
                                        $("#" + updateField2).empty()
                                    @endif
                                @endif


                                refreshActions();
                                checkUserStatus();
                            },
                        });
                    }
                })
            }

            function waive(changeField, userId, bookingId, changeField1, changeField2, reasonField, waiveField, textChange) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to waive this fee",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, waive it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        swal.fire({
                            title: 'Reason for waiving the fee',
                            input: 'text',
                            inputAttributes: {
                                autocapitalize: 'off'
                            },
                            showCancelButton: true,
                            confirmButtonText: 'Submit',
                            showLoaderOnConfirm: true,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    type: "post",
                                    url: "/waive-fee-request",
                                    data: {
                                        changeField: changeField,
                                        bookingId: bookingId,
                                        userId: userId,
                                        reason: result.value,
                                        reasonField: reasonField,
                                        waiveField: waiveField,
                                        textChange: textChange,
                                    },

                                    success: function(data) {

                                        $("#" + changeField1).empty().append(
                                            '&#128993; <span style="color:red; padding-right:5px;">Waived</span>'
                                        )
                                        @if ($paymentBooking)
                                            @if (Auth::user()->role_funding == '1')
                                                $("#" + changeField2).empty().append(
                                                    '<button type="button" class="waiveAprv" onClick="ActionWaiveAprv(\'' +
                                                    changeField +
                                                    '\', \'{{ $paymentBooking->bookingId }}\', \'' +
                                                    changeField1 + '\', \'' + changeField2 +
                                                    '\', \'' + textChange +
                                                    '\')">Approve</button><button type="button" class="waiveRej" onClick="ActionWaiveRej(\'' +
                                                    changeField +
                                                    '\', \'{{ $paymentBooking->bookingId }}\', \'' +
                                                    changeField1 + '\', \'' + changeField2 +
                                                    '\', \'' + textChange + '\', \'' +
                                                    reasonField + '_reject\', \'' + waiveField +
                                                    '\')">Reject</button>')
                                            @else
                                                $("#" + changeField2).empty()
                                            @endif
                                        @endif


                                        refreshActions();
                                        checkUserStatus();
                                    },
                                });
                            }
                        })
                    }
                })
            }

            function waiveBookingFee(userId, bookingId) {
                // $("#waiveThisBookingFee").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to waive this booking fee",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, waive it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        swal.fire({
                            title: 'Reason for waiving the booking fee',
                            input: 'text',
                            inputAttributes: {
                                autocapitalize: 'off'
                            },
                            showCancelButton: true,
                            confirmButtonText: 'Submit',
                            showLoaderOnConfirm: true,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    type: "post",
                                    url: "/waive-booking-fee",
                                    data: {
                                        bookingId: bookingId,
                                        userId: userId,
                                        reason: result.value
                                    },

                                    success: function(data) {
                                        // window.location.reload();

                                        $("#proof_of_paymentStatus").empty().append(
                                            '<div class="button approved"></div>');

                                        var button =
                                            '<button type="button" onClick="btn_view_document_ajax(' +
                                            data + ')">View Document</button>';
                                        $("#proof_of_paymentBanner").empty().append(button);


                                        // $("#booking_fee_waived").val('true');
                                        $("#booking_info").empty().append(
                                            '&#128993; <span style="color:red; padding-right:5px;">Waived</span>'
                                        )
                                        $("#booking_btns").empty().append('');
                                        refreshActions();
                                        checkUserStatus();
                                    },
                                });
                            } else {
                                $("#waiveThisBookingFee").empty().append('waive booking fee')
                            }
                        })


                    } else {
                        $("#waiveThisBookingFee").empty().append('waive booking fee')
                    }
                })


            }

            function reminderMail(userId) {
                $("#sendMailReminder").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                $.ajax({
                    type: "post",
                    url: "/remind/email",
                    data: {
                        userId: userId,
                    },
                    success: function(data) {
                        $("#sendMailReminder").empty().append('EMAIL REMINDER')
                        Swal.fire(
                            'Sent!',
                            'Reminder Email was sent successfully',
                            'success'
                        )

                        refreshActions();
                    },
                });
            }

            function downloadZipDocs(userId) {
                $("#downloadAllDocs").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')

                canDownload = $("#files_to_download").val()

                if (canDownload == 'true') {
                    window.location.replace('/download/docs/' + userId);
                    $("#downloadAllDocs").empty().append('Download All')
                } else {
                    $("#downloadAllDocs").empty().append('Download All')
                    Swal.fire(
                        'Oops!',
                        'Nothing to Download',
                        'warning'
                    )
                }
            }

            function restoreBooking(bookingId, userId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This booking will become active again!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, activate it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "post",
                            url: "/restore/booking",
                            data: {
                                bookingId: bookingId,
                                userId: userId,
                            },

                            success: function(data) {
                                if (data && data.warning && data.message) {
                                    Swal.fire('Application restored', data.message, 'warning').then(() => {
                                        window.location.reload();
                                    });
                                    return;
                                }

                                window.location.reload();
                            },
                            error: function(xhr) {
                                let message = 'Restore not possible.';
                                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }
                                Swal.fire('Restore blocked', message, 'error');
                            }
                        });
                    }
                })
            }

            function cancelBooking(bookingId, userId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You will cancel this booking!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, cancel it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "post",
                            url: "/cancel/booking",
                            data: {
                                bookingId: bookingId,
                                userId: userId,
                            },

                            success: function(data) {
                                window.location = "/applications";
                            },
                        });
                    }
                })
            }

            function deleteBooking(bookingId, userId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "post",
                            url: "/delete/booking",
                            data: {
                                bookingId: bookingId,
                                userId: userId,
                            },

                            success: function(data) {
                                window.location = "/applications";
                            },
                        });
                    }
                })
            }


            function resendLease() {
                $("#myBtn_resend_lease").html('<i class="fas fa-circle-notch fa-spin"></i>');

                $.ajax({
                    type: "post",
                    url: "/resendLease",
                    data: {
                        userId: "{{ $user[0]['userInfo']['userUid'] }}",
                    },

                    success: function(data) {
                        $("#myBtn_resend_lease").html('RESEND LEASE');
                    },
                    error: function(xhr) {
                        showLeaseError(xhr, 'Lease resend failed', 'RESEND LEASE', $("#myBtn_resend_lease"));
                    },
                });
            }

            function showLeaseError(xhr, title, fallbackLabel, button) {
                let message = 'Request failed.';
                if (xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            const parsed = JSON.parse(xhr.responseText);
                            if (parsed && parsed.message) {
                                message = parsed.message;
                            }
                        } catch (e) {
                            message = xhr.responseText.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                        }
                    }
                    if (xhr.status) {
                        message = message ? `${message} (HTTP ${xhr.status})` : `HTTP ${xhr.status}`;
                    }
                }
                Swal.fire({
                    icon: 'error',
                    title: title,
                    text: message,
                });
                if (button && fallbackLabel) {
                    button.html(fallbackLabel);
                }
            }

            $(document).off('click.regenLease').on('click.regenLease', '#myBtn_regen_lease', function(e) {
                let btn = $(this);
                $(btn).html('<i class="fas fa-circle-notch fa-spin"></i>');

                $.ajax({
                    type: "post",
                    url: "/createLease/{{ $user[0]['userInfo']['userUid'] }}",
                    data: {
                        type: "auto",
                    },

                    success: function(data) {
                        // if($("#change_bed_type").val() == "cancel"){
                        let uploadbtn = '<a href="/storage/' + data[0]['fileUrl'] + '" download="' + data[0]
                            ['fileName'] +
                            '" style="margin-right:5px;"><button type="button" id="myBtn_temp_download">Download Lease</button></a> '

                        if ("<?php echo Auth::user()->role_lease_regen; ?>" == "1") {
                            uploadbtn +=
                                '<button type="button" id="myBtn_regen_lease" style="margin-right:5px;">Regen Lease</button> <button type="button" id="myBtn_resend_lease" onClick="resendLease()">Resend Lease</button>'
                        }

                        uploadbtn +=
                            '<label for="moveIn" id="moveInSection" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="moveIn" onChange="UploadDocument(\'moveIn\')" accept="image/*, application/pdf" hidden/>';
                        $("#moveInBanner").empty().append(uploadbtn);
                        $("#moveInStatus").empty().append('<div class="button rejected"></div>');
                        // }      
                        $(btn).html('REGEN LEASE');
                    },
                    error: function(xhr) {
                        showLeaseError(xhr, 'Lease generation blocked', 'REGEN LEASE', btn);
                    },
                });

                e.preventDefault();
            });

            function resendTempLease() {
                $("#myBtn_resend_temp_lease").html('<i class="fas fa-circle-notch fa-spin"></i>');

                $.ajax({
                    type: "post",
                    url: "/resendTempLease",
                    data: {
                        userId: "{{ $user[0]['userInfo']['userUid'] }}",
                    },

                    success: function(data) {
                        $("#myBtn_resend_temp_lease").html('RESEND TEMP LEASE');
                    },
                    error: function(xhr) {
                        showLeaseError(xhr, 'Temp lease resend failed', 'RESEND TEMP LEASE', $("#myBtn_resend_temp_lease"));
                    },
                });
            }

            function resendAssurity() {
                $("#myBtn_resend_assurity").html('<i class="fas fa-circle-notch fa-spin"></i>');

                $.ajax({
                    type: "post",
                    url: "/resend-assurity",
                    data: {
                        userId: "{{ $user[0]['userInfo']['userUid'] }}",
                    },
                    success: function(data) {
                        $("#myBtn_resend_assurity").html('Resend');
                    },
                    error: function(xhr) {
                        showLeaseError(xhr, 'Assurity resend failed', 'Resend', $("#myBtn_resend_assurity"));
                    },
                });
            }

            $(document).off('click.regenAssurity').on('click.regenAssurity', '#myBtn_regen_assurity', function(e) {
                let btn = $(this);
                $(btn).html('<i class="fas fa-circle-notch fa-spin"></i>');

                $.ajax({
                    type: "post",
                    url: "/generate-assurity/{{ $user[0]['userInfo']['userUid'] }}",
                    data: {
                        type: "auto",
                    },
                    success: function(data) {
                        $(btn).html('Generate');
                        window.location.reload();
                    },
                    error: function(xhr) {
                        showLeaseError(xhr, 'Assurity generation failed', 'Generate', btn);
                    },
                });

                e.preventDefault();
            });

            $(document).off('click.regenTempLease').on('click.regenTempLease', 'button#myBtn_regen_temp_lease', function(e) {
                let btn = $(this);
                $(btn).html('<i class="fas fa-circle-notch fa-spin"></i>');

                $.ajax({
                    type: "post",
                    url: "/createTempLease/{{ $user[0]['userInfo']['userUid'] }}",
                    data: {
                        type: "auto",
                    },

                    success: function(data) {
                        // if($("#change_bed_type").val() == "cancel"){
                        // console.log(data)

                        let uploadbtnT = '<a href="/storage/' + data[0]['fileUrl'] + '" download="' + data[0]
                            ['fileName'] +
                            '" style="margin-right:5px;"><button type="button" id="myBtn_temp_download">Download Temp Lease</button></a> '

                        if ("<?php echo Auth::user()->role_lease_regen; ?>" == "1") {
                            uploadbtnT +=
                                '<button type="button" id="myBtn_regen_temp_lease" style="margin-right:5px;">Regen Temp Lease</button> <button type="button" id="myBtn_resend_temp_lease" onClick="resendTempLease()">Resend Temp Lease</button>'
                        }

                        uploadbtnT +=
                            '<label for="tempMoveIn" id="tempMoveInSection" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;"><span class="text">Upload</span><i class="fas fa-file-upload" ></i></label><input type="file" id="tempMoveIn" onChange="UploadDocument(\'tempMoveIn\')" accept="image/*, application/pdf" hidden/>';
                        $("#tempMoveInBanner").empty().append(uploadbtnT);
                        $("#tempMoveInStatus").empty().append('<div class="button rejected"></div>');
                        // }      
                        $(btn).html('REGEN TEMP LEASE');
                    },
                    error: function(xhr) {
                        showLeaseError(xhr, 'Temp lease generation failed', 'REGEN TEMP LEASE', btn);
                    },
                });

                e.preventDefault();
            });

            // function checkData(){
            //     $.ajax({
            //         type: "get",
            //         url: "/action-list/<?php echo $user[0]['userInfo']['userUid']; ?>/all",
            //         success: function (data) {
            //             $(".actionItemList").empty();

            //             data.forEach(myFunction);
            //             function myFunction(item, index) {
            //                 if(item['actionUserRole'] == "1"){
            //                     var role = '<button type="button" class="admin">Admin</button>';
            //                 }else{
            //                     var role = '<button type="button" class="applicant">Applicant</button>';
            //                 }

            //                 $(".actionItemList").append(
            //                     '<div class="infoBlock">'+
            //                         '<div class="responsible">'+
            //                             '<p>'+item['actionUserDisplayName']+'</p>'+
            //                                 role +
            //                             '<p class="datetime">  '+item["created_at"].slice(8, 10)+' | '+item["created_at"].slice(5, 7)+' | '+item["created_at"].slice(0, 4)+' | '+item["created_at"].slice(11, 16)+' </p>'+
            //                         '</div>'+
            //                         '<div class="information">'+
            //                             '<p>'+item['action']+'</p>'+
            //                        ' </div>'+
            //                     '</div>'
            //                 )
            //             }
            //         },
            //     });
            // }

            // setInterval(function(){
            //     checkData()
            // }, 30000)

            jQuery(document).ready(function($) {
                $(document).on("click", "#btn_ban_student", function(e) {
                    var userId = "{{ $user[0]['userInfo']['userUid'] }}";

                    Swal.fire({
                        title: "Ban student?",
                        html: '<div style="text-align:left;">' +
                            '<p>This will block the student from portal access and re-application.</p>' +
                            '<label style="display:block;font-weight:600;margin-top:8px;">Reason (required)</label>' +
                            '<textarea id="swal_ban_reason" class="swal2-textarea" placeholder="Enter the ban reason"></textarea>' +
                            '<label style="display:flex;gap:8px;align-items:flex-start;margin-top:10px;">' +
                            '<input type="checkbox" id="swal_ban_notify" checked />' +
                            '<span>Email the student with this reason.</span>' +
                            "</label>" +
                            "</div>",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Ban",
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            var reasonEl = document.getElementById("swal_ban_reason");
                            var reason = reasonEl ? String(reasonEl.value || "").trim() : "";
                            if (!reason) {
                                Swal.showValidationMessage("Reason is required.");
                                return false;
                            }
                            var notifyEl = document.getElementById("swal_ban_notify");
                            var notify = notifyEl && notifyEl.checked ? 1 : 0;
                            return {
                                reason: reason,
                                notify_student: notify,
                            };
                        },
                    }).then((result) => {
                        if (!result.isConfirmed) {
                            return;
                        }

                        $.ajax({
                            type: "post",
                            url: "/ban-student",
                            data: {
                                userId: userId,
                                reason: result.value.reason,
                                notify_student: result.value.notify_student ? 1 : 0,
                            },
                            success: function() {
                                window.location.reload();
                            },
                            error: function(xhr) {
                                var msg = "Unable to ban student.";
                                if (xhr && xhr.responseText) {
                                    try {
                                        var resp = JSON.parse(xhr.responseText);
                                        if (resp.message) {
                                            msg = resp.message;
                                        }
                                    } catch (e2) {}
                                }
                                Swal.fire("Error", msg, "error");
                            },
                        });
                    });

                    e.preventDefault();
                });

                $(document).on("click", "#btn_unban_student", function(e) {
                    var userId = "{{ $user[0]['userInfo']['userUid'] }}";

                    Swal.fire({
                        title: "Remove ban?",
                        html: '<div style="text-align:left;">' +
                            '<p>This will restore portal access and allow the student to apply again.</p>' +
                            '<label style="display:block;font-weight:600;margin-top:8px;">Reason (optional)</label>' +
                            '<textarea id="swal_unban_reason" class="swal2-textarea" placeholder="Enter an internal note (optional)"></textarea>' +
                            '<label style="display:flex;gap:8px;align-items:flex-start;margin-top:10px;">' +
                            '<input type="checkbox" id="swal_unban_notify" checked />' +
                            '<span>Email the student to notify them that access has been restored.</span>' +
                            "</label>" +
                            "</div>",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, remove ban",
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            var reasonEl = document.getElementById("swal_unban_reason");
                            var reason = reasonEl ? String(reasonEl.value || "").trim() : "";
                            var notifyEl = document.getElementById("swal_unban_notify");
                            var notify = notifyEl && notifyEl.checked ? 1 : 0;
                            return {
                                reason: reason,
                                notify_student: notify,
                            };
                        },
                    }).then((result) => {
                        if (!result.isConfirmed) {
                            return;
                        }

                        $.ajax({
                            type: "post",
                            url: "/unban-student",
                            data: {
                                userId: userId,
                                reason: result.value && result.value.reason ? result.value.reason : "",
                                notify_student: result.value && result.value.notify_student ? 1 : 0,
                            },
                            success: function() {
                                window.location.reload();
                            },
                            error: function(xhr) {
                                var msg = "Unable to remove ban.";
                                if (xhr && xhr.responseText) {
                                    try {
                                        var resp = JSON.parse(xhr.responseText);
                                        if (resp.message) {
                                            msg = resp.message;
                                        }
                                    } catch (e2) {}
                                }
                                Swal.fire("Error", msg, "error");
                            },
                        });
                    });

                    e.preventDefault();
                });

                $(document).on("click", '#btn_clear_bed', function(e) {
                    var isSysAdmin = String($(this).data("sysadmin")) === "1";
                    var leaseApproved = String($(this).data("lease")) === "true";
                    var leaseStatus = parseInt($(this).data("lease-status"), 10);
                    var hasActiveLease = leaseApproved || leaseStatus === 1 || leaseStatus === 2;

                    var msg = "You are about to unallocate this bed. The bed will be available to book again.";
                    if (hasActiveLease) {
                        msg += " This bed has an active lease. If you unallocate, the lease will be reset.";
                    }

                    var html = '<div style="text-align:left;">' + msg + '</div>';
                    if (isSysAdmin) {
                        html +=
                            '<div style="margin-top:12px;text-align:left;">' +
                            '<label style="display:flex;gap:8px;align-items:flex-start;">' +
                            '<input type="checkbox" id="swal_notify_student" checked />' +
                            '<span>Email the student to notify them that the lease was cancelled and the bed will be reallocated.</span>' +
                            '</label>' +
                            '</div>';
                    }

                    Swal.fire({
                        title: 'Are you sure?',
                        html: html,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes!',
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            var notify = 0;
                            var el = document.getElementById("swal_notify_student");
                            if (el && el.checked) {
                                notify = 1;
                            }
                            return {
                                notify_student: notify,
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "post",
                                url: "/unallocate-bed",
                                data: {
                                    userId: "{{ $user[0]['userInfo']['userUid'] }}",
                                    force_cancel_lease: isSysAdmin && hasActiveLease ? 1 : 0,
                                    notify_student: result.value && result.value.notify_student ? 1 : 0,
                                },

                                success: function(data) {
                                    $("#prop_room_row1, #prop_room_row2").remove();
                                    // $("#bed_allocated").val('')

                                    $('#moveInStatus').parent().remove();

                                    //Update change bed to not show lease cancel message
                                    $("#buttonTop").empty().append(
                                        '<button type="button" class="appBooking" id="changeLeaseCancel" onClick="changeBooking({{ $user[0]['userInfo']['userUid'] }}, \'generate\')">Change Bed</button>'
                                    );

                                    var pDoc = document.getElementById("MDAStatus");
                                    if (pDoc && pDoc.parentNode) {
                                        pDoc.parentNode.style.display = 'none';
                                    }

                                    refreshActions();
                                    checkUserStatus();

                                    // Avoid leaving the UI in a partially-updated state; a full reload ensures all
                                    // button visibility + status banners are recalculated from server state.
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 250);

                                }
                            })

                        }
                    });

                    e.preventDefault();
                });

                $(document).on('click', '.btn_edit_inline', function(e) {
                    let formId = $(this).attr('formid');
                    let edittype = $(this).attr('edittype');

                    $('#' + formId).html('<i class="fas fa-circle-notch fa-spin"></i>');

                    $.ajax({
                        type: "get",
                        url: "/edit-inline-data",
                        data: {
                            userUid: "{{ $user[0]['userInfo']['userUid'] }}",
                            formId: formId,
                            type: edittype
                        },

                        success: function(data) {

                            $('#' + formId).html(data);
                            checkUserStatus();
                        },
                    });

                    e.preventDefault();
                });

                $(document).off('blur').on('blur', 'input', function(e) {
                    let fieldId = e.target.id
                    var required = $("#" + fieldId).hasClass('required')

                    if ($("#" + fieldId).val() == "" && required == true) {
                        $("#" + fieldId).addClass('fieldError')
                        $("#" + fieldId + "_error").removeClass('hidden')
                    } else {
                        $("#" + fieldId).removeClass('fieldError')
                        $("#" + fieldId + "_error").addClass('hidden')
                    }
                });

                //Inline Form Validation
                $(document).on('keyup blur', "#userAddressPostal, #kinAddressPost", function() {
                    let postal = $(this).val()
                    let id = $(this).attr('id');

                    if (/\b\d{4}\b/g.test(postal)) {
                        $("#" + id).removeClass("fieldError")
                        $("#" + id + "_error").addClass('hidden')
                    } else {
                        $("#" + id).addClass("fieldError")
                        $("#" + id + "_error").removeClass('hidden')
                    }
                });

                $(document).on('keyup blur', "#kinCellNo, #userCell", function() {
                    let cell = $(this).val();
                    let id = $(this).attr('id');
                    let code = $("#" + id + "Code").val();

                    if (id == "kinCellNo") {
                        code = "kinCellCode";
                    }

                    ValidCell(cell, id, id + "_error", code);
                    duplicateCell();
                });

                $(document).on('keyup blur', "#userCellCode", function() {
                    let cell = $("#userCell").val();
                    let id = "userCell";
                    let code = $("#" + id + "Code").val();

                    // console.log(code)

                    ValidCell(cell, id, "userCell_error", code);
                    duplicateCell();
                });
                $(document).on('keyup blur', "#kinCellCode", function() {
                    let cell = $("#userCell").val();
                    let id = "userCell";
                    let code = $("#kinCellCode").val();

                    // console.log(code)

                    ValidCell(cell, id, "", code);
                    duplicateCell();
                });


                $(document).on('keyup blur', "#userEmail", function() {
                    let id = $(this).attr('id');

                    if (isEmail($("#" + id).val())) {
                        $("#" + id).removeClass("fieldError")
                        $("#" + id + "_error").addClass('hidden')
                    } else {
                        $("#" + id).addClass("fieldError")
                        $("#" + id + "_error").removeClass('hidden')
                    }
                });

                $(document).on('keyup blur', "#kinEmail", function() {
                    let id = $(this).attr('id');

                    if ($("#" + id).val() !== "") {
                        if (isEmail($("#" + id).val())) {
                            $("#" + id).removeClass("fieldError")
                            $("#" + id + "_error").addClass('hidden')
                        } else {
                            $("#" + id).addClass("fieldError")
                            $("#" + id + "_error").removeClass('hidden')
                        }
                    }
                });

                function ValidCell(cell, fieldId, fieldError, code) {
                    // if (/^((\+)27|0)[1-9](\d{2}){4}$/.test(cell)) {
                    //     $("#"+fieldId).removeClass("fieldError")
                    //     $("#"+fieldId + "_error").addClass('hidden')
                    // }else{
                    //     $("#"+fieldId).addClass("fieldError")
                    //     $("#"+fieldId + "_error").removeClass('hidden')
                    // }

                    // console.log(userCell_error)
                    // console.log(fieldError)


                    if (code === "+27") {
                        if (/^[1-9][0-9]{8}$/.test(cell)) {
                            $("#" + fieldId).removeClass("fieldError")
                            $("#" + fieldError).addClass('hidden')
                        } else {
                            $("#" + fieldId).addClass("fieldError")
                            $("#" + fieldError).removeClass('hidden')
                        }
                    } else {
                        if (/^[+][0-9]{1,3}$/.test(code)) {
                            if (/^(0|[1-9][0-9]*)$/.test(cell)) {
                                $("#" + fieldId).removeClass("fieldError")
                                $("#" + fieldError).addClass('hidden')
                            } else {
                                $("#" + fieldId).addClass("fieldError")
                                $("#" + fieldError).removeClass('hidden')
                            }
                        } else {
                            $("#" + fieldId).addClass("fieldError")
                            $("#" + fieldError).removeClass('hidden')
                        }
                    }
                }

                function duplicateCell() {
                    $('#kinCellNo_duplicate').addClass('hidden');
                    $('#cell_no_duplicate').addClass('hidden');
                    $('#userCell, #kinCellNo').removeClass('fieldDuplicate');

                    // console.log($('#userCell').val(), $('#kinCellNo').val());

                    if ($('#userCell').val() != "" && $('#userCell').val() == $('#kinCellNo').val()) {
                        $('#kinCellNo_duplicate').removeClass('hidden');
                        $('#cell_no_duplicate').removeClass('hidden');

                        $('#cell_no_error').removeClass('hidden');
                        $('#kinCellNo_error').removeClass('hidden');

                        $('#userCell').addClass('fieldError');
                        $('#kinCellNo').addClass('fieldError');

                        $('#userCell').addClass('fieldDuplicate');
                        $('#kinCellNo').addClass('fieldDuplicate');
                    } else {
                        cell = $("#userCell").val()
                        ValidCell(cell, "userCell", "userCell_error", $("#userCellCode").val());

                        cell = $("#kinCellNo").val();
                        ValidCell(cell, "kinCellNo", "kinCellNo_error", $("#kinCellCode").val());
                    }
                }

                function isEmail(email) {
                    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                    return regex.test(email);
                }


                $(document).on('click', '.btn_save_inline', function(e) {

                    let formid = $(this).attr('formid')

                    //Check if any hidden fields are required, remove required check
                    $('.edit_info .hidden .fieldError').removeClass('fieldError').val('');

                    var req = 1;

                    if (formid == "inline_edit_kin") {
                        if ($("#kinTitle").val() !== "") {
                            $("#kinTitle").removeClass('fieldError')
                        }
                    }

                    //Check empty fields that are required
                    $('#' + formid + ' .required').each(function() {
                        if ($(this).val() === "" || $(this).val() === null) {
                            if (!$(this).parent().hasClass('hidden')) {
                                req = 0

                                // console.log($(this)[0]["id"])

                                $(this).addClass("fieldError")
                                id = $(this)[0]["id"]
                                $("#" + id + "_error").removeClass("hidden")
                            }
                        }
                    });





                    //Check any other fields with validation errors
                    if ($('#' + formid + ' .fieldError').length > 0) {
                        req = 0;
                    }

                    if (req === 1) {
                        let formId = $(this).attr('formid');
                        let edittype = $(this).attr('edittype');

                        let btn = $(this);

                        let infoData = "NSFAS";

                        if (formId == 'inline_edit_student_type') {
                            if ($("#studentType").val() == "Cash / Direct") {
                                var modal = document.getElementById("myModalSposorInformation");
                                modal.style.display = "block";
                                infoData = "CASH";
                            } else if ($("#studentType").val() == "Bursary") {
                                var modal = document.getElementById("myModalBursarInformation");
                                modal.style.display = "block";
                                infoData = "BURSARY"
                            }
                        }

                        $.ajax({
                            type: "post",
                            url: "/save-inline-data",
                            data: $('#' + formId + ' form').serialize(),

                            success: function(data) {

                                $(btn).next().click();

                                if (infoData == "NSFAS") {
                                    window.location.reload();
                                }

                            },
                        });

                        e.preventDefault();

                    } else {
                        Swal.fire(
                            'Incorrect Data',
                            'Please correctly fill in all Required Fields!',
                            'error'
                        );
                    }
                });

                $(document).on('click', '.btn_approve_inline', function(e) {
                    let formId = $(this).attr('formid');
                    let approvefield = $(this).attr('approvefield');
                    let task = "1";

                    let message = "You are about to Approve this information";

                    let btn = $(this);

                    if ($(this).hasClass("rejected")) {
                        message = "You are about to Reject this information";
                        task = "0";
                    }

                    if (formId == "inline_app_details" && message ==
                        "You are about to Approve this information" && $("#emailverifiedStatus").val() !== '1'
                    ) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Email Not Verified',
                            text: 'Please contact the student directly and ask them to verify their email address ASAP - you may need to resend the verification email.',
                        })
                    } else {

                        Swal.fire({
                            title: 'Are you sure?',
                            text: message,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes!',
                            showLoaderOnConfirm: true,
                            preConfirm: (login) => {
                                $.ajax({
                                    type: "post",
                                    url: "/approve-inline-data",
                                    data: {
                                        userUid: "{{ $user[0]['userInfo']['userUid'] }}",
                                        formId: formId,
                                        approvefield: approvefield,
                                        task: task
                                    },

                                    success: function(data) {

                                        $("#" + approvefield).val(task)

                                        refreshActions();
                                        checkUserStatus();


                                    },
                                });
                            },
                        });
                    }



                    e.preventDefault();
                });
            });

            function addOtherDoc() {
                swal.fire({
                    title: 'Please name this document',
                    input: 'text',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    showLoaderOnConfirm: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        let value = result.value;
                        value = value.replace("'", "");
                        value = value.replace('"', '');
                        $.ajax({
                            type: "post",
                            url: "/add-other-document",
                            data: {
                                userId: "{{ $user[0]['userInfo']['userUid'] }}",
                                docName: value
                            },

                            success: function(data) {

                                $("#documentsSectionDisplay").append(
                                    '<div class="section">' +
                                    '<div class="label">' +
                                    ((data.replace("other", "other |")).replaceAll("_", " "))
                                    .toUpperCase() +
                                    '</div>' +
                                    '<div class="status" id="' + data + 'Status">' +
                                    '<div class="button rejected"></div>' +
                                    '</div>' +
                                    '<div class="banner" id="' + data + 'Banner">' +
                                    '<label for="' + data + '" id="' + data +
                                    'Section" class="outstandingUpload" style="padding:8px 16px; border-radius:10px;">' +
                                    '<span class="text">Upload</span>' +
                                    '<i class="fas fa-file-upload"></i>' +
                                    '</label>' +
                                    '<input type="file" id="' + data + '" onChange="UploadDocument(\'' +
                                    data + '\')" accept="image/*, application/pdf" hidden>' +
                                    '<button type="button" style="margin-left:5px;" onClick="deleteFile(\'' +
                                    data + '\')">Delete</button>' +
                                    '</div>' +
                                    '</div>'
                                )

                                refreshActions();
                                checkUserStatus()
                            },
                        });
                    } else {
                        $("#waiveThisBookingFee").empty().append('waive booking fee')
                    }
                })
            }

            function deleteFile(fileName) {

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "post",
                            url: "/remove-document-upload",
                            data: {
                                userId: "{{ $user[0]['userInfo']['userUid'] }}",
                                fileName: fileName
                            },

                            success: function(data) {

                                window.location.reload();
                            },
                        });
                    }
                })
            }

            $("#app_address_p").on('change', function() {

                if (document.getElementById('app_address_p').checked == true) {
                    $(".pAddress").addClass("hidden");
                } else {
                    $(".pAddress").removeClass('hidden')
                }
            })

            $("#sponsorSubmit").on('click', function() {
                //Check if any hidden fields are required, remove required check
                $('.hidden .fieldError').removeClass('fieldError').val('');
                $("#sponsorSubmit").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')

                cell = $("#pCell_no").val()
                ValidCell(cell, "pCell_no", "pCell_no_error", $("#sponCellCode").val())

                if (isEmail($("#pEmail").val())) {
                    $("#pEmail").removeClass("fieldError")
                    $("#pEmail_error").addClass('hidden')
                } else {
                    $("#pEmail").addClass("fieldError")
                    $("#pEmail_error").removeClass('hidden')
                }


                var req = 1;

                // console.log($("#app_address_p").val())

                if (document.getElementById("app_address_p").checked !== true) {
                    postal = $("#pAddressPostal").val()

                    if (/\b\d{4}\b/g.test(postal)) {
                        $("#pAddressPostal").removeClass("fieldError")
                        $("#pAddressPostal_error").addClass('hidden')
                        // console.log("1")
                    } else {
                        $("#pAddressPostal").addClass("fieldError")
                        $("#pAddressPostal_error").removeClass('hidden')
                        // console.log('2')
                    }

                    var sponArr = ["pFirstName", "pLastName", "pCell_no", "sponCellCode", "pEmail", "pNationality",
                        "pAddress1", "pAddress2", "pAddressCity", "pAddressPostal"
                    ];
                } else {
                    var sponArr = ["pFirstName", "pLastName", "pCell_no", "sponCellCode", "pEmail", "pNationality"];
                }

                if ($("#pNationality").val() == "South Africa") {
                    sponArr.push("pIdNumber")
                } else {
                    sponArr.push("pPassportNo")
                }

                sponArr.forEach(function(value) {
                    if ($("#" + value).val() === "" || $("#" + value).val() === null) {

                        $("#" + value).addClass("fieldError")
                        $("#" + value + "_error").removeClass("hidden")
                    }
                })

                if ($('.fieldDuplicate').length > 0) {
                    req = 0;
                }

                if ($('.fieldError').length > 0) {
                    req = 0;
                }

                if (req == 0) {
                    $("#sponsorSubmit").empty().append('Save Sponsor / Parent Information')
                    Swal.fire(
                        'Blank Fields',
                        'Please fill in all Required Fields!',
                        'error'
                    )

                    return false;
                } else {
                    let total = 1
                    $('.fieldError').each(function() {
                        if ($(this).val() === "") {
                            total = 0

                            $(this).addClass("fieldError")
                            id = $(this)[0]["id"]
                            $("#" + id + "_error").removeClass("hidden")
                        }
                    })

                    if (total == 0) {
                        $("#sponsorSubmit").empty().append('Save Sponsor / Parent Information')
                        Swal.fire(
                            'Blank Fields',
                            'Please fill in all Required Fields!',
                            'error'
                        )

                        return false;
                    } else {
                        $.ajax({
                            type: "post",
                            url: "/update-sponsor-details",
                            data: {
                                userId: "{{ $user[0]['userInfo']['userUid'] }}",
                                pFirstName: $("#pFirstName").val(),
                                pLastName: $("#pLastName").val(),
                                pCell_no: $("#pCell_no").val(),
                                sponCellCode: $("#sponCellCode").val(),
                                pEmail: $("#pEmail").val(),
                                pNationality: $("#pNationality").val(),
                                pPassportNo: $("#pPassportNo").val(),
                                pIdNumber: $("#pIdNumber").val(),
                                app_address_p: document.getElementById("app_address_p").checked !== true ? '0' :
                                    '1',
                                pAddress1: $("#pAddress1").val(),
                                pAddress2: $("#pAddress2").val(),
                                pAddressCity: $("#pAddressCity").val(),
                                pAddressPostal: $("#pAddressPostal").val(),
                            },

                            success: function(data) {

                                window.location.reload();
                            },
                        });
                    }
                }
            })

            $("#saveBursarDetails").on('click', function() {
                //Check if any hidden fields are required, remove required check
                $('.hidden .fieldError').removeClass('fieldError').val('');
                $("#saveBursarDetails").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')

                cell = $("#bContactNumber").val()
                ValidCell(cell, "bContactNumber", "bContactNumber_error", $("#burContactCode").val())

                if (isEmail($("#bEmail").val())) {
                    $("#bEmail").removeClass("fieldError")
                    $("#bEmail_error").addClass('hidden')
                } else {
                    $("#bEmail").addClass("fieldError")
                    $("#bEmail_error").removeClass('hidden')
                }

                postal = $("#bAddressPostal").val()

                if (/\b\d{4}\b/g.test(postal)) {
                    $("#bAddressPostal").removeClass("fieldError")
                    $("#bAddressPostal_error").addClass('hidden')
                } else {
                    $("#bAddressPostal").addClass("fieldError")
                    $("#bAddressPostal_error").removeClass('hidden')
                }


                var req = 1;

                var burArr = ["bCompanyName", "bContactPerson", "bContactNumber", "burContactCode", "bEmail",
                    "bAddress1", "bAddress2", "bAddressCity", "bAddressPostal", "bPayment"
                ];


                if ($("#bPayment").val() == "Via an INTERMEDIARY") {
                    burArr.push("bint")
                }

                if ($("#bint").val() == "Other") {
                    burArr.push("bIntName")
                }

                burArr.forEach(function(value) {
                    if ($("#" + value).val() === "" || $("#" + value).val() === null) {

                        $("#" + value).addClass("fieldError")
                        $("#" + value + "_error").removeClass("hidden")
                    }
                })

                if ($('.fieldDuplicate').length > 0) {
                    req = 0;
                }

                if ($('.fieldError').length > 0) {
                    req = 0;
                }

                if (req == 0) {
                    $("#saveBursarDetails").empty().append('Save Bursar Information')
                    Swal.fire(
                        'Blank Fields',
                        'Please fill in all Required Fields!',
                        'error'
                    )

                    return false;
                } else {
                    let total = 1
                    $('.fieldError').each(function() {
                        if ($(this).val() === "") {
                            total = 0

                            $(this).addClass("fieldError")
                            id = $(this)[0]["id"]
                            $("#" + id + "_error").removeClass("hidden")
                        }
                    })

                    if (total == 0) {
                        $("#saveBursarDetails").empty().append('Save Bursar Information')
                        Swal.fire(
                            'Blank Fields',
                            'Please fill in all Required Fields!',
                            'error'
                        )

                        return false;
                    } else {
                        $.ajax({
                            type: "post",
                            url: "/update-bursar-details",
                            data: {
                                userId: "{{ $user[0]['userInfo']['userUid'] }}",
                                bCompanyName: $("#bCompanyName").val(),
                                bRegistrationNo: $("#bRegistrationNo").val(),
                                bContactPerson: $("#bContactPerson").val(),
                                bContactNumber: $("#bContactNumber").val(),
                                burContactCode: $("#burContactCode").val(),
                                bEmail: $("#bEmail").val(),
                                bAddress1: $("#bAddress1").val(),
                                bAddress2: $("#bAddress2").val(),
                                bAddressCity: $("#bAddressCity").val(),
                                bAddressPostal: $("#bAddressPostal").val(),
                                bPayment: $("#bPayment").val(),
                                bint: $("#bint").val(),
                                bIntName: $("#bIntName").val(),

                            },

                            success: function(data) {

                                window.location.reload();
                            },
                        });
                    }
                }
            })

            function robFix(action, userId, bookingId) {
                Swal.fire({
                    title: 'Comment for change',
                    input: 'textarea',
                    inputPlaceholder: 'Add a short comment...',
                    showCancelButton: true,
                    confirmButtonText: 'Apply',
                    customClass: {
                        confirmButton: 'robFixSwalConfirm',
                        cancelButton: 'robFixSwalCancel'
                    },
                    buttonsStyling: false,
                    preConfirm: (value) => {
                        if (!value || value.trim().length < 3) {
                            Swal.showValidationMessage('Comment is required (min 3 chars).');
                            return false;
                        }
                        return value.trim();
                    }
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        type: "post",
                        url: "/rob-fix",
                        data: {
                            _token: "{{ csrf_token() }}",
                            action: action,
                            userId: userId,
                            bookingId: bookingId,
                            reason: result.value
                        },
                        success: function(data) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated',
                                text: data.message || 'ROB updated.',
                                customClass: {
                                    confirmButton: 'robFixSwalSuccess'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            let message = 'Update failed.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: message,
                                customClass: {
                                    confirmButton: 'robFixSwalError'
                                },
                                buttonsStyling: false
                            });
                        }
                    });
                });
            }
        </script>



    @endsection
