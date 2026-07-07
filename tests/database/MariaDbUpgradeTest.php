<?php

namespace Tests\Database;

use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use mysqli;

/**
 * Proves that an existing PHPBack 1.3.x database (MySQL/MariaDB, InnoDB, signed
 * INT primary keys, `voice_` table prefix) upgrades cleanly to the CI4 schema
 * with no foreign-key errno 150 and with all legacy data preserved.
 *
 * Skipped automatically when no MariaDB/MySQL is reachable. Connection details
 * come from env vars (defaults target a local DBngin instance):
 *   MARIADB_HOST (127.0.0.1) MARIADB_PORT (3316) MARIADB_USER (root) MARIADB_PASS ('')
 *
 * @internal
 */
final class MariaDbUpgradeTest extends CIUnitTestCase
{
    private ?mysqli $conn = null;
    private string $dbName = 'phpback_upgrade_test';
    private string $prefix = 'voice_';
    private string $legacyHash;

    /** @var array<string, mixed> */
    private array $cfg = [];

    protected function setUp(): void
    {
        parent::setUp();

        $host = getenv('MARIADB_HOST') ?: '127.0.0.1';
        $port = (int) (getenv('MARIADB_PORT') ?: 3316);
        $user = getenv('MARIADB_USER') ?: 'root';
        $pass = getenv('MARIADB_PASS') !== false ? (string) getenv('MARIADB_PASS') : '';

        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @mysqli_connect($host, $user, $pass, '', $port);
        if (! $conn instanceof mysqli) {
            $this->markTestSkipped("MariaDB/MySQL not reachable at {$host}:{$port}");
        }
        $this->conn = $conn;

        mysqli_query($conn, "DROP DATABASE IF EXISTS `{$this->dbName}`");
        mysqli_query($conn, "CREATE DATABASE `{$this->dbName}` CHARACTER SET utf8mb4");
        mysqli_select_db($conn, $this->dbName);

        // A legacy $2a$08$ bcrypt hash exactly like the old Hashing library made.
        $this->legacyHash = crypt('legacy123', '$2a$08$' . substr(strtr(base64_encode(random_bytes(16)), '+', '.'), 0, 22) . '$');

        foreach ($this->legacySchema() as $sql) {
            if (! mysqli_query($conn, $sql)) {
                $this->fail('legacy schema load failed: ' . mysqli_error($conn));
            }
        }

        // Connection config pointing at the legacy DB, with the voice_ prefix,
        // so the migration runner and assertions target it.
        $this->cfg = [
            'DSN'      => '',
            'hostname' => $host,
            'username' => $user,
            'password' => $pass,
            'database' => $this->dbName,
            'DBDriver' => 'MySQLi',
            'DBPrefix' => $this->prefix,
            'port'     => $port,
            'charset'  => 'utf8mb4',
            'DBCollat' => 'utf8mb4_general_ci',
            'pConnect' => false,
            'DBDebug'  => true,
        ];
    }

    protected function tearDown(): void
    {
        if ($this->conn instanceof mysqli) {
            mysqli_query($this->conn, "DROP DATABASE IF EXISTS `{$this->dbName}`");
            mysqli_close($this->conn);
        }
        parent::tearDown();
    }

    public function testLegacyDatabaseUpgradesWithoutDataLoss(): void
    {
        // Run the migrations against an explicit connection to the legacy DB
        // (voice_ prefix). Must not throw errno 150 on the signed legacy ids.
        $migrationDb = Database::connect($this->cfg, false);
        $runner      = new MigrationRunner(config('Migrations'), $migrationDb);
        $runner->latest();

        // Assert on a fresh connection so no stale field/table caches remain.
        $db = Database::connect($this->cfg, false);

        // New tables created (prefixed).
        foreach (['tags', 'idea_tags', 'attachments', 'roles', 'permissions', 'role_permissions', 'remember_tokens'] as $table) {
            $this->assertTrue($db->tableExists($table), "expected {$this->prefix}{$table} to exist");
        }
        // New columns added to legacy tables.
        $this->assertTrue($db->fieldExists('role_id', 'users'));
        $this->assertTrue($db->fieldExists('created_at', 'ideas'));

        // Legacy data untouched.
        $this->assertSame(1, $db->table('users')->where('email', 'legacy@example.com')->countAllResults());
        $idea = $db->table('ideas')->get()->getRow();
        $this->assertSame('Legacy idea 1.3.1', $idea->title);
        $this->assertSame(42, (int) $idea->votes);

        // role_id backfilled from isadmin (admin -> role 1).
        $admin = $db->table('users')->where('email', 'legacy@example.com')->get()->getRow();
        $this->assertSame(1, (int) $admin->role_id);

        // Legacy bcrypt password still verifies (users keep their credentials).
        $this->assertTrue(password_verify('legacy123', $admin->pass));
    }

