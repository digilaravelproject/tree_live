<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    // Table name (optional if it follows Laravel convention)
    protected $table = 'faqs';

    // Fillable fields
    protected $fillable = [
        'question',
        'answer',
    ];
}
