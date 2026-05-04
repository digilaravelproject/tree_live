<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StateMaster;
use App\Models\User;
use App\Models\Project;
use App\Models\UserRating;
use App\Models\Tree;
use App\Models\ScientificName;
use App\Models\Family;
use App\Models\MtTree;
use App\Models\District;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Imports\TreesImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProjectSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Exports\TreeExport;
use ZipArchive;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
    public function index()
    {
        return view('index');
    }
    
    // ==========================================
    // ✅ DATABASE CLEANUP FUNCTION ✅
    // ==========================================
    public function cleanDatabase()
    {
        try {
            DB::beginTransaction();

            // 1. Valid Projects aur Trees ki IDs nikal lo
            $validProjectIds = \App\Models\Project::pluck('id')->toArray();
            $validTreeIds = \App\Models\MtTree::pluck('id')->toArray();

            // 2. Un Paid Trees ko hatao jinka Tree ab 'mt_trees' me nahi hai
            if (!empty($validTreeIds)) {
                \App\Models\UserPaidTree::whereNotIn('mt_tree_id', $validTreeIds)->delete();
            } else {
                \App\Models\UserPaidTree::truncate(); // Agar ek bhi tree nahi bacha toh sab clean
            }

            // 3. Un Settings aur Trees ko hatao jinka Project ab 'projects' me nahi hai
            if (!empty($validProjectIds)) {
                \App\Models\ProjectSetting::whereNotIn('project_id', $validProjectIds)->delete();
                \App\Models\MtTree::whereNotIn('project_id', $validProjectIds)->delete();
            } else {
                \App\Models\ProjectSetting::truncate();
                \App\Models\MtTree::truncate();
            }

            // 4. MtTrees delete hone ke baad, bache hue valid trees ki list update karo
            $updatedValidTreeIds = \App\Models\MtTree::pluck('id')->toArray();

            // 5. UserFreeTree (JSON) ko saaf karo
            $freeRecords = \App\Models\UserFreeTree::all();
            foreach ($freeRecords as $record) {
                $currentIds = $record->tree_ids ?? [];
                
                if (!empty($currentIds)) {
                    // Sirf wahi IDs rakho jo naye $updatedValidTreeIds me hain
                    $updatedIds = array_values(array_intersect($currentIds, $updatedValidTreeIds));
                    
                    if (count($currentIds) !== count($updatedIds)) {
                        $record->tree_ids = $updatedIds;
                        $record->used_count = count($updatedIds);
                        $record->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success', 
                'message' => 'Database ekdum clean ho gaya hai! Saara faltu (orphaned) data delete kar diya gaya.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error', 
                'message' => 'Error aayi: ' . $e->getMessage()
            ]);
        }
    }

    public function home()
    {
        $page_title = 'Dashboard';

        // 1. Total Counts
        $projectCount = \App\Models\Project::count();
        $treeCount = \App\Models\MtTree::count();
        $districtCount = \App\Models\District::count();

        // 2. Last 6 Months Data Logic
        $months = [];
        $projectData = [];
        $treeData = [];
        $districtData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subMonths($i);
            $monthName = $date->format('M');
            $months[] = $monthName;

            $projectData[] = \App\Models\Project::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)->count();

            $treeData[] = \App\Models\MtTree::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)->count();

            $districtData[] = District::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)->count();
        }

        // 3. Static Data
        $pieLabels = ['Projects', 'Trees', 'Districts'];
        $pieData = [$projectCount, $treeCount, $districtCount];

        return view('dashboard.home', compact(
            'page_title',
            'projectCount',
            'treeCount',
            'districtCount',
            'months',
            'projectData',
            'treeData',
            'districtData',
            'pieLabels',
            'pieData'
        ));
    }

    public function Profile()
    {
        $page_title = 'Profile';
        return view('dashboard.profile', compact('page_title'));
    }

    public function district_dashboard()
    {
        $page_title = 'District Monitoring Dashoard';
        return view('dashboard.district', compact('page_title'));
    }

    public function add_project()
    {
        $page_title = 'Register New Project';
        $statemaster = StateMaster::all();
        $officers = User::Where('role_id', 2)->get();
        return view('dashboard.new_project', compact('page_title', 'statemaster', 'officers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'state' => 'required|exists:state_master,id',
            'client_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'field_officer_name' => 'required|array',
            'field_officer_name.*' => 'exists:users,id',
            'ward_no'=>'required',
        ]);

        Project::create([
            'project_name' => $request->project_name,
            'state_id' => $request->state,
            'client_name' => $request->client_name,
            'company_name' => $request->company_name,
            'field_officer_id' => json_encode($request->field_officer_name),
            'ward_no' => $request->ward_no,
        ]);

        return redirect()->route('project.list')->with('success', 'Project created successfully!');
    }

    public function settings($id)
    {
        $project = Project::with('settings')->findOrFail($id);
        return view('projects.settings', compact('project'));
    }

    public function updateSettings(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        if ($request->has('settings')) {
            foreach ($request->settings as $key => $data) {
                ProjectSetting::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'field_key' => $key
                    ],
                    [
                        'is_required' => $data['required'] ?? 0,
                        'min_value'   => $data['min'] ?? null,
                        'max_value'   => $data['max'] ?? null,
                    ]
                );
            }
        }
        if ($request->has('accuracy')) {
            $project->accuracy = $request->accuracy;
            $project->save();
        }

        return redirect()->back()->with('success', 'Project settings updated successfully!');
    }

    public function viewSettings($id)
    {
        $project = Project::with('settings')->findOrFail($id);
        return view('projects.settings_view', compact('project'));
    }

    public function project_list()
    {
        $page_title = 'Project List';
        $projects = Project::with(['state', 'fieldOfficer'])->get();
        return view('dashboard.project_list', compact('page_title', 'projects'));
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        return redirect()->route('project.list')->with('success', 'Project deleted successfully.');
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $states = StateMaster::all();
        $officers = User::where('role_id', 2)->get();
        return view('dashboard.project_edit', compact('project', 'states', 'officers'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $request->validate([
            'project_name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'state_id' => 'required|exists:state_master,id',
            'company_name' => 'nullable|string|max:255',
            'field_officer_id' => 'required|array',
            'field_officer_id.*' => 'exists:users,id',
            'ward_no' => 'required|numeric|min:1',
        ]);

        $project->update([
            'project_name' => $request->project_name,
            'client_name' => $request->client_name,
            'state_id' => $request->state_id,
            'company_name' => $request->company_name,
            'field_officer_id' => json_encode($request->field_officer_id),
            'ward_no' => $request->ward_no,
        ]);

        return redirect()->route('project.list')->with('success', 'Project updated successfully.');
    }

    public function storetree_data(Request $request)
    {
        $rules = [
            'project_id' => 'required|exists:projects,id',
            'tree_no'    => 'required',
            'tree_name'  => 'required',
        ];

        if ($request->project_id) {
            $settings = ProjectSetting::where('project_id', $request->project_id)
                ->where('is_required', 1)
                ->get();

            foreach ($settings as $setting) {
                $key = $setting->field_key;
                if ($key == 'all_captured_images') {
                    $rules['tree_images'] = 'required';
                    $rules['tree_images.*'] = 'image|mimes:jpeg,png,jpg|max:5120';
                } else if ($key == 'ward_number') {
                    $rules['ward_plot_no'] = 'required';
                } else {
                    $rules[$key] = 'required';
                }
            }
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();
            $treeData = new MtTree();
            $treeData->user_id  = auth()->id();
            $treeData->datetime = now();

            $treeData->project_id     = $request->project_id;
            $treeData->ward_plot_no   = $request->ward_plot_no;
            $treeData->tree_no        = $request->tree_no;
            $treeData->tree_name      = $request->tree_name;
            $treeData->scientific_name = $request->scientific_name;
            $treeData->family          = $request->family;
            $treeData->girth          = $request->girth;
            $treeData->height         = $request->height;
            $treeData->canopy         = $request->canopy;
            $treeData->age            = $request->age;
            $treeData->condition      = $request->condition;
            $treeData->address        = $request->address;
            $treeData->landmark       = $request->landmark;
            $treeData->ownership      = $request->ownership;
            $treeData->concern_person = $request->concern_person;
            $treeData->latitude       = $request->latitude;
            $treeData->longitude      = $request->longitude;
            $treeData->remark         = $request->remark;
            
            if ($request->hasFile('tree_images')) {
                $imagePaths = [];
                foreach ($request->file('tree_images') as $image) {
                    $filename = 'tree_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('uploads/tree_images'), $filename);
                    $imagePaths[] = 'uploads/tree_images/' . $filename;
                }
                $treeData->all_captured_images = json_encode($imagePaths);
            }

            $treeData->save();
            DB::commit();

            return redirect()->route('tree.list')->with('success', 'Tree data saved successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("MtTree Store Error: " . $e->getMessage());
            return back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
        }
    }

    public function edit_tree($id)
    {
        $page_title = 'Edit Tree';
        $tree = MtTree::with(['tree', 'scientific', 'familyRelation'])->where('id', $id)->first();

        if (!$tree) {
            return redirect()->back()->with('error', 'No tree found for this project.');
        }

        $allTrees = Tree::select('id', 'name')->orderBy('name')->get();
        $allScientific = ScientificName::select('id', 'scientific_name')->orderBy('scientific_name')->get();
        $allFamilies = Family::select('id', 'family_name')->orderBy('family_name')->get();

        $tree->tree_id = $tree->tree_name;
        $tree->scientific_id = $tree->scientific_name;
        $tree->family_id = $tree->family;

        $tree->tree_name = $tree->tree->name ?? $tree->tree_name;
        $tree->scientific_name = $tree->scientific->scientific_name ?? $tree->scientific_name;
        $tree->family = $tree->familyRelation->family_name ?? $tree->family;
        $tree->all_captured_images = json_decode($tree->all_captured_images, true) ?? [];

        return view('dashboard.new_tree', compact('page_title', 'tree', 'allTrees', 'allScientific', 'allFamilies'));
    }

    public function update_tree(Request $request, $tree_id)
    {
        $request->validate([
            'tree_no' => 'required|string|max:255',
            'tree_name' => 'required|string|max:255',
            'tree_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        try {
            $tree = MtTree::findOrFail($tree_id);

            if ($request->filled('ward_plot_no')) $tree->ward_plot_no = $request->ward_plot_no;
            if ($request->filled('tree_no')) $tree->tree_no = $request->tree_no;
            if ($request->filled('tree_name')) $tree->tree_name = $request->tree_name;
            if ($request->filled('scientific_name')) $tree->scientific_name = $request->scientific_name;
            if ($request->filled('family')) $tree->family = $request->family;
            if ($request->filled('girth')) $tree->girth = $request->girth;
            if ($request->filled('height')) $tree->height = $request->height;
            if ($request->filled('canopy')) $tree->canopy = $request->canopy;
            if ($request->filled('age')) $tree->age = $request->age;
            if ($request->filled('condition')) $tree->condition = $request->condition;
            if ($request->filled('address')) $tree->address = $request->address;
            if ($request->filled('landmark')) $tree->landmark = $request->landmark;
            if ($request->filled('ownership')) $tree->ownership = $request->ownership;
            if ($request->filled('concern_person')) $tree->concern_person = $request->concern_person;
            if ($request->filled('latitude')) $tree->latitude = $request->latitude;
            if ($request->filled('longitude')) $tree->longitude = $request->longitude;
            if ($request->filled('remark')) $tree->remark = $request->remark;

            $existingImages = json_decode($tree->all_captured_images, true) ?? [];

            if ($request->images_to_delete) {
                $imagesToDelete = json_decode($request->images_to_delete, true);
                foreach ($imagesToDelete as $imageToDelete) {
                    $filePath = public_path($imageToDelete);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $existingImages = array_diff($existingImages, [$imageToDelete]);
                }
            }

            if ($request->hasFile('tree_images')) {
                $uploadPath = public_path('tree_images');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                foreach ($request->file('tree_images') as $image) {
                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move($uploadPath, $filename);
                    $existingImages[] = 'tree_images/' . $filename;
                }
            }

            $tree->all_captured_images = json_encode(array_values($existingImages));
            $tree->save();

            return redirect()->route('tree.list')->with('success', 'Tree updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ==========================================
    // ✅ 1. UPDATED TREE LIST WITH FILTERS ✅
    // ==========================================
    public function tree_list(Request $request)
    {
        $page_title = 'Tree List';
        
        // Fetch Projects for Dropdown
        $projects = Project::select('id', 'project_name')->get();
        
        // Start Query
        $query = MtTree::with(['project', 'tree', 'scientific', 'familyRelation']);

        // Apply Filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('tree_no')) {
            $query->where('tree_no', 'like', '%' . $request->tree_no . '%');
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00', 
                $request->to_date . ' 23:59:59'
            ]);
        }

        // Get Results (Using get() as per your original code)
        $trees = $query->orderBy('id', 'desc')->get();

        return view('dashboard.tree_list', compact('page_title', 'trees', 'projects'));
    }

    // ==========================================
    // ✅ 2. EXPORT EXCEL FUNCTION ✅
    // ==========================================
    public function export_tree_excel(Request $request)
    {
        return Excel::download(new TreeExport($request), 'trees_export.xlsx');
    }

    // ==========================================
    // ✅ 3. EXPORT PDF FUNCTION ✅
    // ==========================================
    public function export_tree_pdf(Request $request)
    {
        $page_title = 'Tree List Report';
        
        $query = MtTree::with(['project', 'tree', 'scientific', 'familyRelation']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('tree_no')) {
            $query->where('tree_no', 'like', '%' . $request->tree_no . '%');
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00', 
                $request->to_date . ' 23:59:59'
            ]);
        }

        $trees = $query->orderBy('id', 'desc')->get();

        $pdf = Pdf::loadView('dashboard.tree_export_pdf', compact('trees', 'page_title'));
        return $pdf->setPaper('a4', 'landscape')->download('tree_report.pdf');
    }

    public function add_tree_data()
    {
        $page_title = 'Add Tree Data';
        $allProjects = \App\Models\Project::orderBy('project_name', 'asc')->get();
        $projectSettings = \App\Models\ProjectSetting::all()->groupBy('project_id');
        $allTrees = \App\Models\Tree::leftJoin('scientific_names', 'trees.id', '=', 'scientific_names.tree_id')
            ->leftJoin('families', 'trees.id', '=', 'families.tree_id')
            ->select(
                'trees.id',
                'trees.name',
                'scientific_names.id as related_scientific_id',
                'families.id as related_family_id'
            )
            ->orderBy('trees.name', 'asc')
            ->get();
        $allScientific = \App\Models\ScientificName::select('id', 'scientific_name')->orderBy('scientific_name')->get();
        $allFamilies = \App\Models\Family::select('id', 'family_name')->orderBy('family_name')->get();

        return view('dashboard.add_tree_data', compact('page_title', 'allProjects', 'projectSettings', 'allTrees', 'allScientific', 'allFamilies'));
    }

    public function store_tree_data() {}

    public function tree_map()
    {
        $page_title = 'Tree Map';
        $trees = MtTree::with('project')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'tree_name', 'latitude', 'longitude', 'project_id', 'address']);

        return view('dashboard.tree_map', compact('page_title', 'trees'));
    }

    public function Distribution_Tracking()
    {
        $page_title = 'Distribution Tracking';
        return view('dashboard.distribution_tracking', compact('page_title'));
    }

    public function project_report(Request $request)
    {
        $page_title = 'Project Report';
        $query = Project::with(['state', 'fieldOfficer']);

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        $projects = $query->get();

        if ($request->has('download_pdf')) {
            $pdf = Pdf::loadView('dashboard.report_pdf', compact('projects', 'page_title'));
            return $pdf->download('project_report.pdf');
        }

        return view('dashboard.report', compact('page_title', 'projects'));
    }

    public function tree_report(Request $request)
    {
        $page_title = 'Tree Report';
        $query = MtTree::with(['tree', 'scientific', 'familyRelation']);

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        $trees = $query->orderBy('created_at', 'desc')->get();

        if ($request->has('download_pdf')) {
            $pdf = Pdf::loadView('dashboard.tree_report_pdf', compact('trees', 'page_title'));
            return $pdf->download('tree_report.pdf');
        }

        return view('dashboard.tree_report', compact('page_title', 'trees'));
    }

    public function Records()
    {
        $page_title = 'Inspection Records';
        return view('dashboard.inspection_records', compact('page_title'));
    }

    public function Schedule()
    {
        $page_title = 'Inspection Schedule';
        return view('dashboard.inspection_schedule', compact('page_title'));
    }

    public function rate_app()
    {
        $ratings = UserRating::with('user')->get();
        $page_title = 'App Rating';
        return view('dashboard.app_rating', compact('page_title', 'ratings'));
    }

    public function app_rate_update(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $rating = UserRating::findOrFail($id);
        $rating->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->back()->with('success', 'Rating updated successfully!');
    }

    public function add_tree_name()
    {
        $page_title = 'Create Tree Name';
        return view('dashboard.add_tree_name', compact('page_title'));
    }

    public function new_tree_add(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'scientific_name' => 'required|string|max:255',
            'family_name' => 'required|string|max:255',
            'height_ratio' => 'nullable|string|max:255',
            'age_ratio' => 'nullable|string|max:255',
            'canopy_ratio' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $tree = \App\Models\Tree::create([
                'name' => $request->name,
            ]);

            \App\Models\ScientificName::create([
                'tree_id'        => $tree->id,
                'scientific_name' => $request->scientific_name,
                'height_ratio'   => $request->height_ratio,
                'age_ratio'      => $request->age_ratio,
                'canopy_ratio'   => $request->canopy_ratio,
            ]);

            \App\Models\Family::create([
                'tree_id'     => $tree->id,
                'family_name' => $request->family_name,
            ]);
        });

        return redirect()->route('tree.name.list')->with('success', 'Tree created successfully!');
    }

    public function tree_list_add()
    {
        $page_title = "Tree List";
        $trees = \App\Models\Tree::with(['scientific', 'family'])
            ->orderBy('id', 'desc')
            ->get();

        return view('dashboard.tree_name_list', compact('trees', 'page_title'));
    }

    public function list_edit($id)
    {
        $page_title = "Edit Tree";
        $tree = Tree::with(['scientific', 'family'])->findOrFail($id);
        return view('dashboard.list_tree_edit', compact('page_title', 'tree'));
    }

    public function list_update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'scientific_name' => 'required|string|max:255',
            'family_name' => 'required|string|max:255',
            'height_ratio' => 'nullable|string|max:255',
            'age_ratio' => 'nullable|string|max:255',
            'canopy_ratio' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $id) {
            $tree = Tree::findOrFail($id);
            $tree->update(['name' => $request->name]);

            $scientific = ScientificName::where('tree_id', $tree->id)->first();
            if ($scientific) {
                $scientific->update([
                    'scientific_name' => $request->scientific_name,
                    'height_ratio' => $request->height_ratio,
                    'age_ratio' => $request->age_ratio,
                    'canopy_ratio' => $request->canopy_ratio,
                ]);
            }

            $family = Family::where('tree_id', $tree->id)->first();
            if ($family) {
                $family->update(['family_name' => $request->family_name]);
            }
        });

        return redirect()->route('tree.name.list')->with('success', 'Tree updated successfully!');
    }

    public function list_destroy($id)
    {
        DB::transaction(function () use ($id) {
            $tree = Tree::findOrFail($id);
            ScientificName::where('tree_id', $tree->id)->delete();
            Family::where('tree_id', $tree->id)->delete();
            $tree->delete();
        });

        return redirect()->route('tree.name.list')->with('success', 'Tree deleted successfully!');
    }

    public function importTrees(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);
        Excel::import(new TreesImport, $request->file('file'));
        return redirect()->route('tree.name.list')->with('success', 'Trees imported successfully!');
    }
    
   public function subscription_list()
{
    $page_title = 'User Subscriptions';
    $subscriptions = \App\Models\UserPaidTree::with([
        'user', 
        'tree.treeDetail'
    ])->orderBy('created_at', 'desc')->get();

    return view('dashboard.subscriptions', compact('page_title', 'subscriptions'));
}
    
    
    
    public function export_tree_images_zip(Request $request)
{
    // Agar images zyada hain toh execution time badha dete hain
    set_time_limit(0); 

    $query = MtTree::query();

    // 1. Filters (Same as Tree List)
    if ($request->filled('project_id')) {
        $query->where('project_id', $request->project_id);
    }
    if ($request->filled('tree_no')) {
        $query->where('tree_no', 'like', '%' . $request->tree_no . '%');
    }
    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('created_at', [
            $request->from_date . ' 00:00:00',
            $request->to_date . ' 23:59:59'
        ]);
    }

    $trees = $query->with('project')->get();

    if ($trees->isEmpty()) {
        return redirect()->back()->with('error', 'In filters ke liye koi images nahi mili.');
    }

    // 2. Zip File Setup
    $zipFileName = 'Tree_Images_' . date('d-m-Y_H-i') . '.zip';
    $zipPath = storage_path('app/public/' . $zipFileName);

    $zip = new ZipArchive;

    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
        foreach ($trees as $tree) {
            if (!empty($tree->all_captured_images)) {
                // Check if string then decode
                $images = is_string($tree->all_captured_images) 
                          ? json_decode($tree->all_captured_images, true) 
                          : $tree->all_captured_images;

                if (is_array($images)) {
                    foreach ($images as $imagePath) {
                        // Agar DB mein pura path hai toh bhi, aur agar sirf filename hai toh bhi:
                        $fullPath = public_path($imagePath);

                        if (File::exists($fullPath) && is_file($fullPath)) {
                            // Zip ke andar structure: Project_Name/Tree_No_FileName.jpg
                            $projectName = $tree->project->project_name ?? 'Other_Project';
                            $cleanProjectName = str_replace(['/', '\\', ' '], '_', $projectName); // Safe Folder Name
                            
                            $innerPath = $cleanProjectName . '/Tree_' . $tree->tree_no . '_' . basename($imagePath);
                            
                            $zip->addFile($fullPath, $innerPath);
                        }
                    }
                }
            }
        }
        $zip->close();
    }

    // 3. Download and Cleanup
    if (File::exists($zipPath)) {
        return response()->download($zipPath)->deleteFileAfterSend(true);
    } else {
        return redirect()->back()->with('error', 'Zip file generate nahi ho saki.');
    }
}
}