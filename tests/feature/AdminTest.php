<?php

namespace Tests\Feature;

use App\Models\CategoryModel;
use App\Models\CommentModel;
use App\Models\IdeaModel;
use App\Models\SettingModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class AdminTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $refresh   = true;
    protected $namespace = 'App';

    private int $adminId;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();
        model(SettingModel::class)->insert(['name' => 'title', 'value' => 'PHPBack']);
        $this->adminId = (int) model(UserModel::class)->createUser('Admin', 'admin@example.com', 'secret123', 10, 3);
    }

    /**
     * @return array<string, mixed>
     */
    private function adminSession(): array
    {
        return ['isLoggedIn' => true, 'userid' => $this->adminId, 'username' => 'Admin', 'email' => 'admin@example.com', 'isadmin' => 3, 'role_id' => 1];
    }

    public function testAdminAreaRequiresLogin(): void
    {
        $this->get('admin/dashboard')->assertRedirect();
    }

    public function testNonAdminCannotAccessAdminArea(): void
    {
        $result = $this->withSession(['isLoggedIn' => true, 'userid' => 99, 'isadmin' => 0])->get('admin/system');
        $result->assertRedirect(); // AdminFilter redirects level-0 users away
    }

    public function testAdminCanReachDashboardPages(): void
    {
        foreach (['admin/dashboard', 'admin/ideas', 'admin/users', 'admin/system'] as $route) {
            $result = $this->withSession($this->adminSession())->get($route);
            $result->assertOK();
        }
    }

    public function testAdminLoginRejectsNonAdmin(): void
    {
        model(UserModel::class)->createUser('Plain', 'plain@example.com', 'secret123', 10, 0);
        $result = $this->post('adminaction/login', ['email' => 'plain@example.com', 'password' => 'secret123']);
        $result->assertRedirectTo(site_url('admin/index/noadmin'));
    }

    public function testApproveIdeaChangesStatus(): void
    {
        $catId  = model(CategoryModel::class)->addCategory('C', 'd');
        $ideaId = model(IdeaModel::class)->addIdea('Idea to approve here', 'a long enough description', $this->adminId, $catId);

        $this->withSession($this->adminSession())->post('adminaction/approveidea', ['id' => $ideaId]);

        $this->assertSame('considered', model(IdeaModel::class)->find($ideaId)->status);
    }

    public function testChangeStatusAndDeleteComment(): void
    {
        $catId  = model(CategoryModel::class)->addCategory('C', 'd');
        $ideaId = model(IdeaModel::class)->addIdea('Idea for status change', 'a long enough description', $this->adminId, $catId);
        model(IdeaModel::class)->update($ideaId, ['status' => 'considered']);
        $cid = model(CommentModel::class)->add($ideaId, 'a comment', $this->adminId);

        $this->withSession($this->adminSession())->post('adminaction/ideastatus', ['id' => $ideaId, 'status' => 'planned']);
        $this->assertSame('planned', model(IdeaModel::class)->find($ideaId)->status);

        $this->withSession($this->adminSession())->post('adminaction/deletecomment', ['id' => $cid]);
        $this->assertNull(model(CommentModel::class)->find($cid));
    }

    public function testBanAndUnban(): void
    {
        $victim = (int) model(UserModel::class)->createUser('Victim', 'victim@example.com', 'secret123', 10, 0);

        $this->withSession($this->adminSession())->post('adminaction/banuser', ['id' => $victim, 'days' => 0]);
        $this->assertSame(-1, (int) model(UserModel::class)->find($victim)->banned);

        $this->withSession($this->adminSession())->post('adminaction/unban', ['id' => $victim]);
        $this->assertSame(0, (int) model(UserModel::class)->find($victim)->banned);
    }

    /**
     * Regression: CheckboxIsland's hidden sync input only exists in the DOM
     * when checked, so an unchecked box sends no `setting-{id}` field at all.
     * The view compensates with a static hidden fallback (value 0) rendered
     * before the island; this proves editsettings() persists whatever value
     * actually arrives (0 or 1) and that Home::index() honors it.
     */
    public function testHomepageSectionToggleTogglesVisibility(): void
    {
        $settings = model(SettingModel::class);
        $settings->insert(['name' => 'homepage_show_started', 'value' => '1']);
        $settingId = (int) $settings->getInsertID();
        // The "Recently Added" section independently aggregates every public
        // status, so it must be off too or it leaks the idea back onto the
        // page regardless of the "started" toggle under test.
        $settings->insert(['name' => 'homepage_show_recent', 'value' => '0']);

        $catId  = model(CategoryModel::class)->addCategory('C', 'd');
        $ideaId = model(IdeaModel::class)->addIdea('Idea shown when started section is on', 'a long enough description', $this->adminId, $catId);
        model(IdeaModel::class)->update($ideaId, ['status' => 'started']);

        // Unchecked: only the static hidden fallback (value 0) reaches the server.
        $this->withSession($this->adminSession())
            ->post('adminaction/editsettings', ['setting-' . $settingId => '0']);
        $this->assertSame('0', $settings->where('name', 'homepage_show_started')->first()->value);

        $hidden = $this->get('home');
        $hidden->assertOK();
        $hidden->assertDontSee('Idea shown when started section is on');

        // Checked: the island's own hidden input (value 1) reaches the server.
        $this->withSession($this->adminSession())
            ->post('adminaction/editsettings', ['setting-' . $settingId => '1']);
        $this->assertSame('1', $settings->where('name', 'homepage_show_started')->first()->value);

        $shown = $this->get('home');
        $shown->assertOK();
        $shown->assertSee('Idea shown when started section is on');
    }
}
