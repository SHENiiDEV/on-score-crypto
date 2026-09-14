<?php

namespace App\Http\Middleware;

use App\Support\MerchantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMerchant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please sign in to access the merchant portal.');
        }

        $account = MerchantContext::account();

        if ($user->isAdmin() && ! $account) {
            return redirect()->route('admin.clients')
                ->with('error', 'Pick a client and use "View as client" to open the merchant portal.');
        }

        if (! $account) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Your user is not linked to a merchant account. Contact On-Score support.');
        }

        if (! $user->isAdmin() && ! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'This user has been deactivated.');
        }

        if (! $user->isAdmin() && $account->status === 'suspended') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Your account is suspended. Please contact On-Score support.');
        }

        $request->attributes->set('merchant_account', $account);

        return $next($request);
    }
}
