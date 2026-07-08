<?php

namespace Tests\Feature;

use App\Models\AttachmentModel;
use App\Models\CategoryModel;
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
final class HomeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $refresh   = true;
    protected $namespace = 'App';

    private int $catId;
    private int $ideaId;

    protected function setUp(): void
    {
        parent::setUp();
        model(SettingModel::class)->insert(['name' => 'title', 'value' => 'PHPBack']);
        $userId       = (int) model(UserModel::class)->createUser('Author', 'author@example.com', 'secret123', 10, 0);
        $this->catId  = model(CategoryModel::class)->addCategory('Feature Requests', 'desc');
        model(CategoryModel::class)->update($this->catId, ['ideas' => 1]);
        $this->ideaId = model(IdeaModel::class)->addIdea('Dark mode toggle please', 'We would love a dark theme here', $userId, $this->catId);
        model(IdeaModel::class)->update($this->ideaId, ['status' => 'considered', 'votes' => 5]);
    }

    public function testHomepageLoadsAndListsIdeas(): void
    {
        $result = $this->get('/');
        $result->assertOK();
        $result->assertSee('Dark mode toggle please');
    }

    public function testHomepageStatusFilter(): void
    {
        $result = $this->get('/?status=considered');
        $result->assertOK();
        $result->assertSee('Dark mode toggle please');
    }

    public function testCategoryPageLoads(): void
    {
        $result = $this->get('category/' . $this->catId);
        $result->assertOK();
        $result->assertSee('Feature Requests');
        $result->assertSee('Dark mode toggle please');
    }

    public function testIdeaPageShowsTagsAndAttachments(): void
    {
        $tags = model(TagModel::class);
        $tags->attachToIdea($this->ideaId, $tags->getOrCreate(['ui']));
        model(AttachmentModel::class)->insert([
            'idea_id' => $this->ideaId, 'file_name' => 'spec.pdf', 'file_path' => 'x.pdf',
            'file_type' => 'application/pdf', 'file_size' => 1, 'created_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->get('idea/' . $this->ideaId);
        $result->assertOK();
        $result->assertSee('ui');
        $result->assertSee('spec.pdf');
        $result->assertSee('download/attachment/');
    }

    public function testSearchReturnsMatches(): void
    {
        $result = $this->post('search', ['query' => 'dark']);
        $result->assertOK();
        $result->assertSee('Dark mode toggle please');
    }

    public function testLegacyHomeUrlsRedirectPermanently(): void
    {
        $result = $this->get('home/idea/' . $this->ideaId);
        $result->assertStatus(301);
        $this->assertSame(site_url('idea/' . $this->ideaId), $result->getRedirectUrl());

        $result = $this->get('home/login');
        $result->assertStatus(301);
        $this->assertSame(site_url('login'), $result->getRedirectUrl());

        $result = $this->get('home');
        $result->assertStatus(301);
    }
}
