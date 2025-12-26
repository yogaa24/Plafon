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
     * Update email dan/atau password dalam satu form
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi
        $rules = [
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'required',
        ];

        // Jika user mengisi password baru, maka wajib konfirmasi
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed'];
        }

        $request->validate($rules);

        // Verifikasi password saat ini
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai.'
            ])->withInput();
        }

        // Update email
        $user->email = $request->email;

        // Update password jika diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $message = $request->filled('password') 
            ? 'Email dan password berhasil diubah!' 
            : 'Email berhasil diubah!';

        return back()->with('success', $message);
    }
}