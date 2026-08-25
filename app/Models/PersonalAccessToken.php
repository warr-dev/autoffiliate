<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PersonalAccessToken extends Model
{
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
    ];

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Find the token instance for the given raw plain text token.
     */
    public static function findToken(string $plainTextToken): ?self
    {
        if (str_contains($plainTextToken, '|')) {
            [$id, $plain] = explode('|', $plainTextToken, 2);
            $instance = static::find($id);

            if ($instance && hash_equals($instance->token, hash('sha256', $plain))) {
                if ($instance->expires_at && $instance->expires_at->isPast()) {
                    return null;
                }

                $instance->forceFill(['last_used_at' => now()])->save();
                return $instance;
            }
        }

        // Direct token lookup
        $hashed = hash('sha256', $plainTextToken);
        $instance = static::where('token', $hashed)->first();

        if ($instance) {
            if ($instance->expires_at && $instance->expires_at->isPast()) {
                return null;
            }

            $instance->forceFill(['last_used_at' => now()])->save();
            return $instance;
        }

        return null;
    }

    /**
     * Determine if the token has the given ability.
     */
    public function can(string $ability): bool
    {
        $abilities = $this->abilities ?? ['*'];
        return in_array('*', $abilities, true) || in_array($ability, $abilities, true);
    }
}
