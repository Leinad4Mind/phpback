<?php

namespace Application\Database\Migrations;

use CodeIgniter\Database\Migration;
class AddRoleToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'role_id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'after' => 'id',
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'role_id');
    }
}
