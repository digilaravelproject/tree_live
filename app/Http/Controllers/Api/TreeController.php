<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MtTree;
use App\Models\Project;
use App\Models\Family;
use App\Models\ScientificName;
use App\Models\Tree;
use App\Models\District;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessImageUpload; 
use Intervention\Image\Facades\Image;


class TreeController extends Controller
{
    // ✅ Create New Tree Record
    public function store_old(Request $request)
    {
        // ✅ Step 1: Validate request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'tree_name' => 'required|string|max:255',
            'project_id' => 'required|integer|exists:projects,id', // ✅ Must exist in Projects table
            'tree_images.*' => 'image|mimes:jpeg,png,jpg|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // ✅ Step 2: Fetch project to get its limit
        $project = Project::find($request->project_id);
        if (!$project) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid project selected.'
            ], 404);
        }

        // ✅ Step 3: Check current tree count for this project
        $existingTreeCount = MtTree::where('project_id', $request->project_id)->count();

        if ($existingTreeCount >= $project->limit) {
            return response()->json([
                'status' => false,
                'message' => 'Tree creation limit exceeded for this project.'
            ], 409);
        }

        // ✅ Step 4: Handle image upload
        $imagePaths = [];
        if ($request->hasFile('tree_images')) {
            foreach ($request->file('tree_images') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('tree_images');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $file->move($destinationPath, $filename);
                $imagePaths[] = 'tree_images/' . $filename;
            }
        }

        // ✅ Step 5: Create tree record
        $tree = MtTree::create([
            'project_id' => $request->project_id,
            'user_id' => $request->user_id,
            'ward_plot_no' => $request->ward_plot_no,
            'tree_no' => $request->tree_no,
            'tree_name' => $request->tree_name,
            'scientific_name' => $request->scientific_name,
            'family' => $request->family,
            'girth' => $request->girth,
            'height' => $request->height,
            'canopy' => $request->canopy,
            'age' => $request->age,
            'condition' => $request->condition,
            'address' => $request->address,
            'landmark' => $request->landmark,
            'ownership' => $request->ownership,
            'concern_person' => $request->concern_person,
            'remark' => $request->remark,
            'tree_image_upload' => $request->tree_image_upload,
            'captured_image' => $request->captured_image,
            'all_captured_images' => json_encode($imagePaths),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'datetime' => $request->datetime,
        ]);

        // ✅ Step 6: Success response
        return response()->json([
            'status' => true,
            'message' => 'Tree record created successfully',
            'data' => $tree
        ]);
    }

//   public function store(Request $request)
// {
//     // ✅ Step 1: Normalize input (object OR array)
//     $trees = $request->json()->all();

//     if (isset($trees[0]) && is_array($trees[0])) {
//         // Multiple records
//         $treeDataList = $trees;
//     } else {
//         // Single record
//         $treeDataList = [$trees];
//     }

//     $createdTrees = [];

//     foreach ($treeDataList as $index => $treeData) {

//         // ✅ Step 2: Validate each tree
//         $validator = Validator::make($treeData, [
//             'user_id' => 'required',
//             'tree_name' => 'required|string|max:255',
//             'project_id' => 'required|integer|exists:projects,id',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status' => false,
//                 'record' => $index,
//                 'errors' => $validator->errors()
//             ], 422);
//         }

//         // ✅ Step 3: Fetch project
//         $project = Project::find($treeData['project_id']);
//         if (!$project) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Invalid project selected.'
//             ], 404);
//         }

//         // ✅ Step 4: Check project tree limit
//         // $existingTreeCount = MtTree::where('project_id', $treeData['project_id'])->count();

//         // if ($existingTreeCount >= $project->limit) {
//         //     return response()->json([
//         //         'status' => false,
//         //         'message' => 'Tree creation limit exceeded for this project.'
//         //     ], 409);
//         // }

//         // ✅ Step 5: Handle Multiple Base64 Images
//         $savedImagesPaths = [];
        
//         // Input handle (array or string)
//         $imagesInput = $treeData['all_captured_images'] ?? $treeData['tree_image_upload'] ?? [];
        
