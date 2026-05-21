@extends('layouts/commonMaster')

@php
  /* Display elements */
  $contentNavbar = true;
  $containerNav = $containerNav ?? 'container-xxl';
  $isNavbar = $isNavbar ?? true;
  $isMenu = $isMenu ?? true;
  $isFlex = $isFlex ?? false;
  $isFooter = $isFooter ?? true;

  /* HTML Classes */
  $navbarDetached = 'navbar-detached';

  /* Content classes */
  $container = $container ?? 'container-xxl';
@endphp

@section('layoutContent')
  <div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
    <div class="layout-container">

      @if ($isMenu)
        @include('layouts/sections/menu/verticalMenuAuditor')
      @endif

      <div class="layout-page">
        @if ($isNavbar)
          @include('layouts/sections/navbar/navbarAuditor')
        @endif

        <div class="content-wrapper">
          @if ($isFlex)
            <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
          @else
            <div class="{{ $container }} flex-grow-1 container-p-y">
          @endif

            @yield('content')

          </div>

          <div class="content-backdrop fade"></div>
        </div>
      </div>
    </div>

    @if ($isMenu)
      <div class="layout-overlay layout-menu-toggle"></div>
    @endif
    <div class="drag-target"></div>
  </div>
@endsection
