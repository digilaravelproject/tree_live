<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScientificName extends Model
{
    use HasFactory;

    protected $fillable = [
        'tree_id',
        'scientific_name',
        'height_ratio',
        'age_ratio',
        'canopy_ratio',
    ];

    public function tree()
    {
        return $this->belongsTo(Tree::class, 'tree_id');
    }
    public function mtTrees()
    {
        return $this->hasMany(MtTree::class, 'scientific_name', 'id');
    }
}