//         if (is_string($imagesInput)) {
//             // Check if it's already a JSON string, if not, try to explode
//             $decoded = json_decode($imagesInput, true);
//             $imagesInput = is_array($decoded) ? $decoded : explode(',', $imagesInput);
//         }

//         foreach ((array)$imagesInput as $base64Image) {
//             if (!empty($base64Image)) {
//                 // Remove data:image/... prefix if exists
//                 if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
//                     $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
//                 }
                
//                 $imageData = base64_decode($base64Image);
                
//                 if ($imageData !== false) {
//                     $fileName = 'tree_' . time() . '_' . uniqid() . '.jpg';
//                     $destinationPath = public_path('tree_images');
                    
//                     if (!file_exists($destinationPath)) {
//                         mkdir($destinationPath, 0775, true);
//                     }
                    
//                     file_put_contents($destinationPath . '/' . $fileName, $imageData);
//                     $savedImagesPaths[] = 'tree_images/' . $fileName;
//                 }
//             }
//         }

//         // Final format for database: ["path1.jpg", "path2.jpg"]
//         $finalJsonPath = json_encode($savedImagesPaths);

//         // ✅ Step 6: Create Tree
//         $tree = MtTree::create([
//             'project_id' => $treeData['project_id'],
//             'user_id' => $treeData['user_id'],
//             'ward_plot_no' => $treeData['ward_plot_no'] ?? null,
//             'tree_no' => $treeData['tree_no'] ?? null,
//             'tree_name' => $treeData['tree_name'],
//             'scientific_name' => $treeData['scientific_name'] ?? null,
//             'family' => $treeData['family'] ?? null,
//             'girth' => $treeData['girth'] ?? null,
//             'height' => $treeData['height'] ?? null,
//             'canopy' => $treeData['canopy'] ?? null,
//             'age' => $treeData['age'] ?? null,
//             'condition' => $treeData['condition'] ?? null,
//             'address' => $treeData['address'] ?? null,
//             'landmark' => $treeData['landmark'] ?? null,
//             'ownership' => $treeData['ownership'] ?? null,
//             'concern_person' => $treeData['concern_person'] ?? null,
//             'remark' => $treeData['remark'] ?? null,
//             'tree_image_upload' => $finalJsonPath, // Same Format (JSON)
//             'all_captured_images' => $finalJsonPath, // Same Format (JSON)
//             'latitude' => $treeData['latitude'] ?? null,
//             'longitude' => $treeData['longitude'] ?? null,
//             'datetime' => $treeData['datetime'] ?? null,
//         ]);

//         $createdTrees[] = $tree;
//     }

//     // ✅ Step 7: Success Response
//     return response()->json([
//         'status' => true,
//         'message' => 'Tree record(s) created successfully',
//         'count' => count($createdTrees),
//         'data' => $createdTrees
//     ], 201);
// }

// public function store(Request $request)
// {
//     // ✅ Step 1: Normalize input (object OR array)
//     $trees = $request->json()->all();

//     if (isset($trees[0]) && is_array($trees[0])) {
//         $treeDataList = $trees;
//     } else {
//         $treeDataList = [$trees];
//     }

//     $createdTrees = [];

//     foreach ($treeDataList as $index => $treeData) {

//         // ✅ Step 2: Validate each tree
//         $validator = Validator::make($treeData, [
//             'user_id'    => 'required',
//             'tree_name'  => 'required|string|max:255',
//             'project_id' => 'required|integer|exists:projects,id',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status' => false,
//                 'record' => $index,
//                 'errors' => $validator->errors()
//             ], 422);
//         }

//         // ✅ Step 3: Fetch project
//         $project = Project::find($treeData['project_id']);
//         if (!$project) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Invalid project selected.'
//             ], 404);
//         }

//         // ✅ Step 4: Check project tree limit (commented as before)
//         // $existingTreeCount = MtTree::where('project_id', $treeData['project_id'])->count();
//         // if ($existingTreeCount >= $project->limit) {
//         //     return response()->json([
//         //         'status'  => false,
//         //         'message' => 'Tree creation limit exceeded for this project.'
//         //     ], 409);
//         // }

