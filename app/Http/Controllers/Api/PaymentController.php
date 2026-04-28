<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\BakongService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\OwnerPayout;
use App\Models\PaymentSplit;
use Illuminate\Support\Facades\DB;
use Imagick;
use ImagickPixel;


class PaymentController extends Controller
{ 
    // Removed invalid function declaration
    protected BakongService $bakongService;

    public function __construct(BakongService $bakongService)
    {
        $this->bakongService = $bakongService;
    }

    public function index()
    {
        $payments = Payment::with([
            'user',
            'owner',
            'serviceBooking',
            'coupon',
        ])->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Payment list retrieved successfully',
            'data' => $payments,
        ]);
    }

    public function store(StorePaymentRequest $request)
    {
        $payment = DB::transaction(function () use ($request) {
            $payment = Payment::create([
                'user_id' => $request->user_id,
                'owner_id' => $request->owner_id,
                'service_booking_id' => $request->service_booking_id,
                'coupons_id' => $request->coupons_id,
                'transaction_id' => $request->transaction_id,
                'original_amount' => $request->original_amount,
                'discount_amount' => $request->discount_amount ?? 0,
                'final_amount' => $request->final_amount,
                'method' => $request->input('method'),
                'status' => $request->status ?? 'pending',
            ]);
    
            if ($payment->status === 'paid') {
                $serviceAmount = $payment->final_amount;
    
                $adminCommission = round($serviceAmount * 0.10, 2);
                $ownerPayout = round($serviceAmount - $adminCommission, 2);
    
                $split = PaymentSplit::create([
                    'payment_id' => $payment->id,
                    'owner_id' => $payment->owner_id,
                    'service_amount' => $serviceAmount,
                    'admin_commission' => $adminCommission,
                    'owner_payout' => $ownerPayout,
                ]);
    
                OwnerPayout::create([
                    'owner_id' => $payment->owner_id,
                    'split_id' => $split->id,
                    'amount' => $ownerPayout,
                    'method' => 'bank_transfer',
                    'status' => 'pending',
                ]);
            }
    
            return $payment;
        });
    
        return response()->json([
            'success' => true,
            'message' => 'Payment created successfully',
            'data' => $payment,
        ], 201);
    }
    public function show(Payment $payment)
    {
        $payment->load([
            'user',
            'owner',
            'serviceBooking',
            'coupon',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment retrieved successfully',
            'data' => $payment,
        ]);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        $payment->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully',
            'data' => $payment->fresh(),
        ]);
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully',
        ]);
    }

    public function generateIndividualKhqr(Request $request)
    {
        $result = $this->bakongService->generateIndividualKhqr($request->all());

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function generateMerchantKhqr(Request $request)
    {
        $result = $this->bakongService->generateMerchantKhqr($request->all());

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function generateKhqrImage(Request $request)
    {
        $result = $this->bakongService->generateKhqrImage([
            'qr' => $request->qr,
        ]);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function generateDeeplink(Request $request)
    {
        $result = $this->bakongService->generateDeeplink([
            'qr_code' => $request->qr_code,
        ]);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function checkTransactionByMd5(Request $request)
    {
        $request->validate([
            'md5' => ['required', 'string'],
        ]);
    
        $result = $this->bakongService->checkTransactionByMd5($request->md5);
    
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function checkTransactionByHash(Request $request)
    {
        $request->validate([
            'hash' => ['required', 'string'],
        ]);

        $result = $this->bakongService->checkTransactionByHash($request->hash);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function checkBakongAccount(Request $request)
    {
        $request->validate([
            'bakong_account_id' => ['required', 'string'],
        ]);
    
        $result = $this->bakongService->checkBakongAccount(
            $request->bakong_account_id
        );
    
        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'] ?? 'Bakong account check failed',
                'data' => $result['data'] ?? null,
            ], 400);
        }
    
        $bakong = $result['data'] ?? [];
    
        // Bakong real account info is inside data.data
        $account = $bakong['data'] ?? [];
    
        return response()->json([
            'message' => $bakong['responseMessage'] ?? 'Bakong account checked successfully',
            'data' => [
                'accountStatus' => $account['accountStatus'] ?? null,
                'canReceive' => $account['canReceive'] ?? false,
                'frozen' => $account['frozen'] ?? null,
                'fullName' => $account['fullName'] ?? null,
                'kycStatus' => $account['kycStatus'] ?? null,
    
                'responseCode' => $bakong['responseCode'] ?? null,
                'responseMessage' => $bakong['responseMessage'] ?? null,
                'errorCode' => $bakong['errorCode'] ?? null,
            ],
        ]);
    }

    public function checkTransactionByExternalRef(Request $request)
    {
        $request->validate([
            'external_ref' => ['required', 'string'],
        ]);

        $result = $this->bakongService->checkTransactionByExternalRef($request->external_ref);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function generatePayment(Request $request)
    {
        $validated = $request->validate([
            'currency' => 'nullable|string|in:usd',
            'amount' => 'nullable|numeric|min:0',
            'account_information' => 'nullable|string',
            'bill_number' => 'nullable|string|max:25',
            'mobile_number' => 'nullable|string|max:25',
            'store_label' => 'nullable|string|max:25',
            'terminal_label' => 'nullable|string|max:25',
            'purpose_of_transaction' => 'nullable|string|max:25',
            'merchant_city' => 'nullable|string|max:15',
            'merchant_category_code' => ['nullable', 'regex:/^\d{4}$/'],
            'expiration_timestamp' => 'nullable|numeric',
        ]);
    
        $payload = [
            'bakong_account_id' => config('services.bakong.admin_account_id'),
            'merchant_name' => config('services.bakong.merchant_name'),
            'account_information' => $validated['account_information'] ?? null,
            'currency' => strtolower($validated['currency'] ?? 'usd'),
            'amount' => $validated['amount'] ?? null,
            'merchant_city' => $validated['merchant_city'] ?? 'Phnom Penh',
            'bill_number' => $validated['bill_number'] ?? null,
            'mobile_number' => $validated['mobile_number'] ?? null,
            'store_label' => $validated['store_label'] ?? null,
            'terminal_label' => $validated['terminal_label'] ?? null,
            'purpose_of_transaction' => $validated['purpose_of_transaction'] ?? 'Payment',
            'expiration_timestamp' => $validated['expiration_timestamp'] ?? now()->addMinutes(30)->valueOf(),
            'merchant_category_code' => $validated['merchant_category_code'] ?? '5999',
        ];
    
        $khqrResult = $this->bakongService->generateIndividualKhqr($payload);
        $khqrString = $khqrResult['data']['data']['qr'] ?? null;
        $md5 = $khqrResult['data']['data']['md5'] ?? null;
    
        $deeplinkResult = null;
    
        if ($khqrString) {
            $deeplinkResult = $this->bakongService->generateDeeplink([
                'qr_code' => $khqrString,
                'app_icon_url' => config('services.deeplink.icon_url'),
                'app_name' => config('services.deeplink.app_name'),
                'app_deep_link_callback' => config('services.deeplink.callback_url'),
            ]);
        }
    
        $imageResult = null;
        if ($khqrString) {
            $imageResult = $this->bakongService->generateKhqrImage([
                'qr' => $khqrString,
            ]);
        }
    
        return response()->json([
            'success' => true,
            'message' => 'KHQR generated successfully',
            'data' => [
                'payload' => $payload,
                'khqr' => $khqrResult,
                'qr_string' => $khqrString,
                'md5' => $md5,
                'deeplink' => $deeplinkResult,
                'image' => $imageResult,
            ],
        ]);
    }
  
    
    public function downloadPaymentQr(Request $request)
    {
        $validated = $request->validate([
            'qr' => ['required', 'string'],
        ]);
    
        $imageResult = $this->bakongService->generateKhqrImage([
            'qr' => $validated['qr'],
        ]);
    
        if (($imageResult['data']['status']['code'] ?? 1) !== 0) {
            return response()->json([
                'success' => false,
                'message' => $imageResult['data']['status']['message'] ?? 'QR generation failed',
                'response' => $imageResult,
            ], 422);
        }
    
        $image = $imageResult['data']['data']['image'] ?? null;
    
        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to generate QR image',
                'response' => $imageResult,
            ], 422);
        }
    
        $prefix = 'data:image/svg+xml;base64,';
    
        $base64 = str_replace($prefix, '', $image);
        $svg = base64_decode($base64, true);
    
        if ($svg === false) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid SVG image data',
            ], 422);
        }
    
        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="khqr-payment.svg"',
        ]);
    }
}