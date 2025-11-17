@extends('layouts.app')
@section('title', 'Verifikasi Email')
@section('content')
    <section class="signin-section">
        <div class="container-fluid">
            <!-- ========== title-wrapper start ========== -->
            <div class="title-wrapper pt-30">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="title">
                            <h2>Email Verification</h2>
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
                                        Email Verification
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
                                <h1 class="text-primary mb-10">Verify Your Email</h1>
                                <p class="text-medium">
                                    We've sent a verification link to your email address
                                </p>
                            </div>
                            <div class="cover-image">
                                <img src="{{ asset('backend/assets/images/auth/email-verification-image.svg') }}"
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
                            <div class="verification-content">
                                @if (session('resent'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ __('A fresh verification link has been sent to your email address.') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                <div class="verification-icon text-center mb-30">
                                    <i class="lni lni-envelope" style="font-size: 60px; color: #4D44B5;"></i>
                                </div>

                                <h6 class="mb-15 text-center">Check Your Email</h6>
                                <p class="text-sm mb-25 text-center">
                                    {{ __('Before proceeding, please check your email for a verification link.') }}
                                </p>
                                <p class="text-sm mb-30 text-center">
                                    {{ __('If you did not receive the email') }}
                                </p>

                                <form class="d-inline text-center" method="POST"
                                    action="{{ route('verification.resend') }}">
                                    @csrf
                                    <button type="submit" class="main-btn primary-btn btn-hover">
                                        {{ __('Click here to request another') }}
                                    </button>
                                </form>

                                <div class="singin-option pt-40">
                                    <p class="text-sm text-medium text-dark text-center">
                                        Need help? <a href="{{ route('contact') }}">Contact support</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end col -->
            </div>
            <!-- end row -->
        </div>
    </section>

    <style>
        .verification-content {
            padding: 30px 0;
        }

        .verification-icon {
            margin-bottom: 30px;
        }
    </style>
@endsection
