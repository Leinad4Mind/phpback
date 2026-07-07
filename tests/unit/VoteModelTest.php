<?php

namespace Tests\Unit;

use App\Models\CategoryModel;
use App\Models\IdeaModel;
use App\Models\UserModel;
use App\Models\VoteModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class VoteModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh   = true;
    protected $namespace = 'App';

    private int $userId;
    private int $ideaId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userId = (int) model(UserModel::class)->createUser('U', 'u@example.com', 'secret123', 10, 0);
        $cat          = model(CategoryModel::class)->addCategory('C', 'd');
        $this->ideaId = model(IdeaModel::class)->addIdea('Votable idea here', 'a long enough description', $this->userId, $cat);
        model(IdeaModel::class)->update($this->ideaId, ['status' => 'considered']);
    }

    public function testCastVoteMovesCredits(): void
    {
        $this->assertTrue(model(VoteModel::class)->castVote($this->ideaId, $this->userId, 3));
        $this->assertSame(7, (int) model(UserModel::class)->find($this->userId)->votes);
        $this->assertSame(3, (int) model(IdeaModel::class)->find($this->ideaId)->votes);
    }

    public function testCastVoteUpdatesExisting(): void
    {
        $votes = model(VoteModel::class);
        $votes->castVote($this->ideaId, $this->userId, 1);
        $votes->castVote($this->ideaId, $this->userId, 3); // delta +2

        $this->assertSame(7, (int) model(UserModel::class)->find($this->userId)->votes);
        $this->assertSame(3, (int) model(IdeaModel::class)->find($this->ideaId)->votes);
        $this->assertSame(1, $votes->where('ideaid', $this->ideaId)->where('userid', $this->userId)->countAllResults());
    }

    public function testCastVoteRejectsOutOfRangeAndInsufficientBalance(): void
    {
        $votes = model(VoteModel::class);
        $this->assertFalse($votes->castVote($this->ideaId, $this->userId, 4));
        $this->assertFalse($votes->castVote($this->ideaId, $this->userId, 0));

        $poor = (int) model(UserModel::class)->createUser('Poor', 'poor@example.com', 'secret123', 1, 0);
        $this->assertFalse($votes->castVote($this->ideaId, $poor, 3), 'not enough vote balance');
    }

    public function testCastVoteRejectedOnCompletedIdea(): void
    {
        model(IdeaModel::class)->update($this->ideaId, ['status' => 'completed']);
        $this->assertFalse(model(VoteModel::class)->castVote($this->ideaId, $this->userId, 1));
    }
}
