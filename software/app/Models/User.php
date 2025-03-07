<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Check if user have a role/roles
     */
    public function hasRole($role): bool
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }

        return $this->role === $role;
    }

    /**
     * Get all supervisors and admins
     */
    public static function getAllSupervisorsAndAdmins()
    {
        return self::whereIn('role', [UserRole::Supervisor, UserRole::Admin]);
    }

    /**
     * Get all admins
     */
    public static function getAllAdmins()
    {
        return self::where('role', UserRole::Admin);
    }

    /**
     * Get all supervisors
     */
    public static function getAllSupervisors()
    {
        return self::where('role', UserRole::Supervisor);
    }

    /**
     * Get all the projects user created
     */
    public function createdProjects(): HasOne
    {
        return $this->hasOne(Project::class, 'created_by');
    }

    /**
     * Get all the projects user updated last
     */
    public function updatedProjects(): HasOne
    {
        return $this->hasOne(Project::class, 'updated_by');
    }

    /**
     * Get all the projects the user is supervising
     */
    public function supervisedProjects(): HasOne
    {
        return $this->hasOne(Project::class, 'supervisor_id');
    }

    /**
     * Get all the projects assigned to user
     */
    public function assignedProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_assignees');
    }

    /**
     * Get all the projects user is viewer of
     */
    public function viewableProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_viewers');
    }

    /**
     * Get all the tasks user has created
     */
    public function createdTasks(): HasOne
    {
        return $this->hasOne(Task::class, 'created_by');
    }

    /**
     * Get all the tasks user has updated last
     */
    public function updatedTasks(): HasOne
    {
        return $this->hasOne(Task::class, 'updated_by');
    }

    /**
     * Get all the tasks the user is supervising
     */
    public function supervisedTasks(): HasOne
    {
        return $this->hasOne(Task::class, 'supervisor_id');
    }

    /**
     * Get all the tasks assigned to user
     */
    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignees');
    }

    /**
     * Get all the tasks user is viewer of
     */
    public function viewableTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_viewers');
    }

    /**
     * Get all the sub-tasks user has created
     */
    public function createdSubTasks(): HasOne
    {
        return $this->hasOne(SubTask::class, 'created_by');
    }

    /**
     * Get all the sub-tasks user has updated last
     */
    public function updatedSubTasks(): HasOne
    {
        return $this->hasOne(SubTask::class, 'updated_by');
    }

    /**
     * Get all the sub-tasks the user is supervising
     */
    public function supervisedSubTasks(): HasOne
    {
        return $this->hasOne(SubTask::class, 'supervisor_id');
    }

    /**
     * Get all the sub-tasks assigned to user
     */
    public function assignedSubTasks(): BelongsToMany
    {
        return $this->belongsToMany(SubTask::class, 'task_assignees');
    }

    /**
     * Get all the sub-tasks user is viewer of
     */
    public function viewableSubTasks(): BelongsToMany
    {
        return $this->belongsToMany(SubTask::class, 'task_viewers');
    }

    /**
     * Get all the comments user has left
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
