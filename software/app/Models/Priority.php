<?php

namespace App\Models;

use App\Enums\Color;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Priority extends Model
{
    // define table name to help laravel in finding table
    protected $table = 'priorities';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'color',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'color' => Color::class,
    ];

    /**
     * Get the projects associated to the priority
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the tasks associated to the priority
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the sub-tasks associated to the priority
     */
    public function subTasks(): HasMany
    {
        return $this->hasMany(SubTask::class);
    }
}
