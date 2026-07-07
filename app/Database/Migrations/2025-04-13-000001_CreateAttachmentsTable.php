<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttachmentsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('attachments')) {
            return;
        }

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'idea_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'file_name'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_path'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_type'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'file_size'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('idea_id');
        $this->forge->addForeignKey('idea_id', 'ideas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('attachments');
    }

    public function down()
    {
        $this->forge->dropTable('attachments', true);
    }
}
