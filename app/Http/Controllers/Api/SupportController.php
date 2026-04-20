<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\State;

class SupportController extends Controller
{
    /**
     * FAQ List
     */
    public function faqs()
    {
        return response()->json([
            'status' => true,
            'faqs' => Faq::latest()->get()
        ]);
    }

    /**
     * State List
     */
    public function states()
    {
        $states = State::select('id', 'state_name')
            ->orderBy('state_name')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'State list fetched successfully',
            'data' => $states
        ]);
    }
}
