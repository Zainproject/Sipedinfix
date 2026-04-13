<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:150'],
            'nip'     => ['required', 'string', 'max:50'],
            'jabatan' => ['required', 'in:ketua,sekretaris,bendahara'],
            'email'   => ['required', 'email', 'max:120', 'unique:users,email,' . $user->id],
            'avatar'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required'    => 'Nama wajib diisi.',
            'nip.required'     => 'NIP wajib diisi.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'jabatan.in'       => 'Jabatan tidak valid.',
            'email.required'   => 'Email wajib diisi.',
            'email.email'      => 'Format email tidak valid.',
            'email.unique'     => 'Email sudah digunakan.',
            'avatar.image'     => 'File avatar harus berupa gambar.',
            'avatar.mimes'     => 'Avatar harus berformat jpg, jpeg, png, atau webp.',
            'avatar.max'       => 'Ukuran avatar maksimal 2MB.',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->update($validated);

        return back()->with('status', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Kata sandi saat ini tidak sesuai.'
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('status', 'Kata sandi berhasil diperbarui.');
    }
}
