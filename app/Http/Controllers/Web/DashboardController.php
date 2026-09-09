<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ApiClient;
use App\Models\Analysis;
use App\Models\CreditLedger;
use App\Models\Entity;
use App\Services\Analysis\AnalysisService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private AnalysisService $analysisService
    ) {}

    public function index()
    {
        $user = auth()->user();
        $account = $user?->account ?? Account::first();
        $apiClient = $account ? ApiClient::where('account_id', $account->id)->first() : null;
        
        $query = Analysis::with(['snapshot', 'account']);
        if ($user && !in_array($user->role, ['superadmin', 'admin']) && $account) {
            $query->where('account_id', $account->id);
        }
        $recentAnalyses = $query->latest()->limit(20)->get();

        $ledgerQuery = CreditLedger::with('account');
        if ($user && !in_array($user->role, ['superadmin', 'admin']) && $account) {
            $ledgerQuery->where('account_id', $account->id);
        }
        $ledgerRecent = $ledgerQuery->latest('id')->limit(10)->get();

        $entitiesCount = Entity::count();
        $totalAnalysesCount = Analysis::count();
        $vipCount = Analysis::where('segment', 'potential_vip')->orWhere('score_value', '>=', 80)->count();

        return view('dashboard', [
            'account' => $account,
            'apiClient' => $apiClient,
            'recentAnalyses' => $recentAnalyses,
            'ledgerRecent' => $ledgerRecent,
            'entitiesCount' => $entitiesCount,
            'totalAnalysesCount' => $totalAnalysesCount,
            'vipCount' => $vipCount,
        ]);
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'network' => 'required|string|in:tron,ethereum,bsc,solana,bitcoin',
            'external_player_id' => 'nullable|string',
        ]);

        $user = auth()->user();
        $account = $user?->account ?? Account::first();
        $apiClient = $account ? ApiClient::where('account_id', $account->id)->first() : null;

        try {
            $analysis = $this->analysisService->executeAnalysis(
                account: $account,
                network: $request->input('network'),
                address: $request->input('address'),
                apiClient: $apiClient,
                externalPlayerId: $request->input('external_player_id'),
            );

            return redirect()->route('dashboard.report', ['id' => $analysis->id])
                ->with('success', 'Wallet analyzed successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Analysis failed: ' . $e->getMessage());
        }
    }

    public function report(string $id)
    {
        $user = auth()->user();
        $account = $user?->account ?? Account::first();
        
        $query = Analysis::with(['snapshot', 'account'])->where('id', $id);
        if ($user && !in_array($user->role, ['superadmin', 'admin']) && $account) {
            $query->where('account_id', $account->id);
        }
        $analysis = $query->firstOrFail();

        return view('report', [
            'account' => $analysis->account ?? $account,
            'analysis' => $analysis,
            'snapshot' => $analysis->snapshot,
        ]);
    }

    public function topup(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:50|max:10000',
        ]);

        $account = Account::first();
        app(\App\Services\Billing\CreditLedgerService::class)->adjustBalance(
            account: $account,
            delta: (int)$request->input('amount'),
            reason: 'Manual test top-up via Dashboard',
            actor: 'dashboard_user'
        );

        return redirect()->route('dashboard')->with('success', "Added {$request->input('amount')} credits to balance.");
    }
}
