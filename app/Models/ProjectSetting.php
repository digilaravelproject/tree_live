<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'field_key',
        'is_required',
        'min_value',
        'max_value'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
