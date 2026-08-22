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

    /**
     * Check if this rule is due for automated execution at current time (Asia/Manila).
     */
    public function isDue(mixed $now = null): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = $now ? $now->copy()->timezone('Asia/Manila') : now()->timezone('Asia/Manila');
        $currentHourMin = $now->format('h:i A'); // e.g. "08:00 AM"
        $current24 = $now->format('H:i'); // e.g. "08:00"
        $currentDayShort = $now->format('D'); // "Mon"
        $currentDayFull = $now->format('l'); // "Monday"

        // Prevent multiple executions in the same minute / recent 2 minutes
        if ($this->last_run && $this->last_run->timezone('Asia/Manila')->diffInMinutes($now) < 2) {
            return false;
        }

        $ruleDays = $this->days ?? [];
        $ruleTimes = $this->times ?? [];

        // Check Day Match
        $dayMatches = true;
        if (!empty($ruleDays)) {
            $dayMatches = false;
            foreach ($ruleDays as $d) {
                $dLower = strtolower(trim($d));
                if (
                    $dLower === 'everyday' ||
                    $dLower === 'all' ||
                    $dLower === 'daily' ||
                    ($dLower === 'weekdays' && $now->isWeekday()) ||
                    ($dLower === 'weekends' && $now->isWeekend()) ||
                    str_starts_with(strtolower($currentDayFull), substr($dLower, 0, 3)) ||
                    $dLower === strtolower($currentDayShort)
                ) {
                    $dayMatches = true;
                    break;
                }
            }
        }

        if (!$dayMatches) {
            return false;
        }

        // Check Time Match
        if (empty($ruleTimes)) {
            return false;
        }

        foreach ($ruleTimes as $timeStr) {
            $t = trim($timeStr);
            try {
                $parsed = \Carbon\Carbon::parse($t, 'Asia/Manila');
                if ($parsed->format('H:i') === $current24) {
                    return true;
                }
            } catch (\Exception) {
                if ($t === $currentHourMin || $t === $current24) {
                    return true;
                }
            }
        }

        return false;
    }
}
