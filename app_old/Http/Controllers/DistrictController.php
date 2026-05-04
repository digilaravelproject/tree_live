<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\StateMaster;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DistrictsExport;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $page_title = 'District List';
        $states = StateMaster::all();
        $state_id = $request->get('state_id');

        $districts = District::with('state')
            ->when($state_id, fn($q) => $q->where('state_id', $state_id))
            ->orderBy('id', 'desc')
            ->get();

        return view('dashboard.districts.index', compact('page_title', 'districts', 'states', 'state_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:state_master,id',
            'district_name' => 'required|string|max:255',
        ]);

        District::create($request->only('state_id', 'district_name'));
        return back()->with('success', 'District added successfully!');
    }

    public function update(Request $request, District $district)
    {
        $request->validate([
            'state_id' => 'required|exists:state_master,id',
            'district_name' => 'required|string|max:255',
        ]);

        $district->update($request->only('state_id', 'district_name'));
        return back()->with('success', 'District updated successfully!');
    }

    public function destroy(District $district)
    {
        $district->delete();
        return back()->with('success', 'District deleted successfully!');
    }

    public function export(Request $request)
    {
        $state_id = $request->get('state_id');
        return Excel::download(new DistrictsExport($state_id), 'districts.xlsx');
    }
}
