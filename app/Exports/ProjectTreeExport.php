<?php

namespace App\Exports;

use App\Models\MtTree;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectTreeExport implements FromCollection, WithHeadings, WithMapping
{
    protected $project_id;
    protected $tree_ids; // 1. Added property for tree IDs

    // 2. Updated Constructor to accept tree_ids (default is empty array)
    public function __construct($project_id, $tree_ids = [])
    {
        $this->project_id = $project_id;
        $this->tree_ids = $tree_ids;
    }

    public function collection()
    {
        // 3. Start Query
        $query = MtTree::where('project_id', $this->project_id);

        // 4. Apply Filter if tree_ids are passed
        if (!empty($this->tree_ids)) {
            $query->whereIn('id', $this->tree_ids);
        }

        $trees = $query->get();
        
        // Sort trees with robust numeric comparison
        $sorted = $trees->sort(function($a, $b) {
            $numA = intval(preg_replace('/[^0-9]/', '', $a->tree_no));
            $numB = intval(preg_replace('/[^0-9]/', '', $b->tree_no));
            if ($numA == $numB) return 0;
            return ($numA < $numB) ? -1 : 1;
        });
        
        return $sorted->values();
    }

    public function map($tree): array
    {
        // Helper to get names safely (Kept exactly as your code)
        $treeName = \App\Models\Tree::find($tree->tree_name);
        $scientific = \App\Models\ScientificName::find($tree->scientific_name);
        $family = \App\Models\Family::find($tree->family);

        return [
            $tree->tree_no,
            $tree->ward_plot_no,
            $treeName->name ?? 'N/A',
            $scientific->scientific_name ?? 'N/A',
            $family->family_name ?? 'N/A',
            $tree->girth,
            $tree->height,
            $tree->canopy,
            $tree->age,
            $tree->condition,
            $tree->owner_name ?? $tree->ownership, 
            $tree->concern_person, // ✅ Added Concern Person
            $tree->landmark,       // ✅ Added Landmark
            $tree->remark,         // ✅ Added Remark
            $tree->latitude,
            $tree->longitude,
            $tree->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'Tree No',
            'Ward/Plot No',
            'Tree Name',
            'Scientific Name',
            'Family',
            'Girth (cm)',
            'Height (ft)',       // ✅ Changed (m) to (ft)
            'Canopy (ft)',       // ✅ Changed (m) to (ft)
            'Age',
            'Condition',
            'Ownership',
            'Concern Person',    // ✅ Added Heading
            'Landmark',          // ✅ Added Heading
            'Remark',            // ✅ Added Heading
            'Latitude',
            'Longitude',
            'Date Added',
        ];
    }
}