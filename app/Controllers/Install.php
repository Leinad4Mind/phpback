<?php

namespace App\Controllers;

use App\Models\SettingModel;
use App\Models\UserModel;
use Config\Database;
use Throwable;

/**
 * Web installer / upgrader.
 *
 * Detects the current state and acts accordingly:
 *   - noconfig : the database is not reachable -> collect credentials (.env)
 *   - install  : reachable but empty          -> run migrations, seed, create admin
 *   - upgrade  : an existing (e.g. 1.3.1) DB  -> run pending migrations only
 *   - done     : installed and up to date     -> links to the site / admin
 *
 * The core-table migrations are idempotent (tableExists/fieldExists guards),
 * so upgrading a legacy 1.3.1 database only adds what is missing and never
 * touches existing data.
 */
class Install extends BaseController
{
    public function index()
    {
        $state = $this->detectState();

        return view('install/index', $state + ['message' => session('message'), 'error' => session('error')]);
    }

    public function run()
    {
        $action = (string) $this->request->getPost('action');

        if ($action === 'saveconfig') {
            $this->writeEnvDatabase();

            return redirect()->to('install')->with('message', 'DB configuration saved. Verifying connection…');
        }

        $state = $this->detectState();
        if ($state['state'] === 'noconfig') {
            return redirect()->to('install')->with('error', 'Cannot connect to the database. Check your configuration.');
        }
        if ($state['state'] === 'done') {
            return redirect()->to('install')->with('message', 'Already installed and up to date.');
        }

        // Apply all pending migrations (creates core tables on a fresh DB,
        // adds only the missing pieces when upgrading from 1.3.1).
        try {
            service('migrations')->latest();
        } catch (Throwable $e) {
            return redirect()->to('install')->with('error', 'Migration failed: ' . $e->getMessage());
        }

        // Idempotent seed of default settings + roles/permissions.
        $seeder = Database::seeder();
        $seeder->call('SettingsSeeder');
        $seeder->call('RolesPermissionsSeeder');

        // Fresh install: create the first administrator.
        if ($action === 'install') {
            $users = model(UserModel::class);
            if ($users->getAdmins() === []) {
                $name  = trim((string) $this->request->getPost('admin_name'));
                $email = trim((string) $this->request->getPost('admin_email'));
                $pass  = (string) $this->request->getPost('admin_password');

                if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($pass) < 6) {
                    return redirect()->to('install')->with('error', 'Provide a valid admin name, email and a password of at least 6 characters.');
                }

                $votes = (int) (model(SettingModel::class)->get('maxvotes') ?: 10);
                $users->createUser($name, $email, $pass, $votes, 3, 1);
            }

            $title = trim((string) $this->request->getPost('site_title'));
            if ($title !== '') {
                $settings = model(SettingModel::class);
                $row      = $settings->where('name', 'title')->first();
                if ($row !== null) {
                    $settings->updateValue((int) $row->id, $title);
                }
            }
        }

        return redirect()->to('install')->with('message', 'Success! PHPBack is installed and up to date.');
    }

    /**
     * @return array<string, mixed>
     */
    private function detectState(): array
    {
        try {
            $db = Database::connect();
            $db->initialize();
            $db->query('SELECT 1');
        } catch (Throwable $e) {
            return ['state' => 'noconfig', 'connectError' => $e->getMessage(), 'pending' => 0];
        }

        $hasUsers = $db->tableExists('users');
        $pending  = $this->pendingMigrations($db);
        $hasAdmin = $hasUsers && model(UserModel::class)->getAdmins() !== [];

        // No tables yet, or tables exist but no administrator (e.g. someone ran
        // `spark migrate` by hand): treat as a fresh install so the admin can be
        // created and any remaining migrations applied.
        if (! $hasUsers || ! $hasAdmin) {
            return ['state' => 'install', 'pending' => $pending, 'hasAdmin' => $hasAdmin];
        }

        if ($pending > 0) {
            return ['state' => 'upgrade', 'pending' => $pending, 'hasAdmin' => $hasAdmin];
        }

        return ['state' => 'done', 'pending' => 0, 'hasAdmin' => $hasAdmin];
    }

    private function pendingMigrations($db): int
    {
        try {
            $all  = count(service('migrations')->findMigrations());
            $done = $db->tableExists('migrations') ? $db->table('migrations')->countAllResults() : 0;

            return max(0, $all - $done);
        } catch (Throwable $e) {
            return 1;
        }
    }

    /**
     * Writes the database.default.* keys into the project .env file.
     */
    private function writeEnvDatabase(): void
    {
        $values = [
            'database.default.DBDriver' => (string) $this->request->getPost('db_driver') ?: 'MySQLi',
            'database.default.hostname' => (string) $this->request->getPost('db_host') ?: 'localhost',
            'database.default.port'     => (string) ($this->request->getPost('db_port') ?: '3306'),
            'database.default.database' => (string) $this->request->getPost('db_name'),
            'database.default.username' => (string) $this->request->getPost('db_user'),
            'database.default.password' => (string) $this->request->getPost('db_pass'),
            // Table prefix — existing PHPBack installs use `voice_`.
            'database.default.DBPrefix' => (string) $this->request->getPost('db_prefix'),
        ];

        $envPath = ROOTPATH . '.env';
        $lines   = is_file($envPath) ? file($envPath, FILE_IGNORE_NEW_LINES) : [];

        foreach ($values as $key => $value) {
            $line  = $key . ' = ' . $this->envQuote($value);
            $found = false;
            foreach ($lines as $i => $existing) {
                if (preg_match('/^\s*#?\s*' . preg_quote($key, '/') . '\s*=/', $existing)) {
                    $lines[$i] = $line;
                    $found     = true;
                    break;
                }
            }
            if (! $found) {
                $lines[] = $line;
            }
        }

        file_put_contents($envPath, implode("\n", $lines) . "\n", LOCK_EX);
    }

    private function envQuote(string $value): string
    {
        return $value === '' ? "''" : "'" . str_replace("'", "\\'", $value) . "'";
    }
}
