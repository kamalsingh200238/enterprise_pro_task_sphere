<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function hasRole($role)
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }

        return $this->role === $role;
    }

    public static function getAllSupervisorAndAdmins()
    {
        return self::whereIn('role', [UserRole::SUPERVISOR, UserRole::ADMIN])->get();
    }

    public static function getAllSupervisors()
    {
        return self::where('role', UserRole::SUPERVISOR)->get();
    }

    public static function getAllAdmins()
    {
        return self::where('role', UserRole::ADMIN)->get();
    }

    public function createdProjects()
    {
        return $this->hasOne(Project::class, 'created_by');
    }

    public function updatedProjects()
    {
        return $this->hasOne(Project::class, 'updated_by');
    }

    public function supervisedProjects()
    {
        return $this->hasOne(Project::class, 'supervisor_id');
    }

    public function assignedProjects()
    {
        return $this->belongsToMany(Project::class, 'project_assignees');
    }

    public function viewableProjects()
    {
        return $this->belongsToMany(Project::class, 'project_viewers');
    }

    public function createdTasks()
    {
        return $this->hasOne(Task::class, 'created_by');
    }

    public function updatedTasks()
    {
        return $this->hasOne(Task::class, 'updated_by');
    }

    public function supervisedTasks()
    {
        return $this->hasOne(Task::class, 'supervisor_id');
    }

    public function assignedTasks()
    {
        return $this->belongsToMany(Task::class, 'task_assignees');
    }

    public function viewableTasks()
    {
        return $this->belongsToMany(Task::class, 'task_viewers');
    }
}
