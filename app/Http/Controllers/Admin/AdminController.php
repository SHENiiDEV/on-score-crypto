<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ApiClient;
use App\Models\Analysis;
use App\Models\CreditLedger;
use App\Models\Entity;
use App\Models\EntityAddress;
use App\Models\Wallet;
use App\Services\Billing\CreditLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function __construct(
        private CreditLedgerService $creditLedgerService
    ) {}

    public function dashboard()
    {
        $accounts = Account::with(['apiClients', 'users'])
            ->withCount('analyses')
            ->latest()
            ->get();

        $apiClients = ApiClient::with('account')->latest()->get();
        $totalCreditsIssued = CreditLedger::where('type', 'topup')->sum('delta');
        $totalAnalyses = Analysis::count();
        $totalEntities = Entity::count();
        $recentLedger = CreditLedger::with('account')->latest('id')->limit(15)->get();
        $recentAnalyses = Analysis::with(['snapshot', 'account', 'apiClient'])->latest()->limit(25)->get();

        return view('admin.dashboard', [
            'accounts' => $accounts,
            'apiClients' => $apiClients,
            'totalCreditsIssued' => $totalCreditsIssued,
            'totalAnalyses' => $totalAnalyses,
            'totalEntities' => $totalEntities,
            'recentLedger' => $recentLedger,
            'recentAnalyses' => $recentAnalyses,
        ]);
    }

    public function createAccount(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'initial_credits' => 'required|integer|min:0|max:1000000',
            'status' => 'required|in:active,trial,suspended',
        ]);

        $slug = Str::slug($request->input('name')) . '-' . Str::random(4);

        $account = Account::create([
            'name' => $request->input('name'),
            'slug' => $slug,
            'credit_balance' => 0,
            'status' => $request->input('status'),
        ]);

        if ($request->input('initial_credits') > 0) {
            $this->creditLedgerService->adjustBalance(
                account: $account,
                delta: (int)$request->input('initial_credits'),
                reason: 'Initial credit grant upon B2B account onboarding',
                actor: Auth::user()->email ?? 'admin'
            );
        }

        return redirect()->route('admin.dashboard')
            ->with('success', "B2B client '{$account->name}' registered successfully!");
    }

    public function generateApiKey(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'name' => 'required|string|max:255',
            'rate_limit' => 'nullable|integer|min:10|max:5000',
        ]);

        $account = Account::findOrFail($request->input('account_id'));
        $credentials = ApiClient::generateCredentials(
            account: $account,
            name: $request->input('name'),
            scopes: ['analyses:read', 'analyses:write', 'batches:read', 'batches:write', 'credits:read']
        );

        if ($request->filled('rate_limit')) {
            $credentials['client']->update(['rate_limit_per_minute' => (int)$request->input('rate_limit')]);
        }

        return redirect()->route('admin.dashboard')->with('new_api_key', [
            'account_name' => $account->name,
            'key_name' => $credentials['client']->name,
            'key_id' => $credentials['key_id'],
            'secret' => $credentials['secret'],
        ]);
    }

    public function revokeApiKey(string $id)
    {
        $client = ApiClient::findOrFail($id);
        $client->update(['status' => 'revoked']);

        return redirect()->route('admin.dashboard')
            ->with('success', "API key '{$client->name}' has been revoked.");
    }

    public function adjustCredits(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'delta' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        $account = Account::findOrFail($request->input('account_id'));

        $this->creditLedgerService->adjustBalance(
            account: $account,
            delta: (int)$request->input('delta'),
            reason: $request->input('reason'),
            actor: Auth::user()->email ?? 'admin'
        );

        return redirect()->route('admin.dashboard')
            ->with('success', "Balance for '{$account->name}' adjusted by {$request->input('delta')} credits.");
    }

    public function entities()
    {
        $entities = Entity::with('addresses')->latest()->get();

        return view('admin.entities', [
            'entities' => $entities,
        ]);
    }

    public function addEntityAddress(Request $request)
    {
        $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'network' => 'required|in:tron,ethereum,bsc,solana,bitcoin',
            'address' => 'required|string|max:150',
            'label' => 'nullable|string|max:255',
            'confidence' => 'required|numeric|min:0.1|max:1.0',
        ]);

        $entity = Entity::findOrFail($request->input('entity_id'));
        $norm = Wallet::normalize($request->input('network'), $request->input('address'));

        EntityAddress::create([
            'entity_id' => $entity->id,
            'network' => $request->input('network'),
            'address' => $request->input('address'),
            'normalized_address' => $norm,
            'label' => $request->input('label') ?: "{$entity->name} Address",
            'source' => 'admin_manual',
            'confidence' => (float)$request->input('confidence'),
            'status' => 'active',
            'last_validated_at' => now(),
        ]);

        return redirect()->route('admin.entities')
            ->with('success', "Address successfully added to '{$entity->name}' entity database!");
    }

    public function updateScoringRules(Request $request, string $id)
    {
        $account = Account::findOrFail($id);

        $rules = [];

        // Threshold overrides
        if ($request->filled('super_vip_threshold_usd')) {
            $rules['super_vip_threshold_usd'] = (float)$request->input('super_vip_threshold_usd');
        }
        if ($request->filled('potential_vip_threshold_usd')) {
            $rules['potential_vip_threshold_usd'] = (float)$request->input('potential_vip_threshold_usd');
        }
        if ($request->filled('high_value_threshold_usd')) {
            $rules['high_value_threshold_usd'] = (float)$request->input('high_value_threshold_usd');
        }

        // Custom condition rules (metric, operator, value, tag, tier)
        $customRules = [];
        if ($request->filled('rules_json')) {
            $decoded = json_decode($request->input('rules_json'), true);
            if (is_array($decoded)) {
                $customRules = $decoded;
            }
        } elseif ($request->has('metrics') && is_array($request->input('metrics'))) {
            $metrics = $request->input('metrics', []);
            $operators = $request->input('operators', []);
            $values = $request->input('values', []);
            $tags = $request->input('tags', []);
            $tiers = $request->input('tiers', []);

            foreach ($metrics as $idx => $metric) {
                if (empty($metric) || !isset($values[$idx]) || $values[$idx] === '') continue;

                $customRules[] = [
                    'metric' => trim($metric),
                    'operator' => $operators[$idx] ?? '>=',
                    'value' => (float)$values[$idx],
                    'tag' => !empty($tags[$idx]) ? strtoupper(trim($tags[$idx])) : null,
                    'tier' => !empty($tiers[$idx]) ? strtolower(trim($tiers[$idx])) : null,
                ];
            }
        }

        $rules['custom_rules'] = $customRules;
        $account->update(['scoring_rules' => $rules]);

        return redirect()->route('admin.dashboard')
            ->with('success', "Custom scoring rules for client '{$account->name}' updated successfully!");
    }
}