    /**
     * The 1.3.x schema (InnoDB, signed int(11) PKs, tinytext/text) with the
     * voice_ prefix, plus a little seed data.
     *
     * @return list<string>
     */
    private function legacySchema(): array
    {
        $p    = $this->prefix;
        $hash = mysqli_real_escape_string($this->conn, $this->legacyHash);

        return [
            "CREATE TABLE `{$p}users` (`id` int(11) NOT NULL AUTO_INCREMENT, `name` tinytext NOT NULL, `email` tinytext NOT NULL, `pass` tinytext NOT NULL, `votes` int(11) NOT NULL, `isadmin` tinyint(1) NOT NULL, `banned` int(11) NOT NULL DEFAULT 0, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
            "CREATE TABLE `{$p}ideas` (`id` int(11) NOT NULL AUTO_INCREMENT, `title` tinytext NOT NULL, `content` text NOT NULL, `authorid` int(11) NOT NULL, `date` tinytext NOT NULL, `votes` int(11) NOT NULL, `comments` int(11) NOT NULL, `status` tinytext NOT NULL, `categoryid` int(11) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
            "CREATE TABLE `{$p}categories` (`id` int(11) NOT NULL AUTO_INCREMENT, `name` tinytext NOT NULL, `description` text NOT NULL, `ideas` int(11) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
            "CREATE TABLE `{$p}comments` (`id` int(11) NOT NULL AUTO_INCREMENT, `content` text NOT NULL, `ideaid` int(11) NOT NULL, `userid` int(11) NOT NULL, `date` tinytext NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
            "CREATE TABLE `{$p}votes` (`id` int(11) NOT NULL AUTO_INCREMENT, `ideaid` int(11) NOT NULL, `userid` int(11) NOT NULL, `number` int(11) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
            "CREATE TABLE `{$p}settings` (`id` int(11) NOT NULL AUTO_INCREMENT, `name` tinytext NOT NULL, `value` tinytext NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
            "CREATE TABLE `{$p}flags` (`id` int(11) NOT NULL AUTO_INCREMENT, `toflagid` int(11) NOT NULL, `userid` int(11) NOT NULL, `date` tinytext NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
            "CREATE TABLE `{$p}logs` (`id` int(11) NOT NULL AUTO_INCREMENT, `content` tinytext NOT NULL, `date` tinytext NOT NULL, `type` tinytext NOT NULL, `toid` int(11) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
            "CREATE TABLE `{$p}_sessions` (`id` int(11) NOT NULL AUTO_INCREMENT, `userid` int(11) NOT NULL, `token` tinytext NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
            "INSERT INTO `{$p}users` (`name`,`email`,`pass`,`votes`,`isadmin`,`banned`) VALUES ('Legacy Admin','legacy@example.com','{$hash}',10,3,0)",
            "INSERT INTO `{$p}categories` (`name`,`description`,`ideas`) VALUES ('General','legacy',1)",
            "INSERT INTO `{$p}ideas` (`title`,`content`,`authorid`,`date`,`votes`,`comments`,`status`,`categoryid`) VALUES ('Legacy idea 1.3.1','old data',1,'01/01/20 10:00',42,0,'considered',1)",
            "INSERT INTO `{$p}settings` (`name`,`value`) VALUES ('title','My Legacy Feedback')",
        ];
    }
}
