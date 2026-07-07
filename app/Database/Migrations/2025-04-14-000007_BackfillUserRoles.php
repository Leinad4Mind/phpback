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
        // Clear any stale table/field caches left by earlier migrations in this
        // same run (e.g. the users field list read before role_id was added).
        $this->db->resetDataCache();

        if (! $this->db->tableExists('users') || ! $this->db->fieldExists('role_id', 'users')) {
            return;
        }

        // Raw UPDATEs: "unassigned" covers both NULL and 0, regardless of how
        // the column default landed across drivers. (Builder where() with a
        // null value would append "IS NULL" to a raw condition, so use SQL.)
        $users = $this->db->prefixTable('users');
        $this->db->query("UPDATE {$users} SET role_id = 1 WHERE (role_id IS NULL OR role_id = 0) AND isadmin > 0");
        $this->db->query("UPDATE {$users} SET role_id = 2 WHERE (role_id IS NULL OR role_id = 0)");
    }

    public function down()
    {
        // Non-destructive: leave role_id values in place.
    }
}
