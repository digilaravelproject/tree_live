<?php

namespace App\Http\Controllers;

use App\Models\Tahsil;
use App\Models\StateMaster;
use App\Models\District;
use Illuminate\Http\Request;
use App\Exports\TahsilsExport;
use Maatwebsite\Excel\Facades\Excel;


class TahsilController extends Controller
{
    public function index(Request $request)
    {
        $state_id = $request->state_id;
        $district_id = $request->district_id;

        $states = StateMaster::all();
        $districts = District::all();

        $tahsils = Tahsil::with(['state', 'district'])
            ->when($state_id, fn($q) => $q->where('state_id', $state_id))
            ->when($district_id, fn($q) => $q->where('district_id', $district_id))
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('dashboard.tahsil_list', compact('tahsils', 'states', 'districts', 'state_id', 'district_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahsil_name' => 'required|string|max:255',
            'state_id' => 'required|exists:state_master,id',
            'district_id' => 'required|exists:districts_master,id',
        ]);

        Tahsil::create($request->all());
        return back()->with('success', 'Tahsil added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tahsil_name' => 'required|string|max:255',
            'state_id' => 'required|integer',
            'district_id' => 'required|integer',
        ]);

        $tahsil = Tahsil::findOrFail($id);
        $tahsil->update($request->only('tahsil_name', 'state_id', 'district_id'));

        return redirect()->route('tahsil.index')->with('success', 'Tahsil updated successfully!');
    }


    public function destroy($id)
    {
        Tahsil::findOrFail($id)->delete();
        return back()->with('success', 'Tahsil deleted successfully.');
    }
    public function export(Request $request)
    {
        $state_id = $request->get('state_id');
        $district_id = $request->get('district_id');

        return Excel::download(new TahsilsExport($state_id, $district_id), 'tahsils.xlsx');
    }
    public function edit($id)
    {
        $tahsil = Tahsil::with(['state', 'district'])->findOrFail($id);
        $states = StateMaster::all();
        $districts = District::where('state_id', $tahsil->state_id)->get();

        return view('master.tahsil_edit', compact('tahsil', 'states', 'districts'));
    }
}
