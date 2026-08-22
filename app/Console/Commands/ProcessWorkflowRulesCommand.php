<?php

namespace App\Console\Commands;

use App\Jobs\ExecuteWorkflowRuleJob;
use App\Models\WorkflowRule;
use Illuminate\Console\Command;

class ProcessWorkflowRulesCommand extends Command
{
    protected $signature = 'workflows:run {--force : Force execute all active rules regardless of schedule}';
    protected $description = 'Evaluate scheduled automated workflow rules and execute due rules in the background';

    public function handle(): int
    {
        $now = now()->timezone('Asia/Manila');
        $force = (bool) $this->option('force');

        $this->info("Checking workflow rules at {$now->format('Y-m-d H:i:s T')} (Asia/Manila)...");

        $activeRules = WorkflowRule::where('status', 'active')->get();

        if ($activeRules->isEmpty()) {
            $this->comment("No active workflow rules found.");
            return 0;
        }

        $executedCount = 0;

        foreach ($activeRules as $rule) {
            if ($force || $rule->isDue($now)) {
                $this->info("Executing workflow rule: {$rule->name} (ID: {$rule->id})");
                ExecuteWorkflowRuleJob::dispatchSync($rule);
                $executedCount++;
            }
        }

        if ($executedCount > 0) {
            $this->info("Successfully processed {$executedCount} due workflow rule(s).");
        } else {
            $this->line("No workflow rules due for execution at this minute.");
        }

        return 0;
    }
}
