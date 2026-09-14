<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\MerchantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->to($this->homeFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'This user has been deactivated. Contact your account owner.',
            ])->onlyInput('email');
        }

        if ($user->isMerchant() && (! $user->account || $user->account->status === 'suspended')) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Your merchant account is suspended. Please contact On-Score support.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        MerchantContext::stop();
        $user->forceFill(['last_login_at' => now()])->save();

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Welcome back to the On-Score control room.');
        }

        if ($user->must_change_password) {
            return redirect()->route('merchant.settings')
                ->with('warning', 'Please set a new password before you continue.');
        }

        return redirect()->intended(route('merchant.dashboard'))
            ->with('success', 'Signed in successfully.');
    }

    public function logout(Request $request)
    {
        MerchantContext::stop();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been signed out successfully.');
    }

    private function homeFor($user): string
    {
        return $user->isAdmin() ? route('admin.dashboard') : route('merchant.dashboard');
    }
}
