<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['name', 'email', 'pass', 'votes', 'isadmin', 'banned', 'role_id'];

    public function findUser(int $id): ?object
    {
        return $this->find($id);
    }

    public function findByEmail(string $email): ?object
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Verifies credentials in constant time and returns the user on success.
     * Transparently upgrades legacy bcrypt hashes to the current algorithm.
     */
    public function attemptLogin(string $email, string $password): ?object
    {
        $user = $this->findByEmail($email);

        if ($user === null || ! password_verify($password, $user->pass)) {
            return null;
        }

        if (password_needs_rehash($user->pass, PASSWORD_DEFAULT)) {
            $this->update($user->id, ['pass' => password_hash($password, PASSWORD_DEFAULT)]);
        }

        return $user;
    }

    /**
     * Creates a user. Returns the new id, or false if the email already exists.
     *
     * @return int|false
     */
    public function createUser(string $name, string $email, string $password, int $votes, int $isadmin = 0, ?int $roleId = null)
    {
        if ($votes < 1 || $this->findByEmail($email) !== null) {
            return false;
        }

        $this->insert([
            'name'    => $name,
            'email'   => $email,
            'pass'    => password_hash($password, PASSWORD_DEFAULT),
            'votes'   => $votes,
            'isadmin' => $isadmin,
            'banned'  => 0,
            'role_id' => $roleId ?? ($isadmin > 0 ? 1 : 2),
        ]);

        return $this->getInsertID();
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        return $this->update($id, ['pass' => password_hash($newPassword, PASSWORD_DEFAULT)]);
    }

    /**
     * @return list<object>
     */
    public function getAdmins(): array
    {
        return $this->where('isadmin >', 0)->orderBy('id', 'ASC')->findAll();
    }

    /**
     * @return list<object>
     */
    public function listActive(string $order = 'id', int $limit = 30): array
    {
        $order = in_array($order, ['id', 'votes'], true) ? $order : 'id';

        return $this->where('banned', 0)->orderBy($order, 'DESC')->findAll($limit);
    }

    /**
     * @return list<object>
     */
    public function listBanned(int $limit = 100): array
    {
        return $this->where('banned !=', 0)->orderBy('id', 'DESC')->findAll($limit);
    }

    public function setBan(int $id, int $bannedValue): bool
    {
        return $this->update($id, ['banned' => $bannedValue]);
    }

    public function setAdminLevel(int $id, int $level): bool
    {
        if (! in_array($level, [0, 1, 2, 3], true)) {
            return false;
        }

        return $this->update($id, [
            'isadmin' => $level,
            'role_id' => $level > 0 ? 1 : 2,
        ]);
    }

    public function addVotes(int $id, int $delta): bool
    {
        $user = $this->find($id);
        if ($user === null) {
            return false;
        }

        return $this->update($id, ['votes' => (int) $user->votes + $delta]);
    }
}
