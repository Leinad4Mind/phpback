<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds users.google_id for "Sign in with Google". Guarded so it is safe on
 * both fresh installs and upgrades (only added when missing). The OIDC `sub`
 * claim is a string of up to 255 ASCII characters, so it is stored as a
 * VARCHAR — never as an integer.
 */
class AddGoogleIdToUsers extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('users') && ! $this->db->fieldExists('google_id', 'users')) {
            $this->forge->addColumn('users', [
                'google_id' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'email',
                ],
            ]);

            // Unique index; NULLs are exempt from uniqueness on MySQL and SQLite.
            $this->forge->addKey('google_id', false, true);
            $this->forge->processIndexes('users');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('users') && $this->db->fieldExists('google_id', 'users')) {
            $this->forge->dropColumn('users', 'google_id');
        }
    }
}
