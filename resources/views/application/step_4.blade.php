@extends('layouts.app')

@section('content')

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="yellowGradientSmall">
    <div class="container">

        <nav class="navBar">
            <div class="leftMenu">
                <a href="/login"><img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91"></a>
            </div>

            <div class="rightMenu">
                
                <a href="https://campusafrica.co.za/contact-us/" target="_blank"><button type="button" class="contactButton">contact us</button></a>
                <!-- <button type="button" class="menuButton">Menu <i class="fa fa-bars"></i></button> -->
            </div>
        </nav>

        <div class="loginHeader">
            <h1 class="booking">Online Application</h1>
        </div>
    
    </div>
</div>

<div class="container form-card">
    <div class="onlineTabs">
    <div class="onlineTabs">
        <div class="tab complete">1: Applicant Details</div>
        @if(isset($user) && $user->studentType !== "NSFAS") 
            <div class="tab complete">2: @if(isset($user) && $user->studentType == "Bursary") Burser @else Sponsor @endif Information</div>
            <div class="tab complete">3: Next of Kin</div>
            <div class="tab active">4: Upload Documents</div>
            <div class="tab">5: Pay Booking Fee</div>
        @else
            <div class="tab complete">2: Next of Kin</div>
            <div class="tab active">3: Upload Documents</div>
            <div class="tab">4: Pay Booking Fee</div>
        @endif
    </div>
    </div>
    
    <form id="newStudentDocs" class="form onlineForm" autocomplete="off">
        <br/>
        <br/>

        <div>
            <div class="cards onlineAppDocs" id="registerCards">
                <div class="whiteYellowCard" id="cardOnlineAppBooking" style="margin-right:30px">
                    <h2 class="cardHeader">Booking<br/>Documents</h2>

                    <div class="cardBody">
                        <label for="copyID" id="copyIDUpload" class="<?php if($IdCopy && $IdCopy->status == "Uploaded"){echo 'yellowBorder';}elseif($IdCopy && $IdCopy->status == "Approved"){echo 'greenBorder';}else{echo 'redBorder';}?>" style="cursor:pointer">
                            <div class="uploadSection" id="copyIdCard">
                                <h3 class="uploadName">I.D COPY</h3>
                                <div class="uploadFileSection" id="copyIDUploadSection">
                                
                                <div id="copyIDSection">
                                    <?php 
                                        if($IdCopy && $IdCopy->status == "Uploaded"){
                                            echo ' <i class="fas fa-clock" id="yellowClock"></i>';
                                        }elseif($IdCopy && $IdCopy->status == "Approved"){
                                            echo ' <i class="fas fa-file successful"></i>';
                                        }else{
                                            echo '<i class="fas fa-file-upload" ></i>';
                                        }
                                    ?>
                                </div>
                                
                                <?php 
                                    if($IdCopy && $IdCopy->status == "Uploaded"){
                                        echo '<label id="copyIdSucess" class="toUploadLabel">UPLOADED</label>';
                                    }elseif($IdCopy && $IdCopy->status == "Approved"){
                                        echo '<label id="copyIdSucess" class="successLabel">Approved</label>';
                                    }else{ 
                                        echo ' <input type="file" id="copyID" onChange="UploadDocument(\'copyID\')" accept="image/*, application/pdf" hidden/>';
                                    }
                                ?>
                                    
                                </div>
                            </div>
                        </label>

                        <br/>
                        
                        <div class="uploadSection">
                            <h3 class="uploadName" id="bookingFeeUploadTab" style="color:#9a9a9a">BOOKING FEE PROOF OF PAYMENT</h3>
                            <div class="uploadFileSection">
                                <!-- <i class="fas fa-file-upload" style="color:rgba(231, 79, 38, 0.5);"></i> -->
                                <label for="bookingFee" style="cursor: context-menu" class="uploadLabel">SEE NEXT STEP ➜</label>
                                
                            </div>
                        </div>
                    </div>
                    
                    
                </div>

                <div class="whiteYellowCard" id="onlineAppAppDocs">
                    <h2 class="cardHeader" style="height:130px;">Application<br/>Documents</h2>
                    
                    <div class="cardBody">
                        <label for="studentCard" id="studentCardUpload" style="cursor:pointer" class="<?php if($StudentCopy && $StudentCopy->status == "Uploaded"){echo 'yellowBorder';}elseif($StudentCopy && $StudentCopy->status == "Approved"){echo 'greenBorder';}else{ echo 'redBorder';}?>">
                            <div class="uploadSection" id="copyStudentCard">
                                <h3 class="uploadName">COPY OF STUDENT CARD</h3>
                                <div class="uploadFileSection" id="studentCardUploadSection">
                                    
                                    <div id="studentCardSection">
                                        <?php 
                                            if($StudentCopy && $StudentCopy->status == "Uploaded"){
                                                echo ' <i class="fas fa-clock" id="yellowClock"></i>';
                                            }elseif($StudentCopy && $StudentCopy->status == "Approved"){
                                                echo ' <i class="fas fa-file successful"></i>';
                                            }else{
                                                echo '<i class="fas fa-file-upload" ></i>';
                                            }
                                        ?>
                                    </div>

                                    <?php 
                                    if($StudentCopy && $StudentCopy->status == "Uploaded"){
                                        echo '<label id="copyStudCardSucess" class="toUploadLabel">UPLOADED</label>';
                                    }elseif($StudentCopy && $StudentCopy->status == "Approved"){
                                        echo '<label id="copyStudCardSucess" class="successLabel">Approved</label>';
                                    }else{ 
                                        echo ' <input type="file" id="studentCard" onChange="UploadDocument(\'studentCard\')" accept="image/*, application/pdf" hidden/>';
                                    }
                                    ?>
                                    
                                    
                                    
                                    
                                </div>
                            </div>
                        </label>

                        <br/>
                        
                        @if(isset($user) && $user->studentType == "Cash / Direct")
                            <label for="bankStatement" id="bankStatementUpload" style="cursor:pointer" class="<?php if($BankCopy && $BankCopy->status == "Uploaded"){echo 'yellowBorder';}elseif($BankCopy && $BankCopy->status == "Approved"){echo 'greenBorder';}else{echo 'redBorder';}?>">
                                <div class="uploadSection" id="copyBankStatement">
                                    <h3 class="uploadName">3 MONTHS BANK STATEMENTS</h3>
                                    <div class="uploadFileSection" id="bankStatementUploadSection">
                                        
                                        <div id="bankStatementSection">
                                            <?php 
                                                if($BankCopy && $BankCopy->status == "Uploaded"){
                                                    echo ' <i class="fas fa-clock" id="yellowClock"></i>';
                                                }elseif($BankCopy && $BankCopy->status == "Approved"){
                                                    echo ' <i class="fas fa-file successful"></i>';
                                                }else{
                                                    echo '<i class="fas fa-file-upload" ></i>';
                                                }
                                            ?>
                                        </div>

                                        <?php 
                                            if($BankCopy && $BankCopy->status == "Uploaded"){
                                                echo '<label id="bankStatementSuccess" class="toUploadLabel">UPLOADED</label>';
                                            }elseif($BankCopy && $BankCopy->status == "Approved"){
                                                echo '<label id="bankStatementSuccess" class="successLabel">Approved</label>';
                                            }else{ 
                                                echo '<input type="file" id="bankStatement" onChange="UploadDocument(\'bankStatement\')" accept="image/*, application/pdf" hidden/>';
                                            }
                                        ?>
                                        
                                        
                                        
                                    </div>
                                </div>
                            </label>
                        @else
                            <label for="bursaryApproval" id="bursaryApprovalUpload" style="cursor:pointer" class="<?php if($BursaryCopy && $BursaryCopy->status == "Uploaded"){echo 'yellowBorder';}elseif($BursaryCopy && $BursaryCopy->status == "Approved"){echo 'greenBorder';}else{ echo 'redBorder';}?>">
                                <div class="uploadSection" id="copybursaryApproval">
                                    <h3 class="uploadName">@if(isset($user) && $user->studentType == "NSFAS") NSFAS @else BURSARY @endif APPROVAL</h3>
                                    <div class="uploadFileSection" id="bursaryApprovalUploadSection">
                                        
                                        <div id="bursaryApprovalSection">
                                            <?php 
                                                if($BursaryCopy && $BursaryCopy->status == "Uploaded"){
                                                    echo ' <i class="fas fa-clock" id="yellowClock"></i>';
                                                }elseif($BursaryCopy && $BursaryCopy->status == "Approved"){
                                                    echo ' <i class="fas fa-file successful"></i>';
                                                }else{
                                                    echo '<i class="fas fa-file-upload" ></i>';
                                                }
                                            ?>
                                        </div>

                                        <?php 
                                            if($BursaryCopy && $BursaryCopy->status == "Uploaded"){
                                                echo ' <label id="bursaryApprovalSuccess" class="toUploadLabel">UPLOADED</label>';
                                            }elseif($BursaryCopy && $BursaryCopy->status == "Approved"){
                                                echo '<label id="bursaryApprovalSuccess" class="successLabel">Approved</label>';
                                            }else{ 
                                                echo '<input type="file" id="bursaryApproval" onChange="UploadDocument(\'bursaryApproval\')" accept="image/*, application/pdf" hidden/>';
                                            }
                                        ?>
                                        
                                        
                                       
                                        
                                    </div>
                                </div>
                            </label>
                        @endif

                        <br/>
                        
                        <label for="proofRegistration" id="proofRegistrationUpload" style="cursor:pointer" class="<?php if($RegistrationCopy && $RegistrationCopy->status == "Uploaded"){echo 'yellowBorder';}elseif($RegistrationCopy && $RegistrationCopy->status == "Approved"){echo 'greenBorder';}else{echo 'redBorder';}?>">
                            <div class="uploadSection" id="copyProofRegistration">
                                <h3 class="uploadName">PROOF OF REGISTRATION</h3>
                                <div class="uploadFileSection" id="proofRegistrationUploadSection">
                                    
                                        <div id="proofRegistrationSection">
                                            <?php 
                                                if($RegistrationCopy && $RegistrationCopy->status == "Uploaded"){
                                                    echo ' <i class="fas fa-clock" id="yellowClock"></i>';
                                                }elseif($RegistrationCopy && $RegistrationCopy->status == "Approved"){
                                                    echo ' <i class="fas fa-file successful"></i>';
                                                }else{
                                                    echo '<i class="fas fa-file-upload" ></i>';
                                                }
                                            ?>
                                        </div>

                                        <?php 
                                            if($RegistrationCopy && $RegistrationCopy->status == "Uploaded"){
                                                echo '<label id="proofRegistrationSuccess" class="toUploadLabel">UPLOADED</label>';
                                            }elseif($RegistrationCopy && $RegistrationCopy->status == "Approved"){
                                                echo '<label id="proofRegistrationSuccess" class="successLabel">Approved</label>';
                                            }else{ 
                                                echo '<input type="file" id="proofRegistration" onChange="UploadDocument(\'proofRegistration\')" accept="image/*, application/pdf" hidden/>';
                                            }
                                        ?>
                                    
                                    
                                    
                                    
                                </div>
                            </div>
                        </label>
                    </div>

                </div>
            </div>
        </div>

        <a href="/step-5/online-application"><button type="button" class="detailsChange" id="submitForm" style="text-transform:uppercase; background-color:#e74f26">Pay Booking Fee</button></a>

        <!-- <div class="clearSpaceLogin"></div> -->
        <!-- <div class="clearSpaceLogin"></div> -->
        <div class="redBannerNotice">
            <i class="fas fa-exclamation-circle"></i>
            <p>Please note that you can upload these documents via your online profile at a later date, however delays may cause you to not get the room you want! Your booking fee is processed in the next step; and you can upload proof of payment immediately or via your profile later.</p>
        </div>


    </form>

