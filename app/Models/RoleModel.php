<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table         = 'roles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['name'];

    /**
     * Permission names granted to a role.
     *
     * @return list<string>
     */
    public function permissionNames(int $roleId): array
    {
        $rows = $this->db->table('permissions')
            ->select('permissions.name')
            ->join('role_permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $roleId)
            ->get()->getResultArray();

        return array_column($rows, 'name');
    }

    public function hasPermission(int $roleId, string $permission): bool
    {
        return in_array($permission, $this->permissionNames($roleId), true);
    }
}
