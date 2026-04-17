<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // Table name
    protected $table = 'roles';

    // Primary key (optional if it's 'id')
    protected $primaryKey = 'id';

    // Mass assignable columns
    protected $fillable = [
        'name',
        'guard_name',
    ];

    // Timestamps are true since you have created_at and updated_at
    public $timestamps = true;
}
