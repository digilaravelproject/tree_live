<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::latest()->get();
        return view('videos.index', compact('videos'))->with('page_title', 'Videos');
    }

    public function create()
    {
        return view('videos.create')->with('page_title', 'Upload Video');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video' => 'required|mimes:mp4,mov,avi,mkv|max:204800', // 200MB max
        ]);

        // Check if folder exists
        if (!File::exists(public_path('video'))) {
            File::makeDirectory(public_path('video'), 0755, true);
        }

        $file = $request->file('video');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('video'), $filename);

        Video::create([
            'title' => $request->title,
            'video' => $filename
        ]);

        return redirect()->route('videos.index')->with('success', 'Video uploaded successfully!');
    }

    public function edit(Video $video)
    {
        return view('videos.edit', compact('video'))->with('page_title', 'Edit Video');
    }

    public function update(Request $request, Video $video)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video' => 'nullable|mimes:mp4,mov,avi,mkv|max:204800',
        ]);

        $data['title'] = $request->title;

        if ($request->hasFile('video')) {
            // Delete old video
            if (File::exists(public_path('video/' . $video->video))) {
                File::delete(public_path('video/' . $video->video));
            }

            // Upload new video
            $file = $request->file('video');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('video'), $filename);

            $data['video'] = $filename;
        }

        $video->update($data);

        return redirect()->route('videos.index')->with('success', 'Video updated successfully!');
    }

    public function destroy(Video $video)
    {
        // Delete file
        if (File::exists(public_path('video/' . $video->video))) {
            File::delete(public_path('video/' . $video->video));
        }

        $video->delete();

        return redirect()->back()->with('success', 'Video deleted successfully!');
    }
}
