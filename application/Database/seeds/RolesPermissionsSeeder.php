<?php

namespace Application\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Insert Roles (Admin, User)
        $this->db->table('roles')->insertBatch([
            ['name' => 'Admin'],
            ['name' => 'User']
        ]);

        // Insert Permissions (View and Edit Dashboard)
        $this->db->table('permissions')->insertBatch([
            ['name' => 'view_dashboard'],
            ['name' => 'edit_dashboard']
        ]);

        // Assign Permissions to Roles
        $this->db->table('role_permissions')->insertBatch([
            // Admin can view and edit dashboard
            ['role_id' => 1, 'permission_id' => 1], // Admin can view dashboard
            ['role_id' => 1, 'permission_id' => 2], // Admin can edit dashboard
            
            // User can only view dashboard
            ['role_id' => 2, 'permission_id' => 1], // User can view dashboard
        ]);
    }
}
