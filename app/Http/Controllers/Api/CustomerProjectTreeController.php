<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\MtTree;
use App\Models\Wallet;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class CustomerProjectTreeController extends Controller
{
    // Limit Logic
    private function checkProjectLimit($user)
    {
        $defaultLimit = 1;
        $purchasedLimit = Wallet::where('user_id', $user->id)->where('status', 'success')->sum('project_count');
        $totalAllowed = $defaultLimit + $purchasedLimit;

        // Count using extra_user
        $createdProjects = Project::where('extra_user', $user->id)->count();

        return $createdProjects < $totalAllowed;
    }

    // Create Project
    public function createProject_old(Request $request)
    {
        $user = auth()->user();

        // Limit Check for Role 3
        if ($user->role_id == 3) {
            if (!$this->checkProjectLimit($user)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Project limit reached. Please purchase a plan.',
                    'action' => 'redirect_to_payment'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), ['project_name' => 'required']);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $project = Project::create([
            'extra_user' => $user->id, // IDENTIFIER
            'project_name' => $request->project_name,
            'client_name' => $request->client_name ?? null,
            'limit' => 100, // Default Limit
            'required_fields' => json_encode(['girth', 'height']),
            // Defaults to satisfy DB constraints
            'field_officer_id' => 0,
            'state_id' => 0,
            'accuracy' => 0
        ]);

        return response()->json(['status' => true, 'message' => 'Project Created', 'data' => $project]);
    }

    public function createProject(Request $request)
    {
        try {
            $user = auth()->user();
    
            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }
    
            // 🔒 Project limit check for role 3
            // if ($user->role_id == 3 && !$this->checkProjectLimit($user)) {
            //     return response()->json([
            //         'status'  => false,
            //         'message' => 'Project limit reached. Please purchase a plan.',
            //         'action'  => 'redirect_to_payment'
            //     ], 403);
            // }
    
            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'project_name' => 'required|string|max:255',
                'client_name'  => 'nullable|string|max:255',
                'company_name'  => 'nullable|string|max:255',
                'state_id'  => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation error',
                    'errors'  => $validator->errors()
                ], 422);
            }
    
            // 🧠 Create Project
            $project = Project::create([
                'extra_user'       => $user->id,
                'project_name'     => $request->project_name,
                'client_name'      => $request->client_name,
                'company_name'     => $request->company_name,
                'limit'            => 100,
                'required_fields'  => json_encode(['girth', 'height']),
                'field_officer_id' => null,
                'state_id'         => $request->state_id,
                'accuracy'         => 0,
            ]);
    
            return response()->json([
                'status'  => true,
                'message' => 'Project created successfully',
                'data'    => $project
            ], 201);
    
        } catch (QueryException $e) {
            // 🛢️ Database related errors
            Log::error('Project Create DB Error', [
                'error' => $e->getMessage(),
                'user_id' => optional(auth()->user())->id
            ]);
    
            return response()->json([
                'status'  => false,
                'message' => 'Database error while creating project'
            ], 500);
    
        } catch (\Exception $e) {
            // 🔥 Any unexpected error
            Log::error('Project Create Error', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile()
            ]);
    
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }
    
    public function getProject($id)
    {
        try {
            $user = auth()->user();
    
            $project = Project::where('id', $id)
                ->with(['state', 'fieldOfficer'])
                ->first();
    
            if (!$project) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Project not found'
                ], 404);
            }
    
            return response()->json([
                'status'  => true,
                'message' => 'Project fetched successfully',
                'data'    => $project
            ], 200);
    
        } catch (\Exception $e) {
            Log::error('Project Fetch Error', [
                'error' => $e->getMessage(),
                'project_id' => $id
            ]);
    
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }
    
    public function updateProject(Request $request, $id)
    {
        try {
            $user = auth()->user();
    
            $project = Project::where('id', $id)
                ->first();
    
            if (!$project) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Project not found'
                ], 404);
            }
    
            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'project_name' => 'required|string|max:255',
                'client_name'  => 'nullable|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'state_id'     => 'required', // ✅ FIXED
            ]);

    
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation error',
                    'errors'  => $validator->errors()
                ], 422);
            }
    
            // 🧠 Update Project
            $project->update([
                'project_name' => $request->project_name,
                'client_name'  => $request->client_name,
                'company_name' => $request->company_name,
                'state_id'     => $request->state_id,
            ]);
    
            return response()->json([
                'status'  => true,
                'message' => 'Project updated successfully',
                'data'    => $project
            ], 200);
    
        } catch (QueryException $e) {
            Log::error('Project Update DB Error', [
                'error' => $e->getMessage(),
                'project_id' => $id
            ]);
    
            return response()->json([
                'status'  => false,
                'message' => 'Database error while updating project'
            ], 500);
    
        } catch (\Exception $e) {
            Log::error('Project Update Error', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine()
            ]);
    
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }


    // Add Tree
    public function addTree(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'tree_name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Check Ownership
        $project = Project::where('id', $request->project_id)->where('extra_user', $user->id)->first();
        if (!$project) return response()->json(['status' => false, 'message' => 'Access Denied'], 403);

        // Check Tree Limit
        if (MtTree::where('project_id', $project->id)->count() >= $project->limit) {
            return response()->json(['status' => false, 'message' => 'Tree limit reached for this project'], 403);
        }

        $tree = MtTree::create([
            'project_id' => $project->id,
            'extra_usertree' => $user->id, // IDENTIFIER
            'tree_name' => $request->tree_name,
            'tree_no' => MtTree::where('project_id', $project->id)->count() + 1,
            // Defaults
            'user_id' => 0,
            'ward_plot_no' => 0
        ]);

        return response()->json(['status' => true, 'message' => 'Tree Added', 'data' => $tree]);
    }

    // Get Projects
    public function getProjects()
    {
        $projects = Project::where('extra_user', auth()->id())->orderBy('created_at', 'desc')->get();
        return response()->json(['status' => true, 'data' => $projects]);
    }

    // Get Trees
    public function getTrees($project_id)
    {
        $user = auth()->user();
        $project = Project::where('id', $project_id)->where('extra_user', $user->id)->first();

        if (!$project) return response()->json(['status' => false, 'message' => 'Access Denied'], 403);

        $trees = MtTree::where('project_id', $project_id)
            ->where('extra_usertree', $user->id)
            ->orderBy('tree_no', 'desc')
            ->get();

        return response()->json(['status' => true, 'data' => $trees]);
    }
}
