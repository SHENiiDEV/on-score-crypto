<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Analysis;
use App\Models\ApiClient;
use App\Services\Analysis\AnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnalysisController extends Controller
{
    public function __construct(
        private AnalysisService $analysisService
    ) {}

    /**
     * POST /v1/analyses
     * Single wallet analysis
     */
    public function store(Request $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');
        /** @var ApiClient|null $apiClient */
        $apiClient = $request->attributes->get('api_client');

        $validator = Validator::make($request->all(), [
            'address' => 'required|string|min:10|max:120',
            'network' => 'required|string|in:tron,ethereum,bsc,solana,bitcoin',
            'external_player_id' => 'nullable|string|max:100',
            'context' => 'nullable|array',
            'context.deposit_tx_hash' => 'nullable|string',
            'context.deposit_amount' => 'nullable|numeric',
            'context.deposit_asset' => 'nullable|string',
            'context.deposit_timestamp' => 'nullable|string',
            'options' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid request payload.',
                    'request_id' => 'req_' . uniqid(),
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $network = $request->input('network');
        $address = $request->input('address');
        $cost = $this->analysisService->getCreditCost($network);

        if (!$account->hasSufficientCredits($cost)) {
            return response()->json([
                'error' => [
                    'code' => 'INSUFFICIENT_CREDITS',
                    'message' => "Insufficient credits balance. Analysis of network '{$network}' requires {$cost} credits, current balance is {$account->credit_balance}.",
                    'request_id' => 'req_' . uniqid(),
                    'details' => [
                        'required_credits' => $cost,
                        'current_balance' => $account->credit_balance,
                    ],
                ],
            ], 402);
        }

        try {
            $analysis = $this->analysisService->executeAnalysis(
                account: $account,
                network: $network,
                address: $address,
                apiClient: $apiClient,
                externalPlayerId: $request->input('external_player_id'),
                depositContext: $request->input('context'),
            );

            return response()->json($this->formatAnalysisResponse($analysis), 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_ADDRESS',
                    'message' => $e->getMessage(),
                    'request_id' => 'req_' . uniqid(),
                ],
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => [
                    'code' => 'ANALYSIS_FAILED',
                    'message' => $e->getMessage(),
                    'request_id' => 'req_' . uniqid(),
                ],
            ], 500);
        }
    }

    /**
     * GET /v1/analyses/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $analysis = Analysis::with('snapshot')
            ->where('account_id', $account->id)
            ->where('id', $id)
            ->first();

        if (!$analysis) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => "Analysis with ID '{$id}' not found.",
                    'request_id' => 'req_' . uniqid(),
                ],
            ], 404);
        }

        return response()->json($this->formatAnalysisResponse($analysis));
    }

    /**
     * GET /v1/analyses
     * List analysis history with filters
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $query = Analysis::where('account_id', $account->id)->latest();

        if ($request->has('network')) {
            $query->where('network', $request->input('network'));
        }
        if ($request->has('segment')) {
            $query->where('segment', $request->input('segment'));
        }
        if ($request->has('address')) {
            $query->where('address', 'like', '%' . $request->input('address') . '%');
        }

        $analyses = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => $analyses->getCollection()->map(fn($item) => [
                'analysis_id' => $item->id,
                'status' => $item->status,
                'address' => $item->address,
                'network' => $item->network,
                'score_value' => $item->score_value,
                'segment' => $item->segment,
                'confidence' => $item->confidence,
                'cost_credits' => $item->cost_credits,
                'created_at' => $item->created_at->toIso8601String(),
                'completed_at' => $item->completed_at?->toIso8601String(),
            ]),
            'pagination' => [
                'total' => $analyses->total(),
                'per_page' => $analyses->perPage(),
                'current_page' => $analyses->currentPage(),
                'last_page' => $analyses->lastPage(),
            ],
        ]);
    }

    private function formatAnalysisResponse(Analysis $analysis): array
    {
        $snap = $analysis->snapshot;

        return [
            'analysis_id' => $analysis->id,
            'status' => $analysis->status,
            'address' => $analysis->address,
            'network' => $analysis->network,
            'created_at' => $analysis->created_at->toIso8601String(),
            'completed_at' => $analysis->completed_at?->toIso8601String(),
            'credits' => [
                'reserved' => $analysis->cost_credits,
                'charged' => $analysis->status === 'completed' ? $analysis->cost_credits : 0,
            ],
            'wallet' => [
                'visible_balance_usd' => $snap?->balance_assets['visible_balance_usd'] ?? 0,
                'wallet_age_days' => $snap?->wallet_overview['wallet_age_days'] ?? 0,
                'transactions_count' => $snap?->wallet_overview['transactions_count'] ?? 0,
                'is_custodial_cex' => $snap?->wallet_overview['is_custodial_cex'] ?? false,
                'native_balance' => $snap?->balance_assets['native_balance'] ?? 0,
                'token_portfolio' => $snap?->balance_assets['token_portfolio'] ?? [],
            ],
            'turnover' => $snap?->turnover ?? [],
            'gambling' => $snap?->gambling_intelligence ?? [],
            'counterparties' => $snap?->counterparties ?? [],
            'score' => $snap?->score_breakdown ?? [
                'value' => $analysis->score_value,
                'segment' => $analysis->segment,
                'confidence' => $analysis->confidence,
                'model_version' => $analysis->model_version,
            ],
            'warnings' => $snap?->warnings ?? [],
            'data_quality' => [
                'status' => $analysis->status === 'completed' ? 'complete' : 'partial',
                'provenance' => $snap?->provenance ?? [],
            ],
        ];
    }
}
