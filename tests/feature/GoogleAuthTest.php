<?php

namespace Tests\Feature;

use App\Models\SettingModel;
use App\Models\UserModel;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\App;
use Tests\Support\QueuedMockCURLRequest;

/**
 * @internal
 */
final class GoogleAuthTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $refresh   = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        // The shared throttler holds the cache instance it was created with, so
        // recreate it against this test's fresh mock cache before cleaning.
        Services::resetSingle('throttler');
        cache()->clean();
    }

    protected function tearDown(): void
    {
        Services::resetSingle('curlrequest');
        parent::tearDown();
    }

    private function enableGoogle(): void
    {
        $settings = model(SettingModel::class);
        foreach (['google_client_id' => 'test-client-id', 'google_client_secret' => 'test-secret'] as $name => $value) {
            $row = $settings->where('name', $name)->first();
            $settings->updateValue((int) $row->id, $value);
        }
    }

    /**
     * @param list<string> $bodies canned JSON responses, replayed in order
     */
    private function mockGoogleHttp(array $bodies): void
    {
        $mock = new QueuedMockCURLRequest(config(App::class), new URI(), new Response(config(App::class)));
        $mock->setOutputs($bodies);
        Services::injectMock('curlrequest', $mock);
    }

    public function testLoginPageHidesGoogleButtonWithoutCredentials(): void
    {
        $result = $this->get('home/login');

        $result->assertOK();
        $this->assertStringNotContainsString('auth/google', (string) $result->response()->getBody());
    }

    public function testLoginAndRegisterPagesShowGoogleButtonWhenConfigured(): void
    {
        $this->enableGoogle();

        foreach (['home/login', 'home/register'] as $page) {
            $result = $this->get($page);
            $result->assertOK();
            $this->assertStringContainsString('auth/google', (string) $result->response()->getBody());
        }
    }

    public function testGoogleRedirectsToConsentScreen(): void
    {
        $this->enableGoogle();

        $result = $this->get('auth/google');

        $result->assertRedirect();
        $location = (string) $result->getRedirectUrl();
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/v2/auth?', $location);
        $this->assertStringContainsString('client_id=test-client-id', $location);
        $this->assertStringContainsString('state=', $location);
        $this->assertStringContainsString('code_challenge=', $location);
        $this->assertStringContainsString('code_challenge_method=S256', $location);
        $this->assertNotEmpty(session()->get('oauth2_state'));
        $this->assertNotEmpty(session()->get('oauth2_verifier'));
    }

    public function testGoogleRedirectsToLoginWhenNotConfigured(): void
    {
        $result = $this->get('auth/google');

        $result->assertRedirectTo(site_url('home/login'));
    }

    public function testCallbackRejectsMissingState(): void
    {
        $this->enableGoogle();

        $result = $this->get('auth/google/callback', ['state' => 'anything', 'code' => 'x']);

        $result->assertRedirectTo(site_url('home/login/googlefail'));
    }

    public function testCallbackRejectsMismatchedState(): void
    {
        $this->enableGoogle();

        $result = $this->withSession(['oauth2_state' => 'expected', 'oauth2_verifier' => 'v'])
            ->get('auth/google/callback', ['state' => 'wrong', 'code' => 'x']);

        $result->assertRedirectTo(site_url('home/login/googlefail'));
        $this->assertNull(session()->get('oauth2_state'), 'state must be single-use');
    }

    public function testCallbackHandlesProviderError(): void
    {
        $this->enableGoogle();

        $result = $this->withSession(['oauth2_state' => 's', 'oauth2_verifier' => 'v'])
            ->get('auth/google/callback', ['error' => 'access_denied']);

        $result->assertRedirectTo(site_url('home/login/googlefail'));
    }

    public function testCallbackRedirectsHomeWhenLoggedIn(): void
    {
        $result = $this->withSession([
            'isLoggedIn' => true, 'userid' => 1, 'username' => 'x', 'isadmin' => 0,
        ])->get('auth/google/callback');

        $result->assertRedirectTo(site_url('home'));
    }

    public function testCallbackLogsInUserLinkedByGoogleId(): void
    {
        $this->enableGoogle();
        $users = model(UserModel::class);
        $id    = $users->createUser('Linked', 'linked@example.com', 'secret123', 10, 0);
        $users->linkGoogle($id, 'g-sub-1');

        $this->mockGoogleHttp([
            '{"access_token":"tok123","token_type":"Bearer"}',
            '{"sub":"g-sub-1","email":"linked@example.com","email_verified":true,"name":"Linked"}',
        ]);

        $result = $this->withSession(['oauth2_state' => 's', 'oauth2_verifier' => 'v'])
            ->get('auth/google/callback', ['state' => 's', 'code' => 'abc']);

        $result->assertRedirectTo(site_url('home'));
        $this->assertTrue((bool) session()->get('isLoggedIn'));
        $this->assertSame('linked@example.com', session()->get('email'));
    }

    public function testCallbackAutoLinksExistingUserByEmail(): void
    {
        $this->enableGoogle();
        $users = model(UserModel::class);
        $id    = $users->createUser('Existing', 'existing@example.com', 'secret123', 10, 0);

        $this->mockGoogleHttp([
            '{"access_token":"tok123","token_type":"Bearer"}',
            '{"sub":"g-sub-2","email":"Existing@Example.com","email_verified":true,"name":"Existing"}',
        ]);

        $result = $this->withSession(['oauth2_state' => 's', 'oauth2_verifier' => 'v'])
            ->get('auth/google/callback', ['state' => 's', 'code' => 'abc']);

        $result->assertRedirectTo(site_url('home'));
        $this->assertTrue((bool) session()->get('isLoggedIn'));
        $this->assertSame('g-sub-2', $users->find($id)->google_id, 'google_id must be persisted on the matched account');
    }

    public function testCallbackAutoRegistersNewUser(): void
    {
        $this->enableGoogle();

        $this->mockGoogleHttp([
            '{"access_token":"tok123","token_type":"Bearer"}',
            '{"sub":"g-sub-3","email":"new.google@example.com","email_verified":true,"name":"G User"}',
        ]);

        $result = $this->withSession(['oauth2_state' => 's', 'oauth2_verifier' => 'v'])
            ->get('auth/google/callback', ['state' => 's', 'code' => 'abc']);

        $result->assertRedirectTo(site_url('home'));
        $this->assertTrue((bool) session()->get('isLoggedIn'));

        $user = model(UserModel::class)->findByEmail('new.google@example.com');
        $this->assertNotNull($user);
        $this->assertSame('g-sub-3', $user->google_id);
        $this->assertSame('G User', $user->name);
        $this->assertSame(2, (int) $user->role_id);
        $this->assertSame(0, (int) $user->isadmin);
    }

    public function testCallbackRejectsUnverifiedEmail(): void
    {
        $this->enableGoogle();

        $this->mockGoogleHttp([
            '{"access_token":"tok123","token_type":"Bearer"}',
            '{"sub":"g-sub-4","email":"unverified@example.com","email_verified":false}',
        ]);

        $result = $this->withSession(['oauth2_state' => 's', 'oauth2_verifier' => 'v'])
            ->get('auth/google/callback', ['state' => 's', 'code' => 'abc']);

        $result->assertRedirectTo(site_url('home/login/googlefail'));
        $this->assertNull(session()->get('isLoggedIn'));
        $this->assertNull(model(UserModel::class)->findByEmail('unverified@example.com'));
    }

    public function testCallbackRefusesBannedUser(): void
    {
        $this->enableGoogle();
        $users = model(UserModel::class);
        $id    = $users->createUser('Banned', 'banned@example.com', 'secret123', 10, 0);
        $users->linkGoogle($id, 'g-sub-5');
        $users->setBan($id, -1);

        $this->mockGoogleHttp([
            '{"access_token":"tok123","token_type":"Bearer"}',
            '{"sub":"g-sub-5","email":"banned@example.com","email_verified":true}',
        ]);

        $result = $this->withSession(['oauth2_state' => 's', 'oauth2_verifier' => 'v'])
            ->get('auth/google/callback', ['state' => 's', 'code' => 'abc']);

        $result->assertRedirectTo(site_url('home/login/banned/-1'));
        $this->assertNull(session()->get('isLoggedIn'));
    }

    public function testInfiniteBanNoticeRouteResolves(): void
    {
        // Regression: the (:num) placeholder used to 404 on the -1 segment.
        $result = $this->get('home/login/banned/-1');

        $result->assertOK();
    }
}
