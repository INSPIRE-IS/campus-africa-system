@extends('layouts.app')

@section('content')
<div class="yellowGradientSmall">
    <div class="container">

        <nav class="navBar">
            <div class="leftMenu">
                <img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91">
            </div>

            <div class="rightMenu">
                
                <a href="https://campusafrica.co.za/contact-us/" target="_blank"><button type="button" class="contactButton">contact us</button></a>
            </div>
        </nav>

        <div class="loginHeader">
            <h1 class="booking">Reset Password</h1>
        </div>
        
    
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">

                <div class="card-body">

                <div class="cards">
                        <div class="whiteCard mainBlock" style="margin-bottom:50px;">

                            <form method="POST" action="/update-password/{{$userCustomId}}" class="cardForm">
                                @csrf

                                <div class="form-group row">
                                <h2 class="cardHeader"><label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label></h2>

                                    <div class="col-md-6">
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $user->userEmail}}" required disabled style="padding:10px;">

                                    </div>
                                </div>

                                <div class="form-group row">
                                <h2 class="cardHeader"><label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label></h2>

                                    <div class="col-md-6">
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" autofocus style="padding:10px;">
                                    </div>
                                </div>

                                <div class="form-group row">
                                <h2 class="cardHeader"><label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label></h2>

                                    <div class="col-md-6">
                                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                        @if (\Session::has('error'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{!! \Session::get('error') !!}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group row mb-0">
                                    <div class="col-md-6 offset-md-4">
                                        <button type="submit" class="applyButton" style="margin-top:15px;">
                                            {{ __('Reset Password') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    
                </div>
            </div>
        </div>
    </div>
</div>
<div class="clearSpaceLoginPage"></div>
@endsection
