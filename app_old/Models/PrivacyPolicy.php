<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyPolicy extends Model
{
    // Define the table name
    protected $table = 'privacy_policies';

    // Define which fields can be mass-assigned
    protected $fillable = ['title', 'content'];
}
