@extends('layouts.dash')

@section('content')

@if(!Auth::check())
<div class="yellowGradientSmall">
    <div class="container">

        <nav class="navBar">
            <div class="leftMenu">
                <img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91">
            </div>

            <div class="rightMenu">
                <a href="https://campusafrica.co.za/contact-us/" target="_blank">
                    <button type="button" class="contactButton" <?php if($agent->isMobile()){ echo 'style="width:120px !important"';} ?>>contact us</button>
                </a>
            </div>
        </nav>

        <div class="loginHeader">
            <h1 class="booking">Sign Debt Assurity</h1>
        </div>
    </div>
</div>
@endif

<div class="container">
    <div class="row justify-content-center">
        <form class="form" id="sign_assurity" method="post">
            @csrf
            <input type="hidden" id="userCustomId" value="{{ $userCustomId }}">
            <input type="hidden" id="signed_on" name="signed_on" value="{{ date('Y-m-d') }}">
            <div class="scrollable">
                <div class="pdfSection" style="margin-top: 0; height:80vh; overflow-y:scroll; border:1px solid #000; margin-top:50px;">
                        {!!$pdf!!}
                </div>
            </div>

            <div style="margin-top:30px; margin-bottom:20px;">
                <label for="signed_at">Assurity Accept</label><br/><br/>
                <ul style="list-style-type:none">
                    <li style=" <?php if(!$agent->isMobile()){ echo 'display:inline-flex';} ?> ">
                        <input type="checkbox" id="assurity_accept" name="assurity_accept" value="1"  style="margin:4px;">
                        <label for="assurity_accept" style="width:500px">I have read and accept the Debt Assurity</label>
                    </li>
                </ul>
            </div>

            <div>
                <label for="signed_at">Signed At</label><br/>
                <input type="text" name="signed_at" id="signed_at" <?php if($agent->isMobile()){ echo 'style="width:100%"';} ?>>
                <div><small>Place / Location</small></div>
            </div>

            <div style="margin-top:30px;">
                <label>Marital / Spouse Consent</label><br/><br/>
                <ul style="list-style-type:none">
                    <li style="<?php if(!$agent->isMobile()){ echo 'display:inline-flex';} ?>">
                        <input type="checkbox" id="spouse_required" name="spouse_required" value="1" style="margin:4px;">
                        <label for="spouse_required" style="width:650px">
                            I am married in community of property (spouse consent signature is required)
                        </label>
                    </li>
                </ul>

                <div id="spouse_not_required_reason_wrap" style="margin-top:10px;">
                    <label for="spouse_not_required_reason">
                        If not applicable, please state reason here (for example: married by A.N.C. or unmarried etc.).
                    </label><br/>
                    <input type="text" name="spouse_not_required_reason" id="spouse_not_required_reason" <?php if($agent->isMobile()){ echo 'style="width:100%"';} ?> />
                </div>
            </div>

            <div class="column full center signature_container" style="margin-top:30px;">
                <div style="display:flex; width:70%">
                    <div style="width:80%">Please sign here</div>
                    <div style="width:20%; justify-content:right;">
                        <button class="detailsChange" id="reset_signature" style="margin-top:-10px;" type="button">
                            Reset Signature
                        </button>
                    </div>
                </div>
                <div class="signature"></div>

                <input type="hidden" id="signature_string" name="signature_string">
                <input type="hidden" id="signature_given" value="0">
            </div>

            <div id="spouse_signature_section" class="column full center signature_container" style="margin-top:30px; display:none;">
                <div style="display:flex; width:70%">
                    <div style="width:80%">Spouse consent signature</div>
                    <div style="width:20%; justify-content:right;">
                        <button class="detailsChange" id="reset_spouse_signature" style="margin-top:-10px;" type="button">
                            Reset Signature
                        </button>
                    </div>
                </div>
                <div class="spouse_signature"></div>

                <div style="margin-top:10px;">
                    <ul style="list-style-type:none">
                        <li style="<?php if(!$agent->isMobile()){ echo 'display:inline-flex';} ?>">
                            <input type="checkbox" id="spouse_accept" name="spouse_accept" value="1" style="margin:4px;">
                            <label for="spouse_accept" style="width:650px">I consent to the suretyship contained herein</label>
                        </li>
                    </ul>
                </div>

                <input type="hidden" id="spouse_signature_string" name="spouse_signature_string">
                <input type="hidden" id="spouse_signature_given" value="0">
            </div>

            <div class="column full center flex">
                <button type="button" class="detailsChange" id="submitAssurity" style="margin-left:0px;">Submit</button>
            </div>
        </form>
    </div>
</div>

