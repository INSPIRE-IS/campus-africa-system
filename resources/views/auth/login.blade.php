@extends('layouts.app')

@section('content')


<div class="yellowGradient">
    <div class="container">

        <nav class="navBar">
            <div class="leftMenu">
                <img src="/images/logo/Campus_Africa_Logo.png" alt="Campus Africa Logo" width="92" height="91">
            </div>

            <div class="rightMenu">
                <a href="https://campusafrica.co.za/contact-us/" class="contactButton">contact us</a>
            </div>
        </nav>

        <div class="loginHeader">
            <h1 class="booking">Booking made easy.</h1>
            <p class="bookingText">
                Welcome to our online booking and application platform.<br/> 
                We've tried to make your experience as easy as possible.<br/>
                Register now to get started!
            </p>
        </div>

        <div class="cards">
            <div class="whiteCard mainBlock">
                <h2 class="cardHeader">Apply</h2>
                <h4 class="cardSubHeader">Are you new?</h4>
                <p class="cardText">Before you can view and reserve a room at one of our awesome properties, you'll need to apply and submit all the necessary paperwork. Get started now!</p>
                <?php /*<a href="/register"><button type="button" class="applyButton">apply now!</button></a>*/ ?>
                <a href="/online-application/0/0"><button type="button" class="applyButton">apply now!</button></a>
            </div>

            <div class="whiteCard mainBlock" style="margin-left:11px;">
                <h2 class="cardHeader" >Login</h2>
                <h4 class="cardSubHeader">Already registered with Campus Africa? <br/> <span class="info">Come on in!</span></h4>
                <form class="cardForm" method="POST" action="/login" >
                    @csrf
                    <label>Username</label>
                    <input type="text" name="email" id="email">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong style="color:red">{{ $message }}</strong>
                        </span>
                    @enderror
                    <br/>
                    <label>Password</label>
                    <input type="password" name="password" id="password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong style="color:red">{{ $message }}</strong>
                        </span>
                    @enderror
                    <button type="submit" class="submitButton" value="Login">Login</button>
                    <p class="small">Forgotten your password? <a href="/password/reset" style="color:inherit">Click here</a></p>
                </form>
            </div>
        </div>
        
    
    </div>
</div>


<div class="clearSpaceLoginPage"></div>

<!-- <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">


                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> -->
@endsection
