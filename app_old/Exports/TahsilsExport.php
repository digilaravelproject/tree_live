<?php

namespace App\Exports;

use App\Models\Tahsil;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TahsilsExport implements FromCollection, WithHeadings
{
    protected $state_id;
    protected $district_id;

    public function __construct($state_id = null, $district_id = null)
    {
        $this->state_id = $state_id;
        $this->district_id = $district_id;
    }

    public function collection()
    {
        $query = Tahsil::with(['state', 'district'])
            ->select('id', 'tahsil_name', 'district_id', 'state_id');

        if ($this->state_id) {
            $query->where('state_id', $this->state_id);
        }

        if ($this->district_id) {
            $query->where('district_id', $this->district_id);
        }

        $tahsils = $query->get();

        // ✅ Replace IDs with readable names
        return $tahsils->map(function ($tahsil) {
            return [
                'ID' => $tahsil->id,
                'Tahsil Name' => $tahsil->tahsil_name,
                'District' => $tahsil->district->district_name ?? 'N/A',
                'State' => $tahsil->state->state_name ?? 'N/A',
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Tahsil Name', 'District', 'State'];
    }
}
