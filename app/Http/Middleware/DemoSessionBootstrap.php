<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DemoSessionBootstrap
{
    public function handle(Request $request, Closure $next)
    {
        // Only apply demo mode if APP_DEMO is true
        if (!config('app.demo', false)) {
            return $next($request);
        }

        // Start session if not already started
        if (!$request->hasSession()) {
            return $next($request);
        }

        $sessionId = $request->session()->getId();
        $dbPath = $this->generateDatabasePath($sessionId);

        // Check if demo database exists for this session
        if (!File::exists($dbPath)) {
            // Only create database on register route
            if (!$request->is('api/auth/register')) {
                return response()->json([
                    'message' => 'Demo session not initialized. Please register first.',
                    'error'   => 'DEMO_SESSION_NOT_INITIALIZED',
                ], 401);
            }

            // Create and initialize demo SQLite database
            $this->initializeDemoDatabase($dbPath);
        } else {
            // Check if session expired (file older than 5 minutes)
            $lastModified = File::lastModified($dbPath);
            if ($lastModified < now()->subMinutes(5)->timestamp) {
                // Session expired, delete database
                File::delete($dbPath);
                return response()->json([
                    'message' => 'Demo session expired',
                    'error'   => 'DEMO_SESSION_EXPIRED',
                ], 401);
            }

            // Refresh session expiry (sliding window)
            touch($dbPath);
        }

        // Switch to demo database for this request
        $this->switchToDemoDatabase($dbPath);

        return $next($request);
    }

    private function generateDatabasePath(string $sessionId): string
    {
        $demoDbDir = storage_path('demo_databases');

        // Ensure demo databases directory exists
        if (!File::exists($demoDbDir)) {
            File::makeDirectory($demoDbDir, 0755, true);
        }

        return $demoDbDir . '/session_' . $sessionId . '.sqlite';
    }

    private function initializeDemoDatabase(string $dbPath): void
    {
        // Create empty SQLite file
        File::put($dbPath, '');

        // Configure temporary connection for this demo database
        config(['database.connections.demo_temp' => [
            'driver'                  => 'sqlite',
            'database'                => $dbPath,
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ]]);

        // Run migrations on demo database
        $this->runMigrationsOnDemoDb();

        // Seed demo data
        $this->seedDemoData();
    }

    private function runMigrationsOnDemoDb(): void
    {
        DB::connection('demo_temp')->statement('PRAGMA foreign_keys = ON');

        // Run essential migrations for demo
        $migrations = [
            'CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                email_verified_at TIMESTAMP NULL,
                password VARCHAR(255) NOT NULL,
                remember_token VARCHAR(100) NULL,
                team_id INTEGER DEFAULT 1,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )',
            'CREATE TABLE personal_access_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tokenable_type VARCHAR(255) NOT NULL,
                tokenable_id BIGINT NOT NULL,
                name VARCHAR(255) NOT NULL,
                token VARCHAR(64) UNIQUE NOT NULL,
                abilities TEXT NULL,
                last_used_at TIMESTAMP NULL,
                expires_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )',
            'CREATE TABLE companies (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NULL,
                phone VARCHAR(255) NULL,
                address TEXT NULL,
                website VARCHAR(255) NULL,
                industry VARCHAR(255) NULL,
                size VARCHAR(50) NULL,
                notes TEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )',
            'CREATE TABLE contacts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                first_name VARCHAR(255) NOT NULL,
                last_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NULL,
                phone VARCHAR(255) NULL,
                position VARCHAR(255) NULL,
                company_id INTEGER NULL,
                notes TEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE SET NULL
            )',
            'CREATE TABLE deals (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                description TEXT NULL,
                value DECIMAL(15,2) NULL,
                stage VARCHAR(100) NOT NULL DEFAULT "lead",
                probability INTEGER DEFAULT 0,
                expected_close_date DATE NULL,
                company_id INTEGER NULL,
                contact_id INTEGER NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE SET NULL,
                FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE SET NULL
            )',
            'CREATE TABLE activities (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                type VARCHAR(100) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                description TEXT NULL,
                due_date TIMESTAMP NULL,
                completed BOOLEAN DEFAULT 0,
                company_id INTEGER NULL,
                contact_id INTEGER NULL,
                deal_id INTEGER NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE SET NULL,
                FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE SET NULL,
                FOREIGN KEY (deal_id) REFERENCES deals (id) ON DELETE SET NULL
            )',
            'CREATE TABLE notes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                content TEXT NOT NULL,
                company_id INTEGER NULL,
                contact_id INTEGER NULL,
                deal_id INTEGER NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE SET NULL,
                FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE SET NULL,
                FOREIGN KEY (deal_id) REFERENCES deals (id) ON DELETE SET NULL
            )',
        ];

        foreach ($migrations as $migration) {
            DB::connection('demo_temp')->statement($migration);
        }
    }

    private function seedDemoData(): void
    {
        // Insert sample companies
        DB::connection('demo_temp')->table('companies')->insert([
            [
                'name'       => 'Acme Corporation',
                'email'      => 'contact@acme.com',
                'phone'      => '+1 555-0123',
                'industry'   => 'Technology',
                'size'       => '50-200',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name'       => 'Global Solutions Inc',
                'email'      => 'info@globalsolutions.com',
                'phone'      => '+1 555-0456',
                'industry'   => 'Consulting',
                'size'       => '10-50',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // Insert sample contacts
        DB::connection('demo_temp')->table('contacts')->insert([
            [
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'email'      => 'john.doe@acme.com',
                'phone'      => '+1 555-0789',
                'position'   => 'CEO',
                'company_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Jane',
                'last_name'  => 'Smith',
                'email'      => 'jane.smith@globalsolutions.com',
                'phone'      => '+1 555-0987',
                'position'   => 'CTO',
                'company_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // Insert sample deals
        DB::connection('demo_temp')->table('deals')->insert([
            [
                'title'               => 'Enterprise Software License',
                'description'         => 'Annual software license renewal',
                'value'               => 25000.00,
                'stage'               => 'negotiation',
                'probability'         => 75,
                'expected_close_date' => Carbon::now()->addDays(30),
                'company_id'          => 1,
                'contact_id'          => 1,
                'created_at'          => Carbon::now(),
                'updated_at'          => Carbon::now(),
            ],
        ]);
    }

    private function switchToDemoDatabase(string $dbPath): void
    {
        config(['database.connections.demo_current' => [
            'driver'                  => 'sqlite',
            'database'                => $dbPath,
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ]]);

        // Switch default connection to demo database
        config(['database.default' => 'demo_current']);
        DB::purge('demo_current');
    }
}
