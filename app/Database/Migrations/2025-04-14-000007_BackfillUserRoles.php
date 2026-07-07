<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Backfills users.role_id from the legacy `isadmin` level for rows that don't
 * have a role yet (i.e. users upgraded from 1.3.x). Idempotent: only touches
 * rows where role_id IS NULL. On a fresh install there are no users yet, so it
 * is a no-op (new users get role_id at creation).
 *
 * Authorisation still runs off `isadmin`; role_id is a supplementary mapping
 * (1 = Admin, 2 = User) kept consistent for the roles/permissions feature.
 */
class BackfillUserRoles extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('users') || ! $this->db->fieldExists('role_id', 'users')) {
            return;
        }

        // Privileged users -> Admin role (1)
        $this->db->table('users')
            ->where('role_id', null)
            ->where('isadmin >', 0)
            ->update(['role_id' => 1]);

        // Everyone else still unassigned -> User role (2)
        $this->db->table('users')
            ->where('role_id', null)
            ->update(['role_id' => 2]);
    }

    public function down()
    {
        // Non-destructive: leave role_id values in place.
    }
}
