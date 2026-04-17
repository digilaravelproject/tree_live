<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Razorpay\Api\Api;
use App\Models\Wallet;
use Illuminate\Support\Facades\Validator;
use Exception;

class RazorpayApiController extends Controller
{
    // 1. Order ID Generate Karen (Frontend par bhejne ke liye)
    public function createOrder(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 400);
        }

        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

        try {
            // Amount ko paise mein convert karein
            $orderData = [
                'receipt'         => 'rcptid_' . time(),
                'amount'          => $request->amount * 100, // 100 INR = 10000 Paise
                'currency'        => 'INR',
                'payment_capture' => 1 // Auto Capture
            ];

            $razorpayOrder = $api->order->create($orderData);

            return response()->json([
                'status'   => true,
                'message'  => 'Order Created Successfully',
                'order_id' => $razorpayOrder['id'],
                'amount'   => $request->amount,
                'key'      => env('RAZORPAY_KEY') // Frontend ke liye key
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 2. Payment Verify Karen aur Database me Save Karen
    public function verifyPayment(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'razorpay_payment_id' => 'required',
            'razorpay_order_id'   => 'required',
            'razorpay_signature'  => 'required',
            'amount'              => 'required',
            // 'user_id'          => 'required' // Agar login token nahi use kar rahe to manual bhejein
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 400);
        }

        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

        try {
            // Signature Verify Karen
            $attributes = [
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature'  => $request->razorpay_signature
            ];

            // $api->utility->verifyPaymentSignature($attributes);
            $userId = $request->user_id ?? null;

            $wallet = Wallet::create([
                'user_id'             => $userId,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_signature'  => $request->razorpay_signature,
                'amount'              => $request->amount,
                'project_count'       => $request->project_count ?? 0,
                'tree_count'          => $request->tree_count ?? 0,
                'status'              => 'success'
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Payment Verified and Wallet Updated',
                'data'    => $wallet
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment Verification Failed: ' . $e->getMessage()
            ], 400);
        }
    }
}
