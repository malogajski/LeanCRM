<?php

namespace App\Console\Commands\Demo;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class Purge extends Command
{
    protected $signature = 'demo:purge {--ttl=60 : TTL in minutes for demo data cleanup}';

    protected $description = 'Clean up expired demo SQLite databases and upload directories';

    public function handle()
    {
        if (!config('app.demo')) {
            return 1;
        }

        $ttl = (int)$this->option('ttl');
        $cutoffTime = Carbon::now()->subMinutes($ttl);

        $this->info("Cleaning up demo data older than {$ttl} minutes...");

        $sessionsPath = storage_path('demo_databases');
        $uploadsPath = storage_path('app/demo/uploads');

        $deletedFiles = 0;
        $deletedDirs = 0;
        $deletedTokens = 0;

        // Clean up session databases
        if (File::exists($sessionsPath)) {
            $sessionFiles = File::files($sessionsPath);
            foreach ($sessionFiles as $file) {
                if (Carbon::createFromTimestamp($file->getMTime())->lt($cutoffTime)) {
                    File::delete($file->getPathname());
                    $deletedFiles++;
                    $this->info("Deleted expired session database: {$file->getFilename()}");
                }
            }
        }

        // Clean up upload directories
        if (File::exists($uploadsPath)) {
            $uploadDirs = File::directories($uploadsPath);
            foreach ($uploadDirs as $dir) {
                $dirTime = Carbon::createFromTimestamp(File::lastModified($dir));
                if ($dirTime->lt($cutoffTime)) {
                    File::deleteDirectory($dir);
                    $deletedDirs++;
                    $this->info("Deleted expired upload directory: " . basename($dir));
                }
            }
        }

        // Clean up expired demo tokens from cache
        $cachePrefix = 'demo_token:';
        $tokenKeys = [];

        // Get all demo token keys (this is a simplified approach - in production you might want a more efficient method)
        try {
            // For file/database cache, we'll clean up during token validation
            // For Redis, you could use SCAN with pattern matching
            $this->info("Token cleanup handled during validation");
        } catch (\Exception $e) {
            $this->warn("Could not clean cache tokens: " . $e->getMessage());
        }

        $this->info("Cleanup completed:");
        $this->info("- Deleted {$deletedFiles} expired session databases");
        $this->info("- Deleted {$deletedDirs} expired upload directories");

        return 0;
    }
}
