<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateProductUsageStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-product-usage-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update product usage statistics for time-based metrics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $thirtyDaysAgo = now()->subDays(30);
        $sevenDaysAgo = now()->subDays(7);

        // Update last 30 days count
        \App\Models\ProductUsage::chunk(100, function ($usages) use ($thirtyDaysAgo) {
            foreach ($usages as $usage) {
                if ($usage->last_used_at < $thirtyDaysAgo) {
                    $usage->update(['last_30_days_count' => 0]);
                }
            }
        });

        // Update last 7 days count
        \App\Models\ProductUsage::chunk(100, function ($usages) use ($sevenDaysAgo) {
            foreach ($usages as $usage) {
                if ($usage->last_used_at < $sevenDaysAgo) {
                    $usage->update(['last_7_days_count' => 0]);
                }
            }
        });

        $this->info('Product usage statistics updated successfully.');
    }
}
