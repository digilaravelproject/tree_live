<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RatingRequest;
use App\Models\UserRating;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Exception;

class UserRatingController extends Controller
{
    use ApiResponse;

    /**
     * Submit/Update Rating
     */
    public function store(RatingRequest $request)
    {
        try {
            $rating = UserRating::updateOrCreate(
                ['user_id' => $request->user_id, 'rater_id' => Auth::id()],
                ['rating' => $request->rating, 'comment' => $request->comment]
            );

            return $this->success($rating, 'Rating submitted successfully.');
        } catch (Exception $e) {
            return $this->error('Failed to submit rating.', 500);
        }
    }
}
