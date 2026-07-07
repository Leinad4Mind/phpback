<?php

namespace Tests\Feature;

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class AuthTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $refresh   = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean(); // reset the login/register throttler between tests
    }

    public function testRegisterCreatesUser(): void
    {
        $result = $this->post('action/register', [
            'name'      => 'New User',
            'email'     => 'new@example.com',
            'password'  => 'secret123',
            'password2' => 'secret123',
        ]);

        $result->assertRedirectTo(site_url('home/login/register'));
        $this->assertNotNull(model(UserModel::class)->findByEmail('new@example.com'));
    }

    public function testRegisterRejectsDuplicateEmail(): void
    {
        model(UserModel::class)->createUser('Existing', 'dup@example.com', 'secret123', 10, 0);

        $result = $this->post('action/register', [
            'name'      => 'Another',
            'email'     => 'dup@example.com',
            'password'  => 'secret123',
            'password2' => 'secret123',
        ]);

        $result->assertRedirectTo(site_url('home/register/exists'));
    }

    public function testRegisterRejectsShortPassword(): void
    {
        $result = $this->post('action/register', [
            'name'      => 'Short',
            'email'     => 'short@example.com',
            'password'  => '123',
            'password2' => '123',
        ]);

        $result->assertRedirectTo(site_url('home/register/pass'));
        $this->assertNull(model(UserModel::class)->findByEmail('short@example.com'));
    }

    public function testLoginValidStartsSession(): void
    {
        model(UserModel::class)->createUser('Log In', 'login@example.com', 'secret123', 10, 0);

        $result = $this->post('action/login', [
            'email'    => 'login@example.com',
            'password' => 'secret123',
        ]);

        $result->assertRedirectTo(site_url('home'));
        $this->assertTrue((bool) session()->get('isLoggedIn'));
        $this->assertSame('login@example.com', session()->get('email'));
    }

    public function testLoginInvalidIsRejected(): void
    {
        model(UserModel::class)->createUser('Log In', 'login@example.com', 'secret123', 10, 0);

        $result = $this->post('action/login', [
            'email'    => 'login@example.com',
            'password' => 'wrong-password',
        ]);

        $result->assertRedirectTo(site_url('home/login/errorlogin'));
        $this->assertNull(session()->get('isLoggedIn'));
    }

    public function testLogoutDestroysSession(): void
    {
        $result = $this->withSession([
            'isLoggedIn' => true, 'userid' => 1, 'username' => 'x', 'isadmin' => 0,
        ])->post('action/logout');

        $result->assertRedirectTo(site_url('home'));
    }
}
