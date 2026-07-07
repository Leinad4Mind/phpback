<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds users.role_id. Guarded so it is safe on both fresh installs and
 * upgrades from 1.3.1 (only added when missing).
 */
class AddRoleToUsersTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('users') && ! $this->db->fieldExists('role_id', 'users')) {
            $this->forge->addColumn('users', [
                'role_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'id',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('users') && $this->db->fieldExists('role_id', 'users')) {
            $this->forge->dropColumn('users', 'role_id');
        }
    }
}
