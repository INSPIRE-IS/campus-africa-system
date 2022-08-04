<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="whiteYellowCard smallerCard" id="smallerApplication"> 
            
            <h2 class="cardHeader  <?php if($appHeader == 0){ echo 'greenCardHeader';}elseif($appHeader == 1){ echo 'yellowCardHeader';}elseif($appHeader == 2){ echo 'redCardHeader';}else{echo "cancelledHeader";}?>">Application
                <div class="small"><span class="text"><?php if($appHeader == 0){ echo 'Confirmed';}elseif($appHeader == 1){ echo 'Pending Approval';}elseif($appHeader == 2){ echo 'Awaiting Documents';}else{echo "Cancelled";}?> </span>
                    <div class="circle">
                    <?php if($appHeader == 0){ echo '<i class="fas fa-check"></i>';}else{ echo '<i class="fas fa-file-alt"></i>';}?>
                    </div>
                </div>
            </h2>
                
            <div class="cardBody" id="studentAppBody">

            @foreach ($allFiles as $file)

            

                @if($file->fileId !== 'copyID' && $file->fileId !== 'proof_of_payment')

                <div class="appAwait">
                  <h3 class="uploadName">

                    @if($file->fileId == 'copyID')
                    COPY OF ID DOCUMENT
                      @elseif($file->fileId == 'studentCard')
                        STUDENT CARD
                      @elseif($file->fileId == 'bankStatement')
                        BANK STATEMENT
                      @elseif($file->fileId == 'proofRegistration')
                      PROOF OF REGISTRATION
                      @elseif($file->fileId == 'proof_of_payment')
                        BOOKING FEE PROOF OF PAYMENT
                      @elseif($file->fileId == 'bursaryApproval')
                        @if($user->studentType == "NSFAS")
                          NSFAS APPROVAL
                        @else
                          BURSARY APPROVAL
                        @endif
                      @elseif($file->fileId == 'moveIn')
                        LEASE
                      @else
                        <?php echo strtoupper(str_replace("_", " ", str_replace("other", "other |", $file->fileId))); ?>
                      @endif

                    @if($file->status == "Rejected")

                        <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstanding">
                          <span class="text">Outstanding</span>
                          <i class="fas fa-file-upload" ></i>
                          <i class="fas fa-exclamation-circle"></i>
                      </label>
                      </h3>
                      <input type="file" id="{{$file->fileId}}" hidden onChange="UploadDocument('{{$file->fileId}}')"/>

                      <div class="invalid" id="studentCardInvalid">INVALID DOCUMENT - PLEASE REUPLOAD <i class="fas fa-exclamation-circle"></i></div>
                      
                    @elseif($file->status == "None")
                    <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstanding">
                          <span class="text">Outstanding</span>
                          <i class="fas fa-file-upload" ></i>
                          <i class="fas fa-exclamation-circle"></i>
                      </label>
                      </h3>
                      <input type="file" id="{{$file->fileId}}" hidden onChange="UploadDocument('{{$file->fileId}}')"/>
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

                    </div>

                @endif

                
                
            @endforeach 

            <button type="button" id="studentAddOther" onClick="addOtherDoc()">Upload other document</button>


        </div>

<script>
    function addOtherDoc(){
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
                $.ajax({
                    type: "post",
                    url: "/add-other-document",
                    data: {
                        userId: "{{Auth::user()->id}}",
                        docName: result.value
                    },

                    success: function (data) {

                        window.location.reload();
                    },
                });
            }
        })
    }
</script>