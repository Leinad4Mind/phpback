<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id' => 1, // user ID
                'role_id' => 1 // Admin
            ],
            [
                'id' => 2,
                'role_id' => 2 // Editor
            ]
        ];

        foreach ($data as $item) {
            $this->db->table('users')->where('id', $item['id'])->update(['role_id' => $item['role_id']]);
        }
    }
}
