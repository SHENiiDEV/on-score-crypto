<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CreditLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * GET /v1/account/credits
     */
    public function credits(Request $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $ledgerEntries = CreditLedger::where('account_id', $account->id)
            ->latest('id')
            ->limit(30)
            ->get();

        return response()->json([
            'account_id' => $account->id,
            'account_name' => $account->name,
            'credit_balance' => $account->credit_balance,
            'status' => $account->status,
            'ledger_recent' => $ledgerEntries->map(fn($item) => [
                'id' => $item->id,
                'delta' => $item->delta,
                'type' => $item->type,
                'balance_after' => $item->balance_after,
                'reference_id' => $item->reference_id,
                'reason' => $item->reason,
                'created_at' => $item->created_at->toIso8601String(),
            ]),
        ]);
    }
}
