<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Analysis;
use App\Models\ApiClient;
use App\Models\CreditLedger;
use App\Services\Analysis\AnalysisService;
use App\Support\MerchantContext;
use App\Support\ScoringRuleCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MerchantController extends Controller
{
    public function __construct(
        private AnalysisService $analysisService
    ) {}

    /* ---------------------------------------------------------------- */
    /* Overview                                                          */
    /* ---------------------------------------------------------------- */

    public function dashboard()
    {
        $account = $this->account();

        $keys = $account->apiClients()->orderByRaw("status = 'active' DESC")->latest()->get();
        $keyStats = $this->keyStats($account->id);

        $analysesTotal = Analysis::where('account_id', $account->id)->count();
        $analyses30d = Analysis::where('account_id', $account->id)
            ->where('created_at', '>=', now()->subDays(30))->count();
        $creditsSpent30d = (int) CreditLedger::where('account_id', $account->id)
            ->where('type', 'reservation')
            ->where('created_at', '>=', now()->subDays(30))
            ->sum(DB::raw('-delta'));

        $vipCount = Analysis::where('account_id', $account->id)
            ->whereIn('segment', ['super_vip', 'potential_vip'])->count();

        $recentAnalyses = Analysis::with('apiClient')
            ->where('account_id', $account->id)
            ->latest()->limit(12)->get();

        $recentLedger = CreditLedger::where('account_id', $account->id)
            ->latest('id')->limit(8)->get();

        return view('merchant.dashboard', [
            'account' => $account,
            'keys' => $keys,
            'keyStats' => $keyStats,
            'analysesTotal' => $analysesTotal,
            'analyses30d' => $analyses30d,
            'creditsSpent30d' => $creditsSpent30d,
            'vipCount' => $vipCount,
            'recentAnalyses' => $recentAnalyses,
            'recentLedger' => $recentLedger,
            'usageSeries' => $this->usageSeries($account->id),
            'segmentBreakdown' => $this->segmentBreakdown($account->id),
        ]);
    }

    /* ---------------------------------------------------------------- */
    /* API keys                                                          */
    /* ---------------------------------------------------------------- */

    public function keys()
    {
        $account = $this->account();

        return view('merchant.keys', [
            'account' => $account,
            'keys' => $account->apiClients()->orderByRaw("status = 'active' DESC")->latest()->get(),
            'keyStats' => $this->keyStats($account->id),
        ]);
    }

    public function storeKey(Request $request)
    {
        $account = $this->account();

        $request->validate([
            'name' => 'required|string|max:120',
            'rate_limit' => 'nullable|integer|min:10',
        ]);

        if (! $account->self_service_keys) {
            return back()->with('error', 'Self-service key provisioning is disabled for your account. Contact On-Score support.');
        }

        if ($account->status !== 'active') {
            return back()->with('error', 'Your account is not active — new keys cannot be issued.');
        }

        if ($account->remainingKeySlots() <= 0) {
            return back()->with('error', "You have reached your limit of {$account->max_api_keys} active API keys. Revoke one or request a higher limit.");
        }

        $rateLimit = (int) ($request->input('rate_limit') ?: $account->default_rate_limit);
        $rateLimit = min($rateLimit, (int) $account->default_rate_limit);

        $credentials = ApiClient::generateCredentials(
            account: $account,
            name: $request->input('name'),
            createdBy: Auth::user()->email,
            rateLimit: $rateLimit,
        );

        return redirect()->route('merchant.keys')->with('new_api_key', [
            'key_name' => $credentials['client']->name,
            'key_id' => $credentials['key_id'],
            'secret' => $credentials['secret'],
        ]);
    }

    public function revokeKey(Request $request, string $id)
    {
        $account = $this->account();

        $client = ApiClient::where('account_id', $account->id)->findOrFail($id);
        $client->update([
            'status' => 'revoked',
            'revoked_at' => now(),
        ]);

        return redirect()->route('merchant.keys')
            ->with('success', "API key “{$client->name}” has been revoked. Requests using it will now fail.");
    }

    public function renameKey(Request $request, string $id)
    {
        $account = $this->account();
        $request->validate(['name' => 'required|string|max:120']);

        $client = ApiClient::where('account_id', $account->id)->findOrFail($id);
        $client->update(['name' => $request->input('name')]);

        return redirect()->route('merchant.keys')->with('success', 'API key renamed.');
    }

    /* ---------------------------------------------------------------- */
    /* Usage history                                                     */
    /* ---------------------------------------------------------------- */

    public function usage(Request $request)
    {
        $account = $this->account();

        $query = Analysis::with('apiClient')->where('account_id', $account->id);

        if ($request->filled('key')) {
            $query->where('api_client_id', $request->input('key'));
        }
        if ($request->filled('network')) {
            $query->where('network', $request->input('network'));
        }
        if ($request->filled('segment')) {
            $query->where('segment', $request->input('segment'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('address', 'like', "%{$search}%")
                    ->orWhere('external_player_id', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $analyses = $query->latest()->paginate(25)->withQueryString();

        return view('merchant.usage', [
            'account' => $account,
            'analyses' => $analyses,
            'keys' => $account->apiClients()->orderBy('name')->get(),
            'filters' => $request->only(['key', 'network', 'segment', 'status', 'search', 'from', 'to']),
            'usageSeries' => $this->usageSeries($account->id),
        ]);
    }

    public function report(string $id)
    {
        $account = $this->account();

        $analysis = Analysis::with(['snapshot', 'account'])
            ->where('account_id', $account->id)
            ->findOrFail($id);

        return view('report', [
            'account' => $account,
            'analysis' => $analysis,
            'snapshot' => $analysis->snapshot,
        ]);
    }

    public function analyze(Request $request)
    {
        $account = $this->account();

        $request->validate([
            'address' => 'required|string|max:150',
            'network' => 'required|string|in:tron,ethereum,bsc,solana,bitcoin',
            'external_player_id' => 'nullable|string|max:120',
        ]);

        try {
            $analysis = $this->analysisService->executeAnalysis(
                account: $account,
                network: $request->input('network'),
                address: trim($request->input('address')),
                apiClient: $account->activeApiClients()->first(),
                externalPlayerId: $request->input('external_player_id'),
            );

            return redirect()->route('merchant.report', ['id' => $analysis->id])
                ->with('success', 'Wallet analysed successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Analysis failed: ' . $e->getMessage());
        }
    }

    /* ---------------------------------------------------------------- */
    /* Billing                                                           */
    /* ---------------------------------------------------------------- */

    public function billing(Request $request)
    {
        $account = $this->account();

        $query = CreditLedger::where('account_id', $account->id);
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $spendSeries = $this->spendSeries($account->id);

        return view('merchant.billing', [
            'account' => $account,
            'entries' => $query->latest('id')->paginate(30)->withQueryString(),
            'filters' => $request->only('type'),
            'spendSeries' => $spendSeries,
            'toppedUpTotal' => (int) CreditLedger::where('account_id', $account->id)
                ->where('delta', '>', 0)->sum('delta'),
            'spentTotal' => (int) abs((int) CreditLedger::where('account_id', $account->id)
                ->where('delta', '<', 0)->sum('delta')),
        ]);
    }

    /* ---------------------------------------------------------------- */
    /* Scoring rules                                                     */
    /* ---------------------------------------------------------------- */

    public function scoringRules()
    {
        $account = $this->account();

        return view('merchant.scoring-rules', [
            'account' => $account,
            'rules' => $account->effectiveScoringRules(),
            'metrics' => ScoringRuleCatalog::metrics(),
            'operators' => ScoringRuleCatalog::operators(),
            'tiers' => ScoringRuleCatalog::tiers(),
        ]);
    }

    public function updateScoringRules(Request $request)
    {
        $account = $this->account();

        $validated = $request->validate([
            'super_vip_threshold_usd' => 'required|numeric|min:0|max:1000000000',
            'potential_vip_threshold_usd' => 'required|numeric|min:0|max:1000000000',
            'high_value_threshold_usd' => 'required|numeric|min:0|max:1000000000',
            'rules' => 'nullable|array|max:40',
            'rules.*.metric' => 'nullable|string|max:60',
            'rules.*.operator' => 'nullable|string|max:4',
            'rules.*.value' => 'nullable|numeric',
            'rules.*.tag' => 'nullable|string|max:40',
            'rules.*.tier' => 'nullable|string|max:40',
        ]);

        $super = (float) $validated['super_vip_threshold_usd'];
        $potential = (float) $validated['potential_vip_threshold_usd'];
        $high = (float) $validated['high_value_threshold_usd'];

        if (! ($super > $potential && $potential > $high)) {
            return back()->withInput()->with(
                'error',
                'Thresholds must decrease: Super VIP > Potential VIP > High value.'
            );
        }

        $account->update([
            'scoring_rules' => [
                'super_vip_threshold_usd' => $super,
                'potential_vip_threshold_usd' => $potential,
                'high_value_threshold_usd' => $high,
                'custom_rules' => ScoringRuleCatalog::sanitizeRules($request->input('rules', [])),
                'updated_by' => Auth::user()->email,
                'updated_at' => now()->toIso8601String(),
            ],
        ]);

        return redirect()->route('merchant.scoring-rules')
            ->with('success', 'Scoring rules saved — they apply to every new analysis on all of your keys.');
    }

    public function resetScoringRules()
    {
        $account = $this->account();
        $account->update(['scoring_rules' => null]);

        return redirect()->route('merchant.scoring-rules')
            ->with('success', 'Scoring rules reset to On-Score platform defaults.');
    }

    /* ---------------------------------------------------------------- */
    /* Settings                                                          */
    /* ---------------------------------------------------------------- */

    public function settings()
    {
        $account = $this->account();

        return view('merchant.settings', [
            'account' => $account,
            'teamMembers' => $account->users()->orderBy('name')->get(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);

        return redirect()->route('merchant.settings')->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:10|confirmed',
        ]);

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()->with('error', 'Your current password is incorrect.');
        }

        $user->update([
            'password' => $request->input('password'),
            'must_change_password' => false,
        ]);

        return redirect()->route('merchant.settings')->with('success', 'Password changed.');
    }

    public function updateWebhook(Request $request)
    {
        $account = $this->account();

        $request->validate([
            'webhook_url' => 'nullable|url|max:255',
        ]);

        $account->update([
            'webhook_url' => $request->input('webhook_url'),
            'webhook_secret' => $account->webhook_secret ?: 'whsec_' . \Illuminate\Support\Str::random(32),
        ]);

        return redirect()->route('merchant.settings')->with('success', 'Webhook endpoint saved.');
    }

    public function rotateWebhookSecret()
    {
        $account = $this->account();
        $account->update(['webhook_secret' => 'whsec_' . \Illuminate\Support\Str::random(32)]);

        return redirect()->route('merchant.settings')->with('success', 'Webhook signing secret rotated.');
    }

    /* ---------------------------------------------------------------- */
    /* Helpers                                                           */
    /* ---------------------------------------------------------------- */

    private function account()
    {
        $account = MerchantContext::account();

        abort_unless($account, 403, 'No merchant account resolved for this session.');

        return $account;
    }

    /** Per-key aggregates keyed by api_client_id. */
    private function keyStats(string $accountId): array
    {
        return Analysis::where('account_id', $accountId)
            ->whereNotNull('api_client_id')
            ->selectRaw('api_client_id, COUNT(*) as calls, SUM(cost_credits) as credits, MAX(created_at) as last_call')
            ->groupBy('api_client_id')
            ->get()
            ->keyBy('api_client_id')
            ->map(fn ($row) => [
                'calls' => (int) $row->calls,
                'credits' => (int) $row->credits,
                'last_call' => $row->last_call,
            ])
            ->all();
    }

    /** Daily analysis volume for the last 30 days, zero-filled. */
    private function usageSeries(string $accountId): array
    {
        $rows = Analysis::where('account_id', $accountId)
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        return $this->zeroFill($rows->all());
    }

    /** Daily credit spend for the last 30 days, zero-filled. */
    private function spendSeries(string $accountId): array
    {
        $rows = CreditLedger::where('account_id', $accountId)
            ->where('delta', '<', 0)
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as d, SUM(-delta) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        return $this->zeroFill($rows->all());
    }

    private function zeroFill(array $rows): array
    {
        $series = [];

        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $series[] = [
                'date' => $day,
                'label' => now()->subDays($i)->format('d M'),
                'value' => (int) ($rows[$day] ?? 0),
            ];
        }

        return $series;
    }

    private function segmentBreakdown(string $accountId): array
    {
        $rows = Analysis::where('account_id', $accountId)
            ->whereNotNull('segment')
            ->selectRaw('segment, COUNT(*) as c')
            ->groupBy('segment')
            ->pluck('c', 'segment')
            ->all();

        $order = ['super_vip', 'potential_vip', 'high_value', 'good_player', 'regular', 'low_value'];
        $out = [];

        foreach ($order as $segment) {
            if (! empty($rows[$segment])) {
                $out[$segment] = (int) $rows[$segment];
            }
        }

        return $out;
    }
}
