<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reset Password SIPEDIN</title>
</head>

<body style="margin:0; padding:0; background:#f4f6f9; font-family:Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">

                <!-- CONTAINER -->
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff; border-radius:10px; overflow:hidden;">

                    <!-- HEADER -->
                    <tr>
                        <td style="background:#147a52; padding:20px; text-align:center;">
                            <img src="https://ridergalau.id/wp-content/uploads/2026/01/Logo-Kabupaten-Sumenep.png"
                                width="70" style="margin-bottom:10px;">
                            <h2 style="color:#ffffff; margin:0;">SIPEDIN</h2>
                            <p style="color:#d4f5e9; margin:5px 0 0;">Sistem Informasi Perintah Dinas</p>
                        </td>
                    </tr>

                    <!-- BODY -->
                    <tr>
                        <td style="padding:30px; color:#333;">

                            <p style="font-size:16px;">Halo, <strong>{{ $user->name }}</strong> 👋</p>

                            <p>
                                Kami menerima permintaan untuk mereset kata sandi akun <strong>SIPEDIN</strong> Anda.
                            </p>

                            <p>
                                Silakan klik tombol di bawah ini untuk membuat kata sandi baru:
                            </p>

                            <!-- BUTTON -->
                            <div style="text-align:center; margin:30px 0;">
                                <a href="{{ $url }}"
                                    style="
                        background:#147a52;
                        color:#ffffff;
                        padding:12px 25px;
                        text-decoration:none;
                        border-radius:6px;
                        font-weight:bold;
                        display:inline-block;">
                                    Reset Password
                                </a>
                            </div>

                            <p>
                                Link ini hanya berlaku selama <strong>60 menit</strong>.
                            </p>

                            <p>
                                Jika Anda tidak melakukan permintaan ini, abaikan email ini.
                            </p>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background:#f1f5f9; padding:20px; text-align:center; font-size:13px; color:#666;">
                            <p style="margin:0;">Hormat kami,</p>
                            <strong style="color:#147a52;">SIPEDIN</strong><br>
                            Sistem Informasi Perintah Dinas
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
