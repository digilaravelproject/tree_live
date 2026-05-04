<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RatingRequest;
use App\Models\UserRating;
use Illuminate\Support\Facades\Log;
use Exception;

class UserRatingController extends Controller
{
    /**
     * Submit/Update Rating
     */
    public function store(RatingRequest $request)
    {
        try {
            // Reverted to original logic (user_id as unique key) since rater_id column doesn't exist
            $rating = UserRating::updateOrCreate(
                ['user_id' => $request->user_id],
                ['rating' => $request->rating, 'comment' => $request->comment]
            );

            return response()->json([
                'success' => true,
                'message' => 'Rating updated successfully',
                'data' => $rating
            ]);
        } catch (Exception $e) {
            Log::error('Rating Submission Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit rating.'
            ], 500);
        }
    }
}
