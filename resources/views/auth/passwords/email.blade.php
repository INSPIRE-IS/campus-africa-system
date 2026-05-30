@extends('layouts.app')

@section('content')

@if ($errors->has('email') && \Illuminate\Support\Str::contains($errors->first('email'), 'cannot be processed currently'))
    <script>
        Swal.fire(
            'Unable to continue',
            @json($errors->first('email')),
            'error'
        );
    </script>
@endif

<div class="yellowGradientSmall">
    <div class="container">

        <nav class="navBar">
            <div class="leftMenu">
                <img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91">
            </div>

            <div class="rightMenu">
                
                <button type="button" class="contactButton">contact us</button>
                <!-- <button type="button" class="menuButton">Menu <i class="fa fa-bars"></i></button> -->
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
                <!-- <div class="card-header">{{ __('Reset Password') }}</div> -->

                <div class="card-body">

                    <div class="cards">
                        <div class="whiteCard mainBlock" style="margin-bottom:50px; height:260px;">


                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('password.email') }}" class="cardForm">
                                @csrf

                                <div class="form-group row">
                                    <h2 class="cardHeader"><label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label></h2>

                                    <div class="col-md-6">
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Email" style="padding:10px;">

                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row mb-0">
                                    <div class="col-md-6 offset-md-4">
                                        <button type="submit" class="applyButton" style="margin-top:15px;">
                                            {{ __('Send Password Reset Link') }}
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
