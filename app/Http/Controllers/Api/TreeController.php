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
use Exception;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TreeController extends Controller
{
    /**
     * Store Tree Record(s) with Batch Support and Base64 Images
     */
    public function store(Request $request)
    {
        try {
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
                    'latitude' => 'nullable',
                    'longitude' => 'nullable',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false, 
                        'message' => $validator->errors()->first(),
                        'errors' => $validator->errors()
                    ], 422);
                }

                // Handle Images (Array of Base64)
                $savedImagesPaths = [];
                $imagesInput = $treeData['all_captured_images'] ?? $treeData['tree_image_upload'] ?? [];

                if (is_string($imagesInput)) {
                    $decoded = json_decode($imagesInput, true);
                    $imagesInput = is_array($decoded) ? $decoded : explode(',', $imagesInput);
                }

                foreach ((array)$imagesInput as $base64Image) {
                    $savedPath = $this->saveBase64Image($base64Image, $destinationPath);
                    if ($savedPath) {
                        $savedImagesPaths[] = $savedPath;
                    }
                }

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
                    'tree_image_upload' => json_encode($savedImagesPaths), 
                    'all_captured_images' => $savedImagesPaths, 
                    'latitude' => $treeData['latitude'] ?? null,
                    'longitude' => $treeData['longitude'] ?? null,
                    'datetime' => $treeData['datetime'] ?? null,
                    'device_id' => $treeData['device_id'] ?? null,
                    'app_version' => $treeData['app_version'] ?? null,
                ]);

                $createdTrees[] = $tree;
            }

            return response()->json([
                'success' => true,
                'message' => 'Tree record(s) created successfully',
                'count' => count($createdTrees),
                'data' => $createdTrees
            ], 201);

        } catch (Exception $e) {
            Log::error("Tree Store Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save tree records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to save Base64 Image
     */
    private function saveBase64Image($base64Image, $destinationPath)
    {
        if (empty($base64Image)) return null;

        try {
            // Remove data:image/... prefix if exists
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            }

            // Clean base64 string
            $base64Image = str_replace([' ', '\n', '\r'], '', trim($base64Image));
            $imageData = base64_decode($base64Image, true);

            if ($imageData === false || strlen($imageData) === 0) return null;

            $fileName = 'tree_' . time() . '_' . uniqid() . '.jpg';
            
            // Intervention v3 Pattern
            $manager = new ImageManager(new Driver());
            $image = $manager->read($imageData);
            
            // Resize and scale
            $image->scale(width: 800);
            $image->toJpeg(60)->save($destinationPath . '/' . $fileName);

            return 'tree_images/' . $fileName;

        } catch (Exception $e) {
            Log::error("Image Save Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update Tree Record
     */
    public function update(Request $request, $id)
    {
        try {
            $tree = MtTree::find($id);
            if (!$tree) return response()->json(['success' => false, 'message' => 'Tree not found'], 404);

            $data = $request->all();
            
            // Handle image updates if needed
            if ($request->hasFile('tree_images')) {
                $imagePaths = (array)($tree->all_captured_images ?? []);
                
                foreach ($request->file('tree_images') as $file) {
                    $filename = 'tree_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('tree_images'), $filename);
                    $imagePaths[] = 'tree_images/' . $filename;
                }
                $data['all_captured_images'] = $imagePaths;
                $data['tree_image_upload'] = json_encode($imagePaths);
            }

            $tree->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Tree record updated successfully',
                'data' => $tree->fresh()
            ]);
        } catch (Exception $e) {
            Log::error("Tree Update Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tree: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Tree Record
     */
    public function destroy($id)
    {
        try {
            $tree = MtTree::find($id);
            if (!$tree) return response()->json(['success' => false, 'message' => 'Tree not found'], 404);

            $tree->delete();
            return response()->json(['success' => true, 'message' => 'Tree deleted successfully']);
        } catch (Exception $e) {
            Log::error("Tree Delete Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tree: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch Trees for a Project with Accessibility Logic (For Customers)
     */
    public function tree_on_project_id(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'project_id' => 'required|exists:projects,id',
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $user = User::find($request->user_id);
            $freeTreeIds = [];
            $paidTreeIds = [];

            if ($user->role_id == 3) {
                $freeTreeData = UserFreeTree::where('user_id', $user->id)->first();
                $freeTreeIds = $freeTreeData ? (array)$freeTreeData->tree_ids : [];
                $paidTreeIds = UserPaidTree::where('user_id', $user->id)->pluck('mt_tree_id')->toArray();
            }

            $trees = MtTree::where('project_id', $request->project_id)
                ->latest()
                ->get()
                ->map(function ($tree) use ($freeTreeIds, $paidTreeIds, $user) {
                    // Normalize images (safe if cast exists)
                    $tree->all_captured_images = (array)($tree->all_captured_images ?? []);

                    $tree->tree_image_upload = ""; // Privacy/Optimization

                    // Load Name Labels
                    $tree->tree_name_label = Tree::where('id', $tree->tree_name)->value('name') ?? $tree->tree_name;
                    $tree->scientific_name_label = ScientificName::where('id', $tree->scientific_name)->value('scientific_name') ?? $tree->scientific_name;
                    $tree->family_label = Family::where('id', $tree->family)->value('family_name') ?? $tree->family;

                    if ($user->role_id != 3) {
                        $tree->is_accessible = true;
                    } else {
                        $tree->is_free = in_array($tree->id, (array)$freeTreeIds);
                        $tree->is_paid = in_array($tree->id, $paidTreeIds);
                        $tree->is_accessible = $tree->is_free || $tree->is_paid;
                    }

                    return $tree;
                });

            return response()->json(['success' => true, 'data' => $trees]);
        } catch (Exception $e) {
            Log::error("Tree Fetch Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching trees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Single Tree View
     */
    public function show($id)
    {
        try {
            $tree = MtTree::find($id);
            if (!$tree) return response()->json(['success' => false, 'message' => 'Tree not found'], 404);

            $tree->all_captured_images = (array)($tree->all_captured_images ?? []);
            
            $tree->tree_name_label = Tree::where('id', $tree->tree_name)->value('name') ?? $tree->tree_name;
            $tree->scientific_name_label = ScientificName::where('id', $tree->scientific_name)->value('scientific_name') ?? $tree->scientific_name;
            $tree->family_label = Family::where('id', $tree->family)->value('family_name') ?? $tree->family;

            return response()->json(['success' => true, 'data' => $tree]);
        } catch (Exception $e) {
            Log::error("Tree Detail Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch tree details.'], 500);
        }
    }

    /**
     * Dashboard Statistics
     */
    public function dashboard_count(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'role_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $user_id = $request->user_id;
            $role_id = $request->role_id;

            if ($role_id == 3) {
                $projectIds = Project::where('extra_user', $user_id)->pluck('id');
            } elseif ($role_id == 2) {
                $projectIds = Project::whereRaw("JSON_CONTAINS(field_officer_id, '\"$user_id\"')")->pluck('id');
            } else {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'project_count' => Project::count(),
                        'tree_count' => MtTree::count(),
                        'district_count' => District::count(),
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'project_count' => $projectIds->count(),
                    'tree_count' => MtTree::whereIn('project_id', $projectIds)->count(),
                    'district_count' => District::count(),
                ]
            ]);
        } catch (Exception $e) {
            Log::error("Dashboard Count Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load dashboard statistics.'], 500);
        }
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
                'height_ratio' => $request->height_ratio ?? null,
                'age_ratio' => $request->age_ratio ?? null,
                'canopy_ratio' => $request->canopy_ratio ?? null,
            ]);

            Family::create([
                'tree_id' => $tree->id,
                'family_name' => $request->family_name,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'New tree species added successfully.'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Add Tree Species Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to add tree species: ' . $e->getMessage()], 500);
        }
    }
}