<script defer>
    jQuery(document).ready(function($) {
        let userCustomId = $("#userCustomId").val();
        let postUrl = "/save-signed-assurity/" + userCustomId;

        var sigdiv = $(".signature").jSignature({
            'background-color': 'transparent',
            'decor-color': 'transparent',
        });

        // NOTE: jSignature doesn't behave well when initialized while the container is display:none.
        // We initialize the spouse pad only when the spouse section is shown.
        var spouseSigDiv = null;

        $(".signature").on('click', function(e) {
            $('#signature_given').val('1');
            document.getElementById('signed_at').blur();
        });

        $("#reset_signature").on('click', function(e) {
            sigdiv.jSignature("reset");
            $('#signature_given').val('0');
        });

        $("#reset_spouse_signature").on('click', function(e) {
            if (spouseSigDiv) {
                spouseSigDiv.jSignature("reset");
            }
            $('#spouse_signature_given').val('0');
        });

        function initSpousePad() {
            if (spouseSigDiv) {
                return;
            }

            // Ensure the element is visible before init so it gets correct width/height.
            spouseSigDiv = $(".spouse_signature").jSignature({
                'background-color': 'transparent',
                'decor-color': 'transparent',
            });

            $(".spouse_signature").on('click', function(e) {
                $('#spouse_signature_given').val('1');
                document.getElementById('signed_at').blur();
            });
        }

        function updateSpouseUI() {
            if ($("#spouse_required").is(':checked') === true) {
                $("#spouse_signature_section").show();
                $("#spouse_not_required_reason_wrap").hide();
                // Init after show to avoid a zero-width canvas.
                setTimeout(function() {
                    initSpousePad();
                }, 0);
            } else {
                $("#spouse_signature_section").hide();
                $("#spouse_not_required_reason_wrap").show();
                $("#spouse_accept").prop('checked', false);
                if (spouseSigDiv) {
                    spouseSigDiv.jSignature("reset");
                }
                $('#spouse_signature_given').val('0');
            }
        }

        $("#spouse_required").on('change', function() {
            updateSpouseUI();
        });

        updateSpouseUI();

        $("#submitAssurity").on('click', function() {
            var $btn = $(this);
            var data = sigdiv.jSignature('getData', 'image');
            $('#signature_string').val(data);

            var spouseRequired = $("#spouse_required").is(':checked') === true;
            var spouseReasonOk = (!spouseRequired) && ($("#spouse_not_required_reason").val() || "").trim() !== "";

            if (spouseRequired) {
                initSpousePad();
                var spouseData = spouseSigDiv.jSignature('getData', 'image');
                $('#spouse_signature_string').val(spouseData);
            } else {
                $('#spouse_signature_string').val('');
            }

            var baseOk = (sigdiv.jSignature('getData', 'native').length != 0) && ($("#signed_at").val() !== "") && ($("#assurity_accept").is(':checked') === true);
            var spouseOk = (!spouseRequired)
                ? spouseReasonOk
                : ((spouseSigDiv && spouseSigDiv.jSignature('getData', 'native').length != 0) && ($("#spouse_accept").is(':checked') === true));

            if (baseOk && spouseOk) {
                $btn.html('<span></span>Submitting <i class="fas fa-circle-notch fa-spin"></i>');
                $btn.attr('disabled', 'disabled');

                $.ajax({
                    type: "post",
                    url: postUrl,
                    data: $('#sign_assurity').serialize(),
                    success: function(data) {
                        // Predictable sponsor flow: no dashboard access. If a session exists (e.g. student logged in as sponsor),
                        // log it out and show a completion message.
                        var isLoggedIn = ("{{ Auth::check() ? '1' : '0' }}" === "1");
                        $btn.html('<span></span>Completed');

                        Swal.fire({
                            title: 'Thank you!',
                            text: "Your suretyship has been submitted successfully. You may now close this tab (it may not close automatically).",
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            if (isLoggedIn) {
                                window.location.replace('/logout');
                            } else {
                                // Most browsers block window.close() unless the tab was opened by script.
                                // Attempt it anyway; if blocked, the sponsor can close the tab manually.
                                try { window.close(); } catch (e) {}
                            }
                        });
                    },
                    error: function(data) {
                        Swal.fire({
                            title: 'Error!',
                            html: data?.responseText || 'Unable to submit assurity.',
                            allowOutsideClick: true,
                        });
                        $btn.html('Submit');
                        $btn.prop('disabled', false);
                    }
                });
            } else {
                var reason = "";

                if (sigdiv.jSignature('getData', 'native').length == 0) {
                    reason = "Please sign the assurity";
                }

                if ($("#signed_at").val() == "") {
                    reason = "Please fill in the signed at section";
                }

                if ($("#assurity_accept").is(':checked') === false) {
                    reason = "Please accept the debt assurity";
                }

                if (spouseRequired) {
                    if (!spouseSigDiv || spouseSigDiv.jSignature('getData', 'native').length == 0) {
                        reason = "Spouse consent signature is required";
                    }
                    if ($("#spouse_accept").is(':checked') === false) {
                        reason = "Spouse must accept/consent to the suretyship";
                    }
                } else {
                    if (!spouseReasonOk) {
                        reason = "Please provide a reason (e.g. unmarried / married out of community of property)";
                    }
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'We need more information!',
                    text: reason,
                })
            }
        })
    });
</script>

<div class="clearSpaceLoginPage"></div>
@endsection
