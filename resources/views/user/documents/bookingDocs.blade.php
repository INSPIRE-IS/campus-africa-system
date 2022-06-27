<div class="whiteYellowCard smallerCard" id="smallerBooking">
            
            <h2 class="cardHeader <?php if($bookHeader == 0){ echo 'greenCardHeader';}elseif($bookHeader == 1){ echo 'yellowCardHeader';}elseif($bookHeader == 2){ echo 'redCardHeader';}else{echo "cancelledHeader";}?>">Booking
                <div class="small"><span class="text"><?php if($bookHeader == 0){ echo 'Confirmed';}elseif($bookHeader == 1){ echo 'Pending Approval';}elseif($bookHeader == 2){ echo 'Awaiting Documents';}else{echo "Cancelled";}?> </span>
                    <div class="circle">
                    <?php if($bookHeader == 0){ echo '<i class="fas fa-check"></i>';}else{ echo '<i class="fas fa-file-alt"></i>';}?>
                    </div>
                </div>
            </h2>
                
            <div class="cardBody">

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
                        </h3>
                    </div>
                @endif

            @endforeach

            </div>
        </div>