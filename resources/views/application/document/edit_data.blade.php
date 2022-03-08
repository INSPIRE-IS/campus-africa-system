<div class="container form-card" style="margin-top: 20px;">
    <form action="/update-student" method="post" id="editDocData" autocomplete="off">
        @csrf

        @if ($type == "copyID")
            <div class="form-field">
                <label>First Name * <span id="firstName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                <input type="text" name="firstName" id="firstName" value="{{$user->userFirstName}}" class="required">
            </div>

            <div class="form-field">
                <label>Last Name * <span id="lastName_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                <input type="text" name="lastName" id="lastName" value="{{$user->userLastname}}" class="required">
            </div>            

            <div class="form-field">
                <label>Nationality * <span id="nationality_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                <select name="nationality" id='nationality' class="required">
                    <option value="" selected disabled>Please select one</option>
                    @foreach($lookups as $lookup)
                        @if($lookup->group_id == "5")
                            <option value="{{$lookup->lookup_name}}" <?php if($user->userNationality == $lookup->lookup_name){echo "selected";}?>>{{$lookup->lookup_name}}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-field <?php if($user->userNationality !== "South Africa"){echo "hidden";} ?>" id="idField">
                <label>I.D Number * <span id="idNumber_error" class="hidden" style="color:red"><small>Not a valid ID number</small></span></label>
                <input type="text" name="idNumber" id="idNumber" value="{{$user->userId}}" <?php if($user->userNationality == "South Africa"){echo "class='required'";} ?>>
            </div>

            <div class="form-field <?php if($user->userNationality == "South Africa"){echo "hidden";} ?>" id="passportField">
                <label>Passport Number * <span id="passport_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                <input type="text" name="passportNo" id="passportNo" value="{{$user->userPassport}}" <?php if($user->userNationality !== "South Africa"){echo 'class="required"';} ?>>
            </div>

            <div class="form-field">
                <label>Date of Birth * <span id="dob_error" class="hidden" style="color:red"><small>Not a valid date of birth</small></span></label>
                <input type="date" name="dob" id="dob" value="{{$user->userDob}}" max="<?php echo date('Y-m-d'); ?>" class="required">
            </div>
        @endif

        <input type="hidden" name="userUid" value="{{$user->id}}">
        <input type="hidden" name="type" value="{{$type}}">

        <div class="flex" style="width: 100%;">
            <input type="submit" value="Update" class="profileSubmit" style="opacity:1;"/>
            <input type="button" value="Cancel" id="btn_cancel_edit_data">
        </div>
    </form>
</div>

<script>
    jQuery(document).ready( function($) {
        $('#btn_cancel_edit_data').on('click', function(e) {
            $('#infoButtons').show();
            $('#edit_data').html('');
        });

        $('#editDocData').on('submit', function(e) {
            var req = 1;

            $('#editDocData .required').each( function(){
                if($(this).val() === "" || $(this).val() === null){
                    req = 0

                    console.log($(this)[0]["id"])

                    $(this).addClass("fieldError")
                    id = $(this)[0]["id"]
                    $("#"+id+"_error").removeClass("hidden")
                }
            });

            if (req === 1) {
                $.ajax({
                    type: "post",
                    url: "/documents/save-data",
                    data: $(this).serialize(),

                    success: function (data) {

                        if (data === "done") {
                            refreshActions();
                            refresh_this_modal();

                        } else {
                            alert("error");
                            console.log(data);
                        }

                    }
                });
            } else {
                Swal.fire(
                    'Blank Fields',
                    'Please fill in all Required Fields!',
                    'error'
                )

                return false;
            }

            e.preventDefault();
            return false;
        });

        $("#nationality").on('change', function(){
            if($("#nationality").val() == "South Africa"){
                $("#passportField").addClass('hidden')
                $("#idField").removeClass('hidden')

                $("#passportNo").removeClass("required")
                $("#passportNo").removeClass("fieldError")
                $("#idNumber").addClass("required")
            }else{
                $("#idField").addClass('hidden')
                $("#passportField").removeClass('hidden')

                $("#passportNo").addClass("required")
                $("#idNumber").removeClass("required")
                $("#idNumber").removeClass("fieldError")
            }
        })
    });
</script>