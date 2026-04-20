<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;
use Razorpay\Api\Api;

class CustomerWalletController extends Controller
{
    private $key_id = 'rzp_test_RmiFXhQLtLKK03';
    private $key_secret = 'BHUaKbIq953ageW4Ib6pDSAo';

    public function createOrder(Request $request)
    {
        // Frontend sends: amount, project_count
        $api = new Api($this->key_id, $this->key_secret);

        $orderData = [
            'receipt'         => 'ord_' . time(),
            'amount'          => $request->amount * 100, // INR to Paise
            'currency'        => 'INR',
            'notes'           => ['user_id' => auth()->id()]
        ];

        try {
            $razorpayOrder = $api->order->create($orderData);
            return response()->json([
                'success' => true,
                'order_id' => $razorpayOrder['id'],
                'key' => $this->key_id
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function verifyPayment(Request $request)
    {
        $api = new Api($this->key_id, $this->key_secret);
        $input = $request->all();

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $input['razorpay_order_id'],
                'razorpay_payment_id' => $input['razorpay_payment_id'],
                'razorpay_signature' => $input['razorpay_signature']
            ]);

            // Success: Update Wallet
            Wallet::create([
                'user_id' => auth()->id(),
                'project_count' => $input['project_count'], // Frontend sends this
                'tree_count' => $input['tree_count'] ?? 0,
                'amount' => $input['amount'],
                'razorpay_payment_id' => $input['razorpay_payment_id'],
                'razorpay_order_id' => $input['razorpay_order_id'],
                'razorpay_signature' => $input['razorpay_signature'],
                'status' => 'success'
            ]);

            return response()->json(['success' => true, 'message' => 'Wallet Updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Verification Failed']);
        }
    }
}
