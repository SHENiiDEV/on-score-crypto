<?php

namespace App\Support;

use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Resolves which merchant workspace the current web session is operating in.
 *
 * A merchant user always resolves to their own account. An administrator may
 * temporarily "view as" a client, which is stored in the session and clearly
 * surfaced in the UI.
 */
class MerchantContext
{
    public const SESSION_KEY = 'impersonated_account_id';

    public static function account(): ?Account
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if ($user->isAdmin()) {
            $accountId = Session::get(self::SESSION_KEY);

            return $accountId ? Account::find($accountId) : null;
        }

        return $user->account;
    }

    public static function isImpersonating(): bool
    {
        $user = Auth::user();

        return $user
            && $user->isAdmin()
            && Session::has(self::SESSION_KEY)
            && self::account() !== null;
    }

    public static function start(Account $account): void
    {
        Session::put(self::SESSION_KEY, $account->id);
    }

    public static function stop(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
