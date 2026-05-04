<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserRating extends Model
{
    use HasFactory;

    // Explicitly mention table name
    protected $table = 'user_ratings';

    protected $fillable = [
        'user_id',
        'rating',
        'comment',
    ];

    /**
     * Relationship: UserRating belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
