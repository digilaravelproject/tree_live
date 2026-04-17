<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\MtTree;
use App\Models\Tree;
use App\Models\ScientificName;
use App\Models\Family;

class MapController extends Controller
{
    public function mapGenerator()
    {
        $page_title = "Map Point Generator";
        return view('dashboard.map_generator', compact('page_title'));
    }


    public function tree_map(Request $request)
    {
        // If it's an AJAX request (fetched via JavaScript), return JSON data
        if ($request->ajax() || $request->wantsJson()) {

            // Eager load relationships: project, tree (name), scientific (name), family (name)
            $baseQuery = MtTree::with(['project', 'tree', 'scientific', 'family'])->select(
                'id',
                'project_id',
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
                'latitude',
                'longitude',
                'created_at',
                'all_captured_images'
            );

            // --- Filters ---

            // 1. Project Filter
            if ($request->filled('project_id')) {
                $baseQuery->where('project_id', $request->project_id);
            }

            // 3. Ward Number
            if ($request->filled('ward_plot_no')) {
                $baseQuery->where('ward_plot_no', $request->ward_plot_no);
            }

            // 4. Tree Number
            if ($request->filled('tree_no')) {
                $baseQuery->where('tree_no', 'like', '%' . $request->tree_no . '%');
            }

            // 5. Girth
            if ($request->filled('girth')) {
                $baseQuery->where('girth', '>=', $request->girth);
            }

            // 6. Ownership
            if ($request->filled('ownership')) {
                $baseQuery->where('ownership', $request->ownership);
            }

            // Ensure we only get trees with valid coordinates
            $baseQuery->whereNotNull('latitude')
                ->whereNotNull('longitude');

            // Clone query for bounds filtering
            $query = clone $baseQuery;

            // 2. Map Bounds Filter (Try to apply bounds first)
            $hasBoundsFilter = false;
            if ($request->has('north_lat') && $request->has('south_lat') && 
                $request->has('east_lng') && $request->has('west_lng')) {
                
                $north_lat = (float) $request->north_lat;
                $south_lat = (float) $request->south_lat;
                $east_lng = (float) $request->east_lng;
                $west_lng = (float) $request->west_lng;

                // Ensure values are valid numbers
                if (is_numeric($north_lat) && is_numeric($south_lat) && 
                    is_numeric($east_lng) && is_numeric($west_lng)) {
                    
                    $hasBoundsFilter = true;
                    
                    // Latitude bounds (always straightforward)
                    $query->whereBetween('latitude', [min($south_lat, $north_lat), max($south_lat, $north_lat)]);

                    // Longitude bounds - handle antimeridian crossing
                    if ($east_lng >= $west_lng) {
                        // Normal case: bounds don't cross antimeridian
                        $query->whereBetween('longitude', [$west_lng, $east_lng]);
                    } else {
                        // Antimeridian crossing: west > east
                        $query->where(function($q) use ($west_lng, $east_lng) {
                            $q->where('longitude', '>=', $west_lng)
                              ->orWhere('longitude', '<=', $east_lng);
                        });
                    }
                }
            }

            // Get trees - if bounds filtering returns 0 results, try without bounds
            $trees = $query->get();
            
            if ($trees->count() === 0 && $hasBoundsFilter) {
                // Fallback: try without bounds filter
                $trees = $baseQuery->get();
            }

            return response()->json([
                'success' => true,
                'trees' => $trees,
                'count' => $trees->count()
            ]);
        }

        // --- Normal Page Load ---
        $page_title = 'Tree Location Map';

        // Get Data for Dropdowns
        $projects = Project::select('id', 'project_name')->orderBy('project_name')->get();

        // Get distinct values for filters
        $wards = MtTree::distinct()->pluck('ward_plot_no')->filter();
        $ownerships = MtTree::distinct()->pluck('ownership')->filter();

        return view('dashboard.tree_map', compact('page_title', 'projects', 'wards', 'ownerships'));
    }
}
