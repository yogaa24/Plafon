<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $input = trim($request->input('email'));
        $password = $request->input('password');

        // 1. Cek login kecocokan langsung (misal username 'direktur2' atau email persis)
        if (Auth::attempt(['email' => $input, 'password' => $password])) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        // 2. Jika input tanpa domain '@', coba tambahkan '@kiu.com'
        if (!str_contains($input, '@')) {
            if (Auth::attempt(['email' => $input . '@kiu.com', 'password' => $password])) {
                $request->session()->regenerate();
                return redirect()->intended('/');
            }
        }

        return back()->withErrors([
            'email' => 'Username/Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
