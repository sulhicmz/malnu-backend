@extends('layouts.app')
@section('title', 'Kirim Reset Password')
@section('content')
    <section class="signin-section">
        <div class="container">
            <!-- ========== title-wrapper start ========== -->
            <div class="title-wrapper pt-30">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="title">
                            <h2>Reset Password</h2>
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
                                        Reset Password
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
                                <h1 class="text-primary mb-10">Forgot Password?</h1>
                                <p class="text-medium">
                                    No worries, we'll send you reset instructions
                                </p>
                            </div>
                            <div class="cover-image">
                                <img src="{{ asset('backend/assets/images/auth/forgot-password-image.svg') }}"
                                    alt="" />
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
                            <h6 class="mb-15">Password Reset</h6>
                            <p class="text-sm mb-25">
                                Enter your email address and we'll send you a link to reset your password.
                            </p>

                            @if (session('status'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('status') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('password.email') }}">
                                @csrf

                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-style-1">
                                            <label for="email">{{ __('Email Address') }}</label>
                                            <input id="email" type="email"
                                                class="@error('email') is-invalid @enderror" name="email"
                                                value="{{ old('email') }}" required autocomplete="email" autofocus
                                                placeholder="Enter your email">
                                            @error('email')
                                                <span class="text-danger text-sm" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- end col -->
                                    <div class="col-12">
                                        <div class="button-group d-flex justify-content-center flex-wrap mt-40">
                                            <button type="submit" class="main-btn primary-btn btn-hover w-100 text-center">
                                                {{ __('Send Password Reset Link') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- end row -->
                            </form>
                            <div class="singin-option pt-40">
                                <p class="text-sm text-medium text-dark text-center">
                                    Remember your password?
                                    <a href="{{ route('login') }}">Back to login</a>
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
