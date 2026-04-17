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
use App\Models\User;
use App\Models\TreePrice;
use App\Models\UserFreeTree;
use App\Models\UserPaidTree;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Exception;

class TreeController extends Controller
{
    /**
     * Store Tree Record(s) with Batch Support and Base64 Images
     */
    public function store(Request $request)
    {
        $input = $request->json()->all();
        $treeDataList = (isset($input[0]) && is_array($input[0])) ? $input : [$input];
        
        $createdTrees = [];
        $destinationPath = public_path('tree_images');
        
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0775, true);
        }

        foreach ($treeDataList as $index => $treeData) {
            $validator = Validator::make($treeData, [
                'user_id' => 'required',
                'tree_name' => 'required|string|max:255',
                'project_id' => 'required|integer|exists:projects,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'record' => $index, 'errors' => $validator->errors()], 422);
            }

            // Handle Images (Array of Base64)
            $savedImagesPaths = [];
            $imagesInput = $treeData['all_captured_images'] ?? $treeData['tree_image_upload'] ?? [];

            if (is_string($imagesInput)) {
                $decoded = json_decode($imagesInput, true);
                $imagesInput = is_array($decoded) ? $decoded : explode(',', $imagesInput);
            }

            foreach ((array)$imagesInput as $base64Image) {
                if ($path = $this->saveBase64Image($base64Image, $destinationPath)) {
                    $savedImagesPaths[] = $path;
                }
            }

            $finalJsonPath = json_encode($savedImagesPaths);

            $tree = MtTree::create([
                'project_id' => $treeData['project_id'],
                'user_id' => $treeData['user_id'],
                'ward_plot_no' => $treeData['ward_plot_no'] ?? null,
                'tree_no' => $treeData['tree_no'] ?? null,
                'tree_name' => $treeData['tree_name'],
                'scientific_name' => $treeData['scientific_name'] ?? null,
                'family' => $treeData['family'] ?? null,
                'girth' => $treeData['girth'] ?? null,
                'height' => $treeData['height'] ?? null,
                'canopy' => $treeData['canopy'] ?? null,
                'age' => $treeData['age'] ?? null,
                'condition' => $treeData['condition'] ?? null,
                'address' => $treeData['address'] ?? null,
                'landmark' => $treeData['landmark'] ?? null,
                'ownership' => $treeData['ownership'] ?? null,
                'concern_person' => $treeData['concern_person'] ?? null,
                'remark' => $treeData['remark'] ?? null,
                'tree_image_upload' => $savedImagesPaths,
                'all_captured_images' => $savedImagesPaths,
                'latitude' => $treeData['latitude'] ?? null,
                'longitude' => $treeData['longitude'] ?? null,
                'datetime' => $treeData['datetime'] ?? null,
            ]);

            $createdTrees[] = $tree;
        }

        return response()->json([
            'status' => true,
            'message' => 'Tree record(s) created successfully',
            'count' => count($createdTrees),
            'data' => $createdTrees
        ], 201);
    }

    private function saveBase64Image($base64Image, $destinationPath)
    {
        if (empty($base64Image)) return null;

        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image)) {
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
        }

        $base64Image = str_replace([' ', '\n', '\r'], '', trim($base64Image));
        $imageData = base64_decode($base64Image, true);

        if ($imageData === false || strlen($imageData) === 0) return null;

        $fileName = 'tree_' . time() . '_' . uniqid() . '.jpg';
        try {
            // Intervention v3 Pattern
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->read($imageData);
            
            $image->scale(width: 800);
            $image->toJpeg(60)->save($destinationPath . '/' . $fileName);

            return 'tree_images/' . $fileName;
        } catch (Exception $e) {
            Log::error("Image Save Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update Tree Record
     */
    public function update(Request $request, $id)
    {
        $tree = MtTree::find($id);
        if (!$tree) return response()->json(['status' => false, 'message' => 'Tree not found'], 404);

        $data = $request->all();
        
        // Handle image updates if needed
        if ($request->hasFile('tree_images')) {
            $imagePaths = json_decode($tree->all_captured_images ?? '[]', true);
            foreach ($request->file('tree_images') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('tree_images'), $filename);
                $imagePaths[] = 'tree_images/' . $filename;
            }
            $data['all_captured_images'] = json_encode($imagePaths);
            $data['tree_image_upload'] = $data['all_captured_images'];
        }

        $tree->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Tree record updated successfully',
            'data' => $tree->fresh()
        ]);
    }

    /**
     * Delete Tree Record
     */
    public function destroy($id)
    {
        $tree = MtTree::find($id);
        if (!$tree) return response()->json(['status' => false, 'message' => 'Tree not found'], 404);

        $tree->delete();
        return response()->json(['status' => true, 'message' => 'Tree deleted successfully']);
    }

    /**
     * Fetch Trees for a Project with Accessibility Logic (For Customers)
     */
    public function tree_on_project_id(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = User::find($request->user_id);
        $freeTreeIds = [];
        $paidTreeIds = [];

        if ($user->role_id == 3) {
            $freeTreeIds = UserFreeTree::getOrCreate($user->id)->tree_ids ?? [];
            $paidTreeIds = UserPaidTree::where('user_id', $user->id)->pluck('mt_tree_id')->toArray();
        }

        $trees = MtTree::where('project_id', $request->project_id)
            ->latest()
            ->get()
            ->map(function ($tree) use ($freeTreeIds, $paidTreeIds, $user) {
                $tree->all_captured_images = $tree->all_captured_images ?? [];
                $tree->tree_image_upload = ""; // Privacy/Optimization

                // Load Name Labels
                $tree->tree_name_label = Tree::where('id', $tree->tree_name)->value('name') ?? $tree->tree_name;
                $tree->scientific_name_label = ScientificName::where('id', $tree->scientific_name)->value('scientific_name') ?? $tree->scientific_name;
                $tree->family_label = Family::where('id', $tree->family)->value('family_name') ?? $tree->family;

                if ($user->role_id != 3) {
                    $tree->is_accessible = true;
                } else {
                    $tree->is_free = in_array($tree->id, $freeTreeIds);
                    $tree->is_paid = in_array($tree->id, $paidTreeIds);
                    $tree->is_accessible = $tree->is_free || $tree->is_paid;
                }

                return $tree;
            });

        return response()->json(['status' => true, 'data' => $trees]);
    }

    /**
     * Single Tree View
     */
    public function show($id)
    {
        $tree = MtTree::find($id);
        if (!$tree) return response()->json(['status' => false, 'message' => 'Tree not found'], 404);

        $tree->all_captured_images = $tree->all_captured_images ?? [];
        $tree->tree_name_label = Tree::where('id', $tree->tree_name)->value('name') ?? $tree->tree_name;
        $tree->scientific_name_label = ScientificName::where('id', $tree->scientific_name)->value('scientific_name') ?? $tree->scientific_name;
        $tree->family_label = Family::where('id', $tree->family)->value('family_name') ?? $tree->family;

        return response()->json(['status' => true, 'data' => $tree]);
    }

    /**
     * Dashboard Statistics
     */
    public function dashboard_count(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'role_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user_id = $request->user_id;
        $role_id = $request->role_id;

        if ($role_id == 3) {
            $projectIds = Project::where('extra_user', $user_id)->pluck('id');
        } elseif ($role_id == 2) {
            $projectIds = Project::whereRaw("JSON_CONTAINS(field_officer_id, '\"$user_id\"')")->pluck('id');
        } else {
            return response()->json([
                'status' => true,
                'data' => [
                    'project_count' => Project::count(),
                    'tree_count' => MtTree::count(),
                    'district_count' => District::count(),
                ]
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'project_count' => $projectIds->count(),
                'tree_count' => MtTree::whereIn('project_id', $projectIds)->count(),
                'district_count' => District::count(),
            ]
        ]);
    }

    /**
     * Add New Tree Type (Admin/Staff)
     */
    public function new_tree_add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:trees,name',
            'scientific_name' => 'required|string',
            'family_name' => 'required|string',
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);

        try {
            DB::beginTransaction();

            $tree = Tree::create(['name' => $request->name]);
            
            ScientificName::create([
                'tree_id' => $tree->id,
                'scientific_name' => $request->scientific_name,
                'height_ratio' => $request->height_ratio,
                'age_ratio' => $request->age_ratio,
                'canopy_ratio' => $request->canopy_ratio,
            ]);

            Family::create([
                'tree_id' => $tree->id,
                'family_name' => $request->family_name,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'New tree species added.']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to add tree species.'], 500);
        }
    }
}