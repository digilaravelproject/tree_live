<?php

namespace App\Exports;

use App\Models\District;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DistrictsExport implements FromCollection, WithHeadings
{
    protected $state_id;

    public function __construct($state_id = null)
    {
        $this->state_id = $state_id;
    }

    public function collection()
    {
        // Fetch districts with state relation
        return District::with('state')
            ->when($this->state_id, fn($q) => $q->where('state_id', $this->state_id))
            ->get()
            ->map(function ($district) {
                return [
                    'id' => $district->id,
                    'district_name' => $district->district_name,
                    'state_name' => $district->state ? $district->state->state_name : 'N/A',
                ];
            });
    }

    public function headings(): array
    {
        return ['ID', 'District Name', 'State Name'];
    }
}
