<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdeaIdToLogs extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('idea_id', 'logs')) {
            $this->forge->addColumn('logs', [
                'idea_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'toid',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('idea_id', 'logs')) {
            $this->forge->dropColumn('logs', 'idea_id');
        }
    }
}
