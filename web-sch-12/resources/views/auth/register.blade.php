@extends('layouts.app')
@section('title', 'Register')
@section('content')
    <section class="signin-section">
        <div class="container">
            <!-- ========== title-wrapper start ========== -->
            <div class="title-wrapper pt-30">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="title">
                            <h2>Create Account</h2>
                        </div>
                    </div>
                    <!-- end col -->
                    <div class="col-md-6">
                        <div class="breadcrumb-wrapper">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="#0">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item"><a href="#0">Auth</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        Register
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- ========== title-wrapper end ========== -->

            <div class="row g-0 auth-row">
                <div class="col-lg-6">
                    <div class="auth-cover-wrapper bg-primary-100">
                        <div class="auth-cover">
                            <div class="title text-center">
                                <h1 class="text-primary mb-10">Get Started</h1>
                                <p class="text-medium">
                                    Create your account to access all features
                                </p>
                            </div>
                            <div class="cover-image">
                                <img src="{{ asset('backend/assets/images/auth/signup-image.svg') }}" alt="" />
                            </div>
                            <div class="shape-image">
                                <img src="{{ asset('backend/assets/images/auth/shape.svg') }}" alt="" />
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end col -->
                <div class="col-lg-6">
                    <div class="signin-wrapper">
                        <div class="form-wrapper">
                            <h6 class="mb-15">Registration Form</h6>
                            <p class="text-sm mb-25">
                                Fill in the form to create your account.
                            </p>
                            <form method="POST" action="{{ route('register') }}">
                                @csrf

                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-style-1">
                                            <label for="name">{{ __('Name') }}</label>
                                            <input id="name" type="text" class="@error('name') is-invalid @enderror"
                                                name="name" value="{{ old('name') }}" required autocomplete="name"
                                                autofocus placeholder="Your Name">
                                            @error('name')
                                                <span class="text-danger text-sm" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- end col -->
                                    <div class="col-12">
                                        <div class="input-style-1">
                                            <label for="email">{{ __('Email Address') }}</label>
                                            <input id="email" type="email"
                                                class="@error('email') is-invalid @enderror" name="email"
                                                value="{{ old('email') }}" required autocomplete="email"
                                                placeholder="Email Address">
                                            @error('email')
                                                <span class="text-danger text-sm" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- end col -->
                                    <div class="col-12">
                                        <div class="input-style-1">
                                            <label for="password">{{ __('Password') }}</label>
                                            <input id="password" type="password"
                                                class="@error('password') is-invalid @enderror" name="password" required
                                                autocomplete="new-password" placeholder="Password">
                                            @error('password')
                                                <span class="text-danger text-sm" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- end col -->
                                    <div class="col-12">
                                        <div class="input-style-1">
                                            <label for="password-confirm">{{ __('Confirm Password') }}</label>
                                            <input id="password-confirm" type="password" name="password_confirmation"
                                                required autocomplete="new-password" placeholder="Confirm Password">
                                        </div>
                                    </div>
                                    <!-- end col -->
                                    <div class="col-12">
                                        <div class="button-group d-flex justify-content-center flex-wrap mt-40">
                                            <button type="submit" class="main-btn primary-btn btn-hover w-100 text-center">
                                                {{ __('Register') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- end row -->
                            </form>
                            <div class="singin-option pt-40">

                                <p class="text-sm text-medium text-dark text-center">
                                    Already have an account?
                                    <a href="{{ route('login') }}">Sign In</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end col -->
            </div>
            <!-- end row -->
        </div>
    </section>
@endsection
