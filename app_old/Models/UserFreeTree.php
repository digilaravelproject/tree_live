<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFreeTree extends Model
{
    protected $table = 'user_free_trees';

    protected $fillable = ['user_id', 'tree_ids', 'used_count'];

    protected $casts = [
        'tree_ids' => 'array',
    ];

    // User Relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Kisi user ka free tree record fetch karo, na ho toh create karo
     */
    public static function getOrCreate(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            ['tree_ids' => [], 'used_count' => 0]
        );
    }

    /**
     * Check karo kya ye tree ID pehle se free list me hai
     */
    public function hasTree(int $treeId): bool
    {
        return in_array($treeId, $this->tree_ids ?? []);
    }

    /**
     * Remaining free slots kitne hain
     */
    public function remainingFreeSlots(): int
    {
        return max(0, 100 - count($this->tree_ids ?? []));
    }

    /**
     * Naye tree IDs free list me add karo (100 limit ke andar)
     * Returns: actually add hue IDs
     */
    public function addFreeTreeIds(array $newIds): array
    {
        $existing = $this->tree_ids ?? [];
        $remaining = $this->remainingFreeSlots();

        // Sirf wo IDs add karo jo pehle se nahi hain
        $toAdd = array_values(array_diff($newIds, $existing));
        $toAdd = array_slice($toAdd, 0, $remaining); // limit to remaining slots

        if (!empty($toAdd)) {
            $merged = array_merge($existing, $toAdd);
            $this->tree_ids = $merged;
            $this->used_count = count($merged);
            $this->save();
        }

        return $toAdd;
    }
}