//         // ✅ Step 5: Input handle (array or string)
//         $imagesInput = $treeData['all_captured_images'] ?? $treeData['tree_image_upload'] ?? [];

//         if (is_string($imagesInput)) {
//             $decoded     = json_decode($imagesInput, true);
//             $imagesInput = is_array($decoded) ? $decoded : explode(',', $imagesInput);
//         }

//         // ✅ Step 6: Tree pehle bana do — images baad mein queue se lagegi
//         $tree = MtTree::create([
//             'project_id'          => $treeData['project_id'],
//             'user_id'             => $treeData['user_id'],
//             'ward_plot_no'        => $treeData['ward_plot_no'] ?? null,
//             'tree_no'             => $treeData['tree_no'] ?? null,
//             'tree_name'           => $treeData['tree_name'],
//             'scientific_name'     => $treeData['scientific_name'] ?? null,
//             'family'              => $treeData['family'] ?? null,
//             'girth'               => $treeData['girth'] ?? null,
//             'height'              => $treeData['height'] ?? null,
//             'canopy'              => $treeData['canopy'] ?? null,
//             'age'                 => $treeData['age'] ?? null,
//             'condition'           => $treeData['condition'] ?? null,
//             'address'             => $treeData['address'] ?? null,
//             'landmark'            => $treeData['landmark'] ?? null,
//             'ownership'           => $treeData['ownership'] ?? null,
//             'concern_person'      => $treeData['concern_person'] ?? null,
//             'remark'              => $treeData['remark'] ?? null,
//             'tree_image_upload'   => json_encode([]),  // ✅ Abhi empty — queue fill karega
//             'all_captured_images' => json_encode([]),  // ✅ Abhi empty — queue fill karega
//             'latitude'            => $treeData['latitude'] ?? null,
//             'longitude'           => $treeData['longitude'] ?? null,
//             'datetime'            => $treeData['datetime'] ?? null,
//         ]);

//         // ✅ Step 7: Image processing queue mein dispatch karo
//         ProcessImageUpload::dispatch($tree->id, $imagesInput);

//         $createdTrees[] = $tree;
//     }

//     // ✅ Step 8: Turant response — image processing background mein chalegi
//     return response()->json([
//         'status'  => true,
//         'message' => 'Tree record(s) created successfully. Images are being processed.',
//         'count'   => count($createdTrees),
//         'data'    => $createdTrees
//     ], 201);
// }

