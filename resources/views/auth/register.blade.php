<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - SIPEDIN</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        :root {
            --siped-900: #0b3d2e;
            --siped-700: #147a52;
            --siped-500: #2bb673;
            --soft: #f6fbf8;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: "Nunito", sans-serif;
            background: linear-gradient(135deg, var(--siped-900), var(--siped-500));
        }

        .page-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .register-card {
            width: 100%;
            max-width: 900px;
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.18);
            overflow: hidden;
        }

        .register-left {
            min-height: 640px;
            background:
                linear-gradient(135deg, rgba(11, 61, 46, .70), rgba(43, 182, 115, .35)),
                url('{{ asset('img/agri-bg.jpg') }}') center/cover no-repeat;
        }

        .register-right {
            padding: 38px 34px;
            display: flex;
            justify-content: center;
        }

        .form-wrap {
            width: 100%;
            max-width: 460px;
        }

        .page-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .section-box {
            background: var(--soft);
            border-radius: 16px;
            padding: 18px;
            margin-top: 16px;
        }

        .section-title {
            font-weight: 800;
            font-size: 1.1rem;
            color: #6b7280;
            text-align: left;
            margin-bottom: 12px;
        }

        .form-control-user {
            border-radius: 14px !important;
            padding: 12px 14px;
        }

        select.form-control-user {
            height: auto !important;
            color: #495057 !important;
            background-color: #fff !important;
        }

        .btn-siped {
            background: linear-gradient(135deg, var(--siped-700), var(--siped-500));
            border: none;
            border-radius: 14px;
            padding: 12px;
            font-weight: 700;
        }

        .btn-siped:hover {
            opacity: .95;
        }

        .avatar-label {
            cursor: pointer;
            display: inline-block;
            position: relative;
        }

        .avatar-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #eaf5ef;
            border: 3px solid #d1f0df;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0 auto;
        }

        .avatar-plus {
            position: absolute;
            right: 4px;
            bottom: 4px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--siped-500);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .2);
        }

        .login-link {
            text-align: center;
            margin-top: 18px;
        }

        @media (max-width: 991.98px) {
            .register-left {
                display: none;
            }

            .register-right {
                padding: 28px 20px;
            }

            .register-card {
                max-width: 560px;
            }

            .page-title {
                font-size: 1.7rem;
            }
        }
    </style>
</head>

<body>
    <div class="page-wrap">
        <div class="register-card">
            <div class="row no-gutters">
                <div class="col-lg-5 register-left"></div>

                <div class="col-lg-7 register-right">
                    <div class="form-wrap">
                        <h2 class="page-title">Pendaftaran Akun</h2>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 pl-3">
                                    @foreach ($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="section-box text-center">
                                <div class="section-title text-center">Foto Profil (opsional)</div>

                                <label for="avatar" class="avatar-label">
                                    <div class="avatar-circle">
                                        <img id="preview-image"
                                            style="width:100%;height:100%;object-fit:cover;display:none;">
                                        <i id="icon-user" class="fas fa-user" style="font-size:40px;color:#2bb673;"></i>
                                    </div>
                                    <div class="avatar-plus">+</div>
                                </label>

                                <input type="file" id="avatar" name="avatar" accept="image/*" hidden>

                                <small class="text-muted d-block mt-2">
                                    JPG, PNG, JPEG, WebP. Maks 2MB
                                </small>

                                @error('avatar')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="section-box">
                                <div class="section-title">Data Pengguna</div>

                                <input type="text" name="name"
                                    class="form-control form-control-user mb-2 @error('name') is-invalid @enderror"
                                    placeholder="Nama Lengkap" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="text-danger small mb-2">{{ $message }}</div>
                                @enderror

                                <input type="text" name="nip"
                                    class="form-control form-control-user mb-2 @error('nip') is-invalid @enderror"
                                    placeholder="NIP" value="{{ old('nip') }}" required>
                                @error('nip')
                                    <div class="text-danger small mb-2">{{ $message }}</div>
                                @enderror

                                <select name="jabatan"
                                    class="form-control form-control-user @error('jabatan') is-invalid @enderror"
                                    required>
                                    <option value="">-- Pilih Jabatan --</option>
                                    <option value="ketua" {{ old('jabatan') == 'ketua' ? 'selected' : '' }}>Ketua
                                    </option>
                                    <option value="sekretaris" {{ old('jabatan') == 'sekretaris' ? 'selected' : '' }}>
                                        Sekretaris</option>
                                    <option value="bendahara" {{ old('jabatan') == 'bendahara' ? 'selected' : '' }}>
                                        Bendahara</option>
                                </select>
                                @error('jabatan')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="section-box">
                                <div class="section-title">Data Login</div>

                                <input type="email" name="email"
                                    class="form-control form-control-user mb-2 @error('email') is-invalid @enderror"
                                    placeholder="Email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="text-danger small mb-2">{{ $message }}</div>
                                @enderror

                                <div class="row">
                                    <div class="col-md-6 mb-2 mb-md-0">
                                        <input type="password" name="password"
                                            class="form-control form-control-user @error('password') is-invalid @enderror"
                                            placeholder="Password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="password" name="password_confirmation"
                                            class="form-control form-control-user" placeholder="Konfirmasi Password"
                                            required>
                                    </div>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-siped btn-block mt-4 text-white">
                                Daftarkan Akun
                            </button>
                        </form>

                        <div class="login-link">
                            <a href="{{ route('login') }}">Sudah punya akun? Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('avatar').addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function(ev) {
                    document.getElementById('preview-image').src = ev.target.result;
                    document.getElementById('preview-image').style.display = 'block';
                    document.getElementById('icon-user').style.display = 'none';
                }

                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>
