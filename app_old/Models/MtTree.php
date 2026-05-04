<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MtTree extends Model
{
    use HasFactory;

    protected $table = 'mt_trees';

    protected $fillable = [
        'project_id',
        'user_id',
        'ward_plot_no',
        'tree_no',
        'tree_name',
        'scientific_name',
        'family',
        'girth',
        'height',
        'canopy',
        'age',
        'condition',
        'address',
        'landmark',
        'ownership',
        'concern_person',
        'remark',
        'tree_image_upload',
        'captured_image',
        'all_captured_images',
        'latitude',
        'longitude',
        'payment',
        'datetime',
    ];

    protected $casts = [
        'all_captured_images' => 'array',
    ];
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function tree()
    {
        return $this->belongsTo(Tree::class, 'tree_name', 'id');
    }

    // Relation with scientific name
    public function scientific()
    {
        return $this->belongsTo(ScientificName::class, 'scientific_name', 'id');
    }

    // Relation with family
    public function familyRelation()
    {
        return $this->belongsTo(Family::class, 'family', 'id');
    }
    public function family()
    {
        return $this->belongsTo(Family::class, 'family');
    }
    
    public function treeDetail() {
    // Ye 'tree_name' (ID: 3) ko trees table ki 'id' se match karega
    return $this->belongsTo(Tree::class, 'tree_name', 'id');
}
}
