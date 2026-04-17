<?php

namespace App\Imports;

use App\Models\Tree;
use App\Models\ScientificName;
use App\Models\Family;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class TreesImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Skip header row (if exists)
        $rows->skip(1)->each(function ($row) {

            $treeName = trim($row[0]);
            if (empty($treeName)) {
                return;
            }

            // 🔍 Check if tree name already exists
            $existingTree = Tree::where('name', $treeName)->first();

            if ($existingTree) {
                // ✅ Skip this row if tree already exists
                return;
            }

            DB::transaction(function () use ($row, $treeName) {
                // 1️⃣ Create tree
                $tree = Tree::create([
                    'name' => $treeName,
                ]);

                // 2️⃣ Create scientific name record
                ScientificName::create([
                    'tree_id'         => $tree->id,
                    'scientific_name' => trim($row[1]) ?? null, // Column B
                    'height_ratio'    => $row[3] ?? null, // Column D
                    'age_ratio'       => $row[4] ?? null, // Column E
                    'canopy_ratio'    => $row[5] ?? null, // Column F
                ]);

                // 3️⃣ Create family record
                Family::create([
                    'tree_id'      => $tree->id,
                    'family_name'  => trim($row[2]) ?? null, // Column C
                ]);
            });
        });
    }
}
