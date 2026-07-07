<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Secure "remember me" tokens using the selector + validator pattern.
 *
 * The cookie value is `selector:validator`. Only a SHA-256 hash of the
 * validator is stored, and it is checked with hash_equals() (constant time).
 * Both parts come from random_bytes(), replacing the legacy rand() tokens.
 */
class RememberTokenModel extends Model
{
    protected $table         = 'remember_tokens';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id', 'selector', 'hashed_validator', 'expires'];

    /**
     * Issues a new token for a user and returns the raw cookie value.
     */
    public function issue(int $userId, int $days = 30): string
    {
        $selector  = bin2hex(random_bytes(16)); // 32 hex chars
        $validator = bin2hex(random_bytes(32)); // 64 hex chars

        $this->insert([
            'user_id'          => $userId,
            'selector'         => $selector,
            'hashed_validator' => hash('sha256', $validator),
            'expires'          => date('Y-m-d H:i:s', time() + 86400 * $days),
        ]);

        return $selector . ':' . $validator;
    }

    /**
     * Validates a cookie value and returns the user id, or null.
     */
    public function verifyCookie(string $cookie): ?int
    {
        if (! str_contains($cookie, ':')) {
            return null;
        }

        [$selector, $validator] = explode(':', $cookie, 2);
        $row                    = $this->where('selector', $selector)->first();

        if ($row === null) {
            return null;
        }

        if (strtotime($row->expires) < time()) {
            $this->where('selector', $selector)->delete();

            return null;
        }

        if (! hash_equals($row->hashed_validator, hash('sha256', $validator))) {
            return null;
        }

        return (int) $row->user_id;
    }

    public function clearCookie(string $cookie): void
    {
        if (str_contains($cookie, ':')) {
            [$selector] = explode(':', $cookie, 2);
            $this->where('selector', $selector)->delete();
        }
    }

    public function clearForUser(int $userId): void
    {
        $this->where('user_id', $userId)->delete();
    }
}
