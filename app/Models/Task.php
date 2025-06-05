<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Events\TaskCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'user_id',
    ];

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
        'status' => TaskStatusEnum::class,
        'priority' => TaskPriorityEnum::class,
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'user_id' => 'integer',
    ];

    protected $dispatchesEvents = [
        'created' => TaskCreated::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_task');
    }

    /**
     * Scope a query to filter tasks by category.
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->whereHas('categories', function ($query) use ($categoryId) {
            $query->where('categories.id', $categoryId);
        });
    }
}
