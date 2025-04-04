<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthStatus extends Model
{
    protected $table = 'oauth_status';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['enabled'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Check if OAuth authentication is enabled
     */
    public static function isEnabled(): bool
    {
        return self::first()->enabled;
    }

    /**
     * Toggle the OAuth status
     */
    public static function toggle(bool $enabled): bool
    {
        $status = self::first();
        if (! $status) {
            $status = self::create(['enabled' => $enabled]);

            return $enabled;
        }

        $status->update(['enabled' => $enabled]);

        return $enabled;
    }
}
