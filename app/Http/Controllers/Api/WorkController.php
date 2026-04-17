<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Video;
use App\Models\Contact;
use App\Models\Note;
use App\Models\PrivacyPolicy;
use App\Models\Tree;

class WorkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $videos = Video::latest()->get();

        // Return as JSON
        return response()->json([
            'status' => true,
            'videos' => $videos->map(function ($video) {
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'video_url' => url('video/' . $video->video),
                    'created_at' => $video->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $video->updated_at->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }

    public function contact_list()
    {
        try {
            $contacts = Contact::orderBy('id', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Contact list fetched successfully.',
                'data' => $contacts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function notes_list()
    {
        try {
            $notes = Note::orderBy('id', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Notes list fetched successfully.',
                'data' => $notes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function tree_list_old()
    {
        $trees = Tree::all(['id', 'name']);
        return response()->json($trees);
    }

    public function tree_list()
    {
        $trees = Tree::leftJoin('scientific_names', 'trees.id', '=', 'scientific_names.tree_id')
            ->leftJoin('families', 'scientific_names.tree_id', '=', 'families.tree_id')
            ->select(
                'trees.id',
                'trees.name AS tree_name',
                'scientific_names.scientific_name',
                'families.family_name'
            )
            ->get();

        return response()->json($trees);
    }

    public function privacy_po(Request $request)
    {

        $policies = PrivacyPolicy::latest()->get();
        $data = $policies->map(function ($p) {
            return [
                'id'          => $p->id,
                'title'       => $p->title,
                'content'     => $p->content,
                'created_at'  => $p->created_at ? $p->created_at->toDateTimeString() : null,
                'updated_at'  => $p->updated_at ? $p->updated_at->toDateTimeString() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'policies' => $data,
        ], 200);
    }
    public function show($id)
    {
        $tree = Tree::with(['scientificName', 'family'])->find($id);

        if (!$tree) {
            return response()->json(['message' => 'Tree not found'], 404);
        }

        return response()->json([
            'id' => $tree->id,
            'name' => $tree->name,
            'scientific_name_id' => $tree->scientificName->id ?? null,
            'scientific_name' => $tree->scientificName->scientific_name ?? null,
            'family_name_id' => $tree->family->id ?? null,
            'family_name' => $tree->family->family_name ?? null,
        ]);
    }
    public function calculate(Request $request)
    {
        $request->validate([
            'girth' => 'required|numeric|min:1',
        ]);

        $girth = $request->girth;

        $height_m   = $girth / 5;
        $canopy_m   = $girth / 6;
        $age        = $girth / 3;

        $height_ft  = $height_m * 3.2;
        $canopy_ft  = $canopy_m * 3.2;

        return response()->json([
            'girth_cm'            => round($girth),
            'estimated_height_m'  => round($height_m),
            'estimated_height_ft' => round($height_ft),
            'estimated_canopy_m'  => round($canopy_m),
            'estimated_canopy_ft' => round($canopy_ft),
            'estimated_age_years' => round($age),
        ]);
    }
}
