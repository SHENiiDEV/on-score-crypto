<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ApiClient;
use App\Models\BatchJob;
use App\Services\Analysis\AnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BatchController extends Controller
{
    public function __construct(
        private AnalysisService $analysisService
    ) {}

    /**
     * POST /v1/batches
     * Submit batch of wallet addresses
     */
    public function store(Request $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');
        /** @var ApiClient|null $apiClient */
        $apiClient = $request->attributes->get('api_client');

        $input = $request->all();
        if (isset($input['wallets']) && is_array($input['wallets'])) {
            $networkMap = [
                'eth' => 'ethereum',
                'ethereum' => 'ethereum',
                'trx' => 'tron',
                'tron' => 'tron',
                'trc20' => 'tron',
                'btc' => 'bitcoin',
                'bitcoin' => 'bitcoin',
                'sol' => 'solana',
                'solana' => 'solana',
                'bsc' => 'bsc',
                'bnb' => 'bsc',
                'bep20' => 'bsc',
            ];
            foreach ($input['wallets'] as $idx => $w) {
                if (is_array($w)) {
                    if (isset($w['chain']) && !isset($w['network'])) {
                        $input['wallets'][$idx]['network'] = $w['chain'];
                    }
                    if (isset($input['wallets'][$idx]['network'])) {
                        $nKey = strtolower(trim($input['wallets'][$idx]['network']));
                        if (isset($networkMap[$nKey])) {
                            $input['wallets'][$idx]['network'] = $networkMap[$nKey];
                        }
                    }
                }
            }
        }
        $request->merge($input);

        $validator = Validator::make($request->all(), [
            'wallets' => 'required|array|min:1|max:500',
            'wallets.*.address' => 'required|string',
            'wallets.*.network' => 'required|string|in:tron,ethereum,bsc,solana,bitcoin',
            'wallets.*.external_player_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid batch payload.',
                    'request_id' => 'req_' . uniqid(),
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $wallets = $request->input('wallets');
        $totalWallets = count($wallets);

        // Estimate total credits needed
        $totalCost = 0;
        foreach ($wallets as $w) {
            $totalCost += $this->analysisService->getCreditCost($w['network']);
        }

        if (!$account->hasSufficientCredits($totalCost)) {
            return response()->json([
                'error' => [
                    'code' => 'INSUFFICIENT_CREDITS',
                    'message' => "Insufficient credit balance for batch. Total required: {$totalCost} credits, available: {$account->credit_balance}.",
                    'request_id' => 'req_' . uniqid(),
                ],
            ], 402);
        }

        $job = BatchJob::create([
            'account_id' => $account->id,
            'total_wallets' => $totalWallets,
            'queued_count' => $totalWallets,
            'status' => 'processing',
        ]);

        // Process batch items
        $completed = 0;
        $failed = 0;

        foreach ($wallets as $item) {
            try {
                $this->analysisService->executeAnalysis(
                    account: $account,
                    network: $item['network'],
                    address: $item['address'],
                    apiClient: $apiClient,
                    externalPlayerId: $item['external_player_id'] ?? null,
                    batchJob: $job
                );
                $completed++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        $status = ($failed === 0) ? 'completed' : ($completed > 0 ? 'partial' : 'failed');
        $job->update([
            'queued_count' => 0,
            'processing_count' => 0,
            'completed_count' => $completed,
            'failed_count' => $failed,
            'status' => $status,
        ]);

        return response()->json([
            'job_id' => $job->id,
            'status' => $job->status,
            'total_wallets' => $job->total_wallets,
            'completed_count' => $job->completed_count,
            'failed_count' => $job->failed_count,
            'created_at' => $job->created_at->toIso8601String(),
        ], 202);
    }

    /**
     * GET /v1/batches/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $job = BatchJob::where('account_id', $account->id)->where('id', $id)->first();

        if (!$job) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => "Batch job '{$id}' not found.",
                    'request_id' => 'req_' . uniqid(),
                ],
            ], 404);
        }

        return response()->json([
            'job_id' => $job->id,
            'status' => $job->status,
            'total_wallets' => $job->total_wallets,
            'completed_count' => $job->completed_count,
            'failed_count' => $job->failed_count,
            'created_at' => $job->created_at->toIso8601String(),
            'updated_at' => $job->updated_at->toIso8601String(),
        ]);
    }

    /**
     * GET /v1/batches/{id}/results
     */
    public function results(Request $request, string $id): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $job = BatchJob::with(['analyses.snapshot'])
            ->where('account_id', $account->id)
            ->where('id', $id)
            ->first();

        if (!$job) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => "Batch job '{$id}' not found.",
                    'request_id' => 'req_' . uniqid(),
                ],
            ], 404);
        }

        return response()->json([
            'job_id' => $job->id,
            'status' => $job->status,
            'results' => $job->analyses->map(fn($an) => [
                'analysis_id' => $an->id,
                'address' => $an->address,
                'network' => $an->network,
                'status' => $an->status,
                'score_value' => $an->score_value,
                'segment' => $an->segment,
                'confidence' => $an->confidence,
                'visible_balance_usd' => $an->snapshot?->balance_assets['visible_balance_usd'] ?? 0,
                'gambling_status' => $an->snapshot?->gambling_intelligence['status'] ?? 'not_detected',
                'gambling_flow_365d' => $an->snapshot?->gambling_intelligence['total_flow_365d_usd'] ?? 0,
            ]),
        ]);
    }
}
