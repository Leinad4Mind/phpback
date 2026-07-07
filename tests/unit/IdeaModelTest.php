<?php

namespace Tests\Unit;

use App\Models\AttachmentModel;
use App\Models\CategoryModel;
use App\Models\CommentModel;
use App\Models\FlagModel;
use App\Models\IdeaModel;
use App\Models\TagModel;
use App\Models\UserModel;
use App\Models\VoteModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class IdeaModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh   = true;
    protected $namespace = 'App';

    private int $userId;
    private int $catId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userId = (int) model(UserModel::class)->createUser('Author', 'author@example.com', 'secret123', 10, 0);
        $this->catId  = model(CategoryModel::class)->addCategory('General', 'desc');
    }

    private function makeIdea(string $title, string $status = 'considered', int $votes = 0): int
    {
        $ideas = model(IdeaModel::class);
        $id    = $ideas->addIdea($title, 'a long enough description here', $this->userId, $this->catId);
        $ideas->update($id, ['status' => $status, 'votes' => $votes]);

        return $id;
    }

    public function testGetFilteredByStatusAndTag(): void
    {
        $ideas = model(IdeaModel::class);
        $a     = $this->makeIdea('Dark mode toggle', 'considered');
        $b     = $this->makeIdea('Faster search now', 'planned');

        $tags   = model(TagModel::class);
        $tagIds = $tags->getOrCreate(['ui']);
        $tags->attachToIdea($a, $tagIds);

        $byStatus = $ideas->getFiltered(['status' => 'considered']);
        $this->assertCount(1, $byStatus);
        $this->assertSame('Dark mode toggle', $byStatus[0]->title);

        // Prefixed JOIN on idea_tags must resolve correctly.
        $byTag = $ideas->getFiltered(['tag' => $tagIds[0]]);
        $this->assertCount(1, $byTag);
        $this->assertSame($a, (int) $byTag[0]->id);
    }

    public function testSearchMatchesTitle(): void
    {
        $this->makeIdea('Dark mode toggle');
        $this->makeIdea('Bright theme');

        $results = model(IdeaModel::class)->search('dark');
        $this->assertCount(1, $results);
        $this->assertSame('Dark mode toggle', $results[0]->title);
    }

    public function testGetByCategoryStatusFilterExcludesNew(): void
    {
        $ideas = model(IdeaModel::class);
        $this->makeIdea('Approved idea', 'considered');
        $ideas->addIdea('Pending idea title', 'a long enough description here', $this->userId, $this->catId); // status new

        $all = $ideas->getByCategory($this->catId);
        $this->assertCount(1, $all, 'new ideas are excluded from public category listing');

        $planned = $ideas->getByCategory($this->catId, 'votes', 'desc', 1, 'planned');
        $this->assertCount(0, $planned);
    }

    public function testChangeStatusToCompletedRestoresVotes(): void
    {
        $ideaId = $this->makeIdea('Idea to complete', 'considered');
        $voter  = (int) model(UserModel::class)->createUser('Voter', 'voter@example.com', 'secret123', 10, 0);

        model(VoteModel::class)->castVote($ideaId, $voter, 3);
        $this->assertSame(7, (int) model(UserModel::class)->find($voter)->votes);

        model(IdeaModel::class)->changeStatus($ideaId, 'completed');
        $this->assertSame(10, (int) model(UserModel::class)->find($voter)->votes, 'votes returned on completion');
        $this->assertSame(0, model(VoteModel::class)->where('ideaid', $ideaId)->countAllResults());
    }

    public function testDeleteIdeaRemovesDependentRows(): void
    {
        $ideaId = $this->makeIdea('Idea to delete', 'considered');
        $voter  = (int) model(UserModel::class)->createUser('V2', 'v2@example.com', 'secret123', 10, 0);

        model(CommentModel::class)->add($ideaId, 'nice', $voter);
        model(VoteModel::class)->castVote($ideaId, $voter, 1);
        $tags = model(TagModel::class);
        $tags->attachToIdea($ideaId, $tags->getOrCreate(['ui', 'ux']));
        model(AttachmentModel::class)->insert([
            'idea_id' => $ideaId, 'file_name' => 'f.pdf', 'file_path' => 'x.pdf',
            'file_type' => 'application/pdf', 'file_size' => 1, 'created_at' => date('Y-m-d H:i:s'),
        ]);

        model(IdeaModel::class)->deleteIdea($ideaId);

        $this->assertNull(model(IdeaModel::class)->find($ideaId));
        $this->assertSame(0, model(CommentModel::class)->where('ideaid', $ideaId)->countAllResults());
        $this->assertSame(0, model(VoteModel::class)->where('ideaid', $ideaId)->countAllResults());
        $this->assertSame(0, $this->db->table('idea_tags')->where('idea_id', $ideaId)->countAllResults());
        $this->assertSame(0, model(AttachmentModel::class)->where('idea_id', $ideaId)->countAllResults());
    }

    public function testApproveBumpsCategoryAndStatus(): void
    {
        $ideas  = model(IdeaModel::class);
        $ideaId = $ideas->addIdea('Brand new idea here', 'a long enough description here', $this->userId, $this->catId);

        $ideas->approve($ideaId);

        $this->assertSame('considered', $ideas->find($ideaId)->status);
        $this->assertSame(1, (int) model(CategoryModel::class)->find($this->catId)->ideas);
    }
}
