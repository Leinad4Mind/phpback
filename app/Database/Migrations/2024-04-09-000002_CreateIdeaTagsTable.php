<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Pivot table linking ideas to tags.
 *
 * Replaces the original (broken) `post_tags` migration, which referenced a
 * non-existent `posts` table while the application code joined `idea_tags`.
 */
class CreateIdeaTagsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('idea_tags')) {
            return;
        }

        $this->forge->addField([
            'idea_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tag_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        ]);
        $this->forge->addKey(['idea_id', 'tag_id'], true);
        // No FK on idea_id: the legacy 1.3.x `ideas.id` is signed INT, so an
        // unsigned FK would fail on InnoDB during an upgrade (errno 150).
        // Integrity is handled in IdeaModel::deleteIdea(). `tags` is always
        // created fresh by us, so that FK is safe to keep.
        $this->forge->addForeignKey('tag_id', 'tags', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('idea_tags');
    }

    public function down()
    {
        $this->forge->dropTable('idea_tags', true);
    }
}
