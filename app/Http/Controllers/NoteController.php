<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        $notes = Note::latest()->get();
        $page_title = 'Notes';
        return view('notes.index', compact('notes', 'page_title'));
    }

    public function create()
    {
        $page_title = 'Add Note';
        return view('notes.create', compact('page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        Note::create($request->all());
        return redirect()->route('notes.index')->with('success', 'Note added successfully!');
    }

    public function edit(Note $note)
    {
        $page_title = 'Edit Note';
        return view('notes.edit', compact('note', 'page_title'));
    }

    public function update(Request $request, Note $note)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $note->update($request->all());
        return redirect()->route('notes.index')->with('success', 'Note updated successfully!');
    }

    public function destroy(Note $note)
    {
        $note->delete();
        return redirect()->route('notes.index')->with('success', 'Note deleted successfully!');
    }
}
