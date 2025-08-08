@extends('layouts.dash')

@section('content')
    <div class="container form-card">
        <div class="onlineTabs">
            <div class="tab">Android App</div>
            <div class="tab">iOS App</div>
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
            </div>
            <div
                style="flex : 1; display: flex;  width:100%; flex-direction: row; align-items: center; justify-content: center;">
                <div class="form-field">
                    <label>Password <span id="lastName_error" class="hidden" style="color:red"><small>Min 6
                                Characters</small></span></label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="form-field">
                    <label>Role</label>
                    <input type="text" name="role" id="role" readonly value="<?php echo $user['role']; ?>">
                </div>
            </div>
            @if ($user['uid'] != null)
                <button type="submit"
                    style="text-transform:uppercase; background-color:#e74f26; color:white; width:50%; height:37px; border: none; padding:10px; border-radius:10px; margin-bottom:20px;">
                    Update
                </button>
            @else
                <button type="submit"
                    style="text-transform:uppercase; background-color:#e74f26; color:white; width:50%; height:37px; border: none; padding:10px; border-radius:10px; margin-bottom:20px;">
                    Submit
            @endif
        </form>
    @endsection
