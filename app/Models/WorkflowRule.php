<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowRule extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'category',
        'frequency',
        'times',
        'days',
        'target_page',
        'workflow_actions',
        'action_contexts',
        'general_context',
        'weather_context',
        'occasion_context',
        'tones',
        'personas',
        'custom_persona',
        'manual_prompt',
        'status',
        'last_run',
    ];

    protected $casts = [
        'times' => 'array',
        'days' => 'array',
        'workflow_actions' => 'array',
        'action_contexts' => 'array',
        'tones' => 'array',
        'personas' => 'array',
        'last_run' => 'datetime',
    ];
}
