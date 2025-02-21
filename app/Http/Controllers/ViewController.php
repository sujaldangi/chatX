<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class ViewController extends Controller
{
    public function registerView(Request $request)
    {
        return view('auth.register');
    }

    public function loginView(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return response()
        ->view('auth.login')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }

    public function dashboardView(Request $request)
    {
        return view('dashboard');
    }

    public function ForgetPasswordForm(Request $request)
    {
        return view('auth.forgetPassword');
    }

    public function ResetPasswordForm($token)
    {
        return view('auth.forgetPasswordLink', ['token' => $token]);
    }
}
