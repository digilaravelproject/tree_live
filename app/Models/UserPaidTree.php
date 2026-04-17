<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPaidTree extends Model
{
    protected $table = 'user_paid_trees';
    protected $fillable = ['user_id', 'project_id', 'mt_tree_id', 'payment_id', 'amount'];

    // User Relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Tree Relationship (Check if mt_tree_id is the foreign key)
    public function tree()
    {
        return $this->belongsTo(MtTree::class, 'mt_tree_id');
    }
}
