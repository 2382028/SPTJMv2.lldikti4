@extends('layouts/blankLayout')

@section('title', 'SPTJM Online')

@section('page-style')
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}">
@endsection

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-4">

                <!-- Forgot Password -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                                <img src="{{ asset('assets/img/favicon/logo-lldikti-4.png') }}" height="50"
                                    alt="View Badge User">
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-2">Atur Ulang Kata Sandi? 🔒</h4>
                        <p class="mb-4">Masukkan email Anda dan kami akan mengirimkan petunjuk untuk mengatur ulang kata
                            sandi Anda</p>
                        <form id="formAuthentication" class="mb-3" action="{{ url('/') }}" method="GET">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" class="form-control" id="email" name="email"
                                    placeholder="Enter your email" autofocus>
                            </div>
                            <button class="btn btn-primary d-grid w-100">Kirim Reset Link</button>
                        </form>
                        <div class="text-center">
                            <a href="{{ url('/login') }}" class="d-flex align-items-center justify-content-center">
                                <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                                Kembali ke Login
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /Forgot Password -->
            </div>
        </div>
    </div>
@endsection
