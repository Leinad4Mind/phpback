<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Secure "remember me" tokens (selector + hashed validator pattern).
 *
 * Replaces the legacy `_sessions` remember-me table, whose tokens were built
 * with the non-cryptographic rand(). Here the validator is stored only as a
 * SHA-256 hash and verified in constant time.
 */
class CreateRememberTokensTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('remember_tokens')) {
            return;
        }

        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'selector'        => ['type' => 'VARCHAR', 'constraint' => 32],
            'hashed_validator'=> ['type' => 'VARCHAR', 'constraint' => 64],
            'expires'         => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('selector');
        $this->forge->addKey('user_id');
        $this->forge->createTable('remember_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('remember_tokens', true);
    }
}
