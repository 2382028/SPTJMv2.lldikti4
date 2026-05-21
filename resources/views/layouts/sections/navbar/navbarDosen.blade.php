@php
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = $navbarDetached ?? '';
@endphp
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme"
    id="layout-navbar">
@endif
@if (isset($navbarDetached) && $navbarDetached == '')
<nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="{{ $containerNav }}">
@endif

        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0" href="javascript:void(0);" aria-label="Toggle menu">
                <i class="bx bx-menu bx-sm" aria-hidden="true"></i>
            </a>
        </div>

        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                    <h5 class="mb-0" style="min-width: 0;">
                        <span class="d-inline d-md-none fw-semibold small text-truncate d-inline-block" style="max-width: 100%;">Dashboard Dosen {{ session('tahun') }}</span>
                        <span class="d-none d-md-inline fw-semibold">
                            Dashboard Dosen Tahun {{ session('tahun') }} -
                            {{ Auth::guard('dosen')->user()->nama_dosen ?? '-' }}
                            @if(!empty(Auth::guard('dosen')->user()->nama_pts))
                                - {{ Auth::guard('dosen')->user()->nama_pts }}
                            @endif
                        </span>
                    </h5>
                </div>
            </div>

            <ul class="navbar-nav flex-row align-items-center ms-auto">
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                        <div class="avatar avatar-online">
                            <img src="{{ asset('assets/img/avatars/user.png') }}" alt class="w-px-40 h-auto rounded-circle">
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar avatar-online">
                                            <img src="{{ asset('assets/img/avatars/user.png') }}" alt class="w-px-40 h-auto rounded-circle">
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="fw-medium d-block">{{ Auth::guard('dosen')->user()->nama_dosen ?? '-' }}</span>
                                        <small class="text-muted">Dosen</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="button" id="btn-logout" class="dropdown-item">
                                    <i class='bx bx-power-off me-2'></i>
                                    <span class="align-middle">Log Out</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

@if (!isset($navbarDetached))
    </div>
@endif
</nav>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const btnLogout = document.getElementById("btn-logout")
  if (!btnLogout) return;

  btnLogout.addEventListener('click', () => {
    Swal.fire({
      title: "Apakah Anda Yakin?",
      text: "Kamu akan logout dan tidak bisa kembali!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Ya, logout!",
      cancelButtonText: "Batal!"
    }).then((result) => {
      if (result.isConfirmed) {
        document.getElementById("logout-form").submit();
      }
    });
  })
})
</script>
