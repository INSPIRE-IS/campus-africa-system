<div class="whiteYellowCard smallerCard" id="<?php if($bookHeader == 2){ echo 'smallerBookingResize';}else{ echo 'smallerBooking';}?>">
            
            <h2 class="cardHeader <?php if($bookHeader == 0){ echo 'greenCardHeader';}elseif($bookHeader == 1){ echo 'yellowCardHeader';}elseif($bookHeader == 2){ echo 'redCardHeader';}else{echo "cancelledHeader";}?>">Booking 
                <div class="small"><span class="text"><?php if($bookHeader == 0){ echo 'Confirmed';}elseif($bookHeader == 1){ echo 'Pending Approval';}elseif($bookHeader == 2){ echo 'Awaiting Documents';}else{echo "Cancelled";}?> </span>
                    <div class="circle">
                    <?php if($bookHeader == 0){ echo '<i class="fas fa-check"></i>';}else{ echo '<i class="fas fa-file-alt"></i>';}?>
                    </div>
                </div>
            </h2>
                
            <div class="cardBody">

            @if($user->rob_60_cancelled==0)
              @foreach ($allFiles as $file)

                  @if($file->fileId == 'copyID' || $file->fileId == 'proof_of_payment')

                  <div class="appAwait" id="idCopySection">
                          <h3 class="uploadName">

                          

                            @if($file->fileId == 'copyID')
                              I.D COPY
                            @elseif($file->fileId == 'studentCard')
                              STUDENT CARD
                            @elseif($file->fileId == 'bankStatement')
                              BANK STATEMENT
                            @elseif($file->fileId == 'proofRegistration')
                              REGISTRATION
                            @elseif($file->fileId == 'proof_of_payment')
                              BOOKING FEE <br/>PROOF OF PAYMENT
                            @elseif($file->fileId == 'moveIn')
                              LEASE
                            @else
                              <?php echo strtoupper(str_replace("_", " ", str_replace("other", "other |", $file->fileId))); ?>
                            @endif
                              

                            @if($file->status == "Rejected")


                            

                            @if($file->fileId == 'proof_of_payment')
                              
                            <div id="paymentSection">
                                  <label onClick="paymentRef()" id="{{$file->fileId}}Section" class="outstanding">
                                    <span class="text">Upload Proof of Payment</span>
                                    <i class="fas fa-file-upload" ></i>
                                    <i class="fas fa-exclamation-circle"></i>
                                  </label>
                              </div>
                              <br/>
                              <div class="invalid" id="studentCardInvalid">INVALID DOCUMENT - PLEASE REUPLOAD <i class="fas fa-exclamation-circle"></i></div>


                                <button types="button" class="paymentBtn" onClick="createpayment()" style="width:100%">Pay Online Now</button>
                                <form action="https://secure.paygate.co.za/payweb3/process.trans" method="POST" id="paygateForm">
                                    <input type="hidden" name="PAY_REQUEST_ID" id="PAY_REQUEST_ID" value="">
                                    <input type="hidden" name="CHECKSUM" id="CHECKSUM" value="">
                                </form>
                              @else
                                <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstanding">
                                <span class="text">Outstanding</span>
                                <i class="fas fa-file-upload" ></i>
                                <i class="fas fa-exclamation-circle"></i>
                              </label>
                              </h3>
                              <input type="file" id="{{$file->fileId}}" hidden onChange="UploadDocument('{{$file->fileId}}')" accept="image/*, application/pdf"/>

                              <div class="invalid" id="studentCardInvalid">INVALID DOCUMENT - PLEASE REUPLOAD <i class="fas fa-exclamation-circle"></i></div>
                              @endif

                            @elseif($file->status == "None")
                            
                            
                            @if($file->fileId == 'proof_of_payment')
                            <div id="paymentSection">
                                  <label onClick="paymentRef()" id="{{$file->fileId}}Section" class="outstanding">
                                  <span class="text">Outstanding</span>
                                  <i class="fas fa-file-upload" ></i>
                                  <i class="fas fa-exclamation-circle"></i>
                                </label>
                              </div>
                              
                                <button types="button" class="paymentBtn" onClick="createpayment()" style="width:100%">Pay Online Now</button>
                                <form action="https://secure.paygate.co.za/payweb3/process.trans" method="POST" id="paygateForm">
                                    <input type="hidden" name="PAY_REQUEST_ID" id="PAY_REQUEST_ID" value="">
                                    <input type="hidden" name="CHECKSUM" id="CHECKSUM" value="">
                                </form>
                              @else
                              


                              <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstanding">
                                <span class="text">Outstanding</span>
                                <i class="fas fa-file-upload" ></i>
                                <i class="fas fa-exclamation-circle"></i>
                              </label>
                              </h3>
                              <input type="file" id="{{$file->fileId}}" hidden onChange="UploadDocument('{{$file->fileId}}')" accept="image/*, application/pdf"/>

                              @endif
                            @elseif($file->status == "Uploaded")
                            <label id="registrationPending" class="pending">
                              <span class="text">Pending Approval</span>
                              <i class="fad fa-clock"></i>
                            </label>
                            @elseif($file->status == "Approved")
                            <label class="approved">
                                <span class="text">Approved</span>
                                <i class="fas fa-check-circle"></i>
                            </label>
                            @endif
                          </h3>
                      </div>
                  @endif

              @endforeach
            @endif

            </div>
        </div>

