<?php

namespace App\Console\Commands;

use App\Models\SocialAccount;
use Illuminate\Console\Command;

class ExportPagesCommand extends Command
{
    protected $signature = 'pages:export 
                            {--file= : Destination JSON file path (e.g. storage/app/pages.json)}';

    protected $aliases = ['accounts:export', 'social:export'];

    protected $description = 'Export all connected social media accounts / pages to a JSON backup file or stdout';

    public function handle(): int
    {
        $accounts = SocialAccount::all();

        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'count' => $accounts->count(),
            'pages' => $accounts->map(function ($acc) {
                return [
                    'id' => $acc->id,
                    'platform' => $acc->platform,
                    'name' => $acc->name,
                    'account_id' => $acc->account_id,
                    'access_token' => $acc->access_token,
                    'extra_config' => $acc->extra_config,
                    'is_enabled' => (bool) $acc->is_enabled,
                    'status' => $acc->status ?? 'active',
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
            $this->info("✅ Exported {$accounts->count()} page / account(s) to: {$filePath}");
        } else {
            $this->line($json);
        }

        return 0;
    }
}
