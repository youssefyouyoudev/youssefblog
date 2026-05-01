<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class EnhanceBlogContent extends Command
{
    protected $signature = 'blog:enhance-content
        {--dry-run : Show what would change without saving}
        {--limit= : Enhance only this many posts}
        {--only= : Enhance one specific post by slug}
        {--status=keep : Save enhanced posts with this status, or keep current status}
        {--min-words=1800 : Target minimum word count for upgraded articles}';

    protected $description = 'Safely enhance existing blog posts while preserving production status by default.';

    public function handle(): int
    {
        $params = [
            '--status' => $this->option('status'),
            '--min-words' => $this->option('min-words'),
        ];

        foreach (['dry-run', 'limit', 'only'] as $option) {
            $value = $this->option($option);

            if ($value !== false && filled($value)) {
                $params['--'.$option] = $value;
            } elseif ($value === true) {
                $params['--'.$option] = true;
            }
        }

        $exitCode = Artisan::call('posts:upgrade-existing-content', $params);
        $this->output->write(Artisan::output());

        return $exitCode;
    }
}