<script>
  function createpayment(){

    Swal.fire({
      title: 'Select a Payment Method',
      input: 'select',
      inputOptions: {
        'Online Payments': {
          Credit$Debit_Card: 'Credit/Debit Card',
          Online_EFT: 'Online EFT',
        },
      },
      inputPlaceholder: 'Select a Method',
      showCancelButton: true,
      inputValidator: (value) => {
        return new Promise((resolve) => {

          resolve();

          let newValue = (value.replace("_", " ")).replace("%", "/");
          $.ajax({
              type: "post",
              url: "/initiate-payment",
              data: {
                  reference: "{{$booking->booking_code}}",
                  amount: "{{$booking->room_rate}}",
                  id: "{{Auth::user()->id}}",
                  payment_type: newValue,
                  req_type: "on_screen",
              },

              success: function (data) {

                  $("#PAY_REQUEST_ID").val(data[0])
                  $("#CHECKSUM").val(data[1])

                  $("#paygateForm").submit();

              },
          });
        })
      }
    })

    
  }

    async function paymentRef(){
      const steps=["1","2"];
      const firstStep = swal.mixin({
        confirmButtonText:'Next',
        cancelButtonText:'Cancel',
        progressSteps: steps,
        title:"Please enter the Payment Reference you used:",
        input: 'text',
        inputAttributes: {
          required: true
        },
      reverseButtons: true,
      validationMessage: 'This field is required'
      })
      const secondStep = swal.mixin({
        confirmButtonText:'Submit',
        cancelButtonText:'Back',
        progressSteps: steps,
        title: "Please upload you Proof of Payment (image or PDF)",
        input: 'file',
        inputAttributes: {
          'accept':"image/*, application/pdf",
          required: true
        },
      reverseButtons: true,
      validationMessage: 'This field is required'
      })

    const values=[]
    let currentStep
    const mixins=[firstStep,secondStep]

    for(currentStep=0;currentStep<steps.length;) {
      const result = await mixins[currentStep].fire({
        inputValue:values[currentStep],
        showCancelButton:true,
        currentProgressStep: currentStep
      })

      if (result.value) {
        values[currentStep] = result.value
        currentStep++
      } else if (result.dismiss === swal.DismissReason.cancel) {
        currentStep --
      } else {
        break
      }
    }

    if (currentStep === steps.length) {
      $.ajax({
        type:"post",
        url: "/UpdatePaymentRef",
        data: {
          bookingId: "{{$booking->id}}",
          reason: values[0]
        }
      })

      let userId = "<?php echo Auth::user()->id;?>";

      var form_data = new FormData();
        form_data.append("file", values[1]);
        form_data.append("userUid", userId);
        form_data.append("fileType", 'proof_of_payment');
        form_data.append("adminName", "{{Auth::user()->userFirstName}}");

        $.ajax({
            type: "post",
            url: "/move-in-form-admin",
            data: form_data,
            cache: false,
            contentType: false,
            processData: false,

            success: function (data) {
                if (data[0]> 0) {
                    
                    window.location.reload();

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
    }
  }
    
</script>