<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'platform',
        'name',
        'account_id',
        'access_token',
        'extra_config',
        'is_enabled',
        'status',
    ];

    protected $casts = [
        'extra_config' => 'array',
        'is_enabled' => 'boolean',
    ];
}
