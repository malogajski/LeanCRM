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
        // Debug log
        \Log::info('DemoSessionBootstrap middleware called for: ' . $request->path());

        // Only apply demo mode if APP_DEMO is true
        if (!config('app.demo', false)) {
            \Log::info('APP_DEMO is false, skipping demo mode');
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

        // Debug: Check if we have the right token in SQLite
        $token = $request->bearerToken();
        if ($token) {
            $hashedToken = hash('sha256', $token);
            $tokenExists = DB::table('personal_access_tokens')
                ->where('token', $hashedToken)
                ->exists();
            \Log::info("Token exists in SQLite DB: " . ($tokenExists ? 'YES' : 'NO'));
        }

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

    private function runLaravelMigrations(): void
    {
        // Run Laravel migrations on demo database
        \Artisan::call('migrate', [
            '--database' => 'demo_temp',
            '--force' => true
        ]);
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
                team_id INTEGER DEFAULT 1,
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
                team_id INTEGER DEFAULT 1,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE SET NULL
            )',
            'CREATE TABLE deals (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                description TEXT NULL,
                amount DECIMAL(15,2) NULL,
                stage VARCHAR(100) NOT NULL DEFAULT "lead",
                probability INTEGER DEFAULT 0,
                expected_close_date DATE NULL,
                company_id INTEGER NULL,
                contact_id INTEGER NULL,
                user_id INTEGER NULL,
                team_id INTEGER DEFAULT 1,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE SET NULL,
                FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE SET NULL,
                FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
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
                team_id INTEGER DEFAULT 1,
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
                team_id INTEGER DEFAULT 1,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE SET NULL,
                FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE SET NULL,
                FOREIGN KEY (deal_id) REFERENCES deals (id) ON DELETE SET NULL
            )',
            'CREATE TABLE permissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                guard_name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                UNIQUE(name, guard_name)
            )',
            'CREATE TABLE roles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                team_id INTEGER NULL,
                name VARCHAR(255) NOT NULL,
                guard_name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                UNIQUE(team_id, name, guard_name)
            )',
            'CREATE TABLE model_has_permissions (
                permission_id INTEGER NOT NULL,
                model_type VARCHAR(255) NOT NULL,
                model_id INTEGER NOT NULL,
                PRIMARY KEY (permission_id, model_id, model_type),
                FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
            )',
            'CREATE TABLE model_has_roles (
                role_id INTEGER NOT NULL,
                model_type VARCHAR(255) NOT NULL,
                model_id INTEGER NOT NULL,
                team_id INTEGER NULL,
                PRIMARY KEY (role_id, model_id, model_type),
                FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
            )',
            'CREATE TABLE role_has_permissions (
                permission_id INTEGER NOT NULL,
                role_id INTEGER NOT NULL,
                PRIMARY KEY (permission_id, role_id),
                FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE,
                FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
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
                'team_id'    => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name'       => 'Global Solutions Inc',
                'email'      => 'info@globalsolutions.com',
                'phone'      => '+1 555-0456',
                'industry'   => 'Consulting',
                'size'       => '10-50',
                'team_id'    => 1,
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
                'team_id'    => 1,
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
                'team_id'    => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // Insert sample deals
        DB::connection('demo_temp')->table('deals')->insert([
            [
                'title'               => 'Enterprise Software License',
                'description'         => 'Annual software license renewal',
                'amount'              => 25000.00,
                'stage'               => 'negotiation',
                'probability'         => 75,
                'expected_close_date' => Carbon::now()->addDays(30),
                'company_id'          => 1,
                'contact_id'          => 1,
                'user_id'             => null,
                'team_id'             => 1,
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
