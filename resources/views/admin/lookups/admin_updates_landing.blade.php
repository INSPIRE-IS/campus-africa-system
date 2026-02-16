@extends('layouts.admin')

@section('content')
    <div class="container">
        @if (Auth::user() && Auth::user()->role_super == 1)
            <h1>New</h1>
            <div>
                <button style="float: none;" type="button" id="beds_update" class="detailsChange">Update Beds - Super</button>
                <button style="float: none;" type="button" id="gender_update" class="detailsChange">Update Gender -
                    Super</button>

                <button style="float: none;" type="button" id="nsfas_rate_update" class="detailsChange">Update NSFAS Rate -
                    Super</button>
                <br /><br />
                <button style="float: none;" type="button" id="reportBookingPricingLocal" class="detailsChange">Report Lease
                    Fee/Deposit Differences (Local)</button>
                <button style="float: none;" type="button" id="reportBookingPricingFull" class="detailsChange">Report Full
                    Booking Pricing (WP)</button>
                <button style="float: none;" type="button" id="updateBookingPricingLocal" class="detailsChange">Update Lease
                    Fee/Deposit (Local)</button>
                <button style="float: none;" type="button" id="updateBookingPricingFull" class="detailsChange">Update Full
                    Booking Pricing (WP)</button>
            </div>


            <h1>Legacy</h1>
            <div>
                <button style="float: none;" type="button" id="newCancelled" class="detailsChange">Cancelled on
                    bookings</button>
                <button style="float: none;" type="button" id="newStudentsBtn" class="detailsChange">New Students
                    Table</button>
                <button style="float: none;" type="button" id="newPayments" class="detailsChange">Payments Update</button>
                <button style="float: none;" type="button" id="nameCase" class="detailsChange">Name Case Update</button>
                <br /><br />
                <button style="float: none;" type="button" id="updateDataNewRobots" class="detailsChange">New Robots - Data
                    Update</button>
                <button style="float: none;" type="button" id="updateProperties" class="detailsChange">Properties
                    Update</button>
                <button style="float: none;" type="button" id="updatePropIds" class="detailsChange">Properties ID
                    Update</button>
                <button style="float: none;" type="button" id="newBookingCode" class="detailsChange">New Booking
                    Code</button>
                <br /><br />
                <button style="float: none;" type="button" id="updateBookingFee" class="detailsChange">Update All Booking
                    Fees</button>
                <button style="float: none;" type="button" id="updateProcessingStatus" class="detailsChange">Update Student
                    Processing Status</button>
                <button style="float: none;" type="button" id="updateStudentsStatus" class="detailsChange">Update Student
                    Overall Status</button>
            </div>
        @endif
    </div>

    <script>
        $("#updateBookingFee").on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#updateBookingFee").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/update/booking-fees",

                        success: function(data) {
                            $("#updateBookingFee").empty().append('Update All Booking Fees');

                            Swal.fire(
                                'Success',
                                'All booking fees updated',
                                'success'
                            )
                        },
                    });
                }
            })
        });

        $("#updateProcessingStatus").on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#updateProcessingStatus").empty().append(
                        '<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/update/processing-status",

                        success: function(data) {
                            $("#updateProcessingStatus").empty().append(
                                'Update Student processing Status');

                            Swal.fire(
                                'Success',
                                'All Student Processing Status updated',
                                'success'
                            )
                        },
                    });
                }
            })
        });

        $("#updateStudentsStatus").on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#updateStudentsStatus").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/all_student_update_status",

                        success: function(data) {
                            $("#updateStudentsStatus").empty().append(
                                'Update Students Overall Status');

                            Swal.fire(
                                'Success',
                                'All Student Overall Status updated',
                                'success'
                            )
                        },
                    });
                }
            })
        });

        $("#updateDataNewRobots").on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#updateDataNewRobots").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/admin-updates/fix-data",

                        success: function(data) {
                            $("#updateDataNewRobots").empty().append(
                            'New Robots - Data Update');

                            Swal.fire(
                                'Success',
                                'Data update complete',
                                'success'
                            )
                        },
                    });
                }
            })
        });

        $("#updateProperties").on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#updateProperties").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/admin-updates/update-properties",

                        success: function(data) {
                            $("#updateProperties").empty().append('Properties Update');

                            Swal.fire(
                                'Success',
                                'Data update complete',
                                'success'
                            )
                        },
                    });
                }
            })
        })

        $("#updatePropIds").on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#updatePropIds").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/admin-updates/update-property-id",

                        success: function(data) {
                            $("#updatePropIds").empty().append('Properties ID Update');

                            Swal.fire(
                                'Success',
                                'Data update complete',
                                'success'
                            )
                        },
                    });
                }
            })
        })

        $("#nameCase").on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#nameCase").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/admin-updates/name-case-update",

                        success: function(data) {
                            $("#nameCase").empty().append('Name Case Update');

                            Swal.fire(
                                'Success',
                                'Data update complete',
                                'success'
                            )
                        },
                    });
                }
            })
        })

        $("#newPayments").on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#newPayments").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/admin-updates/payments-update",

                        success: function(data) {
                            $("#newPayments").empty().append('Payments Update');

                            Swal.fire(
                                'Success',
                                'Data update complete',
                                'success'
                            )
                        },
                    });
                }
            })
        })

        $("#newStudentsBtn").on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#newStudentsBtn").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/admin-updates/update-new-students",

                        success: function(data) {
                            $("#newStudentsBtn").empty().append('New Students Table');

                            Swal.fire(
                                'Success',
                                'Data update complete',
                                'success'
                            )
                        },
                    });
                }
            })
        })

        $("#nsfas_rate_update").on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#nsfas_rate_update").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/admin-updates/update-nsfas-rate",

                        success: function(data) {
                            $("#nsfas_rate_update").empty().append('Update NSFAS Rate');

                            Swal.fire(
                                'Success',
                                'Data update complete',
                                'success'
                            )
                        },
                    });
                }
            })
        })

        $("#gender_update").on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#gender_update").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/admin-updates/update-gender",

                        success: function(data) {
                            $("#gender_update").empty().append('Update Gender');

                            Swal.fire(
                                'Success',
                                'Data update complete',
                                'success'
                            )
                        },
                    });
                }
            })
        })

        $("#beds_update").on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#beds_update").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/admin-updates/update-beds",

                        success: function(data) {
                            $("#beds_update").empty().append('Update beds');

                            Swal.fire(
                                'Success',
                                'Data update complete',
                                'success'
                            )
                        },
                    });
                }
            })
        })

        $("#newCancelled").on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#newCancelled").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/admin-updates/update-cancelled",

                        success: function(data) {
                            $("#newCancelled").empty().append('Cancelled on bookings');

                            Swal.fire(
                                'Success',
                                'Data update complete',
                                'success'
                            )
                        },
                    });
                }
            })
        })

        $("#newBookingCode").on('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#newBookingCode").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/admin-updates/update-booking-code",

                        success: function(data) {
                            $("#newBookingCode").empty().append('New Booking Code');

                            Swal.fire(
                                'Success',
                                'Data update complete',
                                'success'
                            )
                        },
                    });
                }
            })
        })

        $("#reportBookingPricingLocal").on('click', function() {
            $("#reportBookingPricingLocal").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
            $.ajax({
                type: "get",
                url: "/admin-updates/report-booking-pricing-local",
                success: function(data) {
                    $("#reportBookingPricingLocal").empty().append('Report Lease Fee/Deposit Differences (Local)');
                    const emailText = data.email ? `An email will be sent to ${data.email}.` : 'An email will be sent shortly.';
                    Swal.fire(
                        'Report queued',
                        `The local report is being generated for ${data.current_year}. ${emailText}`,
                        'success'
                    )
                },
                error: function(xhr) {
                    $("#reportBookingPricingLocal").empty().append('Report Lease Fee/Deposit Differences (Local)');
                    const message = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Report failed';
                    Swal.fire('Error', message, 'error');
                }
            });
        });

        $("#reportBookingPricingFull").on('click', function() {
            $("#reportBookingPricingFull").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
            $.ajax({
                type: "get",
                url: "/admin-updates/report-booking-pricing-full",
                success: function(data) {
                    $("#reportBookingPricingFull").empty().append('Report Full Booking Pricing (WP)');
                    const emailText = data.email ? `An email will be sent to ${data.email}.` : 'An email will be sent shortly.';
                    Swal.fire(
                        'Report queued',
                        `The WP report is being generated for ${data.current_year}. ${emailText}`,
                        'success'
                    )
                },
                error: function(xhr) {
                    $("#reportBookingPricingFull").empty().append('Report Full Booking Pricing (WP)');
                    const message = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Report failed';
                    Swal.fire('Error', message, 'error');
                }
            });
        });

        $("#updateBookingPricingLocal").on('click', function() {
            Swal.fire({
                title: 'Update Lease Fee & Deposit?',
                text: "This will update loan admin fee and deposit using local lookup values for the current year.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#updateBookingPricingLocal").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/admin-updates/update-booking-pricing-local",
                        success: function(data) {
                            $("#updateBookingPricingLocal").empty().append('Update Lease Fee/Deposit (Local)');
                            Swal.fire(
                                'Success',
                                `Updated ${data.updated_bookings} bookings.`,
                                'success'
                            )
                        },
                        error: function(xhr) {
                            $("#updateBookingPricingLocal").empty().append('Update Lease Fee/Deposit (Local)');
                            const message = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Update failed';
                            Swal.fire('Error', message, 'error');
                        }
                    });
                }
            })
        });

        $("#updateBookingPricingFull").on('click', function() {
            Swal.fire({
                title: 'Update Full Booking Pricing?',
                text: "This will recompute booking fee, deposit, and related fields using WP data for the current year.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#updateBookingPricingFull").empty().append('<i class="fas fa-circle-notch fa-spin"></i>')
                    $.ajax({
                        type: "get",
                        url: "/admin-updates/update-booking-pricing-full",
                        success: function(data) {
                            $("#updateBookingPricingFull").empty().append('Update Full Booking Pricing (WP)');
                            Swal.fire(
                                'Success',
                                `Updated ${data.updated_bookings} bookings.`,
                                'success'
                            )
                        },
                        error: function(xhr) {
                            $("#updateBookingPricingFull").empty().append('Update Full Booking Pricing (WP)');
                            const message = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Update failed';
                            Swal.fire('Error', message, 'error');
                        }
                    });
                }
            })
        });
    </script>
@endsection
