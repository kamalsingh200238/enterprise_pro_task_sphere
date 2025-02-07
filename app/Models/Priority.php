<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Color;

class Priority extends Model
{
    use HasFactory;

    protected $table = "priorities";
    protected $fillable = [
        "name",
        "color"
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'color' => Color::class
    ];
}
