<?php

namespace Tests\Unit;

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class UserModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh   = true;
    protected $namespace = 'App';

    public function testCreateUserStoresHashedPassword(): void
    {
        $users = model(UserModel::class);
        $id    = $users->createUser('Alice', 'alice@example.com', 'secret123', 10, 0);

        $this->assertIsInt($id);
        $user = $users->find($id);
        $this->assertNotSame('secret123', $user->pass);
        $this->assertTrue(password_verify('secret123', $user->pass));
        $this->assertSame(2, (int) $user->role_id); // non-admin -> User role
    }

    public function testDuplicateEmailIsRejected(): void
    {
        $users = model(UserModel::class);
        $users->createUser('Alice', 'a@example.com', 'secret123', 10, 0);

        $this->assertFalse($users->createUser('Alice 2', 'a@example.com', 'secret123', 10, 0));
    }

    public function testAttemptLoginValidAndInvalid(): void
    {
        $users = model(UserModel::class);
        $users->createUser('Bob', 'bob@example.com', 'secret123', 10, 0);

        $this->assertNotNull($users->attemptLogin('bob@example.com', 'secret123'));
        $this->assertNull($users->attemptLogin('bob@example.com', 'wrong-pass'));
        $this->assertNull($users->attemptLogin('nobody@example.com', 'secret123'));
    }

    public function testLegacyBcryptHashVerifiesAndRehashes(): void
    {
        // Reproduce a legacy $2a$08$ hash from the old Hashing library.
        $salt   = '$2a$08$' . substr(strtr(base64_encode(random_bytes(16)), '+', '.'), 0, 22) . '$';
        $legacy = crypt('legacy123', $salt);
        $this->assertStringStartsWith('$2a$', $legacy);

        $users = model(UserModel::class);
        $users->insert([
            'name' => 'Old', 'email' => 'old@example.com', 'pass' => $legacy,
            'votes' => 10, 'isadmin' => 0, 'banned' => 0,
        ]);

        $user = $users->attemptLogin('old@example.com', 'legacy123');
        $this->assertNotNull($user, 'Legacy bcrypt password should verify');

        $fresh = $users->findByEmail('old@example.com');
        $this->assertStringStartsWith('$2y$', $fresh->pass, 'Legacy hash should be upgraded on login');
    }

    public function testSetAdminLevelRejectsOutOfRange(): void
    {
        $users = model(UserModel::class);
        $id    = $users->createUser('Carol', 'carol@example.com', 'secret123', 10, 0);

        $this->assertFalse($users->setAdminLevel($id, 9));
        $this->assertTrue($users->setAdminLevel($id, 3));
        $this->assertSame(1, (int) $users->find($id)->role_id); // admin -> Admin role
    }
}
