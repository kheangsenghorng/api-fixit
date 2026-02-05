<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayWayController extends Controller
{
    public function getPaymentData(Request $request)
    {
        $req_time = now()->format('YmdHis');
        $merchant_id = env('ABA_MERCHANT_ID');
        $tran_id = 'TXN' . time();

        $amount = number_format($request->amount ?? 10, 2, '.', '');
        $currency = "USD";

        $items = base64_encode(json_encode([
            [
                "name" => "Order ".$tran_id,
                "quantity" => 1,
                "price" => $amount
            ]
        ]));

        $return_url = base64_encode(env('FRONTEND_URL') . "/success");
        $cancel_url = base64_encode(env('FRONTEND_URL') . "/cancel");

        $fields = [
            "req_time" => $req_time,
            "merchant_id" => $merchant_id,
            "tran_id" => $tran_id,
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "customer@test.com",
            "phone" => "012345678",
            "type" => "purchase",
            "payment_option" => "cards",
            "items" => $items,
            "shipping" => "0",
            "amount" => $amount,
            "currency" => $currency,
            "return_url" => $return_url,
            "cancel_url" => $cancel_url,
            "skip_success_page" => "1",
            "continue_success_url" => "",
            "return_deeplink" => "",
            "custom_fields" => "",
            "return_params" => "",
            "payout" => "",
            "lifetime" => "",
            "additional_params" => "",
            "google_pay_token" => "",
        ];

        // Ensure no null values
        foreach ($fields as $k => $v) {
            $fields[$k] = $v ?? '';
        }

        // Hash string (ORDER IS CRITICAL)
        $hashStr =
            $fields['req_time'] .
            $fields['merchant_id'] .
            $fields['tran_id'] .
            $fields['amount'] .
            $fields['items'] .
            $fields['shipping'] .
            $fields['firstname'] .
            $fields['lastname'] .
            $fields['email'] .
            $fields['phone'] .
            $fields['type'] .
            $fields['payment_option'] .
            $fields['return_url'] .
            $fields['cancel_url'] .
            $fields['continue_success_url'] .
            $fields['return_deeplink'] .
            $fields['currency'] .
            $fields['custom_fields'] .
            $fields['return_params'] .
            $fields['payout'] .
            $fields['lifetime'] .
            $fields['additional_params'] .
            $fields['google_pay_token'] .
            $fields['skip_success_page'];

        $fields['hash'] = base64_encode(
            hash_hmac('sha512', $hashStr, env('ABA_API_KEY'), true)
        );

        // Return fields ONLY (browser will post to PayWay)
        return response()->json($fields);
    }

    public function checkTransactionV2(Request $request)
    {
        $req_time = now()->format('YmdHis');
        $merchant_id = env('ABA_MERCHANT_ID');
        $tran_id = $request->tran_id;

        $hash = base64_encode(
            hash_hmac('sha512',$req_time.$merchant_id.$tran_id,env('ABA_API_KEY'),true)
        );

        return Http::post(env('ABA_CHECK_TRANSACTION2_URL'),[
            "req_time"=>$req_time,
            "merchant_id"=>$merchant_id,
            "tran_id"=>$tran_id,
            "hash"=>$hash
        ])->json();
    }

    public function callback(Request $request)
    {
        logger("ABA CALLBACK",$request->all());
        return response()->json(["status"=>"ok"]);
    }
}
