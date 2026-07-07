<?php

namespace Tests\Feature;

use App\Models\SettingModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class InstallTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $refresh   = true;
    protected $namespace = 'App';

    public function testInstallerPageLoads(): void
    {
        $this->get('install')->assertOK();
    }

    public function testFreshInstallCreatesAdminAndSettings(): void
    {
        $result = $this->post('install/run', [
            'action'         => 'install',
            'site_title'     => 'My Feedback',
            'admin_name'     => 'Boss',
            'admin_email'    => 'boss@example.com',
            'admin_password' => 'secret123',
        ]);

        $result->assertRedirect();

        $admins = model(UserModel::class)->getAdmins();
        $this->assertNotEmpty($admins);
        $this->assertSame('boss@example.com', $admins[0]->email);
        $this->assertSame(3, (int) $admins[0]->isadmin);

        // Seeded settings + applied title
        $this->assertSame('My Feedback', model(SettingModel::class)->get('title'));
        $this->assertNotFalse(model(SettingModel::class)->get('maxvotes'));
    }
}
