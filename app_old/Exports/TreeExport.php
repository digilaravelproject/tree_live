<?php

namespace App\Exports;

use App\Models\MtTree;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class TreeExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = MtTree::with(['project', 'tree', 'scientific', 'familyRelation']);

        if ($this->request->filled('project_id')) {
            $query->where('project_id', $this->request->project_id);
        }

        if ($this->request->filled('tree_no')) {
            $query->where('tree_no', 'like', '%' . $this->request->tree_no . '%');
        }

        if ($this->request->filled('from_date') && $this->request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $this->request->from_date . ' 00:00:00', 
                $this->request->to_date . ' 23:59:59'
            ]);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Tree No',
            'Project Name',
            'Ward/Plot',
            'Common Name',
            'Scientific Name',
            'Family',
            'Girth',
            'Height (ft)',
            'Canopy (ft)',
            'Age',
            'Condition',
            'Ownership',
            'Concern Person',
            'Landmark',
            'Remark',
            'Address',
            'Latitude',
            'Longitude',
            'Created At'
        ];
    }

    public function map($tree): array
    {
        return [
            $tree->tree_no,
            $tree->project->project_name ?? '',
            $tree->ward_plot_no,
            $tree->tree->name ?? $tree->tree_name,
            $tree->scientific->scientific_name ?? $tree->scientific_name,
            $tree->familyRelation->family_name ?? $tree->family,
            $tree->girth,
            $tree->height,
            $tree->canopy,
            $tree->age,
            $tree->condition,
            $tree->ownership,
            $tree->concern_person,
            $tree->landmark,
            $tree->remark,
            $tree->address,
            $tree->latitude,
            $tree->longitude,
            $tree->created_at->format('Y-m-d'),
        ];
    }
}