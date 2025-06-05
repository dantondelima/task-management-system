<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name' => 'string',
    ];

    /**
     * Get the tasks that belong to this category.
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'category_task');
    }
}
