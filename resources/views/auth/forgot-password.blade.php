<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password - SIPEDIN</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        :root {
            --siped-700: #147a52;
            --siped-600: #1f9d62;
            --siped-500: #2bb673;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Nunito", sans-serif;
        }

        .bg-gradient-primary {
            background:
                radial-gradient(1000px 500px at 20% 10%, rgba(43, 182, 115, .25), transparent 60%),
                radial-gradient(900px 500px at 80% 90%, rgba(20, 122, 82, .25), transparent 55%),
                linear-gradient(135deg, #0b3d2e, #1f9d62);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 22px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 28px 80px rgba(0, 0, 0, .25);
        }

        @media (min-width: 992px) {
            .login-card {
                max-width: 560px;
            }
        }

        .login-header {
            background: linear-gradient(135deg, var(--siped-700), var(--siped-500));
            color: #fff;
            padding: 44px 40px;
            text-align: center;
        }

        .login-header h1 {
            font-weight: 900;
            font-size: 2rem;
        }

        .login-body {
            padding: 48px 44px 36px;
        }

        .brand-logo {
            width: 88px;
            height: 88px;
            object-fit: contain;
            border-radius: 18px;
            background: #fff;
            padding: 10px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, .12);
        }

        .form-control-user {
            border-radius: 14px !important;
            padding: 1rem;
            border: 1px solid #e7eaf0;
            background: #fbfcfe;
        }

        .form-control-user:focus {
            border-color: rgba(43, 182, 115, .6);
            box-shadow: 0 0 0 .2rem rgba(43, 182, 115, .2);
            background: #fff;
        }

        .btn-user {
            border-radius: 14px !important;
            padding: .95rem;
            font-weight: 800;
        }

        .btn-siped {
            background: linear-gradient(135deg, var(--siped-700), var(--siped-500));
            border: none;
        }
    </style>
</head>

<body class="bg-gradient-primary">

    <div class="login-card">
        <div class="login-header">
            <h1>SIPEDIN</h1>
            <p>Lupa Kata Sandi</p>
        </div>

        <div class="login-body text-center">
            <img src="{{ asset('img/sumenep.png') }}" class="brand-logo mb-4" alt="Logo">

            <p class="small text-muted text-left">
                Masukkan email akun Anda. Sistem akan mengirimkan link untuk membuat kata sandi baru.
            </p>

            @if (session('status'))
                <div class="alert alert-success small text-left">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger small text-left">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="user text-left" method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group">
                    <label class="small font-weight-bold text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control form-control-user @error('email') is-invalid @enderror"
                        placeholder="nama@instansi.go.id" required>
                    @error('email')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-user btn-block text-white btn-siped">
                    <i class="fas fa-paper-plane mr-2"></i> Kirim Link Reset
                </button>
            </form>

            <hr>

            <div class="text-center">
                <a class="small" href="{{ route('login') }}">Kembali ke Login</a>
            </div>
        </div>
    </div>

</body>

</html>
