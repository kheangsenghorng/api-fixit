<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletTransactionController extends Controller
{
    /**
     * Get all wallet transactions
     */
    public function index(): JsonResponse
    {
        $transactions = WalletTransaction::with([
            'wallet',
            'user',
            'payment',
            'serviceBooking',
        ])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Wallet transactions retrieved successfully',
            'data' => $transactions,
        ]);
    }

    /**
     * Get wallet transactions by user id
     */
    public function showByUserId(int $userId): JsonResponse
    {
        $transactions = WalletTransaction::with([
            'wallet',
            'payment',
            'serviceBooking',
        ])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'User wallet transactions retrieved successfully',
            'data' => $transactions,
        ]);
    }

    /**
     * Get wallet transactions by wallet id
     */
    public function showByWalletId(int $walletId): JsonResponse
    {
        $transactions = WalletTransaction::with([
            'wallet',
            'user',
            'payment',
            'serviceBooking',
        ])
            ->where('wallet_id', $walletId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Wallet transactions retrieved successfully',
            'data' => $transactions,
        ]);
    }

    /**
     * Create wallet transaction
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'wallet_id' => ['required', 'exists:wallets,wallet_id'],
            'user_id' => ['required', 'exists:users,id'],
            'payment_id' => ['nullable', 'exists:payments,id'],
            'service_booking_id' => ['nullable', 'exists:service_bookings,id'],

            // credit = add money, debit = remove money
            'type' => ['required', 'in:credit,debit'],

            // payment/top-up method
           'method' => ['nullable', 'in:wallet,aba,bakong,cash,khqr'],

            'transaction_ref' => ['nullable', 'string', 'max:255'],
            'external_transaction_id' => ['nullable', 'string', 'max:255'],

            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            $transaction = DB::transaction(function () use ($data) {
                $wallet = Wallet::where('wallet_id', $data['wallet_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int) $wallet->user_id !== (int) $data['user_id']) {
                    abort(422, 'Wallet does not belong to this user.');
                }

                if ($wallet->status !== 'active' || !$wallet->is_active) {
                    abort(422, 'Wallet is not active.');
                }

                $amount = (float) $data['amount'];
                $balanceBefore = (float) $wallet->balance;

                if ($data['type'] === 'debit') {
                    if ($balanceBefore < $amount) {
                        abort(422, 'Insufficient wallet balance.');
                    }

                    $balanceAfter = $balanceBefore - $amount;
                } else {
                    $balanceAfter = $balanceBefore + $amount;
                }

                $wallet->update([
                    'balance' => $balanceAfter,
                ]);

                return WalletTransaction::create([
                    'wallet_id' => $wallet->wallet_id,
                    'user_id' => $data['user_id'],
                    'payment_id' => $data['payment_id'] ?? null,
                    'service_booking_id' => $data['service_booking_id'] ?? null,

                    'type' => $data['type'],
                    'method' => $data['method'] ?? null,
                    'transaction_ref' => $data['transaction_ref'] ?? null,
                    'external_transaction_id' => $data['external_transaction_id'] ?? null,

                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'description' => $data['description'] ?? null,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Wallet transaction created successfully',
                'data' => $transaction->load([
                    'wallet',
                    'user',
                    'payment',
                    'serviceBooking',
                ]),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Show one transaction
     */
    public function show(int $id): JsonResponse
    {
        $transaction = WalletTransaction::with([
            'wallet',
            'user',
            'payment',
            'serviceBooking',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Wallet transaction details',
            'data' => $transaction,
        ]);
    }

    /**
     * Delete transaction
     */
    public function destroy(int $id): JsonResponse
    {
        $transaction = WalletTransaction::findOrFail($id);
        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wallet transaction deleted successfully',
        ]);
    }
}