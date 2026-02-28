<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DoctorAuthController extends Controller
{
    /**
     * Show the doctor login form.
     */
    public function showLoginForm()
    {
        if (Auth::guard('doctor')->check()) {
            return redirect()->route('doctor.dashboard');
        }
        return view('doctor.login');
    }

    /**
     * Handle doctor login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('doctor')->attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('doctor.dashboard'));
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    /**
     * Handle doctor logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('doctor')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('access');
    }
}
