<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::latest()->get();
        $page_title = 'Contact List';
        return view('contacts.index', compact('contacts', 'page_title'));
    }

    public function create()
    {
        $page_title = 'Add Contact';
        return view('contacts.create', compact('page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'instagram' => 'nullable|url',
            'facebook' => 'nullable|url',
            'whatsapp' => 'nullable|string|max:255',
            'youtube' => 'nullable|url',
            'linkedin' => 'nullable|url',
        ]);

        Contact::create($request->all());

        return redirect()->route('contacts.index')->with('success', 'Contact created successfully!');
    }

    public function edit(Contact $contact)
    {
        $page_title = 'Edit Contact';
        return view('contacts.edit', compact('contact', 'page_title'));
    }

    public function update(Request $request, Contact $contact)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'instagram' => 'nullable|url',
            'facebook' => 'nullable|url',
            'whatsapp' => 'nullable|string|max:255',
            'youtube' => 'nullable|url',
            'linkedin' => 'nullable|url',
        ]);

        $contact->update($request->all());

        return redirect()->route('contacts.index')->with('success', 'Contact updated successfully!');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully!');
    }
}
