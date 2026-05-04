<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StateMaster;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'extra_user',
        'project_name',
        'state_id',
        'client_name',
        'company_name',
        'field_officer_id',
        'ward_no', // ✅ new column added
    ];

    public function state()
    {
        return $this->belongsTo(StateMaster::class, 'state_id');
    }

    public function fieldOfficer()
    {
        return $this->belongsTo(User::class, 'field_officer_id');
    }

    public function mtTree()
    {
        return $this->hasOne(MtTree::class, 'project_id');
    }
    public function settings()
    {
        return $this->hasMany(ProjectSetting::class, 'project_id');
    }
    public function getSettingVal($key, $column)
    {
        $setting = $this->settings->where('field_key', $key)->first();
        return $setting ? $setting->$column : null;
    }
    public function trees()
    {
        return $this->hasMany(MtTree::class, 'project_id', 'id');
    }

}
