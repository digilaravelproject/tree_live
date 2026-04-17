<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;

class RazorpayController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('razorpay-view', compact('user'));
    }
    public function store(Request $request)
    {
        $input = $request->all();
        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

        if (!empty($input['razorpay_payment_id'])) {
            try {
                $payment = $api->payment->fetch($input['razorpay_payment_id']);

                if ($payment['status'] == 'authorized') {
                    $payment->capture(['amount' => $payment['amount']]);
                }

                Wallet::create([
                    'user_id'             => Auth::id() ?? null,
                    'razorpay_payment_id' => $payment['id'],
                    'razorpay_order_id'   => $payment['order_id'] ?? 'N/A',
                    'razorpay_signature'  => $input['razorpay_signature'] ?? null,
                    'amount'              => $payment['amount'] / 100,
                    'project_count'       => $request->project_count ?? 0,
                    'tree_count'          => $request->tree_count ?? 0,
                    'status'              => 'success'
                ]);

                Session::put('success', 'Payment successful! Transaction ID: ' . $input['razorpay_payment_id']);
            } catch (\Exception $e) {
                Session::put('error', $e->getMessage());
                return redirect()->back();
            }
        }
        return redirect()->back();
    }
}
