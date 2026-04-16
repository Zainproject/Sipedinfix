@php
    $user = auth()->user();
    $role = $user->role ?? null;

    $isKetua = $role === 'ketua';
    $isSekretaris = $role === 'sekretaris';
    $isBendahara = $role === 'bendahara';
@endphp

<ul class="navbar-nav sidebar sidebar-dark accordion shadow-sm" id="accordionSidebar"
    style="
        background: linear-gradient(180deg, #0b3d2e 0%, #0f5a3f 55%, #147a52 100%);
        border-top-right-radius: 18px;
        border-bottom-right-radius: 18px;
        min-height: 100vh;
    ">

    <a class="sidebar-brand d-flex flex-column align-items-center justify-content-center" href="{{ route('home') }}"
        style="margin-top: 18px; padding-bottom: 16px; text-decoration: none;">

        <img src="{{ asset('img/sumenep.png') }}" alt="Logo" style="height: 42px;">

        <div class="sidebar-brand-text text-white text-center mt-2">
            SIPEDIN
            <div style="font-size: 0.7rem; opacity: 0.9;">
                Sistem Informasi Perintah Dinas
            </div>
        </div>
    </a>

    <br>
    <hr class="sidebar-divider my-0">

    {{-- DASHBOARD: semua role bisa --}}
    <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
        <a class="nav-link text-white {{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">


    @if ($isKetua || $isSekretaris)
        <div class="sidebar-heading text-white-50">Persuratan</div>

        <li class="nav-item {{ request()->routeIs('spt.*') ? 'active' : '' }}">
            <a class="nav-link text-white {{ request()->routeIs('spt.*') ? 'is-active' : '' }}"
                href="{{ route('spt.index') }}">
                <i class="fas fa-file-signature"></i>
                <span>S P T</span>
            </a>
        </li>

        <li class="nav-item {{ request()->routeIs('rekap-surat-keluar.*') ? 'active' : '' }}">
            <a class="nav-link text-white {{ request()->routeIs('rekap-surat-keluar.*') ? 'is-active' : '' }}"
                href="{{ route('rekap-surat-keluar.index') }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Rekap Surat Keluar</span>
            </a>
        </li>

        <hr class="sidebar-divider">
    @endif


    @if ($isKetua || $isBendahara)
        <div class="sidebar-heading text-white-50">Anggaran</div>

        <li class="nav-item {{ request()->routeIs('keuangan.*') ? 'active' : '' }}">
            <a class="nav-link text-white {{ request()->routeIs('keuangan.*') ? 'is-active' : '' }}"
                href="{{ route('keuangan.index') }}">
                <i class="fas fa-wallet"></i>
                <span>Keuangan SPT</span>
            </a>
        </li>

        <li class="nav-item {{ request()->routeIs('dana-masuk.*') ? 'active' : '' }}">
            <a class="nav-link text-white {{ request()->routeIs('dana-masuk.*') ? 'is-active' : '' }}"
                href="{{ route('dana-masuk.index') }}">
                <i class="fas fa-money-check-alt"></i>
                <span>Dana Masuk</span>
            </a>
        </li>

        <li class="nav-item {{ request()->routeIs('rekap-anggaran.*') ? 'active' : '' }}">
            <a class="nav-link text-white {{ request()->routeIs('rekap-anggaran.*') ? 'is-active' : '' }}"
                href="{{ route('rekap-anggaran.index') }}">
                <i class="fas fa-wallet"></i>
                <span>Rekap SPT</span>
            </a>
        </li>

        <hr class="sidebar-divider">
    @endif


    @if ($isKetua || $isSekretaris)
        <div class="sidebar-heading text-white-50">Master Data</div>

        <li class="nav-item {{ request()->routeIs('petugas.*') ? 'active' : '' }}">
            <a class="nav-link text-white {{ request()->routeIs('petugas.*') ? 'is-active' : '' }}"
                href="{{ route('petugas.index') }}">
                <i class="fas fa-users"></i>
                <span>Petugas</span>
            </a>
        </li>

        <li class="nav-item {{ request()->routeIs('poktan.*') ? 'active' : '' }}">
            <a class="nav-link text-white {{ request()->routeIs('poktan.*') ? 'is-active' : '' }}"
                href="{{ route('poktan.index') }}">
                <i class="fas fa-layer-group"></i>
                <span>Poktan</span>
            </a>
        </li>

        <hr class="sidebar-divider">
    @endif


    @if ($isKetua || $isSekretaris)
        <div class="sidebar-heading text-white-50">Utilitas</div>

        <li class="nav-item {{ request()->routeIs('import.*') ? 'active' : '' }}">
            <a class="nav-link text-white {{ request()->routeIs('import.*') ? 'is-active' : '' }}"
                href="{{ route('import.index') }}">
                <i class="fas fa-file-import"></i>
                <span>Import & Export</span>
            </a>
        </li>
    @endif

    <li class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
        <a class="nav-link text-white {{ request()->routeIs('profile.*') ? 'is-active' : '' }}"
            href="{{ route('profile.edit') }}">
            <i class="fas fa-user-cog"></i>
            <span>Profil</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline pb-3">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
