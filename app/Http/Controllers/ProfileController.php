<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Tampilkan form edit profile
     */
    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Update nama, email dan/atau password dalam satu form
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi
        $rules = [
            'name' => 'required|string|max:255', // TAMBAH: Validasi nama
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'required',
        ];

        // Jika user mengisi password baru, maka wajib konfirmasi
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', 'min:8'];
        }

        $request->validate($rules);

        // Verifikasi password saat ini
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai.'
            ])->withInput();
        }

        // TAMBAH: Update nama
        $user->name = $request->name;
        
        // Update email
        $user->email = $request->email;

        // Update password jika diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // UBAH: Pesan sukses yang lebih dinamis
        $message = 'Profil berhasil diperbarui!';
        if ($request->filled('password')) {
            $message = 'Nama, email, dan password berhasil diubah!';
        }

        return redirect()->to(
            $request->redirect_to ?? url()->previous()
        )->with('success', $message);
        
    }
}