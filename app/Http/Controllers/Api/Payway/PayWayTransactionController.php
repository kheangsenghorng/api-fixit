<?php

namespace App\Http\Controllers\Api\Payway;

use App\Http\Controllers\Controller;
use App\Services\PayWayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayWayTransactionController extends Controller
{
    public function __construct(
        protected PayWayService $payWayService
    ) {}


    public function purchase(Request $request): JsonResponse
{
    $data = $request->validate([
        'tran_id' => ['nullable', 'string', 'max:20'],
        'amount' => ['required', 'numeric', 'min:0.01'],
        'currency' => ['nullable', 'in:USD,KHR'],

        'firstname' => ['nullable', 'string', 'max:100'],
        'lastname' => ['nullable', 'string', 'max:100'],
        'email' => ['nullable', 'email', 'max:50'],
        'phone' => ['nullable', 'string', 'max:20'],

        'type' => ['nullable', 'in:purchase,pre-auth'],

        'payment_option' => [
            'nullable',
            'in:cards,abapay_khqr,abapay_khqr_deeplink,alipay,wechat,google_pay',
        ],

        'items' => ['nullable', 'array'],
        'items.*.name' => ['required_with:items', 'string'],
        'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
        'items.*.price' => ['required_with:items', 'numeric', 'min:0'],

        'return_params' => ['nullable', 'string'],
        'view_type' => ['nullable', 'in:hosted_view,popup'],
        'lifetime' => ['nullable', 'integer', 'min:3'],
        'skip_success_page' => ['nullable', 'in:0,1'],
    ]);

    $response = $this->payWayService->purchase($data);

    return response()->json([
        'success' => true,
        'message' => 'PayWay purchase created successfully',
        'data' => $response,
    ]);
}

    /**
     * Check PayWay transaction status
     */
    public function checkTransaction(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tran_id' => ['required', 'string', 'max:20'],
        ]);

        $response = $this->payWayService->checkTransaction($data['tran_id']);

        return response()->json([
            'success' => true,
            'message' => 'Transaction checked successfully',
            'data' => $response,
        ]);
    }

    /**
     * Close / cancel PayWay transaction
     */
    public function closeTransaction(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tran_id' => ['required', 'string', 'max:20'],
        ]);

        $response = $this->payWayService->closeTransaction($data['tran_id']);

        return response()->json([
            'success' => true,
            'message' => 'Transaction close request sent successfully',
            'data' => $response,
        ]);
    }

    /**
     * Generate ABA Account Link QR
     */
    public function linkAccountQr(Request $request): JsonResponse
    {
        $data = $request->validate([
            'return_param' => ['required', 'string', 'max:255'],
        ]);

        $response = $this->payWayService->requestAccountLinkQr($data['return_param']);

        return response()->json([
            'success' => true,
            'message' => 'ABA account link QR generated successfully',
            'data' => $response,
        ]);
    }
}