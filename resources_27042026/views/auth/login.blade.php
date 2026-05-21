@extends('layouts/blankLayout')

@section('title', 'SPTJM Online - Login')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}">
<style>
    /* Custom Style untuk meniru desain referensi (ringkas/dikurangi ukuran) */
    body, html {
        height: 100%;
        overflow-x: hidden;
    }

    .login-container {
        min-height: 100vh;
        min-height: 100dvh; /* better on mobile browsers with dynamic address bar */
        width: 100%;
        position: relative;
        /* Menggunakan gambar background yang menyesuaikan layar */
        background-image: url("{{ $loginBackgroundUrl ?? asset('background/background_login.png') }}");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
    /* Batasi lebar konten utama agar tidak melebar di monitor sangat besar */
    .login-container .row {
        max-width: 1200px;
        margin: 0 auto; /* center */
        width: 100%;
        padding-left: 1rem;
        padding-right: 1rem;
        min-height: 100vh;
        min-height: 100dvh;
    }
    @media (min-width: 1600px) {
        .login-container .row { max-width: 1400px; }
    }

    /* Area Kiri (Putih di gambar) */
    .left-section {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
        padding: clamp(5.5rem, 11vh, 9.5rem) clamp(1rem, 3vw, 2rem);
        color: #333; /* Warna teks gelap */
        text-align: center;
    }

    /* Area Kanan (Biru di gambar) */
    .right-section {
        display: flex;
        flex-direction: column;
        justify-content: flex-start; /* letakkan konten dari atas sehingga padding-top menurunkan posisi */
        padding: clamp(5.5rem, 11vh, 8.5rem) clamp(1rem, 3vw, 1.5rem) clamp(1rem, 3vh, 1.5rem);
        color: #fff; /* Warna teks putih agar kontras dengan background biru */
    }

    /* Menghilangkan style Card bawaan template agar transparan menyatu dengan background */
    .authentication-inner {
        max-width: min(380px, 90vw) !important;
        width: 100%;
        margin: 0 auto;
    }
    .card {
        background: transparent !important;
        box-shadow: none !important;
        border: 0;
    }
    .card-body {
        padding: 1rem !important;
    }

    /* Styling Input agar lebih kecil (Pill) */
    .form-control, .form-select, .input-group-text {
        border-radius: 999px !important;
        border: 1px solid #ced4da;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
        height: clamp(2.1rem, 2.4vw, 2.4rem);
        font-size: clamp(0.85rem, 1.4vw, 0.95rem);
    }

    /* Ensure select (tahun) appears as a solid white control */
    .right-section .form-select {
        background: #fff !important;
        color: #212529 !important; /* default input text black */
        -webkit-appearance: none !important;
        appearance: none !important;
        background-image: none !important;
        outline: none !important;
        box-shadow: none !important;
        border-radius: 999px !important;
        height: clamp(2.1rem, 2.4vw, 2.4rem);
        padding-left: 0.75rem;
    }

    /* Option styling (some browsers limit styling of <option>) */
    .right-section .form-select option {
        background: #fff;
        color: #212529;
    }

    /* Remove native dropdown arrow in IE/Edge */
    .right-section .form-select::-ms-expand {
        display: none;
    }

    /* Remove focus outlines and shadows on select */
    .right-section .form-select:focus {
        outline: none;
        box-shadow: none;
    }

    /* Khusus input group (password) */
    .input-group .form-control {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    .input-group .input-group-text {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        background: #fff;
        border-left: 0;
        height: clamp(2.1rem, 2.4vw, 2.4rem);
        padding: 0 0.5rem;
        font-size: clamp(0.8rem, 1.3vw, 0.9rem);
    }

    /* Ensure username and password inputs have solid white background and dark text */
    .right-section input#login,
    .right-section input#password,
    .right-section .form-control,
    .right-section .form-select {
        background: #fff !important;
        color: #212529 !important; /* input text black */
        -webkit-text-fill-color: #212529 !important;
        opacity: 1 !important;
    }

    /* Placeholder color for inputs */
    .right-section input::placeholder,
    .right-section textarea::placeholder {
        color: #9aa0a6 !important; /* lighter gray for placeholders */
        opacity: 1 !important;
    }
    .right-section input::-webkit-input-placeholder { color: #9aa0a6 !important; }
    .right-section input:-ms-input-placeholder { color: #9aa0a6 !important; }
    .right-section input::-ms-input-placeholder { color: #9aa0a6 !important; }

    /* Make the empty/default select option appear as placeholder (gray) */
    .right-section .form-select option[value=""] { color: #9aa0a6; }

    /* Tombol Login Hijau (lebih kecil) */
    .btn-success-custom {
        background-color: #4CD964; /* Hijau muda */
        color: #ffffff !important;
        border: none;
        border-radius: 999px;
        height: clamp(2.3rem, 2.7vw, 2.6rem);
        font-weight: 700;
        font-size: clamp(0.85rem, 1.5vw, 0.9rem);
        padding: 0 clamp(0.9rem, 2.1vw, 1.2rem);
    }
    .btn-success-custom:hover {
        background-color: #3AC455; /* sedikit lebih gelap saat hover */
        color: #ffffff !important;
    }

    /* Teks Header (diperkecil) */
    .welcome-text {
        font-weight: 700;
        color: #4a90e2; /* Biru muda sesuai teks WELCOME BACK */
        font-size: clamp(1.35rem, 2.2vw, 1.7rem);
    }
    .sub-welcome {
        color: #6c757d;
        font-size: clamp(0.85rem, 1.5vw, 1rem);
    }

    .login-header {
        font-weight: 700;
        color: #fff;
        font-size: clamp(1.1rem, 2vw, 1.35rem);
        text-transform: uppercase;
        text-align: center;
        margin-bottom: 1rem;
    }
    .login-subheader {
        color: #e0e0e0;
        text-align: center;
        margin-bottom: 0.8rem;
        font-size: clamp(0.75rem, 1.3vw, 0.85rem);
    }

    /* Label form di sisi kanan dibuat putih */
    .form-label {
        color: #fff;
        margin-left: 8px;
        text-transform: none !important;
        font-size: clamp(0.8rem, 1.4vw, 0.9rem);
    }
    
    .text-link-white {
        color: #fff !important;
        text-decoration: underline;
    }

    /* Reduce vertical gaps between form fields on right side */
    .right-section .mb-3,
    .right-section .mb-4 {
        margin-bottom: clamp(0.35rem, 0.9vh, 0.6rem) !important;
    }
    /* Increase gap between Tahun select (.mb-4) and the login button */
    .right-section .mb-4 { margin-bottom: clamp(0.9rem, 1.8vh, 1.25rem) !important; }

    /* Responsive adjustments */
    @media (max-width: 1199px) {
        .left-section {
            padding: clamp(2.3rem, 6vh, 3.2rem) 1.5rem;
        }
        .right-section {
            padding: clamp(2rem, 5vh, 3rem) 1.5rem 1.5rem;
        }
    }

    @media (max-width: 991px) {
        .login-container {
            background-size: cover;
            background-position: center;
        }
        .left-section { display: none; }

        /* Mobile: hide all logos on login page */
        .login-corner-logos,
        .logo-row,
        .sptjm-logo {
            display: none !important;
        }

        .form-control, .form-select, .input-group-text {
            height: clamp(2.4rem, 5vw, 2.7rem);
            font-size: clamp(0.9rem, 2.8vw, 1rem);
        }
        .btn-success-custom {
            height: clamp(2.5rem, 5.2vw, 2.8rem);
            font-size: clamp(0.9rem, 2.8vw, 1rem);
        }
    }

    /* Additional adjustments for tablet and monitor: push content further down */
    @media (min-width: 768px) {
        .left-section {
            padding: clamp(6rem, 12vh, 10rem) clamp(1rem, 3vw, 2rem);
        }
        .right-section {
            padding: clamp(6rem, 12vh, 9.5rem) clamp(1rem, 3vw, 1.5rem) clamp(1rem, 3vh, 1.5rem);
        }
    }

    @media (min-width: 1200px) {
        .left-section {
            padding: clamp(6.5rem, 13vh, 11rem) clamp(1rem, 3vw, 2rem);
        }
        .right-section {
            padding: clamp(6.5rem, 13vh, 10.5rem) clamp(1rem, 3vw, 1.5rem) clamp(1rem, 3vh, 1.5rem);
        }
    }

    /* smaller logo helper */
    .logo-small { height: clamp(40px, 6vh, 60px); }
    /* SPTJM header logo on login */
    /* Make SPTJM header logo span the same width as the username field */
    .sptjm-logo {
        display: block;
        width: 90%;       /* match container width (same as input fields which are 100%) */
        max-width: 90%;
        height: auto;      /* preserve aspect ratio */
        max-height: clamp(60px, 12vh, 140px); /* limit excessive height */
        object-fit: contain;
        margin: 0 auto 0.6rem;
    }
</style>
@endsection

@section('content')
<div class="login-container">
    @php
        $loginHeaderMode = $loginHeaderMode ?? 'default';
    @endphp

    @if ($loginHeaderMode === 'corner')
        <div class="login-corner-logos position-absolute top-0 start-0 p-3 d-flex align-items-center" style="z-index: 10;">
            <img src="{{ asset('assets/img/favicon/logo-lldikti-4.png') }}" height="40" alt="Logo LLDikti" class="logo-small me-2">
            <img src="{{ asset('logo_berdampak.png') }}" height="40" alt="Logo Berdampak" class="logo-small ms-2">
        </div>
    @endif

    <div class="row h-100 g-0">
        
        <div class="col-lg-7 left-section">
            @if ($loginHeaderMode === 'default')
                <div class="d-flex justify-content-center align-items-center mb-3 w-100 logo-row">
                    <img src="{{ asset('assets/img/favicon/logo-lldikti-4.png') }}" height="40" alt="Logo LLDikti" class="logo-small me-2">
                    <img src="{{ asset('logo_berdampak.png') }}" height="40" alt="Logo Berdampak" class="logo-small ms-2">
                </div>

                <div class="w-100 mt-1">
                    <h1 class="welcome-text mb-1">Selamat Datang di SPTJM Online</h1>
                    <p class="sub-welcome">Silahkan Masuk untuk Memulai Aplikasi</p>
                </div>
            @endif
        </div>

        <div class="col-lg-5 right-section">
            <div class="authentication-inner">
                <div class="card">
                    <div class="card-body">
                        
                        <div class="text-center">
                            <img src="{{ asset('sptjm_online.png') }}" alt="SPTJM Online" class="sptjm-logo">
                        </div>

                        @if (session('error'))
                        <div class="alert alert-danger rounded-pill">
                            <i class="bx bx-error-circle me-2"></i> {{ session('error') }}
                        </div>
                        @endif

                        <form id="formAuthentication" action="{{ route('login') }}" method="POST" autocomplete="on">
                            @csrf

                            <div class="mb-3">
                                <label for="login" class="form-label">Username</label>
                                <input type="text" class="form-control" id="login" name="login"
                                    placeholder="Username" required autocomplete="username" autocapitalize="none" spellcheck="false">
                            </div>

                            <div class="mb-3 form-password-toggle">
                                <label class="form-label" for="password">Password</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="Password" aria-describedby="password" required autocomplete="current-password" />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="tahun" class="form-label">Tahun</label>
                                <select id="tahun" name="tahun" class="form-select" required>
                                    <option value="">Pilih Tahun</option>
                                    @foreach($tahun_versi as $th)
                                        <option value="{{ $th }}">{{ $th }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <button class="btn btn-success-custom w-100" type="submit">
                                    Login
                                </button>
                            </div>

                            

                        </form>
                        
                        

                    </div>
                </div>
            </div>
        </div>
        </div>
</div>

<script>
// Opsi tahun versi diisi dari server (hanya tahun Aktif)
</script>
@endsection