<?php

namespace App\Console\Commands;

use App\Models\SocialAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportPagesCommand extends Command
{
    protected $signature = 'pages:import 
                            {file : Path to the JSON file to import} 
                            {--replace : Delete all existing connected accounts and replace with imported ones}';

    protected $aliases = ['accounts:import', 'social:import'];

    protected $description = 'Import social media accounts / pages from a JSON backup file into the database';

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

        $pages = $decoded['pages'] ?? ($decoded['accounts'] ?? ($decoded['socialAccounts'] ?? (isset($decoded[0]) ? $decoded : [$decoded])));

        if (! is_array($pages) || empty($pages)) {
            $this->error('❌ No connected accounts or pages found in the JSON file.');

            return 1;
        }

        if ($this->option('replace')) {
            if ($this->confirm('⚠️  This will delete all existing connected accounts/pages. Do you want to proceed?', true)) {
                SocialAccount::truncate();
                $this->warn('🗑️ Cleared existing connected accounts.');
            } else {
                $this->info('Import cancelled.');

                return 0;
            }
        }

        $imported = 0;

        foreach ($pages as $pageData) {
            if (empty($pageData['name'])) {
                continue;
            }

            $id = $pageData['id'] ?? (string) Str::uuid();

            $attributes = [
                'platform' => $pageData['platform'] ?? 'facebook',
                'name' => $pageData['name'],
                'account_id' => $pageData['account_id'] ?? ($pageData['accountId'] ?? ($pageData['page_id'] ?? null)),
                'access_token' => $pageData['access_token'] ?? ($pageData['accessToken'] ?? ($pageData['token'] ?? null)),
                'extra_config' => is_array($pageData['extra_config'] ?? null)
                    ? $pageData['extra_config']
                    : (is_array($pageData['extraConfig'] ?? null) ? $pageData['extraConfig'] : []),
                'is_enabled' => isset($pageData['is_enabled']) ? (bool) $pageData['is_enabled'] : (isset($pageData['isEnabled']) ? (bool) $pageData['isEnabled'] : true),
                'status' => $pageData['status'] ?? 'active',
            ];

            SocialAccount::updateOrCreate(
                ['id' => $id],
                $attributes
            );

            $this->line("  ✔ Imported: <fg=cyan>{$attributes['name']}</> (Page ID: {$attributes['account_id']})");
            $imported++;
        }

        $this->info("\n✅ Successfully imported {$imported} page / social account(s)!");

        return 0;
    }
}
