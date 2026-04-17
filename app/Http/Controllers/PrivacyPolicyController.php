<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;

class PrivacyPolicyController extends Controller
{
    public function index()
    {
        $policies = PrivacyPolicy::orderBy('id', 'desc')->get();
        return view('privacy.index', compact('policies'));
    }

    public function create()
    {
        return view('privacy.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
        ]);

        PrivacyPolicy::create($request->all());

        return redirect()->route('privacy.index')->with('success', 'Policy added successfully.');
    }

    public function edit(PrivacyPolicy $privacy)
    {
        return view('privacy.edit', compact('privacy'));
    }

    public function update(Request $request, PrivacyPolicy $privacy)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
        ]);

        $privacy->update($request->all());

        return redirect()->route('privacy.index')->with('success', 'Policy updated successfully.');
    }

    public function destroy(PrivacyPolicy $privacy)
    {
        $privacy->delete();
        return redirect()->route('privacy.index')->with('success', 'Policy deleted successfully.');
    }

    public function print(PrivacyPolicy $privacy)
    {
        return view('privacy.print', compact('privacy'));
    }
}
