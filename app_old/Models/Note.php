<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    // Define the table name
    protected $table = 'notes';

    // Define which fields can be mass-assigned
    protected $fillable = ['title', 'content'];
}
