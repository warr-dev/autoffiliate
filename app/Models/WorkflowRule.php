<?php

namespace App\Models;

use Carbon\Carbon;
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
     * Includes a 30-minute flexible grace window to accommodate Hostinger / shared cron timing drift.
     */
    public function isDue(mixed $now = null, int $graceMinutes = 30): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = $now ? $now->copy()->timezone('Asia/Manila') : now()->timezone('Asia/Manila');
        $currentDayShort = $now->format('D'); // "Mon"
        $currentDayFull = $now->format('l');  // "Monday"

        $ruleDays = $this->days ?? [];
        $ruleTimes = $this->times ?? [];

        if (empty($ruleTimes)) {
            return false;
        }

        // 1. Check Day Match
        $dayMatches = true;
        if (! empty($ruleDays)) {
            $dayMatches = false;
            foreach ($ruleDays as $d) {
                $dLower = strtolower(trim((string) $d));
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

        if (! $dayMatches) {
            return false;
        }

        // 2. Check Time Slots with Grace Window & Duplicate Run Prevention
        $lastRunManila = $this->last_run ? $this->last_run->copy()->timezone('Asia/Manila') : null;

        foreach ($ruleTimes as $timeStr) {
            $t = trim((string) $timeStr);
            try {
                // Parse target slot time today in Asia/Manila (e.g. 2026-08-23 08:00:00)
                $slotTime = Carbon::parse($t, 'Asia/Manila')->setDate($now->year, $now->month, $now->day);
            } catch (\Exception) {
                continue;
            }

            // Difference in minutes between now and scheduled slot ($now - $slotTime)
            $diffMinutes = $slotTime->diffInMinutes($now, false);

            // Trigger if current time is within [0 min, graceMinutes] after the scheduled slot
            if ($diffMinutes >= 0 && $diffMinutes <= $graceMinutes) {
                // Check if this rule has ALREADY executed for this target slot today
                if ($lastRunManila) {
                    $sameDay = $lastRunManila->isSameDay($now);
                    $ranAfterSlot = $lastRunManila->greaterThanOrEqualTo($slotTime->copy()->subMinutes(2));

                    if ($sameDay && $ranAfterSlot) {
                        // Already executed for this slot today
                        continue;
                    }
                }

                // Rule is due for execution!
                return true;
            }
        }

        return false;
    }
}
