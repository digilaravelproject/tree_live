<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'districts_master';

    protected $fillable = [
        'district_name',
        'short_code',
        'state_id', // ✅ Added this line
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'district_id');
    }

    // ✅ Optional: if you have a State model, you can add this relationship
    public function state()
    {
        return $this->belongsTo(StateMaster::class, 'state_id');
    }
}
