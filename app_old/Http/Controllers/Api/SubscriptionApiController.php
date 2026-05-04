<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserPaidTree;
use Illuminate\Support\Facades\Validator;

class SubscriptionApiController extends Controller
{
    public function getUserSubscriptions(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
    }

    // MtTree -> Tree (treeDetail) relationship load kar rahe hain
    $subscriptions = UserPaidTree::where('user_id', $request->user_id)
        ->with([
            'user:id,name', 
            'tree.treeDetail:id,name' 
        ])
        ->orderBy('created_at', 'desc')
        ->get();

    $data = $subscriptions->map(function ($sub) {
        return [
            'id' => $sub->id,
            'user_name' => $sub->user->name ?? 'N/A',
            'payment_id' => $sub->payment_id,
            'amount' => $sub->amount,
            'tree_no' => $sub->tree->tree_no ?? 'N/A',
            // Yahan ID se Name match hoke aayega (Mango, Neem, etc.)
            'tree_name' => $sub->tree->treeDetail->name ?? $sub->tree->tree_name, 
            'latitude' => $sub->tree->latitude ?? '',
            'longitude' => $sub->tree->longitude ?? '',
            'address' => $sub->tree->address ?? '',
            'date' => $sub->created_at->format('Y-m-d H:i')
        ];
    });

    return response()->json([
        'success' => true,
        'message' => 'Subscriptions fetched successfully',
        'count'   => $data->count(),
        'data'    => $data
    ], 200);
}
}