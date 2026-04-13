<style>
    :root {
        --agri-primary: #0f5a3f;
        --agri-secondary: #147a52;
    }

    #welcomeToast.alert-success {
        background-color: #d4edda !important;
        color: #155724 !important;
        border-color: #c3e6cb !important;
    }

    #welcomeToast.alert-success .close {
        color: #155724 !important;
        opacity: .8;
    }

    #welcomeToast.alert-success .close:hover {
        opacity: 1;
    }

    .bg-primary {
        background-color: var(--agri-primary) !important;
    }

    .btn-primary {
        background-color: var(--agri-primary) !important;
        border-color: var(--agri-primary) !important;
        color: #fff !important;
    }

    .btn-primary:hover {
        background-color: var(--agri-secondary) !important;
        border-color: var(--agri-secondary) !important;
    }

    .dropdown-list .dropdown-header {
        background-color: var(--agri-primary) !important;
        color: #ffffff !important;
    }

    .icon-circle.bg-primary {
        background-color: var(--agri-primary) !important;
    }
</style>

@php
    use App\Models\Activity;

    $latestActivities = collect();
    $badgeCount = 0;

    $user = auth()->user();
    $avatarUrl = asset('img/undraw_profile.svg');

    if ($user && !empty($user->avatar)) {
        $avatarUrl = asset('storage/' . $user->avatar);
    }

    if (auth()->check()) {
        $latestActivities = Activity::where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();

        $badgeCount = Activity::where('user_id', auth()->id())
            ->where('created_at', '>=', now()->subDay())
            ->count();
    }

    $badgeText = $badgeCount > 9 ? '9+' : $badgeCount;
@endphp

@if (session('login_success'))
    <div id="welcomeToast" class="alert alert-success alert-dismissible fade show mb-3 shadow"
        style="position: fixed; top: 90px; right: 20px; z-index: 1050;">
        <strong>Selamat datang!</strong> {{ auth()->user()->name ?? 'User' }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>

    <script>
        setTimeout(function() {
            let el = document.getElementById('welcomeToast');
            if (el) el.remove();
        }, 3000);
    </script>
@endif

<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <ul class="navbar-nav ml-auto">

        <li class="nav-item mx-1">
            <a class="nav-link" href="{{ route('spt.index') }}" title="Data SPT">
                <i class="fas fa-file-alt fa-fw"></i>
            </a>
        </li>

        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                <i class="fas fa-bell fa-fw"></i>

                @if ($badgeCount > 0)
                    <span class="badge badge-danger badge-counter">{{ $badgeText }}</span>
                @endif
            </a>

            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow">

                <div class="d-flex justify-content-between align-items-center dropdown-header">
                    <span>AKTIVITAS</span>

                    @if (Route::has('activities.destroyAll'))
                        <form method="POST" action="{{ route('activities.destroyAll') }}"
                            onsubmit="return confirm('Hapus semua aktivitas?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-link text-white p-0">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>

                @forelse($latestActivities as $act)
                    @php
                        $payload = json_decode($act->payload ?? '{}', true);
                        $keterangan = $payload['keterangan'] ?? ucfirst($act->action ?? 'Aktivitas');
                        $redirect = !empty($act->url) ? $act->url : '#';
                    @endphp

                    <a class="dropdown-item d-flex align-items-center" href="{{ $redirect }}">
                        <div class="mr-3">
                            <div class="icon-circle bg-primary">
                                <i class="fas fa-bell text-white"></i>
                            </div>
                        </div>
                        <div class="w-100">
                            <div class="small text-gray-500">
                                {{ $act->created_at?->diffForHumans() }}
                            </div>

                            <span class="font-weight-bold d-block">
                                {{ $keterangan }}
                            </span>

                            <div class="small text-gray-500">
                                {{ strtoupper($act->method ?? '-') }}
                                @if (!empty($act->route))
                                    - {{ $act->route }}
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="dropdown-item text-center small text-gray-500">
                        Belum ada aktivitas
                    </div>
                @endforelse

            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                    {{ auth()->user()->name ?? 'User' }}
                </span>
                <img class="img-profile rounded-circle" src="{{ $avatarUrl }}" alt="Foto Profil">
            </a>

            <div class="dropdown-menu dropdown-menu-right shadow">

                @if (Route::has('profile.edit'))
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                        Profil
                    </a>
                @endif

                <div class="dropdown-divider"></div>

                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>
</nav>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Yakin ingin logout?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                Klik logout untuk keluar dari aplikasi
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Logout</button>
                </form>
            </div>

        </div>
    </div>
</div>
