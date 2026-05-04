<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tahsil extends Model
{
    use HasFactory;

    protected $table = 'tahsils_master';

    protected $fillable = [
        'tahsil_name',
        'short_code',
        'state_id',
        'district_id',
    ];

    public function state()
    {
        return $this->belongsTo(StateMaster::class, 'state_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }
}
