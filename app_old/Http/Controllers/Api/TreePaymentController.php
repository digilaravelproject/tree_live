<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MtTree;
use App\Models\UserPaidTree;
use App\Models\UserFreeTree;
use App\Models\TreePrice;
use App\Models\Wallet;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TreePaymentController extends Controller
{
    private $keyId     = 'rzp_test_S9yXFuXcf0S6Ll';
    private $keySecret = '8esSABFrAQrY8r14S7T22Q4D';

    // =====================================================================
    // 1. CREATE ORDER
    // Input: { "user_id": 1, "tree_ids": [10, 12, 14] }
    // Amount auto-calculated from active tree_price
    // =====================================================================
    public function createOrder(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'user_id'    => 'required|exists:users,id',
            'tree_ids'   => 'required|array|min:1',
            'tree_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = User::find($request->user_id);

        // Only role_id == 3 users need to make payment
        if ($user->role_id != 3) {
            return response()->json([
                'success' => false,
                'message' => 'Payment is only required for Customer (role_id=3). Your account can access directly.',
            ], 403);
        }

        // Fetch active price
        $activePrice = TreePrice::active()->orderBy('id', 'desc')->first();
        if (!$activePrice || $activePrice->price <= 0) {
            return response()->json(['success' => false, 'message' => 'No active tree price has been set. Please contact the admin.'], 400);
        }

        $treeIds = $request->tree_ids;
        $userId  = $request->user_id;

        // Filter out already accessible trees (free + already paid)
        $freeRecord        = UserFreeTree::getOrCreate($userId);
        $alreadyFreeIds    = array_intersect($treeIds, $freeRecord->tree_ids ?? []);
        $alreadyPaidIds    = UserPaidTree::where('user_id', $userId)
                                ->whereIn('mt_tree_id', $treeIds)
                                ->pluck('mt_tree_id')
                                ->toArray();

        $alreadyAccessible = array_unique(array_merge(array_values($alreadyFreeIds), $alreadyPaidIds));
        $payableTreeIds    = array_values(array_diff($treeIds, $alreadyAccessible));

        if (empty($payableTreeIds)) {
            return response()->json([
                'success' => false,
                'message' => 'All selected trees are already accessible to you. No payment is required.',
            ], 400);
        }

        $pricePerTree  = $activePrice->price;
        $totalAmount   = $pricePerTree * count($payableTreeIds);
        $amountInPaise = $totalAmount * 100;

        $api       = new Api($this->keyId, $this->keySecret);
        $orderData = [
            'receipt'  => 'rcpt_' . time(),
            'amount'   => $amountInPaise,
            'currency' => 'INR',
            'notes'    => [
                'user_id'    => $userId,
                'tree_count' => count($payableTreeIds),
            ],
        ];

        try {
            $razorpayOrder = $api->order->create($orderData);

            return response()->json([
                'success'          => true,
                'message'          => 'Order created successfully.',
                'order_id'         => $razorpayOrder['id'],
                'price_per_tree'   => $pricePerTree,
                'tree_count'       => count($payableTreeIds),
                'total_amount'     => $totalAmount,
                'payable_tree_ids' => $payableTreeIds,
                'key'              => $this->keyId,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Razorpay Error: ' . $e->getMessage()], 500);
        }
    }

    // =====================================================================
    // 2. VERIFY PAYMENT & GRANT LIFETIME ACCESS
    // Input: { "razorpay_order_id": "...", "razorpay_payment_id": "...",
    //          "razorpay_signature": "...", "user_id": 1,
    //          "tree_ids": [10, 12], "project_id": 5 }
    // =====================================================================
    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'razorpay_order_id'   => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature'  => 'required',
            'user_id'             => 'required|exists:users,id',
            'tree_ids'            => 'required|array|min:1',
            'tree_ids.*'          => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $api = new Api($this->keyId, $this->keySecret);

        try {
            // Verify signature
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature'  => $request->razorpay_signature,
            ]);

            DB::beginTransaction();

            $treeIds   = $request->tree_ids;
            $userId    = $request->user_id;
            $projectId = $request->project_id ?? null;

            // Calculate amount from active price
            $activePrice  = TreePrice::active()->orderBy('id', 'desc')->first();
            $pricePerTree = $activePrice ? $activePrice->price : 0;
            $totalAmount  = $pricePerTree * count($treeIds);

            // 1. Update MT_TREES payment flag
            MtTree::whereIn('id', $treeIds)->update(['payment' => 1]);

            // 2. Create lifetime records in UserPaidTree (with duplicate check)
            $newlyPaidCount = 0;
            foreach ($treeIds as $treeId) {
                if (!UserPaidTree::where('user_id', $userId)->where('mt_tree_id', $treeId)->exists()) {
                    UserPaidTree::create([
                        'user_id'    => $userId,
                        'project_id' => $projectId,
                        'mt_tree_id' => $treeId,
                        'payment_id' => $request->razorpay_payment_id,
                        'amount'     => $pricePerTree,
                    ]);
                    $newlyPaidCount++;
                }
            }

            // 3. Create wallet entry
            Wallet::create([
                'user_id'             => $userId,
                'project_count'       => 0,
                'tree_count'          => count($treeIds),
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_signature'  => $request->razorpay_signature,
                'amount'              => $totalAmount,
                'status'              => 'success',
            ]);

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => 'Payment verified successfully! Lifetime access has been granted for the selected trees.',
                'paid_count'   => $newlyPaidCount,
                'total_amount' => $totalAmount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Payment verification failed: ' . $e->getMessage()], 500);
        }
    }

    // =====================================================================
    // 3. GET PRICE INFO — For frontend to check price before payment
    // Input: { "user_id": 1, "tree_ids": [10, 12, 14] }
    // =====================================================================
    public function getPriceInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'    => 'required|exists:users,id',
            'tree_ids'   => 'required|array|min:1',
            'tree_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user    = User::find($request->user_id);
        $treeIds = $request->tree_ids;

        // Role != 3: No payment needed
        if ($user->role_id != 3) {
            return response()->json([
                'success'        => true,
                'payment_needed' => false,
                'message'        => 'Your account can access all trees freely.',
            ]);
        }

        $freeRecord     = UserFreeTree::getOrCreate($request->user_id);
        $alreadyPaidIds = UserPaidTree::where('user_id', $request->user_id)
                            ->whereIn('mt_tree_id', $treeIds)
                            ->pluck('mt_tree_id')
                            ->toArray();

        $alreadyFreeIds    = array_values(array_intersect($treeIds, $freeRecord->tree_ids ?? []));
        $alreadyAccessible = array_unique(array_merge($alreadyFreeIds, $alreadyPaidIds));
        $newTreeIds        = array_values(array_diff($treeIds, $alreadyAccessible));

        $remainingFreeSlots = $freeRecord->remainingFreeSlots();
        $canGetFree         = array_slice($newTreeIds, 0, $remainingFreeSlots);
        $needsPayment       = array_values(array_diff($newTreeIds, $canGetFree));

        $activePrice  = TreePrice::active()->orderBy('id', 'desc')->first();
        $pricePerTree = $activePrice ? $activePrice->price : 0;
        $totalAmount  = $pricePerTree * count($needsPayment);

        return response()->json([
            'success'              => true,
            'free_slots_remaining' => $remainingFreeSlots,
            'already_accessible'   => count($alreadyAccessible),
            'will_get_free'        => count($canGetFree),
            'needs_payment_count'  => count($needsPayment),
            'needs_payment_ids'    => $needsPayment,
            'price_per_tree'       => $pricePerTree,
            'total_amount'         => $totalAmount,
            'payment_needed'       => !empty($needsPayment),
        ]);
    }
}