<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\State;
use App\Traits\ApiResponse;

class SupportController extends Controller
{
    use ApiResponse;

    /**
     * FAQ List
     */
    public function faqs()
    {
        return $this->success(Faq::latest()->get(), 'FAQs fetched successfully.');
    }

    /**
     * State List
     */
    public function states()
    {
        $states = State::select('id', 'state_name')
            ->orderBy('state_name')
            ->get();
            
        return $this->success($states, 'States fetched successfully.');
    }
}
