@extends('layouts/blankLayout')

@section('title', 'SPTJM Online')

@section('page-style')
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-misc.css') }}">
@endsection

@section('content')
    <!--Under Maintenance -->
    <div class="container-xxl container-p-y">
        <div class="misc-wrapper">
            <h2 class="mb-2 mx-2">Sedang Dalam Perbaikan!</h2>
            <p class="mb-4 mx-2">
                Mohon maaf atas ketidaknyamanannya, tetapi saat ini kami sedang melakukan perbaikan
            </p>
            <a href="{{ url('/admin/dashboard') }}" class="btn btn-primary">Back to home</a>
            <div class="mt-4">
                <img src="{{ asset('assets/img/illustrations/girl-doing-yoga-light.png') }}" alt="girl-doing-yoga-light"
                    width="500" class="img-fluid">
            </div>
        </div>
    </div>
    <!-- /Under Maintenance -->
@endsection
