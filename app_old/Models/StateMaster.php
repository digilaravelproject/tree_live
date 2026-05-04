<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StateMaster extends Model
{
    use HasFactory;

    // Table name
    protected $table = 'state_master';

    // Primary key
    protected $primaryKey = 'id';

    // Allow auto-increment
    public $incrementing = true;

    // Disable timestamps (since your table doesn't have created_at/updated_at)
    public $timestamps = false;

    // Columns that can be mass assigned
    protected $fillable = [
        'state_name',
        'short_code',
    ];
    public function districts()
    {
        return $this->hasMany(District::class, 'state_id');
    }
}
