<?php

namespace Tests\Feature;

use App\Models\CategoryModel;
use App\Models\IdeaModel;
use App\Models\UserModel;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class SecurityTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $refresh   = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();
        model('App\Models\SettingModel')->insert(['name' => 'title', 'value' => 'PHPBack']);
    }

    public function testStoredIdeaTitleIsEscapedOnOutput(): void
    {
        $userId = (int) model(UserModel::class)->createUser('A', 'a@example.com', 'secret123', 10, 0);
        $catId  = model(CategoryModel::class)->addCategory('General', 'd');

        $this->withSession($this->sessionFor($userId))->post('action/newidea', [
            'title'       => '<script>alert(1)</script> dark mode toggle',
            'category'    => $catId,
            'description' => 'A description long enough to pass validation checks',
        ]);

        $idea = model(IdeaModel::class)->getLastIdea();
        $this->assertNotNull($idea);

        $html = $this->get('idea/' . $idea->id)->getBody();
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function testCommentContentIsEscapedOnOutput(): void
    {
        $userId = (int) model(UserModel::class)->createUser('A', 'a@example.com', 'secret123', 10, 0);
        $catId  = model(CategoryModel::class)->addCategory('General', 'd');
        $ideaId = model(IdeaModel::class)->addIdea('An idea to comment on', 'desc long enough here', $userId, $catId);
        model(IdeaModel::class)->update($ideaId, ['status' => 'considered']);

        $this->withSession($this->sessionFor($userId))->post('action/comment', [
            'idea_id' => $ideaId,
            'content' => 'Some text <img src=x onerror=alert(2)>',
        ]);

        $html = $this->get('idea/' . $ideaId)->getBody();
        $this->assertStringContainsString('Some text', $html);
        $this->assertStringNotContainsString('<img src=x onerror=alert(2)>', $html);
        $this->assertStringNotContainsString('&lt;img', $html);
    }

    public function testCsrfRejectsPostWithoutToken(): void
    {
        // The global CSRF filter is disabled during the test suite, so exercise
        // the Security service directly: a POST without a token must be rejected.
        $request = Services::incomingrequest(null, false);
        $request->setMethod('POST');
        $request->setGlobal('request', []); // no csrf token present

        $this->expectException(SecurityException::class);
        Services::security()->verify($request);
    }

    /**
     * @return array<string, mixed>
     */
    private function sessionFor(int $userId): array
    {
        return ['isLoggedIn' => true, 'userid' => $userId, 'username' => 'A', 'email' => 'a@example.com', 'isadmin' => 0, 'role_id' => 2];
    }
}
