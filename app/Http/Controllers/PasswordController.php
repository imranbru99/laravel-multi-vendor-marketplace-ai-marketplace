<?php

namespace App\Http\Controllers;

use App\Domain\TenantContext;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function request(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:255']);
        Password::sendResetLink($data + ['tenant_id' => app(TenantContext::class)->id()]);

        // Avoid disclosing whether this email has an account in this marketplace.
        return back()->with('success', 'If that address belongs to this marketplace, a reset link has been sent. Check your configured mail delivery.');
    }

    public function reset(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:255', 'token' => 'required|string|max:255', 'password' => 'required|string|confirmed|min:10|max:128']);
        $status = Password::reset($data + ['tenant_id' => app(TenantContext::class)->id()], function ($user, $password) {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            $user->tokens()->delete();
            event(new PasswordReset($user));
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect('/login')->with('success', 'Password reset. Sign in with your new password.');
    }
}
