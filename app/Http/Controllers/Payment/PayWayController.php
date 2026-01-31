<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayWayController extends Controller
{
    private function reqTime()
    {
        return now()->utc()->format('YmdHis');
    }

    private function tran()
    {
        return 'TX'.now()->format('ymdHis').rand(100,999);
    }

    private function sign($b4hash)
    {
        return base64_encode(hash_hmac(
            'sha512',
            $b4hash,
            env('ABA_API_KEY'),
            true
        ));
    }

    public function index()
{
    return view('pay');
}
public function success(Request $request)
{
    return view('success',[
        'tran_id'=>$request->tran_id ?? null
    ]);
}
public function cancel()
{
    return view('cancel');
}

    // ================= CARD HOSTED =================
public function card(Request $request)
{
    $req_time = now()->utc()->format('YmdHis');
    $merchant_id = env('ABA_MERCHANT_ID');
    $tran_id = 'TXN'.time();
    $amount = number_format($request->amount ?? 1, 2, '.', '');

    $items = base64_encode(json_encode([
        ["name"=>"Test Product","quantity"=>1,"price"=>$amount]
    ]));

    $firstname="Test";
    $lastname="User";
    $email="test@test.com";
    $phone="012345678";

    $type="purchase";
    $payment_option="cards";
    $shipping="0";
    $currency="USD";

    $return_url = base64_encode(url('/api/payment/payway/callback'));
    $cancel_url = base64_encode(env('FRONTEND_URL')."/cancel");

    $continue_success_url="";
    $return_deeplink="";
    $custom_fields="";
    $return_params="";
    $payout="";
    $lifetime="";
    $additional_params="";
    $google_pay_token="";
    $skip_success_page="";

    // EXACT HASH ORDER
    $b4hash =
        $req_time.$merchant_id.$tran_id.$amount.$items.$shipping.
        $firstname.$lastname.$email.$phone.$type.$payment_option.
        $return_url.$cancel_url.$continue_success_url.$return_deeplink.
        $currency.$custom_fields.$return_params.$payout.$lifetime.
        $additional_params.$google_pay_token.$skip_success_page;

    $hash = base64_encode(hash_hmac('sha512',$b4hash,env('ABA_API_KEY'),true));

    $res = Http::asMultipart()->post(env('ABA_PURCHASE_URL'),[
        'req_time'=>$req_time,
        'merchant_id'=>$merchant_id,
        'tran_id'=>$tran_id,
        'amount'=>$amount,
        'items'=>$items,
        'shipping'=>$shipping,
        'firstname'=>$firstname,
        'lastname'=>$lastname,
        'email'=>$email,
        'phone'=>$phone,
        'type'=>$type,
        'payment_option'=>$payment_option,
        'currency'=>$currency,
        'return_url'=>$return_url,
        'cancel_url'=>$cancel_url,
        'continue_success_url'=>$continue_success_url,
        'return_deeplink'=>$return_deeplink,
        'custom_fields'=>$custom_fields,
        'return_params'=>$return_params,
        'payout'=>$payout,
        'lifetime'=>$lifetime,
        'additional_params'=>$additional_params,
        'google_pay_token'=>$google_pay_token,
        'skip_success_page'=>$skip_success_page,
        'view_type'=>'popup',
        'hash'=>$hash,
    ]);

    $html = $res->getBody()->getContents();

   return response($html,200)
    ->header('Accept','application/json');

}

    
    // ================= QR =================
    public function qr(Request $request)
    {
        $req_time=$this->reqTime();
        $tran_id=$this->tran();
        $merchant_id=env('ABA_MERCHANT_ID');

        $amount=number_format($request->amount,2,'.','');

        $payment_option="abapay_khqr";
        $currency="USD";

        $b4hash=$req_time.$merchant_id.$tran_id.$amount.$payment_option.$currency;

        $hash=$this->sign($b4hash);

        $res=Http::post(env('ABA_QR_URL'),[
            'req_time'=>$req_time,
            'merchant_id'=>$merchant_id,
            'tran_id'=>$tran_id,
            'amount'=>$amount,
            'currency'=>$currency,
            'payment_option'=>$payment_option,
            'qr_image_template'=>'template3_color',
            'hash'=>$hash,
        ]);

        return response()->json($res->json());
    }

    // ================= CHECK =================
    public function checkTransactionV2(Request $r)
    {
        $req_time=$this->reqTime();
        $merchant_id=env('ABA_MERCHANT_ID');
        $tran_id=$r->tran_id;

        $hash=$this->sign($req_time.$merchant_id.$tran_id);

        $res=Http::post(env('ABA_CHECK_TRANSACTION2_URL'),[
            'req_time'=>$req_time,
            'merchant_id'=>$merchant_id,
            'tran_id'=>$tran_id,
            'hash'=>$hash,
        ]);

        return response()->json($res->json());
    }

    // ================= CALLBACK =================
    public function callback(Request $r)
    {
        logger($r->all());
        return response()->json(['ok'=>true]);
    }
}
