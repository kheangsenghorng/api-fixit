<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Get all wallets
     */
    public function index(): JsonResponse
    {
        $wallets = Wallet::with(['user', 'transactions'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Wallets retrieved successfully',
            'data' => $wallets,
        ]);
    }

    /**
     * Create wallet
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id', 'unique:wallets,user_id'],
            'balance' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', 'in:active,frozen'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $wallet = Wallet::create([
            'user_id' => $data['user_id'],
            'balance' => $data['balance'] ?? 0,
            'currency' => $data['currency'] ?? 'USD',
            'status' => $data['status'] ?? 'active',
            'phone' => $data['phone'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wallet created successfully',
            'data' => $wallet->load('user'),
        ], 201);
    }

    /**
     * Show wallet by wallet id
     */
    public function show(int $walletId): JsonResponse
    {
        $wallet = Wallet::with(['user', 'transactions'])
            ->where('wallet_id', $walletId)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Wallet details retrieved successfully',
            'data' => $wallet,
        ]);
    }

    /**
     * Show wallet by user id
     */
    public function showByUserId(int $userId): JsonResponse
    {
        $wallet = Wallet::with([
                'user',
                'transactions' => function ($query) {
                    $query->orderByDesc('wallet_transaction_id');
                }
            ])
            ->where('user_id', $userId)
            ->first();
    
        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found for this user',
                'data' => null,
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'message' => 'User wallet retrieved successfully',
            'data' => $wallet,
        ]);
    }
    /**
     * Update wallet
     */
    public function update(Request $request, int $walletId): JsonResponse
    {
        $wallet = Wallet::where('wallet_id', $walletId)->firstOrFail();

        $data = $request->validate([
            'currency' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', 'in:active,frozen'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $wallet->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Wallet updated successfully',
            'data' => $wallet->fresh()->load('user'),
        ]);
    }

    /**
     * Top up wallet balance
     */
    public function topUp(Request $request, int $walletId): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['nullable', 'in:wallet,aba,bakong,cash,khqr'],
            'transaction_ref' => ['nullable', 'string', 'max:255'],
            'external_transaction_id' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
    
        try {
            $wallet = DB::transaction(function () use ($walletId, $data) {
                $wallet = Wallet::where('wallet_id', $walletId)
                    ->lockForUpdate()
                    ->firstOrFail();
    
                if ($wallet->status !== 'active' || !$wallet->is_active) {
                    abort(422, 'Wallet is not active.');
                }
    
                $amount = (float) $data['amount'];
                $balanceBefore = (float) $wallet->balance;
                $balanceAfter = $balanceBefore + $amount;
    
                $wallet->update([
                    'balance' => $balanceAfter,
                ]);
    
                $wallet->transactions()->create([
                    'user_id' => $wallet->user_id,
                    'type' => 'credit',
                    'method' => $data['method'],
                    'transaction_ref' => $data['transaction_ref'] ?? strtoupper($data['method']) . '-' . time(),
                    'external_transaction_id' => $data['external_transaction_id'] ?? null,
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'description' => $data['description'] ?? 'Wallet top up by ' . strtoupper($data['method']),
                ]);
    
                return $wallet->fresh();
            });
    
            return response()->json([
                'success' => true,
                'message' => 'Wallet topped up successfully',
                'data' => $wallet->load(['user', 'transactions']),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Freeze wallet
     */
    public function freeze(int $walletId): JsonResponse
    {
        $wallet = Wallet::where('wallet_id', $walletId)->firstOrFail();

        $wallet->update([
            'status' => 'frozen',
            'is_active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wallet frozen successfully',
            'data' => $wallet,
        ]);
    }

    /**
     * Activate wallet
     */
    public function activate(int $walletId): JsonResponse
    {
        $wallet = Wallet::where('wallet_id', $walletId)->firstOrFail();

        $wallet->update([
            'status' => 'active',
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wallet activated successfully',
            'data' => $wallet,
        ]);
    }

    /**
     * Delete wallet
     */
    public function destroy(int $walletId): JsonResponse
    {
        $wallet = Wallet::where('wallet_id', $walletId)->firstOrFail();
        $wallet->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wallet deleted successfully',
        ]);
    }
}