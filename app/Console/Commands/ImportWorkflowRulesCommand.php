<?php

namespace App\Console\Commands;

use App\Models\WorkflowRule;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportWorkflowRulesCommand extends Command
{
    protected $signature = 'workflows:import 
                            {file : Path to the JSON file to import} 
                            {--replace : Delete all existing workflow rules and replace with imported ones}';

    protected $description = 'Import workflow rules from a JSON backup file into the database';

    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (! file_exists($filePath)) {
            $this->error("❌ File not found: {$filePath}");

            return 1;
        }

        $content = file_get_contents($filePath);
        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            $this->error('❌ Invalid JSON format in input file.');

            return 1;
        }

        $rules = $decoded['workflows'] ?? (isset($decoded[0]) ? $decoded : [$decoded]);

        if (! is_array($rules) || empty($rules)) {
            $this->error('❌ No workflow rules found in the JSON file.');

            return 1;
        }

        if ($this->option('replace')) {
            if ($this->confirm('⚠️  This will delete all existing workflow rules. Do you want to proceed?', true)) {
                WorkflowRule::truncate();
                $this->warn('🗑️ Cleared existing workflow rules.');
            } else {
                $this->info('Import cancelled.');

                return 0;
            }
        }

        $imported = 0;

        foreach ($rules as $ruleData) {
            if (empty($ruleData['name'])) {
                continue;
            }

            $id = $ruleData['id'] ?? ('sch_'.time().'_'.Str::random(4));

            $attributes = [
                'name' => $ruleData['name'],
                'category' => $ruleData['category'] ?? 'Connection & Community',
                'frequency' => $ruleData['frequency'] ?? 'daily',
                'times' => is_array($ruleData['times'] ?? null) ? $ruleData['times'] : ['08:00 AM'],
                'days' => is_array($ruleData['days'] ?? null) ? $ruleData['days'] : [],
                'target_page' => $ruleData['target_page'] ?? $ruleData['targetPage'] ?? 'Tech Sulit Deals',
                'workflow_actions' => is_array($ruleData['workflow_actions'] ?? null)
                    ? $ruleData['workflow_actions']
                    : (is_array($ruleData['workflowActions'] ?? null) ? $ruleData['workflowActions'] : []),
                'action_contexts' => is_array($ruleData['action_contexts'] ?? null)
                    ? $ruleData['action_contexts']
                    : (is_array($ruleData['actionContexts'] ?? null) ? $ruleData['actionContexts'] : []),
                'general_context' => $ruleData['general_context'] ?? $ruleData['generalContext'] ?? '',
                'weather_context' => $ruleData['weather_context'] ?? $ruleData['weatherContext'] ?? '',
                'occasion_context' => $ruleData['occasion_context'] ?? $ruleData['occasionContext'] ?? '',
                'tones' => is_array($ruleData['tones'] ?? null) ? $ruleData['tones'] : [],
                'personas' => is_array($ruleData['personas'] ?? null) ? $ruleData['personas'] : [],
                'custom_persona' => $ruleData['custom_persona'] ?? $ruleData['customPersona'] ?? '',
                'manual_prompt' => $ruleData['manual_prompt'] ?? $ruleData['manualPrompt'] ?? '',
                'status' => ($ruleData['status'] ?? 'active') === 'disabled' ? 'disabled' : 'active',
            ];

            WorkflowRule::updateOrCreate(
                ['id' => $id],
                $attributes
            );

            $this->line("  ✔ Imported: <fg=cyan>{$attributes['name']}</> (ID: {$id})");
            $imported++;
        }

        $this->info("\n✅ Successfully imported {$imported} workflow rule(s)!");

        return 0;
    }
}
