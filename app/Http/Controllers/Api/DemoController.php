<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Artisan;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Log;

class DemoController extends Controller
{
    public function createToken(Request $request)
    {
        if (!config('crm.demo_mode')) {
            return response()->json([
                'error' => 'Demo mode is not enabled',
            ], 503);
        }

        $tokenId = Str::uuid()->toString();
        $token = Str::random(64);
        $expiresIn = config('crm.demo_token_ttl', 30) * 60; // Convert to seconds
        $expiresAt = Carbon::now()->addSeconds($expiresIn);

        // Create demo database for this token
        $dbPath = $this->createTokenDatabase($tokenId);

        // Store token in cache
        Cache::put("demo_token:{$token}", [
            'token_id'   => $tokenId,
            'db_path'    => $dbPath,
            'created_at' => Carbon::now()->toISOString(),
            'expires_at' => $expiresAt->toISOString(),
        ], $expiresIn);

        return response()->json([
            'token'      => $token,
            'expires_in' => $expiresIn,
            'expires_at' => $expiresAt->toISOString(),
            'message'    => 'Use this token in X-Demo-Token header for API requests',
        ]);
    }

    private function createTokenDatabase(string $tokenId): string
    {
        $templatePath = config('crm.demo_template_path');
        $tokenDir = storage_path('app/demo/tokens');
        $tokenPath = "{$tokenDir}/{$tokenId}.sqlite";

        // Ensure tokens directory exists
        if (!File::exists($tokenDir)) {
            File::makeDirectory($tokenDir, 0755, true);
        }

        // Copy template database to token database
        if (File::exists($templatePath)) {
            File::copy($templatePath, $tokenPath);
        } else {
            // If no template exists, create empty database and run migrations
            touch($tokenPath);

            // Temporarily switch to demo connection for migrations
            config(['database.connections.demo_sqlite.database' => $tokenPath]);

            try {
                Artisan::call('migrate', [
                    '--database' => 'demo_sqlite',
                    '--force'    => true,
                ]);

                Artisan::call('db:seed', [
                    '--database' => 'demo_sqlite',
                    '--force'    => true,
                ]);
            } catch (Exception $e) {
                Log::error("Failed to migrate demo token database: " . $e->getMessage());
            }
        }

        // Create upload directory for this token
        $uploadPath = storage_path("app/demo/uploads/{$tokenId}");
        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        return $tokenPath;
    }
}
