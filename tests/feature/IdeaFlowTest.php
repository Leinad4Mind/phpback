<?php

namespace Tests\Feature;

use App\Models\CategoryModel;
use App\Models\CommentModel;
use App\Models\IdeaModel;
use App\Models\SettingModel;
use App\Models\TagModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class IdeaFlowTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $refresh   = true;
    protected $namespace = 'App';

    private int $userId;
    private int $catId;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();
        model(SettingModel::class)->insert(['name' => 'title', 'value' => 'PHPBack']);
        $this->userId = (int) model(UserModel::class)->createUser('U', 'u@example.com', 'secret123', 10, 0);
        $this->catId  = model(CategoryModel::class)->addCategory('General', 'd');
    }

    /**
     * @return array<string, mixed>
     */
    private function session(): array
    {
        return ['isLoggedIn' => true, 'userid' => $this->userId, 'username' => 'U', 'email' => 'u@example.com', 'isadmin' => 0, 'role_id' => 2];
    }

    public function testNewIdeaSavesTags(): void
    {
        $this->withSession($this->session())->post('action/newidea', [
            'title'       => 'Add keyboard shortcuts',
            'category'    => $this->catId,
            'description' => 'Please add keyboard shortcuts for power users',
            'tags'        => 'ui, accessibility',
        ]);

        $idea = model(IdeaModel::class)->getLastIdea();
        $this->assertSame('Add keyboard shortcuts', $idea->title);
        $this->assertSame(2, $this->db->table('idea_tags')->where('idea_id', $idea->id)->countAllResults());
        $this->assertSame(2, model(TagModel::class)->countAllResults());
    }

    public function testNewIdeaRejectsShortTitle(): void
    {
        $result = $this->withSession($this->session())->post('action/newidea', [
            'title'       => 'short',
            'category'    => $this->catId,
            'description' => 'a long enough description here for sure',
        ]);

        $result->assertRedirectTo(site_url('home/postidea/errortitle'));
        $this->assertNull(model(IdeaModel::class)->getLastIdea());
    }

    public function testCommentIncrementsCount(): void
    {
        $ideaId = model(IdeaModel::class)->addIdea('Commentable idea here', 'a long enough description', $this->userId, $this->catId);
        model(IdeaModel::class)->update($ideaId, ['status' => 'considered']);

        $this->withSession($this->session())->post('action/comment', [
            'idea_id' => $ideaId,
            'content' => 'Nice idea!',
        ]);

        $this->assertSame(1, model(CommentModel::class)->where('ideaid', $ideaId)->countAllResults());
        $this->assertSame(1, (int) model(IdeaModel::class)->find($ideaId)->comments);
    }

    public function testGuestCannotPostIdea(): void
    {
        // No session -> the 'login' route filter redirects to the login page.
        $this->post('action/newidea', [
            'title'       => 'Guest idea attempt',
            'category'    => $this->catId,
            'description' => 'a long enough description here for sure',
        ])->assertRedirectTo(site_url('home/login'));

        $this->assertNull(model(IdeaModel::class)->getLastIdea());
    }
}
