@extends('layouts.admin')

@section('content')

<div class="container form-card">
<form action="/save-residence" method="post" autocomplete="off" id="residence">
    @csrf
        <br/><br/>

        <div class="form-field">
            <label>Residence</label>
            <input type="text" name="residence" id="residenceName" value="<?php if(!empty($propertyData)){echo $propertyData->residence;}?>">
            <!-- <select name="residence" id='residence'>
                <option value="" selected disabled>Select One</option>
                <option value="Braamlofts" <?php if(!empty($propertyData) && $propertyData->residence == "Braamlofts"){echo "selected";}?>>Braamlofts</option>
                <option value="80 Jorissen" <?php if(!empty($propertyData) && $propertyData->residence == "80 Jorissen"){echo "selected";}?>>80 Jorissen</option>
                <option value="49 Jorissen" <?php if(!empty($propertyData) && $propertyData->residence == "49 Jorissen"){echo "selected";}?>>49 Jorissen</option>
                <option value="Dunvista Mansions" <?php if(!empty($propertyData) && $propertyData->residence == "Dunvista Mansions"){echo "selected";}?>>Dunvista Mansions</option>
                <option value="Wynton Joy" <?php if(!empty($propertyData) && $propertyData->residence == "Wynton Joy"){echo "selected";}?>>Wynton Joy</option>
                <option value="126 Siemert" <?php if(!empty($propertyData) && $propertyData->residence == "126 Siemert"){echo "selected";}?>>126 Siemert</option>
                <option value="YMCA" <?php if(!empty($propertyData) && $propertyData->residence == "YMCA"){echo "selected";}?>>YMCA</option>
            </select> -->
            <input type="hidden" name="propertyId" id="propertyId" value="<?php if(!empty($propertyData)){echo $propertyData->id;}else{echo "0";}?>"/>
        </div>

        <div class="form-field">
            <label>Owner</label>
            <input type="text" name="owner" id="owner" value="<?php if(!empty($propertyData)){echo $propertyData->owner;}?>">
        </div>


        <div class="form-field">
            <label>Address</label>
            <input type="text" name="address" id="address" value="<?php if(!empty($propertyData)){echo $propertyData->address;}?>">
        </div>

        <div class="form-field">
            <label>MDA Property Code <span id="building_prefix_error" class="hidden" style="color:red"><small>MDA Property Code already in use</small></span></label>
            <input type="text" name="building_prefix" id="building_prefix" value="<?php if(!empty($propertyData)){echo $propertyData->building_prefix;}?>" onKeyUp="updatePrefix()" maxLength="2">
        </div>

        <!-- <div class="form-field">
            <label>MDA Property Link</label>
            <select name="mda_id" id='mda_id'>
                <option value="" selected disabled>Select One</option>
                {{-- @foreach($mda as $m)
                    <option value="{{$m->PropertyId}}" <?php if(!empty($propertyData) && $propertyData->mda_id == $m->PropertyId){echo "selected";}?>>{{$m->PropertyName}}</option>
                @endforeach --}}
            </select>
        </div>

        <div class="form-field">
            <br/>
        </div> -->

        <div class="form-field">
            <label>Company Registration</label>
            <input type="text" name="company_reg" id="company_reg" value="<?php if(!empty($propertyData)){echo $propertyData->company_reg;}?>">
        </div>

        <div class="form-field">
            <label>VAT Number</label>
            <input type="text" name="company_vat" id="company_vat" value="<?php if(!empty($propertyData)){echo $propertyData->company_vat;}?>">
        </div>

        <hr/>

        <div class="form-field">
            <br/>
            <label>Bank</label>
            <input type="text" name="bank_name" id="bank_name" value="<?php if(!empty($propertyData)){echo $propertyData->bank_name;}?>">
        </div>

        <div class="form-field">
            <br/>
            <label>Account Name</label>
            <input type="text" name="account_name" id="account_name" value="<?php if(!empty($propertyData)){echo $propertyData->account_name;}?>">
        </div>

        <div class="form-field">
            <br/>
            <label>Account Number</label>
            <input type="text" name="account_number" id="account_number" value="<?php if(!empty($propertyData)){echo $propertyData->account_number;}?>">
        </div>

        <!-- <div class="form-field">
            <br/>
            <label>Branch</label>
            <input type="text" name="branch" id="branch" value="<?php if(!empty($propertyData)){echo $propertyData->branch;}?>">
        </div> -->

        <div class="form-field">
            <br/>
            <label>Branch Code</label>
            <input type="text" name="branch_code" id="branch_code" value="<?php if(!empty($propertyData)){echo $propertyData->branch_code;}?>">
        </div>


        <div class="form-field" style="height:150px !important;">
            <br/>
            <hr/>
            <br/>
            <label>Status</label>
            <select name="status" id='status'>
                <option value="0" <?php if(!empty($propertyData) && ($propertyData->status == '0' || $propertyData->status == null)){echo "selected";}?>>Active</option>
                <option value="1" <?php if(!empty($propertyData) && $propertyData->status == '1'){echo "selected";}?>>Inactive</option>
            </select>
        </div>
        <div class="form-field" style="height:150px !important;">
            <br/>
            <hr/>
            <br/>
            <label>Gender <span id="gender_error" class="hidden" style="color:red"><small>Please select at least one</small></label>

            <ul style="list-style-type:none; width:100%">
                <li style="display:inline-flex"><input type="checkbox" id="male" name="male" style="width:27px !important" <?php if(isset($propertyData->male) && $propertyData->male == 1){echo "checked";}?> value='1'><label for="male">&nbsp; &nbsp;Male</label></li>
                <li style="display:inline-flex; margin-left:30px;"><input type="checkbox" id="female" name="female" style="width:27px !important" <?php if(isset($propertyData->female) && $propertyData->female == 1){echo "checked";}?> value='1'><label for="female">&nbsp; &nbsp;Female</label></li>
            </ul>

        </div>
        <div class="form-field" style="height:150px !important;">
            <br/>
            <hr/>
            <br/>
            <label>Active Date</label>
            <input type="date" name="active_date" id="active_date" value="<?php if(!empty($propertyData) && $propertyData->active_date){echo $propertyData->active_date;}?>">
        </div>

        <div class="form-field" style="height:150px !important;">
            <br/>
            <hr/>
            <br/>
            <label>Inactive Date</label>
            <input type="date" name="inactive_date" id="inactive_date" value="<?php if(!empty($propertyData) && $propertyData->inactive_date){echo $propertyData->inactive_date;}?>">
        </div>




        <div class="form-field">
            <br/>
        </div>
        <div class="form-field">
            <br/>
        </div>


        <input type="submit" value="Update" class="profileSubmit" style="opacity:1"/>
    </form>
</div>

<script>

    function updatePrefix(){
        $.ajax({
            type: "post",
            url: "/check-building-prefix",
            data: {building: $("#building_prefix").val(), residence: $("#residenceName").val()},

            success: function (data) {
                if(data == "false"){
                    $("#building_prefix").addClass('fieldError')
                    $("#building_prefix_error").removeClass('hidden')
                }else{
                    $("#building_prefix").removeClass('fieldError')
                    $("#building_prefix_error").addClass('hidden')
                }
            },
        });
    }
    

    $('#residence').submit(function() {
        //gender_error

        if($("#building_prefix").hasClass('fieldError') || (document.getElementById('male').checked == false && document.getElementById('female').checked == false)){

            if(document.getElementById('male').checked == false && document.getElementById('female').checked == false){
                $("#gender_error").removeClass('hidden')
            }
            return false;
        }else{
            return true;
        }

    });

</script>

@endsection
