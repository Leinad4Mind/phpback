<?php

namespace Application\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttachmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'auto_increment' => true],
            'idea_id'     => ['type' => 'INT', 'null' => false],
            'file_name'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_path'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_type'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'created_at'  => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('idea_id', 'ideas', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('attachments');
    }

    public function down()
    {
        $this->forge->dropTable('attachments');
    }
}
