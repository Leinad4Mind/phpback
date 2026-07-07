<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds a proper DATETIME `created_at` to ideas so the homepage filters can
 * sort by date reliably (the legacy `date` column is a display string).
 * Guarded for upgrade safety.
 */
class AddCreatedAtToIdeas extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('ideas') && ! $this->db->fieldExists('created_at', 'ideas')) {
            $this->forge->addColumn('ideas', [
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('ideas') && $this->db->fieldExists('created_at', 'ideas')) {
            $this->forge->dropColumn('ideas', 'created_at');
        }
    }
}
