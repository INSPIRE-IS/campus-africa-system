<div class="container form-card">
    <form action="/update-profile" method="post">
    @csrf
        <br/>
        <h1 class="header fs-title">Applicant Details</h2>
        <br/>

        <div class="form-field">
            <label>First Name *</label>
            <input type="text" name="firstName" id="firstName" value="{{Auth::user()->userFirstName}}" required>
            <input type="hidden" name="userUid" id="userUid" value="{{Auth::user()->id}}" required>
        </div>

        <div class="form-field">
            <label>Last Name</label>
            <input type="text" name="lastName" id="lastName" value="{{Auth::user()->userLastname}}">
        </div>

        <div class="form-field">
            <label>Title</label>
            <select name="title" id='title'>
                <option value="Mr" <?php if(Auth::user()->userTitle == "Mr"){echo "selected";} ?>>Mr</option>
                <option value="Ms" <?php if(Auth::user()->userTitle == "Ms"){echo "selected";} ?>>Ms</option>
                <option value="Mrs" <?php if(Auth::user()->userTitle == "Mrs"){echo "selected";} ?>>Mrs</option>
                <option value="Miss" <?php if(Auth::user()->userTitle == "Miss"){echo "selected";} ?>>Miss</option>
            </select>
        </div>

        <div class="form-field">
            <label>Date of Birth</label>
            <input type="date" name="dob" id="dob" value="{{Auth::user()->userDob}}">
        </div>

        <div class="form-field">
            <label>Cellphone Number</label>
            <input type="text" name="cell_no" id="cell_no" value="{{Auth::user()->userCell}}">
        </div>

        <div class="form-field">
            <label>Email Address *</label>
            <input type="email" name="email" id="email" required value="{{Auth::user()->userEmail}}">
        </div>

        <div class="form-field">
            <label>I.D Number</label>
            <input type="text" name="idNumber" id="idNumber" value="{{Auth::user()->userId}}">
        </div>

        <div class="form-field">
            <label>Nationality</label>
            <select name="nationality" id='nationality'>
                <option value="South African" <?php if(Auth::user()->userNationality == "South African"){echo "selected";}?>>South African</option>
            </select>
        </div>

        <div class="form-field">
            <label>Passport Number</label>
            <input type="text" name="passportNo" id="passportNo" value="{{Auth::user()->userPassport}}">
        </div>

        <div class="form-field">
            <label>Institution of Study</label>
            <select name="institStudy" id="institStudy">
                <option value="Choose one" selected disabled>Choose one</option>
            </select>
        </div>

        <div class="form-field">
            <label>Student Number</label>
            <input type="text" name="studNumber" id="studNumber" value="{{Auth::user()->studentNo}}">
        </div>

        <div class="form-field">
            <label>Insitution Campus</label>
            <select name="institCampus" id="institCampus">
                <option value="Choose one" selected diabled>Choose one</option>
            </select>
        </div>

        <div class="form-field">
            <label>Faculty</label>
            <select name="faculty" id="faculty">
                <option value="Choose one" disabled selected>Choose one</option>
            </select>
        </div>

        <div class="form-field">
            <label>Year</label>
            <select name="year" id="year">
                <option value="2022" <?php if(Auth::user()->year == "2022"){echo "selected";}?>>2022</option>
                <option value="2021" <?php if(Auth::user()->year == "2021"){echo "selected";}?>>2021</option>
                <option value="2020" <?php if(Auth::user()->year == "2020"){echo "selected";}?>>2020</option>
                <option value="2019" <?php if(Auth::user()->year == "2019"){echo "selected";}?>>2019</option>
                <option value="2018" <?php if(Auth::user()->year == "2018"){echo "selected";}?>>2018</option>
                <option value="2017" <?php if(Auth::user()->year == "2017"){echo "selected";}?>>2017</option>
            </select>
        </div>

        <h1 class="fs-title">Sponsor / Burser Information</h2>
        <h2 class="fs-subtitle">Parent / guardians / sponsor details</h4>
        <br/>

        <div class="form-field">
            <label>First Name</label>
            <input type="text" name="pFirstName" id="pFirstName" value="{{Auth::user()->sponFirstName}}">
        </div>

        <div class="form-field">
            <label>Last Name</label>
            <input type="text" name="pLastName" id="pLastName" value="{{Auth::user()->sponLastName}}">
        </div>

        <div class="form-field">
            <label>Cellphone Number</label>
            <input type="text" name="pCell_no" id="pCell_no" value="{{Auth::user()->sponCell}}">
        </div>

        <div class="form-field">
            <label>Email Address</label>
            <input type="email" name="pEmail" id='pEmail' value="{{Auth::user()->sponEmail}}">
        </div>

        <div class="form-field">
            <label>I.D Number</label>
            <input type="text" name="pIdNumber" id="pIdNumber" value="{{Auth::user()->sponId}}">
        </div>

        <div class="form-field">
            <br/>
        </div>

        <h4 class="fs-subtitle">Bursar Details</h4>
        <br/>

        <div class="form-field">
            <label>Company Name</label>
            <input type="text" name="bCompanyName" id='bCompanyName' value="{{Auth::user()->burCompanyName}}">
        </div>

        <div class="form-field">
            <label>Registration Number</label>
            <input type="text" name="bRegistrationNo" id="bRegistrationNo" value="{{Auth::user()->burRegNo}}">
        </div>

        <div class="form-field">
            <label>Contact Person</label>
            <input type="text" name="bContactPerson" id="bContactPerson" value="{{Auth::user()->burContactPerson}}">
        </div>

        <div class="form-field">
            <label>Contact Number</label>
            <input type="text" name="bContactNumber" id="bContactNumber" value="{{Auth::user()->burContactNo}}">
        </div>

        <div class="form-field">
            <label>Email Address</label>
            <input type="email" name="bEmail" id="bEmail" value="{{Auth::user()->burEmail}}">
        </div>

        <div class="form-field">
            <br/>
        </div>

        <div class="form-field">
            <label>First Name</label>
            <input type="text" name="kFirstName" id="kFirstName" value="{{Auth::user()->kinFirstName}}">
        </div>
        <div class="form-field">
            <label>Last Name</label>
            <input type="text" name="kLastName" id="kLastName" value="{{Auth::user()->kinLastName}}">
        </div>

        <div class="form-field">
            <label>Title</label>
            <select name="kTitle" id='kTitle'>
                <option value="Mr" <?php if(Auth::user()->kinTitle == "Mr"){ echo "selected";} ?>>Mr</option>
                <option value="Ms" <?php if(Auth::user()->kinTitle == "Ms"){ echo "selected";} ?>>Ms</option>
                <option value="Mrs" <?php if(Auth::user()->kinTitle == "Mrs"){ echo "selected";} ?>>Mrs</option>
                <option value="Miss" <?php if(Auth::user()->kinTitle == "Miss"){ echo "selected";} ?>>Miss</option>
            </select>
        </div>

        <div class="form-field">
            <label>I.D Number</label>
            <input type="text" name="kIdNumber" id="kIdNumber" value="{{Auth::user()->kinId}}">
        </div>

        <div class="form-field-big">
            <label>Physical Address</label>
            <input type="text" placeholder="Street No" name="kAddress1" id="kAddress1" style="width:98% !important" value="{{Auth::user()->kinAddressStr}}">
        </div>
        <div class="form-field-big">
            <input type="text" placeholder="Suburb" name="kAddress2" id="kAddress2" style="width:98% !important" value="{{Auth::user()->kinAddressSuburb}}">
        </div>
        <div class="form-field">
            <input type="text" placeholder="City" name="kAddressCity" id='kAddressCity' value="{{Auth::user()->kinAddressCity}}">
        </div>
        <div class="form-field">
            <input type="text" placeholder="postal Code" name="kAddressPostal" id="kAddressPostal" value="{{Auth::user()->kinAddressPost}}">
        </div>

        <div class="form-field">
            <label>Relationship</label>
            <input type="text" name="kRelationship" id="kRelationship" value="{{Auth::user()->kinRelationship}}">
        </div>

        <div class="form-field">
            <label>Telephone Number</label>
            <input type="text" name="kTeleNo" id='kTeleNo' value="{{Auth::user()->kinTelNo}}">
        </div>

        <div class="form-field">
            <label>Cellphone Number</label>
            <input type="text" name="kCellNo" id='kCellNo' value="{{Auth::user()->kinCellNo}}">
        </div>

        <div class="form-field">
            <label>Work Number</label>
            <input type="text" name="kWorkNo" id="kWorkNo" value="{{Auth::user()->kinWorkNo}}">
        </div>

        <div class="form-field">
            <label>Email Address</label>
            <input type="email" name="kEmail" id="kEmail" value="{{Auth::user()->kinEmail}}">
        </div>

        <div class="form-field">
            <br/>
        </div>

        <input type="submit" value="Update" class="profileSubmit"/>
    </form>
</div>