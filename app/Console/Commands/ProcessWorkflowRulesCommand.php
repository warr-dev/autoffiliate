<?php

namespace App\Console\Commands;

use App\Jobs\ExecuteWorkflowRuleJob;
use App\Models\WorkflowRule;
use Illuminate\Console\Command;

class ProcessWorkflowRulesCommand extends Command
{
    protected $signature = 'workflows:run 
                            {--rule= : Target a specific workflow rule by ID or Name} 
                            {--force : Force execute all active rules regardless of schedule}';

    protected $description = 'Evaluate scheduled automated workflow rules and execute due rules in the background';

    public function handle(): int
    {
        $now = now()->timezone('Asia/Manila');
        $force = (bool) $this->option('force');
        $targetRule = $this->option('rule');

        $this->line("<fg=cyan>[$(date +'%H:%M:%S')]</> <fg=blue;options=bold>Autoffiliate Automated Workflow Engine</>");
        $this->line("Current Server Time: <fg=yellow>{$now->format('Y-m-d h:i:s A T')} (Asia/Manila)</>");

        $query = WorkflowRule::query();

        if ($targetRule) {
            $query->where(function ($q) use ($targetRule) {
                $q->where('id', $targetRule)
                    ->orWhere('name', 'like', "%{$targetRule}%");
            });
            $force = true; // Automatically force when targeting a specific rule
        } else {
            $query->where('status', 'active');
        }

        $rules = $query->get();

        if ($rules->isEmpty()) {
            if ($targetRule) {
                $this->error("❌ No workflow rule found matching '{$targetRule}'.");
            } else {
                $this->warn('ℹ️ No active workflow rules found in database (Rules must have status="active").');
            }

            return 0;
        }

        $this->line("Found <fg=green>{$rules->count()}</> workflow rule(s) to evaluate.\n");

        $executedCount = 0;

        foreach ($rules as $rule) {
            $timesStr = ! empty($rule->times) ? implode(', ', (array) $rule->times) : 'None';
            $daysStr = ! empty($rule->days) ? implode(', ', (array) $rule->days) : 'Everyday';
            $lastRunStr = $rule->last_run ? $rule->last_run->timezone('Asia/Manila')->format('M d, h:i A') : 'Never';

            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line("📋 <options=bold>Rule:</> <fg=cyan>{$rule->name}</> (ID: {$rule->id})");
            $this->line("   📅 Days: <fg=gray>{$daysStr}</> | ⏰ Times: <fg=gray>{$timesStr}</> | 🕒 Last Run: <fg=gray>{$lastRunStr}</>");

            if ($force || $rule->isDue($now)) {
                $reason = $targetRule
                    ? "Targeted by --rule='{$targetRule}'"
                    : ($force ? 'Force flag (--force) supplied' : 'Schedule matched within active window');
                $this->line("   ⚡ Status: <fg=green;options=bold>🚀 TRIGGERED & EXECUTING NOW</> ({$reason})");

                try {
                    $result = ExecuteWorkflowRuleJob::dispatchSync($rule);
                    $executedCount++;

                    $postTitle = $result['title'] ?? 'Generated Post';
                    $fbUrl = $result['facebook_post_url'] ?? null;
                    $tokens = $result['tokens_used'] ?? 0;

                    $this->line("   ✔ Post Created: <fg=yellow>{$postTitle}</>");
                    $this->line("   ✔ AI Tokens Used: <fg=magenta>{$tokens}</>");
                    if ($fbUrl) {
                        $this->line("   ✔ Facebook Post Published: <fg=blue;options=underscore>{$fbUrl}</>");
                    } else {
                        $this->line('   ✔ Saved as Draft (Check Drafts tab or Facebook token settings)');
                    }
                } catch (\Exception $e) {
                    $this->error("   ❌ Execution Failed: {$e->getMessage()}");
                }
            } else {
                $this->line("   ⏳ Status: <fg=yellow>SKIPPED (Not due at current time {$now->format('h:i A')})</>");
            }
        }

        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");

        if ($executedCount > 0) {
            $this->info("✅ Successfully triggered and processed {$executedCount} workflow rule(s)!");
        } else {
            $this->comment("ℹ️ Evaluation complete: 0 rules were due at {$now->format('h:i A')}.");
            $this->line('💡 Tip: Use <fg=yellow>php artisan workflows:run --force</> to test immediate execution anytime.');
            $this->line('💡 Tip: Use <fg=yellow>php artisan workflows:run --rule="Name or ID"</> to execute a specific rule.');
        }

        return 0;
    }
}
