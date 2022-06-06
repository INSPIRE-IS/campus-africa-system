<div class="whiteYellowCard smallerCard"> 
            
            <h2 class="cardHeader  <?php if($appHeader == 0){ echo 'greenCardHeader';}elseif($appHeader == 1){ echo 'yellowCardHeader';}else{ echo 'redCardHeader';}?>">Application
                <div class="small"><span class="text"><?php if($appHeader == 0){ echo 'Confirmed';}elseif($appHeader == 1){ echo 'Pending Approval';}else{ echo 'Awaiting Documents';}?> </span>
                    <div class="circle">
                    <?php if($appHeader == 0){ echo '<i class="fas fa-check"></i>';}else{ echo '<i class="fas fa-file-alt"></i>';}?>
                    </div>
                </div>
            </h2>
                
            <div class="cardBody">

            @foreach ($allFiles as $file)

            

                @if($file->fileId == 'bankStatement' || $file->fileId == 'studentCard' || $file->fileId == 'proofRegistration' || $file->fileId == 'bursaryApproval')

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
                        @if(Auth::user()->studentType == "NSFAS")
                          NSFAS APPROVAL
                        @else
                          BURSARY APPROVAL
                        @endif
                      @elseif($file->fileId == 'moveIn')
                        LEASE
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


        </div>