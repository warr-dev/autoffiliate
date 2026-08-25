<?php

namespace App\Traits;

use App\Models\PersonalAccessToken;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

trait HasApiTokens
{
    protected ?PersonalAccessToken $currentAccessToken = null;

    public function tokens(): MorphMany
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    /**
     * Create a new personal access token for the user.
     */
    public function createToken(string $name, array $abilities = ['*'], ?\DateTimeInterface $expiresAt = null)
    {
        $plainTextToken = Str::random(40);

        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        $formattedToken = "{$token->id}|{$plainTextToken}";

        return new class($token, $formattedToken) {
            public function __construct(
                public PersonalAccessToken $accessToken,
                public string $plainTextToken
            ) {}
        };
    }

    public function currentAccessToken(): ?PersonalAccessToken
    {
        return $this->currentAccessToken;
    }

    public function withAccessToken(?PersonalAccessToken $accessToken): static
    {
        $this->currentAccessToken = $accessToken;
        return $this;
    }
}