</div>

<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    checkDocuments();
    console.log('checkDocuments', {{ auth()->user()->userUid ?? '' }})

    function checkDocuments(){
        $.ajax({
            type: "post",
            url: "/check-uploaded-docs",
            data: {userUid: "{{ auth()->user()->userUid ?? '' }}"},

            success: function (data) {
                console.log(data)
                if(data[0].status == "Uploaded"){
                    $("#copyIDSection").empty().append('<i class="fas fa-clock" id="yellowClock"></i>')
                    $("#copyIDUploadSection").empty().append('<label id="copyIDSection" class="toUploadLabel">UPLOADED</label>')
                    $("#copyIDUpload").addClass('yellowBorder')
                    $("#copyIDUpload").removeClass('redBorder')
                }

                if(data[1].status == "Uploaded"){
                    $("#studentCardSection").empty().append('<i class="fas fa-clock" id="yellowClock"></i>')
                    $("#studentCardUploadSection").empty().append('<label id="studentCardSection" class="toUploadLabel">UPLOADED</label>')
                    $("#studentCardUpload").addClass('yellowBorder')
                    $("#studentCardUpload").removeClass('redBorder')
                }

                if(data[2].status == "Uploaded"){
                    $("#bursaryApprovalSection").empty().append('<i class="fas fa-clock" id="yellowClock"></i>')
                    $("#bursaryApprovalUploadSection").empty().append('<label id="bursaryApprovalSection" class="toUploadLabel">UPLOADED</label>')
                    $("#bursaryApprovalUpload").addClass('yellowBorder')
                    $("#bursaryApprovalUpload").removeClass('redBorder')
                }

                if(data[3].status == "Uploaded"){
                    $("#bursaryApprovalSection").empty().append('<i class="fas fa-clock" id="yellowClock"></i>')
                    $("#bursaryApprovalUploadSection").empty().append('<label id="bursaryApprovalSection" class="toUploadLabel">UPLOADED</label>')
                    $("#bursaryApprovalUpload").addClass('yellowBorder')
                    $("#bursaryApprovalUpload").removeClass('redBorder')
                }

                if(data[4].status == "Uploaded"){
                    $("#proofRegistrationSection").empty().append('<i class="fas fa-clock" id="yellowClock"></i>')
                    $("#proofRegistrationUploadSection").empty().append('<label id="proofRegistrationSection" class="toUploadLabel">UPLOADED</label>')
                    $("#proofRegistrationUpload").addClass('yellowBorder')
                    $("#proofRegistrationUpload").removeClass('redBorder')
                }

            },
        });
    }

    function UploadDocument(field){

        if(document.getElementById(field).files[0].type == "application/pdf" || document.getElementById(field).files[0].type.split("/")[0] == "image"){

            $("#"+field+"Section").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

            var form_data = new FormData();
            form_data.append("file", document.getElementById(field).files[0]);
            form_data.append("userUid", "{{ auth()->user()->userUid ?? '' }}");
            form_data.append("fileType", field);

            $.ajax({
                type: "post",
                url: "/application-file-upload",
                data: form_data,
                cache: false,
                contentType: false,
                processData: false,

                success: function (data) {
                    if (data[0]> 0) {
                        
                        // $("#"+field+"Section").empty().append('<i class="fas fa-file successful"></i>')
                        $("#"+field+"Section").empty().append('<i class="fas fa-clock" id="yellowClock"></i>')
                        // $("#"+field+"Sucess").removeClass('hidden')
                        $("#"+field+"UploadSection").append('<label id="'+field+'Section" class="toUploadLabel">UPLOADED</label>')
                        // $("#"+field+"Upload").addClass('greenBorder')
                        $("#"+field+"Upload").addClass('yellowBorder')
                        $("#"+field+"Upload").removeClass('redBorder')
                        

                    } else {
                        Swal.fire(
                            'Error',
                            data[0],
                            'error'
                        )
                        $("#" + field + "Section").empty().append('<span class="text">Upload</span>  <i class="fas fa-file-upload" ></i>');
                    }
                },
            });
        }else{
            Swal.fire(
                'Error',
                'Only PDF and Images are allowed',
                'error'
            )
        }
    }

</script>


@endsection
