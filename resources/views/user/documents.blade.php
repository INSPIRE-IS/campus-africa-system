@extends('layouts.dash')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>

<div class="container">
    <br/>
      <div class="appSearchForm">
        <div class="indexTab flex" id="applicationIndex">
            <label>Legend</label>
            <div class="keys">
                <div id="indexOne"><div class="button approved"></div><span class="text">Approved</span></div>
                <div id="indexOne"><div class="button action"></div><span class="text">Pending Approval</span></div>
                <div id="indexOne"><div class="button rejected"></div><span class="text">Action Required</span></div>
                <!-- <div id="indexOne"><div class="button na"></div><span class="text">Not Applicable</span></div> -->
            </div>
        </div>
      </div>
        <br/>
        <br/>
        <div style="height:50px;"></div>
        <br/>
    <table id="documents_table" class="display">
        <thead>
            <tr>
                <th>Document Name</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($allFiles as $file)
                <tr>
                    <td>
                        @if($file->fileId == 'copyID')
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
                          @if(Auth::user()->studentType == "NSFAS")
                            NSFAS APPROVAL
                          @else
                            BURSARY APPROVAL
                          @endif
                        @elseif($file->fileId == 'moveIn')
                          LEASE
                          @else
                            <?php echo strtoupper(str_replace("_", " ", str_replace("other", "other |", $file->fileId))); ?>
                        @endif
                    </td>
                    
                        @if($file->status == 'None')
                          @if($file->fileId == 'proof_of_payment')
                            
     
                                <td><span class="flex"><div class="rejectedRobot"></div> &nbsp;&nbsp; Outstanding</span></td>
                                <td>
                                  <div id="paymentSection">
                                    <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload" onClick="paymentRef()">
                                        <span class="text">Upload</span>
                                        @if (!$agent->isMobile())
                                            <i class="fas fa-file-upload" ></i>
                                        @endif
                                        
                                    </label>
                                  </div>
                                    </h3>
                                    <!-- <input type="file" id="{{$file->fileId}}" hidden onChange="UploadDocument('{{$file->fileId}}')"/> -->
                                    </td>
                              @else
                                <td><span class="flex"><div class="rejectedRobot"></div> &nbsp;&nbsp; Outstanding</span></td>
                                <td>
                                    <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload">
                                        <span class="text">Upload</span>
                                        @if (!$agent->isMobile())
                                            <i class="fas fa-file-upload" ></i>
                                        @endif
                                        
                                    </label>
                                    </h3>
                                    <input type="file" id="{{$file->fileId}}" hidden onChange="UploadDocument('{{$file->fileId}}')" accept="image/*, application/pdf" />
                                    </td>
                                @endif
                        @elseif($file->status == 'Rejected')

                        @if($file->fileId == 'proof_of_payment')
                            
     
                                <td><span class="flex"><div class="rejectedRobot"></div> &nbsp;&nbsp; Invalid Document Please re-upload</span></td>
                                <td>
                                  <div id="paymentSection">
                                    <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload" onClick="paymentRef()">
                                        <span class="text">Upload</span>
                                        @if (!$agent->isMobile())
                                            <i class="fas fa-file-upload" ></i>
                                        @endif
                                        
                                    </label>
                                  </div>
                                    </h3>
                                    <!-- <input type="file" id="{{$file->fileId}}" hidden onChange="UploadDocument('{{$file->fileId}}')"/> -->
                                    </td>
                              @else
                              <td><span class="flex"><div class="rejectedRobot"></div> &nbsp;&nbsp; Invalid Document Please re-upload</span></td>
                            <td>
                                <label for="{{$file->fileId}}" id="{{$file->fileId}}Section" class="outstandingUpload">
                                    <span class="text">Upload</span>
                                    @if (!$agent->isMobile())
                                        <i class="fas fa-file-upload" ></i>
                                    @endif
                                    
                                </label>
                                </h3>
                                <input type="file" id="{{$file->fileId}}" hidden onChange="UploadDocument('{{$file->fileId}}')" accept="image/*, application/pdf" />
                                </td>
                              @endif
                            
                        @elseif($file->status == 'Uploaded')
                            <td><span class="flex"><div class="pendingRobot"></div> &nbsp;&nbsp; Pending Approval</span></td>
                            <td>
                                <a href="/storage/{{$file->fileUrl}}" style="text-decoration:inherit" target="_blank"><span class="outstandingUpload" style="padding:5px 67px;">View</span></a>
                                </td>
                        @elseif($file->status == 'Approved')
                            <td><span class="flex"><div class="approvedRobot"></div> &nbsp;&nbsp; Approved</span></td>
                            <td>
                              @if($file->fileUrl !== null && $file->fileUrl !== "")
                                <a href="/storage/{{$file->fileUrl}}" style="text-decoration:inherit" target="_blank"><span class="outstandingUpload" style="padding:5px 67px;">View</span></a>
                              @endif
                            </td>
                        @endif
                    
                </tr>
            @endforeach

           
        </tbody>
    </table>
    <br/>
</div>

<script>

$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

  $(document).ready( function () {
        $('#documents_table').DataTable({
          "order": []
        });
    } );

    function UploadDocument(field){
        // console.log("JG")

        if(document.getElementById(field).files[0].type == "application/pdf" || document.getElementById(field).files[0].type.split("/")[0] == "image"){

          $("#" + field + "Section").empty().append('<i class="fas fa-circle-notch fa-spin"></i>');

          let userId = "<?php echo Auth::user()->id;?>";

          var form_data = new FormData();
          form_data.append("file", document.getElementById(field).files[0]);
          form_data.append("userUid", userId);
          form_data.append("fileType", field);
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
        }else{
            Swal.fire(
                'Error',
                'Only PDF and Images are allowed',
                'error'
            )
        }
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
        showCancelButton:currentStep>0,
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
      console.log("Values", values)
      $.ajax({
        type:"post",
        url: "/UpdatePaymentRef",
        data: {
          bookingId: "{{$user->bookingId}}",
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


@endsection