public function store(Request $request)
{
    // echo'hghgh';die;
    // ✅ Step 1: Normalize input (object OR array)
    $trees = $request->json()->all();

    if (isset($trees[0]) && is_array($trees[0])) {
        $treeDataList = $trees;
    } else {
        $treeDataList = [$trees];
    }

    $createdTrees = [];

    foreach ($treeDataList as $index => $treeData) {

        // ✅ Step 2: Validate each tree
        $validator = Validator::make($treeData, [
            'user_id'    => 'required',
            'tree_name'  => 'required|string|max:255',
            'project_id' => 'required|integer|exists:projects,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'record' => $index,
                'errors' => $validator->errors()
            ], 422);
        }

        // ✅ Step 3: Fetch project
        $project = Project::find($treeData['project_id']);
        if (!$project) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid project selected.'
            ], 404);
        }

        // ✅ Step 4: Check project tree limit (commented as before)
        // $existingTreeCount = MtTree::where('project_id', $treeData['project_id'])->count();
        // if ($existingTreeCount >= $project->limit) {
        //     return response()->json([
        //         'status'  => false,
        //         'message' => 'Tree creation limit exceeded for this project.'
        //     ], 409);
        // }

        // ✅ Step 5: Handle Multiple Base64 Images
        $savedImagesPaths = [];

        // Input handle (array or string)
        $imagesInput = $treeData['all_captured_images'] ?? $treeData['tree_image_upload'] ?? [];

        if (is_string($imagesInput)) {
            $decoded     = json_decode($imagesInput, true);
            $imagesInput = is_array($decoded) ? $decoded : explode(',', $imagesInput);
        }

        $destinationPath = public_path('tree_images');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0775, true);
        }

        foreach ((array)$imagesInput as $base64Image) {
            if (!empty($base64Image)) {

                // Remove data:image/... prefix if exists
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                }

                // ✅ Clean base64 string
                $base64Image = str_replace([' ', '\n', '\r'], '', trim($base64Image));

                // ✅ Strict base64 decode
                $imageData = base64_decode($base64Image, true);

                if ($imageData !== false && strlen($imageData) > 0) {
                    $fileName = 'tree_' . time() . '_' . uniqid() . '.jpg';

                    try {
                        // ✅ Compress & Resize — size reduce hoga
                        Image::make($imageData)
                            ->resize(800, null, function ($constraint) {
                                $constraint->aspectRatio(); // Ratio same rahega
                                $constraint->upsize();      // Chhoti image aur chhoti nahi hogi
                            })
                            ->save($destinationPath . '/' . $fileName, 60); // 60% quality
                            
                        // echo'<pre>';print_r($imageData);die;

                        $savedImagesPaths[] = 'tree_images/' . $fileName;
                    
                        Log::info("✅ Tree [{$index}] Image saved: {$fileName}");

                    } catch (\Exception $e) {
                        // ✅ Image fail ho toh bhi tree save hoga — skip image
                        Log::error("❌ Tree [{$index}] Image failed: " . $e->getMessage());
                    }
                } else {
                    Log::warning("⚠️ Tree [{$index}] Invalid base64 — skip");
                }
            }
        }

        // ✅ Final JSON path
        $finalJsonPath = json_encode($savedImagesPaths);

        // ✅ Step 6: Create Tree — same as original
        $tree = MtTree::create([
            'project_id'          => $treeData['project_id'],
            'user_id'             => $treeData['user_id'],
            'ward_plot_no'        => $treeData['ward_plot_no'] ?? null,
            'tree_no'             => $treeData['tree_no'] ?? null,
            'tree_name'           => $treeData['tree_name'],
            'scientific_name'     => $treeData['scientific_name'] ?? null,
            'family'              => $treeData['family'] ?? null,
            'girth'               => $treeData['girth'] ?? null,
            'height'              => $treeData['height'] ?? null,
            'canopy'              => $treeData['canopy'] ?? null,
            'age'                 => $treeData['age'] ?? null,
            'condition'           => $treeData['condition'] ?? null,
            'address'             => $treeData['address'] ?? null,
            'landmark'            => $treeData['landmark'] ?? null,
            'ownership'           => $treeData['ownership'] ?? null,
            'concern_person'      => $treeData['concern_person'] ?? null,
            'remark'              => $treeData['remark'] ?? null,
            'tree_image_upload'   => $finalJsonPath, // Same Format (JSON)
            'all_captured_images' => $finalJsonPath, // Same Format (JSON)
            'latitude'            => $treeData['latitude'] ?? null,
            'longitude'           => $treeData['longitude'] ?? null,
            'datetime'            => $treeData['datetime'] ?? null,
        ]);

        $createdTrees[] = $tree;
    }

    // ✅ Step 7: Success Response
    return response()->json([
        'status'  => true,
        'message' => 'Tree record(s) created successfully',
        'count'   => count($createdTrees),
        'data'    => $createdTrees
    ], 201);
}

    // ✅ Update Tree Record
    public function update(Request $request, $id)
    {
        $tree = MtTree::find($id);
        if (!$tree) {
            return response()->json(['status' => false, 'message' => 'Tree not found'], 404);
        }

        // Decode old images
        $imagePaths = json_decode($tree->all_captured_images ?? '[]', true);

        // Handle new uploaded images
        if ($request->hasFile('tree_images')) {
            foreach ($request->file('tree_images') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('tree_images'), $filename);
                $imagePaths[] = 'tree_images/' . $filename;
            }
        }

        // Collect data safely (no filter yet)
        $data = $request->only([
            'project_id',
            'user_id',
            'ward_plot_no',
            'tree_no',
            'tree_name',
            'scientific_name',
            'family',
            'girth',
            'height',
            'canopy',
            'age',
            'condition',
            'address',
            'landmark',
            'ownership',
            'concern_person',
            'remark',
            'tree_image_upload',
            'captured_image',
            'latitude',
            'longitude',
            'datetime'
        ]);

        // Debug: check what we're getting
        if (empty(array_filter($data))) {
            return response()->json([
                'status' => false,
                'message' => 'No valid fields provided for update',
                'received' => $request->all()
            ], 400);
        }

        // Add image array
        $data['all_captured_images'] = json_encode($imagePaths);

        // Update the record
        $tree->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Tree record updated successfully',
            'updated_fields' => $data,
            'data' => $tree->fresh()
        ]);
    }


    // ✅ Delete
    public function destroy($id)
    {

        $tree = MtTree::find($id);
        if (!$tree) {
            return response()->json(['status' => false, 'message' => 'Tree not found'], 404);
        }

        $tree->delete();
        return response()->json(['status' => true, 'message' => 'Tree deleted successfully']);
    }

    public function index($id)
    {
        $trees = \App\Models\MtTree::where('user_id', $id)
            ->latest()
            ->get()
            ->map(function ($tree) {
                // Decode image JSON
                $tree->all_captured_images = json_decode($tree->all_captured_images, true);
                $treeModel = Tree::where('id', $tree->tree_name)->first();
                $tree->tree_name = $treeModel ? $treeModel->name : $tree->tree_name;
                $scientificModel = ScientificName::where('id', $tree->scientific_name)->first();
                $tree->scientific_name = $scientificModel ? $scientificModel->scientific_name : $tree->scientific_name;
                $familyModel = Family::where('id', $tree->family)->first();
                $tree->family = $familyModel ? $familyModel->family_name : $tree->family;

                return $tree;
            });

        return response()->json([
            'status' => true,
            'data' => $trees
        ]);
    }

    public function tree_on_project_id(Request $request)
    {
        // ✅ Step 1: Validate
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'user_id'    => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $projectId = $request->project_id;
        $userId    = $request->user_id;
        $user      = \App\Models\User::find($userId);

        // ✅ Step 2: Fetch free and paid tree IDs (only for role_id == 3)
        $freeTreeIds = [];
        $paidTreeIds = [];

        if ($user->role_id == 3) {
            $freeRecord  = \App\Models\UserFreeTree::getOrCreate($userId);
            $freeTreeIds = $freeRecord->tree_ids ?? [];

            $paidTreeIds = \App\Models\UserPaidTree::where('user_id', $userId)
                            ->pluck('mt_tree_id')
                            ->toArray();
        }

        // ✅ Step 3: Fetch trees
        $trees = \App\Models\MtTree::where('project_id', $projectId)
            ->latest()
            ->get()
            ->map(function ($tree) use ($freeTreeIds, $paidTreeIds, $user) {
                // ✅ Step 3a: tree_image_upload ko hamesha empty string set karein
                $tree->tree_image_upload = "";

                // ✅ Step 3b: Baaki JSON data decode karein
                $tree->all_captured_images = json_decode($tree->all_captured_images, true);

                // ✅ Step 3c: Relations fetch karein (Tree Name, Scientific Name, Family)
                $treeModel = Tree::where('id', $tree->tree_name)->first();
                $tree->tree_name = $treeModel ? $treeModel->name : $tree->tree_name;

                $scientificModel = ScientificName::where('id', $tree->scientific_name)->first();
                $tree->scientific_name = $scientificModel ? $scientificModel->scientific_name : $tree->scientific_name;

                $familyModel = Family::where('id', $tree->family)->first();
                $tree->family = $familyModel ? $familyModel->family_name : $tree->family;

                // ✅ Step 3d: role_id != 3 → all trees accessible, no restriction
                if ($user->role_id != 3) {
                    $tree->is_free       = false;
                    $tree->is_paid       = false;
                    $tree->is_accessible = true;
                } else {
                    // role_id == 3 → check free and paid lists
                    $tree->is_free       = in_array($tree->id, $freeTreeIds);
                    $tree->is_paid       = in_array($tree->id, $paidTreeIds);
                    $tree->is_accessible = $tree->is_free || $tree->is_paid;
                }

                return $tree;
            });

        return response()->json([
            'status' => true,
            'data'   => $trees,
            'summary' => [
                'total_trees'      => $trees->count(),
                'free_trees'       => $trees->where('is_free', true)->count(),
                'paid_trees'       => $trees->where('is_paid', true)->count(),
                'accessible_trees' => $trees->where('is_accessible', true)->count(),
                'locked_trees'     => $trees->where('is_accessible', false)->count(),
            ]
        ]);
    }


    // ✅ Get Single
    public function show($id)
    {
        $tree = MtTree::find($id);

        if (!$tree) {
            return response()->json(['status' => false, 'message' => 'Tree not found'], 404);
        }
        $tree->all_captured_images = json_decode($tree->all_captured_images, true);

        $treeModel = Tree::where('id', $tree->tree_name)->first();
        $tree->tree_name = $treeModel ? $treeModel->name : $tree->tree_name;
        $scientificModel = ScientificName::where('id', $tree->scientific_name)->first();
        $tree->scientific_name = $scientificModel ? $scientificModel->scientific_name : $tree->scientific_name;
        $familyModel = Family::where('id', $tree->family)->first();
        $tree->family = $familyModel ? $familyModel->family_name : $tree->family;

        return response()->json([
            'status' => true,
            'data' => $tree
        ]);
    }

    public function dashboard_count(Request $request)
    {
        try {
            // 1. Validation for User ID and Role ID
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'role_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user_id = $request->user_id;
            $role_id = $request->role_id;

            // Initialize counts
            $projectCount = 0;
            $treeCount = 0;
            // District count remains global as requested
            $districtCount = District::count();

            // =========================================================
            // CASE 1: ROLE 3 (Customer / Extra User)
            // =========================================================
            if ($role_id == 3) {
                // Check 'extra_user' column for matching User ID
                $projects = Project::where('extra_user', $user_id)->get();
                $projectCount = $projects->count();
                
                // Count trees only within these specific projects
                $projectIds = $projects->pluck('id');
                $treeCount = MtTree::whereIn('project_id', $projectIds)->count();
            }

            // =========================================================
            // CASE 2: ROLE 2 (Staff / Field Officer)
            // =========================================================
            elseif ($role_id == 2) {
                // Check JSON 'field_officer_id' column for matching User ID
                $projects = Project::whereRaw("JSON_CONTAINS(field_officer_id, '\"$user_id\"')")->get();
                $projectCount = $projects->count();

                // Count trees only within these assigned projects
                $projectIds = $projects->pluck('id');
                $treeCount = MtTree::whereIn('project_id', $projectIds)->count();
            }

            // =========================================================
            // CASE 3: ADMIN (Role 1 or others) - Show All
            // =========================================================
            else {
                $projectCount = Project::count();
                $treeCount = MtTree::count();
            }

            return response()->json([
                'status' => true,
                'message' => 'Dashboard data fetched successfully',
                'data' => [
                    'project_count' => $projectCount,
                    'tree_count' => $treeCount,
                    'district_count' => $districtCount,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    public function new_tree_add(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'            => 'required|string|max:255',
        'scientific_name' => 'required|string|max:255',
        'family_name'     => 'required|string|max:255',
        'height_ratio'    => 'nullable|string|max:255',
        'age_ratio'       => 'nullable|string|max:255',
        'canopy_ratio'    => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    try {
        $result = DB::transaction(function () use ($request) {
            $tree = \App\Models\Tree::create([
                'name' => $request->name,
            ]);

            $scientificName = \App\Models\ScientificName::create([
                'tree_id'         => $tree->id,
                'scientific_name' => $request->scientific_name,
                'height_ratio'    => $request->height_ratio,
                'age_ratio'       => $request->age_ratio,
                'canopy_ratio'    => $request->canopy_ratio,
            ]);

            $family = \App\Models\Family::create([
                'tree_id'     => $tree->id,
                'family_name' => $request->family_name,
            ]);

            return compact('tree', 'scientificName', 'family');
        });

        return response()->json([
            'success' => true,
            'message' => 'Tree created successfully!',
            'data'    => [
                'tree'            => $result['tree'],
                'scientific_name' => $result['scientificName'],
                'family'          => $result['family'],
            ],
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
}