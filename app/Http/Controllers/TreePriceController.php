<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TreePrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TreePriceController extends Controller
{
    /**
     * Display a listing of the prices (History).
     */
    public function index()
    {
        $page_title = 'Tree Price Settings';

        // Fetch prices: Active first, then by date descending
        $prices = TreePrice::orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.tree_price_list', compact('page_title', 'prices'));
    }

    /**
     * Show the form for creating a new price.
     */
    public function create()
    {
        $page_title = 'Set New Tree Price';
        return view('dashboard.tree_price_add', compact('page_title'));
    }

    /**
     * Store a newly created price in storage.
     * This makes the new price Active and all others Inactive.
     */
    public function store(Request $request)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Step 1: Deactivate ALL existing prices
                TreePrice::query()->update(['is_active' => 0]);

                // Step 2: Create the NEW price as Active
                TreePrice::create([
                    'price'     => $request->price,
                    'is_active' => 1
                ]);
            });

            return redirect()->route('tree.price.list')->with('success', 'New Tree Price updated and activated!');
        } catch (\Exception $e) {
            Log::error("Tree Price Store Error: " . $e->getMessage());
            return back()->with('error', 'Something went wrong!')->withInput();
        }
    }

    /**
     * Manually activate a specific historical price.
     */
    public function makeActive($id)
    {
        try {
            DB::transaction(function () use ($id) {
                // Step 1: Deactivate ALL prices
                TreePrice::query()->update(['is_active' => 0]);

                // Step 2: Activate the selected price
                $priceRecord = TreePrice::findOrFail($id);
                $priceRecord->update(['is_active' => 1]);
            });

            return redirect()->back()->with('success', 'Price activated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating status.');
        }
    }

    /**
     * Remove the specified price from storage.
     */
    public function destroy($id)
    {
        $price = TreePrice::findOrFail($id);
        $price->delete();

        return redirect()->back()->with('success', 'Record deleted successfully.');
    }
}
