<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tree extends Model
{
    protected $table = 'trees';
    protected $fillable = ['name'];

    public function scientificName()
    {
        return $this->hasOne(ScientificName::class);
    }

    public function family()
    {
        return $this->hasOne(Family::class);
    }
    public function mtTrees()
    {
        return $this->hasMany(MtTree::class, 'tree_name', 'id');
    }
    public function scientific()
    {
        return $this->hasOne(ScientificName::class, 'tree_id');
    }
}
