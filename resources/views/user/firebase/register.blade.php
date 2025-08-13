@extends('layouts.dash')

@section('content')
    <div class="container form-card">
        <div class="onlineTabs">
            <a style="width=200px; margin: 20px 20px 20px 0px;"
                href="https://play.google.com/store/apps/details?id=com.campusafrica.maintenance&pcampaignid=web_share"
                target="_blank" rel="noopener noreferrer">
                <img src="/images/logo/android.png" alt="Play Store Logo" width="140" height="45">
            </a>
            <a style="width=200px; margin: 20px 20px 20px 0px;"
                href="https://apps.apple.com/za/app/campus-africa/id1607476667" target="_blank" rel="noopener noreferrer">
                <img src="/images/logo/apple.png" alt="App Store Logo" width="140" height="45">
            </a>
        </div>
        @if ($errors->any())
            <div style="color: red; margin-bottom: 1rem;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('firebase.register') }}"
            style="display: flex; flex-direction: column; align-items: center;" autocomplete="off">
            @csrf
            <div
                style="flex : 1; display: flex;  width:100%; flex-direction: row; align-items: center; justify-content: center;">
                <div class="form-field">
                    <label>First Name</label>
                    <input type="text" name="first_name" id="first_name" readonly value="<?php echo $user['first_name']; ?>">
                </div>
                <div class="form-field">
                    <label>last Name</label>
                    <input type="text" name="last_name" id="last_name" readonly value="<?php echo $user['last_name']; ?>">
                </div>
            </div>

            <div
                style="flex : 1; display: flex;  width:100%; flex-direction: row; align-items: center; justify-content: center;">
                <div class="form-field">
                    <label>email</label>
                    <input type="text" name="email" id="email" readonly value="<?php echo $user['email']; ?>">
                </div>
                <div class="form-field">
                    <label>phone_number</label>
                    <input type="text" name="phone_number" id="phone_number" readonly value="<?php echo $user['phone_number']; ?>">
                </div>
            </div>
            <div
                style="flex : 1; display: flex;  width:100%; flex-direction: row; align-items: center; justify-content: center;">
                <div class="form-field">
                    <label>Bed Code</label>
                    <input type="text" name="bed_code" id="bed_code" readonly value="<?php echo $user['bed_code']; ?>">
                </div>
                <div class="form-field">
                    <label>Residence</label>
                    <input type="text" name="residence" id="residence" readonly value="<?php echo $user['residence']; ?>">
                </div>
                <div class="form-field">
                    <label>Role</label>
                    <input type="text" name="role" id="role" readonly value="<?php echo $user['role']; ?>">
                </div>
            </div>
            <div
                style="flex : 1; display: flex;  width:100%; flex-direction: row; align-items: center; justify-content: center;">
                <div class="form-field" style="position: relative;">
                    <label>Password *</label>
                    <input type="password" name="password" id="password" required>
                    <span class="toggle-password" data-target="password"
                        style="position:absolute; right:50px; top:38px; cursor:pointer;">
                        <img src="/images/eye-close.png" alt="Eye Icon" width="20" height="20">
                    </span>
                </div>

                <div class="form-field" style="position: relative;">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                    <span class="toggle-password" data-target="confirm_password"
                        style="position:absolute; right:50px; top:38px; cursor:pointer;">
                        <img src="/images/eye-close.png" alt="Eye Icon" width="20" height="20">
                    </span>
                </div>
            </div>
            <div
                style="flex: 1; display: flex; width:100%; flex-direction: row; align-items: center; justify-content: center; margin-bottom:20px;">
                <span style="color:red; font-size:20px;">This will update both your web and mobile
                    password
                    to be the same</span>
            </div>
            @if ($user['uid'] != null)
                <button id="submitBtn" type="submit"
                    style="display:flex; align-items:center; justify-content:center; gap:8px;
                        text-transform:uppercase; background-color:#e74f26; color:white; width:50%; height:37px; 
                        border:none; padding:10px; border-radius:10px; margin-bottom:20px; font-weight:bold;">
                    Update
                </button>
            @else
                <button id="submitBtn" type="submit"
                    style="display:flex; align-items:center; justify-content:center; gap:8px;
                        text-transform:uppercase; background-color:#e74f26; color:white; width:50%; height:37px; 
                        border:none; padding:10px; border-radius:10px; margin-bottom:20px; font-weight:bold;">
                    Submit
                </button>
            @endif
        </form>
    @endsection


    @section('scripts')
        <script defer>
            document.querySelector("form").addEventListener("submit", function() {
                const btn = document.getElementById("submitBtn");

                // Disable button immediately
                btn.disabled = true;

                // Add spinner + text
                btn.innerHTML = `<span class="spinner" style="
                    border: 3px solid rgba(255, 255, 255, 0.3);
                    border-top: 3px solid white;
                    border-radius: 50%;
                    width: 16px;
                    height: 16px;
                    animation: spin 1s linear infinite;"></span> Processing...`;

                // Timeout after 15 seconds
                setTimeout(() => {
                    btn.innerHTML = "Taking too long, please refresh the page and try again";
                    btn.style.backgroundColor = "#999"; // make it look inactive
                }, 15000);
            });

            // Spinner animation
            const style = document.createElement("style");
            style.innerHTML = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }`;
            document.head.appendChild(style);


            document.querySelectorAll(".toggle-password").forEach(icon => {
                icon.addEventListener("click", function() {
                    const targetId = this.getAttribute("data-target");
                    const input = document.getElementById(targetId);
                    if (input.type === "password") {
                        input.type = "text";
                        this.innerHTML =
                            '<img src="/images/eye-open.png" alt="Eye Icon " width="20" height="20">';
                    } else {
                        input.type = "password";
                        this.innerHTML =
                            '<img src="/images/eye-close.png" alt="Eye Icon " width="20" height="20">';
                    }
                });
            });

            // Optional: Password match check
            document.querySelector("form").addEventListener("submit", function(e) {
                const password = document.getElementById("password").value;
                const confirmPassword = document.getElementById("confirm_password").value;
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert("Passwords do not match");
                }
            });
        </script>
    @endsection
