<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    public function run()
    {
        if ($this->db->table('roles')->countAllResults() === 0) {
            $this->db->table('roles')->insertBatch([
                ['id' => 1, 'name' => 'Admin'],
                ['id' => 2, 'name' => 'User'],
            ]);
        }

        if ($this->db->table('permissions')->countAllResults() === 0) {
            $this->db->table('permissions')->insertBatch([
                ['id' => 1, 'name' => 'view_dashboard'],
                ['id' => 2, 'name' => 'edit_dashboard'],
            ]);
        }

        if ($this->db->table('role_permissions')->countAllResults() === 0) {
            $this->db->table('role_permissions')->insertBatch([
                ['role_id' => 1, 'permission_id' => 1], // Admin: view
                ['role_id' => 1, 'permission_id' => 2], // Admin: edit
                ['role_id' => 2, 'permission_id' => 1], // User: view
            ]);
        }
    }
}
