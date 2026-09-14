<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Analysis;
use App\Models\ApiClient;
use App\Models\CreditLedger;
use App\Models\Entity;
use App\Models\EntityAddress;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Billing\CreditLedgerService;
use App\Support\MerchantContext;
use App\Support\ScoringRuleCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function __construct(
        private CreditLedgerService $creditLedgerService
    ) {}

    /* ----------------------------- Overview ----------------------------- */

    public function dashboard()
    {
        return view('admin.overview', [
            'accountsCount' => Account::count(),
            'activeAccountsCount' => Account::where('status', 'active')->count(),
            'activeKeysCount' => ApiClient::where('status', 'active')->count(),
            'totalCreditsIssued' => (int) CreditLedger::where('delta', '>', 0)->sum('delta'),
            'totalCreditsSpent' => (int) abs((int) CreditLedger::where('delta', '<', 0)->sum('delta')),
            'outstandingCredits' => (int) Account::sum('credit_balance'),
            'totalAnalyses' => Analysis::count(),
            'analyses30d' => Analysis::where('created_at', '>=', now()->subDays(30))->count(),
            'totalEntities' => Entity::count(),
            'topAccounts' => Account::withCount('analyses')->orderByDesc('analyses_count')->limit(6)->get(),
            'lowBalanceAccounts' => Account::where('status', 'active')
                ->whereColumn('credit_balance', '<=', 'low_balance_threshold')
                ->orderBy('credit_balance')->limit(6)->get(),
            'recentAnalyses' => Analysis::with(['account', 'apiClient'])->latest()->limit(12)->get(),
            'recentLedger' => CreditLedger::with('account')->latest('id')->limit(10)->get(),
            'usageSeries' => $this->platformUsageSeries(),
            'segmentBreakdown' => $this->platformSegmentBreakdown(),
        ]);
    }

    /* ------------------------------ Clients ----------------------------- */

    public function clients(Request $request)
    {
        $query = Account::withCount(['analyses', 'users'])
            ->withCount(['apiClients as active_keys_count' => fn ($q) => $q->where('status', 'active')]);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('contact_email', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return view('admin.clients', [
            'accounts' => $query->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function clientShow(string $id)
    {
        $account = Account::withCount('analyses')->findOrFail($id);

        $keyStats = Analysis::where('account_id', $account->id)
            ->whereNotNull('api_client_id')
            ->selectRaw('api_client_id, COUNT(*) as calls, SUM(cost_credits) as credits, MAX(created_at) as last_call')
            ->groupBy('api_client_id')
            ->get()->keyBy('api_client_id')
            ->map(fn ($r) => ['calls' => (int) $r->calls, 'credits' => (int) $r->credits, 'last_call' => $r->last_call])
            ->all();

        return view('admin.client-show', [
            'account' => $account,
            'keys' => $account->apiClients()->orderByRaw("status = 'active' DESC")->latest()->get(),
            'keyStats' => $keyStats,
            'users' => $account->users()->orderBy('name')->get(),
            'ledger' => CreditLedger::where('account_id', $account->id)->latest('id')->limit(25)->get(),
            'recentAnalyses' => Analysis::with('apiClient')->where('account_id', $account->id)
                ->latest()->limit(15)->get(),
            'rules' => $account->effectiveScoringRules(),
            'metrics' => ScoringRuleCatalog::metrics(),
            'operators' => ScoringRuleCatalog::operators(),
            'tiers' => ScoringRuleCatalog::tiers(),
            'creditsSpent' => (int) abs((int) CreditLedger::where('account_id', $account->id)
                ->where('delta', '<', 0)->sum('delta')),
        ]);
    }

    public function createAccount(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'initial_credits' => 'required|integer|min:0|max:10000000',
            'status' => 'required|in:active,trial,suspended',
            'max_api_keys' => 'nullable|integer|min:1|max:100',
            'default_rate_limit' => 'nullable|integer|min:10|max:10000',
            'owner_name' => 'required|string|max:120',
            'owner_email' => 'required|email|max:190|unique:users,email',
        ]);

        $slug = Str::slug($validated['name']) . '-' . Str::lower(Str::random(4));
        $password = $this->generatePassword();

        [$account, $user] = DB::transaction(function () use ($validated, $slug, $password) {
            $account = Account::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'credit_balance' => 0,
                'status' => $validated['status'],
                'max_api_keys' => $validated['max_api_keys'] ?? 5,
                'default_rate_limit' => $validated['default_rate_limit'] ?? 120,
                'low_balance_threshold' => 100,
                'contact_email' => $validated['owner_email'],
                'self_service_keys' => true,
            ]);

            $user = User::create([
                'account_id' => $account->id,
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => $password,
                'role' => 'merchant_owner',
                'status' => 'active',
                'must_change_password' => true,
            ]);

            return [$account, $user];
        });

        if ((int) $validated['initial_credits'] > 0) {
            $this->creditLedgerService->adjustBalance(
                account: $account,
                delta: (int) $validated['initial_credits'],
                reason: 'Initial credit grant upon B2B onboarding',
                actor: Auth::user()->email ?? 'admin'
            );
        }

        return redirect()->route('admin.clients.show', $account->id)->with('new_merchant_access', [
            'account_name' => $account->name,
            'portal_url' => route('login'),
            'email' => $user->email,
            'password' => $password,
        ]);
    }

    public function updateAccount(Request $request, string $id)
    {
        $account = Account::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,trial,suspended',
            'max_api_keys' => 'required|integer|min:0|max:100',
            'default_rate_limit' => 'required|integer|min:10|max:10000',
            'low_balance_threshold' => 'required|integer|min:0|max:1000000',
            'contact_email' => 'nullable|email|max:190',
        ]);

        $validated['self_service_keys'] = $request->boolean('self_service_keys');
        $account->update($validated);

        return redirect()->route('admin.clients.show', $account->id)
            ->with('success', 'Client settings updated.');
    }

    /* -------------------- Merchant users (portal access) ---------------- */

    public function createUser(Request $request, string $id)
    {
        $account = Account::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190|unique:users,email',
            'role' => ['required', Rule::in(['merchant_owner', 'merchant_member'])],
        ]);

        $password = $this->generatePassword();

        User::create([
            'account_id' => $account->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $password,
            'role' => $validated['role'],
            'status' => 'active',
            'must_change_password' => true,
        ]);

        return redirect()->route('admin.clients.show', $account->id)->with('new_merchant_access', [
            'account_name' => $account->name,
            'portal_url' => route('login'),
            'email' => $validated['email'],
            'password' => $password,
        ]);
    }

    public function resetUserPassword(string $id, string $userId)
    {
        $account = Account::findOrFail($id);
        $user = User::where('account_id', $account->id)->findOrFail($userId);

        $password = $this->generatePassword();
        $user->update(['password' => $password, 'must_change_password' => true]);

        return redirect()->route('admin.clients.show', $account->id)->with('new_merchant_access', [
            'account_name' => $account->name,
            'portal_url' => route('login'),
            'email' => $user->email,
            'password' => $password,
        ]);
    }

    public function toggleUser(string $id, string $userId)
    {
        $account = Account::findOrFail($id);
        $user = User::where('account_id', $account->id)->findOrFail($userId);

        $user->update(['status' => $user->status === 'active' ? 'disabled' : 'active']);

        return redirect()->route('admin.clients.show', $account->id)
            ->with('success', "Portal access for {$user->email} is now {$user->status}.");
    }

    /* --------------------- Impersonation (view as) ---------------------- */

    public function impersonate(string $id)
    {
        $account = Account::findOrFail($id);
        MerchantContext::start($account);

        return redirect()->route('merchant.dashboard')
            ->with('success', "You are now viewing the portal as “{$account->name}”.");
    }

    public function stopImpersonating()
    {
        $account = MerchantContext::account();
        MerchantContext::stop();

        if ($account) {
            return redirect()->route('admin.clients.show', $account->id)
                ->with('success', 'Returned to the admin console.');
        }

        return redirect()->route('admin.clients')->with('success', 'Returned to the admin console.');
    }

    /* ----------------------------- API keys ----------------------------- */

    public function keys(Request $request)
    {
        $query = ApiClient::with('account');

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->input('account_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('key_id', 'like', "%{$search}%"));
        }

        return view('admin.keys', [
            'keys' => $query->orderByRaw("status = 'active' DESC")->latest()->paginate(25)->withQueryString(),
            'accounts' => Account::orderBy('name')->get(),
            'filters' => $request->only(['account_id', 'status', 'search']),
        ]);
    }

    public function generateApiKey(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'name' => 'required|string|max:255',
            'rate_limit' => 'nullable|integer|min:10|max:10000',
        ]);

        $account = Account::findOrFail($validated['account_id']);

        $credentials = ApiClient::generateCredentials(
            account: $account,
            name: $validated['name'],
            createdBy: Auth::user()->email ?? 'admin',
            rateLimit: $validated['rate_limit'] ?? null,
        );

        return back()->with('new_api_key', [
            'account_name' => $account->name,
            'key_name' => $credentials['client']->name,
            'key_id' => $credentials['key_id'],
            'secret' => $credentials['secret'],
        ]);
    }

    public function revokeApiKey(string $id)
    {
        $client = ApiClient::findOrFail($id);
        $client->update(['status' => 'revoked', 'revoked_at' => now()]);

        return back()->with('success', "API key “{$client->name}” has been revoked.");
    }

    /* ------------------------------ Credits ----------------------------- */

    public function adjustCredits(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'delta' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:255',
        ]);

        $account = Account::findOrFail($validated['account_id']);

        try {
            $this->creditLedgerService->adjustBalance(
                account: $account,
                delta: (int) $validated['delta'],
                reason: $validated['reason'],
                actor: Auth::user()->email ?? 'admin'
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $verb = $validated['delta'] > 0 ? 'credited' : 'debited';

        return back()->with('success', "{$account->name} was {$verb} " . number_format(abs((int) $validated['delta'])) . ' credits.');
    }

    public function ledger(Request $request)
    {
        $query = CreditLedger::with('account');

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->input('account_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        return view('admin.ledger', [
            'entries' => $query->latest('id')->paginate(40)->withQueryString(),
            'accounts' => Account::orderBy('name')->get(),
            'filters' => $request->only(['account_id', 'type']),
            'issued' => (int) CreditLedger::where('delta', '>', 0)->sum('delta'),
            'spent' => (int) abs((int) CreditLedger::where('delta', '<', 0)->sum('delta')),
        ]);
    }

    /* ----------------------------- Analyses ----------------------------- */

    public function analyses(Request $request)
    {
        $query = Analysis::with(['account', 'apiClient']);

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->input('account_id'));
        }
        if ($request->filled('network')) {
            $query->where('network', $request->input('network'));
        }
        if ($request->filled('segment')) {
            $query->where('segment', $request->input('segment'));
        }
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(fn ($q) => $q->where('address', 'like', "%{$search}%")
                ->orWhere('external_player_id', 'like', "%{$search}%"));
        }

        return view('admin.analyses', [
            'analyses' => $query->latest()->paginate(30)->withQueryString(),
            'accounts' => Account::orderBy('name')->get(),
            'filters' => $request->only(['account_id', 'network', 'segment', 'search']),
        ]);
    }

    /* ----------------------------- Entities ----------------------------- */

    public function entities()
    {
        return view('admin.entities', [
            'entities' => Entity::with('addresses')->latest()->get(),
        ]);
    }

    public function addEntityAddress(Request $request)
    {
        $validated = $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'network' => 'required|in:tron,ethereum,bsc,solana,bitcoin',
            'address' => 'required|string|max:150',
            'label' => 'nullable|string|max:255',
            'confidence' => 'required|numeric|min:0.1|max:1.0',
        ]);

        $entity = Entity::findOrFail($validated['entity_id']);
        $norm = Wallet::normalize($validated['network'], $validated['address']);

        EntityAddress::updateOrCreate(
            ['network' => $validated['network'], 'normalized_address' => $norm],
            [
                'entity_id' => $entity->id,
                'address' => $validated['address'],
                'label' => $validated['label'] ?: "{$entity->name} Address",
                'source' => 'admin_manual',
                'confidence' => (float) $validated['confidence'],
                'status' => 'active',
                'last_validated_at' => now(),
            ]
        );

        return redirect()->route('admin.entities')
            ->with('success', "Address linked to “{$entity->name}”.");
    }

    /* ------------------- Scoring rules on behalf of client -------------- */

    public function updateScoringRules(Request $request, string $id)
    {
        $account = Account::findOrFail($id);

        $validated = $request->validate([
            'super_vip_threshold_usd' => 'required|numeric|min:0|max:1000000000',
            'potential_vip_threshold_usd' => 'required|numeric|min:0|max:1000000000',
            'high_value_threshold_usd' => 'nullable|numeric|min:0|max:1000000000',
            'rules' => 'nullable|array|max:40',
        ]);

        $super = (float) $validated['super_vip_threshold_usd'];
        $potential = (float) $validated['potential_vip_threshold_usd'];
        $high = isset($validated['high_value_threshold_usd']) && $validated['high_value_threshold_usd'] !== null && $validated['high_value_threshold_usd'] !== ''
            ? (float) $validated['high_value_threshold_usd']
            : min(25000.0, round($potential * 0.4, 2));

        if (! ($super > $potential && $potential > $high)) {
            return back()->with('error', 'Thresholds must decrease: Super VIP > Potential VIP > High value.');
        }

        $rawRules = $request->input('rules', []);
        if (empty($rawRules) && $request->has('metrics') && is_array($request->input('metrics'))) {
            $metrics = $request->input('metrics', []);
            $operators = $request->input('operators', []);
            $values = $request->input('values', []);
            $tags = $request->input('tags', []);
            $tiers = $request->input('tiers', []);
            foreach ($metrics as $i => $m) {
                $rawRules[] = [
                    'metric' => $m,
                    'operator' => $operators[$i] ?? '>=',
                    'value' => $values[$i] ?? null,
                    'tag' => $tags[$i] ?? null,
                    'tier' => $tiers[$i] ?? null,
                ];
            }
        }

        $account->update([
            'scoring_rules' => [
                'super_vip_threshold_usd' => $super,
                'potential_vip_threshold_usd' => $potential,
                'high_value_threshold_usd' => $high,
                'custom_rules' => ScoringRuleCatalog::sanitizeRules($rawRules),
                'updated_by' => Auth::user()->email ?? 'admin',
                'updated_at' => now()->toIso8601String(),
            ],
        ]);

        if (str_contains($request->path(), 'accounts/')) {
            return redirect('/admin')->with('success', "Scoring rules for “{$account->name}” updated.");
        }

        return redirect()->route('admin.clients.show', $account->id)
            ->with('success', "Scoring rules for “{$account->name}” updated.");
    }

    /* ------------------------------ Helpers ----------------------------- */

    private function generatePassword(): string
    {
        return Str::random(6) . '-' . Str::random(6) . '-' . random_int(100, 999);
    }

    private function platformUsageSeries(): array
    {
        $rows = Analysis::where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')->pluck('c', 'd')->all();

        $series = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $series[] = [
                'date' => $day->format('Y-m-d'),
                'label' => $day->format('d M'),
                'value' => (int) ($rows[$day->format('Y-m-d')] ?? 0),
            ];
        }

        return $series;
    }

    private function platformSegmentBreakdown(): array
    {
        $rows = Analysis::whereNotNull('segment')
            ->selectRaw('segment, COUNT(*) as c')
            ->groupBy('segment')->pluck('c', 'segment')->all();

        $order = ['super_vip', 'potential_vip', 'high_value', 'good_player', 'regular', 'low_value'];
        $out = [];
        foreach ($order as $s) {
            if (! empty($rows[$s])) {
                $out[$s] = (int) $rows[$s];
            }
        }

        return $out;
    }
}
