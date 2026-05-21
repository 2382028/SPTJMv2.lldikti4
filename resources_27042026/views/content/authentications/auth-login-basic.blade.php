@extends('layouts/blankLayout')

@section('title', 'SPTJM Online')

@section('page-style')
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}">
@endsection

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Register -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <img src="{{ asset('assets/img/favicon/logo-lldikti-4.png') }}" height="60"
                                alt="View Badge User">
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-2 text-center">Selamat Datang di SPTJM Online</h4>
                        <p class="mb-4 text-center">Silahkan Masuk atau Daftar untuk Memulai Aplikasi</p>

                        <form id="formAuthentication" class="mb-3" action="{{ url('/') }}" method="GET">
                            <div class="mb-3">
                                <label for="email" class="form-label"> Email atau Username</label>
                                <input type="text" class="form-control" id="email" name="email-username"
                                    placeholder="Enter your email or username" autofocus>
                            </div>
                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label" for="password">Kata Sandi</label>
                                    <a href="{{ url('auth/forgot-password-basic') }}">
                                        <small>Lupa Kata Sandi?</small>
                                    </a>
                                </div>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="password" />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="tahun" class="form-label"> Tahun</label>
                                <select id="tahun" class="select2 form-select">
                                    <option value="">Plih Tahun</option>
                                    <option value="2024">2024</option>
                                    <option value="2023">2023</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember-me">
                                    <label class="form-check-label" for="remember-me">
                                        Ingat Saya
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">Masuk Akun</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /Register -->
        </div>
    </div>
    </div>
@endsection
