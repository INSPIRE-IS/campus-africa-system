@extends('layouts.dash')

@section('content')

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>

    <style>
        .additional-documents {
            width: 89%;
            margin: 36px 0 24px;
            padding-top: 24px;
            border-top: 1px solid #d7d7d7;
        }

        .additional-documents h2 {
            margin: 0 0 18px;
            font-size: 24px;
        }

        .additional-document-form {
            display: grid;
            grid-template-columns: minmax(180px, 1fr) minmax(220px, 1fr) auto;
            gap: 12px;
            align-items: end;
            margin-bottom: 24px;
        }

        .additional-document-field label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .additional-document-field input {
            width: 100%;
            min-height: 42px;
            padding: 8px 10px;
            border: 1px solid #aaa;
            border-radius: 4px;
            background: #fff;
        }

        .additional-document-submit {
            min-height: 42px;
            padding: 8px 18px;
            border: 0;
            border-radius: 4px;
            background: #e65332;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }

        .additional-document-message {
            margin: 0 0 16px;
            padding: 10px 12px;
            border-left: 4px solid #3f8f47;
            background: #f3f8f3;
        }

        .additional-document-message.error {
            border-left-color: #c43d3d;
            background: #fff3f3;
            color: #8a2020;
        }

        .additional-document-table-wrap {
            overflow-x: auto;
        }

        .additional-document-table {
            width: 100%;
            border-collapse: collapse;
        }

        .additional-document-table th,
        .additional-document-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }

        .additional-document-table th:last-child,
        .additional-document-table td:last-child {
            width: 90px;
            text-align: right;
        }

        @media (max-width: 767px) {
            .additional-documents {
                width: 100%;
            }

            .additional-document-form {
                grid-template-columns: 1fr;
            }

            .additional-document-submit {
                width: 100%;
            }
        }
    </style>

    <div class="container">
        <div style="display: flex; justify-content: flex-end; margin-bottom: 4px;margin-top: 12px;">
            <form method="GET" action="{{ route('my-documents') }}" style="display: flex; align-items: center;">
                <label for="year" style="margin-right: 10px; font-weight: 600; font-size: 16px; color: #555;">
                    Select Year:
                </label>
                <select name="year" id="year" onchange="this.form.submit()"
                    style="padding: 10px 20px; border: 1px solid #ccc; border-radius: 8px; background-color: #eece5b; color: #333; font-size: 16px; font-weight: 700; cursor: pointer; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);  width:240px;">
                    @foreach ($years as $year)
                        <option value="{{ $year->lookup_name }}"
                            style="font-size: 16px;font-weight: 400;line-height: 1.5;color: #000; text-align:center;"
                            {{ $selected_year == $year->lookup_name ? 'selected' : '' }}>
                            {{ $year->lookup_name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <br />
        <div class="appSearchForm" style="margin-top: 10px;">
            <div class="indexTab flex" id="applicationIndex">
                <label>Legend</label>
                <div class="keys">
                    <div id="indexOne">
                        <div class="button approved"></div><span class="text">Approved</span>
                    </div>
                    <div id="indexOne">
                        <div class="button action"></div><span class="text">Pending Approval</span>
                    </div>
                    <div id="indexOne">
                        <div class="button rejected"></div><span class="text">Action Required</span>
                    </div>
                    <!-- <div id="indexOne"><div class="button na"></div><span class="text">Not Applicable</span></div> -->
                </div>
            </div>
        </div>
        <br />
        <br />
        <div style="height:50px;"></div>
        <br />
        <table id="documents_table" class="display" style="width:89%">
            <thead style="width:100%">
                <tr style="width:100%">
                    <th style="width:33%; text-align:left;">Document Name</th>
                    <th style="width:33%; text-align:left;">Status</th>
                    <th style="width:33%; text-align:center;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($allFiles as $file)
                    <tr style="margin-bottom: 4px; !important;">
                        <td>
                            @if ($file->fileId == 'copyID')
                                I.D COPY
                            @elseif($file->fileId == 'studentCard')
                                STUDENT CARD
                            @elseif($file->fileId == 'bankStatement')
                                BANK STATEMENT
                            @elseif($file->fileId == 'proofRegistration')
                                REGISTRATION
                            @elseif($file->fileId == 'proof_of_payment')
                                BOOKING FEE PROOF OF PAYMENT
                            @elseif($file->fileId == 'bursaryApproval')
                                @if (Auth::user()->studentType == 'NSFAS')
                                    NSFAS APPROVAL
                                @else
                                    BURSARY APPROVAL
                                @endif
                            @elseif($file->fileId == 'moveIn')
                                LEASE
                            @elseif(\App\Support\MonthlyPopDocument::isMonthlyPop($file->fileId))
                                {{ strtoupper(\App\Support\MonthlyPopDocument::label($file->fileId)) }}
                            @else
                                <?php echo strtoupper(str_replace('_', ' ', str_replace('other', 'other |', $file->fileId))); ?>
                            @endif
                        </td>

                        @if ($file->status == 'None')
                            @if ($file->fileId == 'proof_of_payment')
                                <td><span class="flex">
                                        <div class="rejectedRobot"></div> &nbsp;&nbsp; Outstanding
                                    </span></td>
                                @if (isset($user->rob_60_cancelled) && $user->rob_60_cancelled == 0)
                                    <td style="width:33%; text-align:center;">
                                        <div id="paymentSection">
                                            <label for="{{ $file->fileId }}" id="{{ $file->fileId }}Section"
                                                class="outstandingUpload" onClick="paymentRef()">
                                                <span class="text">Upload</span>
                                                @if (!$agent->isMobile())
                                                    <i class="fas fa-file-upload"></i>
                                                @endif

                                            </label>
                                        </div>
                                        </h3>
                                        <!-- <input type="file" id="{{ $file->fileId }}" hidden onChange="UploadDocument('{{ $file->fileId }}')"/> -->
                                    </td>
                                @endif
                            @else
                                <td><span class="flex">
                                        <div class="rejectedRobot"></div> &nbsp;&nbsp; Outstanding
                                    </span></td>
                                @if (isset($user->rob_60_cancelled) && $user->rob_60_cancelled == 0)
                                    <td style="width:33%; text-align:center;">
                                        <label for="{{ $file->fileId }}" id="{{ $file->fileId }}Section"
                                            class="outstandingUpload">
                                            <span class="text">Upload</span>
                                            @if (!$agent->isMobile())
                                                <i class="fas fa-file-upload"></i>
                                            @endif

                                        </label>
                                        </h3>
                                        <input type="file" id="{{ $file->fileId }}" hidden
                                            onChange="UploadDocument('{{ $file->fileId }}')"
                                            accept="image/*, application/pdf" />
                                    </td>
                                @endif
                            @endif
                        @elseif($file->status == 'Rejected')
                            @if ($file->fileId == 'proof_of_payment')
                                <td><span class="flex">
                                        <div class="rejectedRobot"></div> &nbsp;&nbsp; Invalid Document Please re-upload
                                    </span></td>
                                @if ($user->rob_60_cancelled == 0)
                                    <td style="width:33%; text-align:center;">
                                        <div id="paymentSection">
                                            <label for="{{ $file->fileId }}" id="{{ $file->fileId }}Section"
                                                class="outstandingUpload" onClick="paymentRef()">
                                                <span class="text">Upload</span>
                                                @if (!$agent->isMobile())
                                                    <i class="fas fa-file-upload"></i>
                                                @endif

                                            </label>
                                        </div>
                                        </h3>
                                        <!-- <input type="file" id="{{ $file->fileId }}" hidden onChange="UploadDocument('{{ $file->fileId }}')"/> -->
                                    </td>
                                @endif
                            @else
                                <td><span class="flex">
                                        <div class="rejectedRobot"></div> &nbsp;&nbsp; Invalid Document Please re-upload
                                    </span></td>
                                @if ($user->rob_60_cancelled == 0)
                                    <td style="width:33%; text-align:center;">
                                        <label for="{{ $file->fileId }}" id="{{ $file->fileId }}Section"
                                            class="outstandingUpload">
                                            <span class="text">Upload</span>
                                            @if (!$agent->isMobile())
                                                <i class="fas fa-file-upload"></i>
                                            @endif

                                        </label>
                                        </h3>
                                        <input type="file" id="{{ $file->fileId }}" hidden
                                            onChange="UploadDocument('{{ $file->fileId }}')"
                                            accept="image/*, application/pdf" />
                                    </td>
                                @endif
                            @endif
                        @elseif($file->status == 'Uploaded')
                            <td><span class="flex">
                                    <div class="pendingRobot"></div> &nbsp;&nbsp;
                                    {{ \App\Support\MonthlyPopDocument::isMonthlyPop($file->fileId) ? 'Uploaded' : 'Pending Approval' }}
                                </span></td>
                            <td style="width:33%; text-align:center;">
                                <a href="/storage/{{ $file->fileUrl }}" style="text-decoration:inherit"
                                    target="_blank"><span class="outstandingUpload"
                                        style="padding:5px 67px;">View</span></a>
                            </td>
                        @elseif($file->status == 'Approved')
                            <td><span class="flex">
                                    <div class="approvedRobot"></div> &nbsp;&nbsp; Approved
                                </span></td>
                            <td style="width:33%; text-align:center;">
                                @if ($file->fileUrl !== null && $file->fileUrl !== '')
                                    <a href="/storage/{{ $file->fileUrl }}" style="text-decoration:inherit"
                                        target="_blank"><span class="outstandingUpload"
                                            style="padding:5px 67px;">View</span></a>
                                @endif
                            </td>
                        @endif

                    </tr>
                @endforeach


            </tbody>
        </table>

        @if ($supplementaryUploadEnabled || $supplementaryDocuments->isNotEmpty())
        <section class="additional-documents" aria-labelledby="additional-documents-heading">
            <h2 id="additional-documents-heading">Additional Documents</h2>

            @if (session('supplementary_success'))
                <p class="additional-document-message">{{ session('supplementary_success') }}</p>
            @endif

            @if ($errors->any())
                <div class="additional-document-message error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if ($supplementaryUploadEnabled)
                <form method="POST" action="{{ route('supplementary-documents.store') }}"
                    enctype="multipart/form-data" class="additional-document-form">
                    @csrf
                    <div class="additional-document-field">
                        <label for="additional_document_title">Document title</label>
                        <input type="text" id="additional_document_title" name="title" maxlength="150"
                            value="{{ old('title') }}" required>
                    </div>
                    <div class="additional-document-field">
                        <label for="additional_document_file">Document</label>
                        <input type="file" id="additional_document_file" name="file"
                            accept="image/jpeg,image/png,image/gif,image/webp,application/pdf" required>
                    </div>
                    <button type="submit" class="additional-document-submit">
                        <i class="fas fa-upload" aria-hidden="true"></i> Upload
                    </button>
                </form>
            @endif

            @if ($supplementaryDocuments->isNotEmpty())
                <div class="additional-document-table-wrap">
                    <table class="additional-document-table">
                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Uploaded</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($supplementaryDocuments as $document)
                                <tr>
                                    <td>{{ $document->title }}</td>
                                    <td>{{ $document->created_at->format('d|m|Y H:i') }}</td>
                                    <td>
                                        {{ $document->status }}
                                        @if ($document->status === 'Rejected' && $document->rejection_reason)
                                            <div><small>{{ $document->rejection_reason }}</small></div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('supplementary-documents.file', $document->id) }}"
                                            target="_blank" rel="noopener">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
        @endif
        <br />
    </div>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            $('#documents_table').DataTable({
                "order": []
            });
        });

        function UploadDocument(field) {
            // console.log("JG")

            if (document.getElementById(field).files[0].type == "application/pdf" || document.getElementById(field).files[0]
                .type.split("/")[0] == "image") {

                $("#" + field + "Section").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

                let userId = "<?php echo Auth::user()->id; ?>";

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
            } else {
                Swal.fire(
                    'Error',
                    'Only PDF and Images are allowed',
                    'error'
                )
            }
        }

        async function paymentRef() {
            const steps = ["1", "2"];
            const firstStep = swal.mixin({
                confirmButtonText: 'Next',
                cancelButtonText: 'Cancel',
                progressSteps: steps,
                title: "Please enter the Payment Reference you used:",
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

                const user = @json(Auth::user());
                const bookingId = user?.bookingId;
                if (!bookingId) {
                    return;
                }

                $.ajax({
                    type: "post",
                    url: "/UpdatePaymentRef",
                    data: {
                        bookingId: bookingId,
                        reason: values[0]
                    }
                })

                let userId = "<?php echo Auth::user()->id; ?>";

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
    </script>


@endsection
