<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    // Define the table name
    protected $table = 'contacts';

    // Define which fields can be mass-assigned
    protected $fillable = [
        'name',
        'email',
        'phone',
        'instagram',
        'facebook',
        'whatsapp',
        'youtube',
        'linkedin',
        'details'
    ];
}
