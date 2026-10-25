<?php

namespace App\Http\Controllers;

use App\Domain\TenantContext;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt($data)) {
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }
        $request->session()->regenerate();

        return redirect()->intended('/account');
    }

    public function register(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'email' => 'required|email|max:255|unique:users,email', 'password' => 'required|confirmed|min:10|max:128']);
        $user = User::create($data + ['tenant_id' => app(TenantContext::class)->id(), 'role' => 'customer']);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/account');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
