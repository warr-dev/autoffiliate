<?php

namespace App\Console\Commands;

use App\Models\WorkflowRule;
use Illuminate\Console\Command;

class ExportWorkflowRulesCommand extends Command
{
    protected $signature = 'workflows:export 
                            {--file= : Destination JSON file path (e.g. storage/app/workflows.json)} 
                            {--pretty : Output formatted pretty-printed JSON}';

    protected $description = 'Export all workflow rules to a JSON backup file or stdout';

    public function handle(): int
    {
        $workflows = WorkflowRule::latest()->get();

        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'count' => $workflows->count(),
            'workflows' => $workflows->map(function ($w) {
                return [
                    'id' => $w->id,
                    'name' => $w->name,
                    'category' => $w->category,
                    'frequency' => $w->frequency,
                    'times' => $w->times,
                    'days' => $w->days,
                    'target_page' => $w->target_page,
                    'workflow_actions' => $w->workflow_actions,
                    'action_contexts' => $w->action_contexts,
                    'general_context' => $w->general_context,
                    'weather_context' => $w->weather_context,
                    'occasion_context' => $w->occasion_context,
                    'tones' => $w->tones,
                    'personas' => $w->personas,
                    'custom_persona' => $w->custom_persona,
                    'manual_prompt' => $w->manual_prompt,
                    'status' => $w->status,
                ];
            }),
        ];

        $json = json_encode(
            $exportData,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        $filePath = $this->option('file');

        if ($filePath) {
            $dir = dirname($filePath);
            if (! is_dir($dir) && $dir !== '.') {
                mkdir($dir, 0755, true);
            }

            file_put_contents($filePath, $json);
            $this->info("✅ Exported {$workflows->count()} workflow rule(s) to: {$filePath}");
        } else {
            $this->line($json);
        }

        return 0;
    }
}
