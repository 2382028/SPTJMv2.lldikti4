<!DOCTYPE html>

<html class="light-style layout-menu-fixed" data-theme="theme-default" data-assets-path="{{ asset('/assets') . '/' }}"
    data-base-url="{{ url('/') }}" data-framework="laravel" data-template="vertical-menu-laravel-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>@yield('title') </title>
    <meta name="description"
        content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
    <meta name="keywords"
        content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}">
    <!-- laravel CRUD token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Canonical SEO -->
    <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/icon.png') }}" />
    <!-- Include Styles -->
    @include('layouts/sections/styles')

    <!-- Global zoom: scale overall UI on desktop -->
    <style>
        @media (min-width: 768px) {
            html {
                zoom: 1.0;
            }
        }
    </style>
    <!-- Make tables responsive: allow horizontal scrolling and ensure full-width tables -->
    <style>
        /* wrapper for tables to enable horizontal scroll on small screens */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* ensure tables expand to container width when possible */
        table {
            width: 100% !important;
            table-layout: auto;
        }

        /* make DataTables wrapper allow overflow if needed */
        .dataTables_wrapper {
            width: 100%;
            overflow-x: auto;
        }

        /* small tweak: keep action columns from shrinking too much */
        table th:last-child,
        table td:last-child {
            white-space: nowrap;
        }
    </style>

    <!-- Prevent sidebar transition flicker between page navigations -->
    <script>
        (function () {
            try {
                document.documentElement.classList.add('sptjm-no-menu-transition');
            } catch (e) {
                // ignore
            }
        })();
    </script>
    <style>
        html.sptjm-no-menu-transition .layout-menu,
        html.sptjm-no-menu-transition .layout-menu * {
            transition: none !important;
            animation: none !important;
        }
    </style>

    <!-- Sidebar brand should stay visible when menu scrolls -->
    <style>
        /* Keep the brand/header above the scrollable menu items */
        .layout-menu .app-brand {
            position: sticky;
            top: 0;
            z-index: 10;
            background: inherit;
        }

        /* Ensure shadow doesn't cover brand text */
        .layout-menu .menu-inner-shadow {
            position: sticky;
            top: 0;
            z-index: 9;
            background: inherit;
        }

        /* Menu content stays beneath the brand */
        .layout-menu .menu-inner {
            position: relative;
            z-index: 1;
        }
            .sptjm-dashboard-title {
                font-size: clamp(0.95rem, 2.2vw, 1.25rem);
                line-height: 1.2;
            }
    </style>
    <!-- Include Scripts for customizer, helper, analytics, config -->
    @include('layouts/sections/scriptsIncludes')
</head>

<body>


    <!-- Layout Content -->
    @yield('layoutContent')
    <!--/ Layout Content -->


    <!-- Include Scripts -->
    @include('layouts/sections/scripts')

    {{-- Page-level scripts (from @push('scripts')) --}}
    @stack('scripts')

</body>

</html>
