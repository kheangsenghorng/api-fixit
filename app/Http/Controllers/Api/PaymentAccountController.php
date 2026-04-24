<?php

namespace App\Http\Controllers\Api;

use App\Models\PaymentAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentAccountRequest;
use App\Http\Requests\UpdatePaymentAccountRequest;
use App\Http\Resources\PaymentAccountResource;

class PaymentAccountController extends Controller
{
    public function index()
    {
        $paymentAccounts = PaymentAccount::all();

        return PaymentAccountResource::collection($paymentAccounts);
    }

    public function checkCompanyBankAccount($userId)
{
    $paymentAccount = PaymentAccount::where('user_id', $userId)->first();

    if (!$paymentAccount) {
        return response()->json([
            'message' => 'Company has not added bank account yet',
            'has_bank_account' => false,
            'data' => null,
        ], 200);
    }

    return response()->json([
        'message' => 'Company already added bank account',
        'has_bank_account' => true,
        'data' => new PaymentAccountResource($paymentAccount),
    ], 200);
}

    public function store(StorePaymentAccountRequest $request)
    {
        $data = $request->validated();
    
        $data['currency'] = 'USD';
    
        $paymentAccount = PaymentAccount::create($data);
    
        return response()->json([
            'message' => 'Payment account created successfully',
            'data' => new PaymentAccountResource($paymentAccount),
        ], 201);
    }

    public function show($id)
    {
        $paymentAccount = PaymentAccount::with('user')->findOrFail($id);

        return new PaymentAccountResource($paymentAccount);
    }

    public function showByUser($userId)
    {
        $paymentAccounts = PaymentAccount::with('user')
            ->where('user_id', $userId)
            ->get();
    
        return PaymentAccountResource::collection($paymentAccounts);
    }
    public function update(UpdatePaymentAccountRequest $request, $id)
    {
        $paymentAccount = PaymentAccount::findOrFail($id);

        $paymentAccount->update($request->validated());

        return response()->json([
            'message' => 'Payment account updated successfully',
            'data' => new PaymentAccountResource($paymentAccount),
        ]);
    }

    public function destroy($id)
    {
        $paymentAccount = PaymentAccount::findOrFail($id);

        $paymentAccount->delete();

        return response()->json([
            'message' => 'Payment account deleted successfully',
        ]);
    }
}