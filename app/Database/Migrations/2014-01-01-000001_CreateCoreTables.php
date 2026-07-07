<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the original PHPBack (1.3.x) core schema.
 *
 * Every table is guarded with tableExists() so this migration is safe to run
 * against a fresh database (creates everything) OR against an existing 1.3.1
 * database being upgraded (creates nothing, leaving legacy data untouched).
 */
class CreateCoreTables extends Migration
{
    public function up()
    {
        // users
        if (! $this->db->tableExists('users')) {
            $this->forge->addField([
                'id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'name'    => ['type' => 'VARCHAR', 'constraint' => 255],
                'email'   => ['type' => 'VARCHAR', 'constraint' => 255],
                'pass'    => ['type' => 'VARCHAR', 'constraint' => 255],
                'votes'   => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'isadmin' => ['type' => 'INT', 'constraint' => 2, 'default' => 0],
                'banned'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('email');
            $this->forge->createTable('users');
        }

        // ideas
        if (! $this->db->tableExists('ideas')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'title'      => ['type' => 'VARCHAR', 'constraint' => 255],
                'content'    => ['type' => 'TEXT'],
                'authorid'   => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'date'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'votes'      => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'comments'   => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'status'     => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'new'],
                'categoryid' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('status');
            $this->forge->addKey('categoryid');
            $this->forge->createTable('ideas');
        }

        // comments
        if (! $this->db->tableExists('comments')) {
            $this->forge->addField([
                'id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'content' => ['type' => 'TEXT'],
                'ideaid'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'userid'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'date'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('ideaid');
            $this->forge->createTable('comments');
        }

        // votes
        if (! $this->db->tableExists('votes')) {
            $this->forge->addField([
                'id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'ideaid' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'userid' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'number' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['userid', 'ideaid']);
            $this->forge->createTable('votes');
        }

        // categories
        if (! $this->db->tableExists('categories')) {
            $this->forge->addField([
                'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
                'description' => ['type' => 'TEXT', 'null' => true],
                'ideas'       => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('categories');
        }

        // settings
        if (! $this->db->tableExists('settings')) {
            $this->forge->addField([
                'id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'name'  => ['type' => 'VARCHAR', 'constraint' => 255],
                'value' => ['type' => 'TEXT', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('name');
            $this->forge->createTable('settings');
        }

        // flags
        if (! $this->db->tableExists('flags')) {
            $this->forge->addField([
                'id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'toflagid' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'userid'   => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'date'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('flags');
        }

        // logs
        if (! $this->db->tableExists('logs')) {
            $this->forge->addField([
                'id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'content' => ['type' => 'TEXT'],
                'date'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'type'    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'toid'    => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('logs');
        }
    }

    public function down()
    {
        foreach (['logs', 'flags', 'settings', 'categories', 'votes', 'comments', 'ideas', 'users'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
