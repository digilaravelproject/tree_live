<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    protected $table = 'families'; // 👈 Table name
    protected $fillable = ['tree_id', 'family_name'];

    public function tree()
    {
        return $this->belongsTo(Tree::class);
    }
    public function mtTrees()
    {
        return $this->hasMany(MtTree::class, 'family', 'id');
    }
}
