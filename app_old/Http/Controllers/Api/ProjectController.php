<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProjectController extends Controller
{
    /**
     * Project List for Authenticated Staff
     */
    public function index()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            $query = Project::with(['state', 'fieldOfficer'])->withCount('trees');

            // --- Role based filtering (Same as old logic but cleaner) ---
            if ($user->role_id == 3) {
                $query->where('extra_user', $user->id);
            } elseif ($user->role_id == 2) {
                $query->whereRaw("JSON_CONTAINS(field_officer_id, '\"$user->id\"')");
            }
            
            $projects = $query->get();

            // Fetch active tree price (Merge from old logic)
            $activePrice = \App\Models\TreePrice::where('is_active', 1)->orderBy('id', 'desc')->first();
            $priceStr = $activePrice ? number_format((float)$activePrice->price, 2, '.', '') : "0.00";

            return response()->json([
                'success' => true,
                'active_tree_price' => $priceStr,
                'count' => $projects->count(),
                'data' => $projects
            ]);
        } catch (Exception $e) {
            Log::error("Project List Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch projects.'
            ], 500);
        }
    }


    /**
     * Tree Field Requirements per Project/Role
     */
    public function requirements(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|integer',
            'project_id' => 'required|integer|exists:projects,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $role_id = $request->role_id;
            $project_id = $request->project_id;
            $project = Project::find($project_id);

            $requirements = [
                'all_captured_images' => ['is_required' => false],
                'ward_plot_no' => ['is_required' => false],
                'tree_no' => ['is_required' => false],
                'tree_name' => ['is_required' => false],
                'scientific_name' => ['is_required' => false],
                'family' => ['is_required' => false],
                'girth' => ['is_required' => false],
                'height' => ['is_required' => false],
                'canopy' => ['is_required' => false],
                'age' => ['is_required' => false],
                'condition' => ['is_required' => false],
                'address' => ['is_required' => false],
                'landmark' => ['is_required' => false],
                'ownership' => ['is_required' => false],
                'concern_person' => ['is_required' => false],
                'remark' => ['is_required' => false],
                'tree_images' => ['is_required' => false],
            ];

            if ($role_id == 2) {
                $settings = ProjectSetting::where('project_id', $project_id)->get();
                foreach ($settings as $setting) {
                    if (isset($requirements[$setting->field_key])) {
                        $requirements[$setting->field_key] = [
                            'is_required' => (bool)$setting->is_required,
                            'min_value' => $setting->min_value,
                            'max_value' => $setting->max_value,
                        ];
                    }
                }
            } else if ($role_id == 3) {
                $requirements['tree_name']['is_required'] = true;
                $requirements['girth']['is_required'] = true;
                $requirements['address']['is_required'] = true;
                $requirements['tree_images']['is_required'] = true;
            }

            return response()->json([
                'success' => true,
                'ward_no' => $project->ward_no ?? "",
                'requirements' => $requirements
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch requirements.'], 500);
        }
    }
}
