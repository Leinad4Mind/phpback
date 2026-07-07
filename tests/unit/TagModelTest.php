<?php

namespace Tests\Unit;

use App\Models\CategoryModel;
use App\Models\IdeaModel;
use App\Models\TagModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class TagModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh   = true;
    protected $namespace = 'App';

    public function testGetOrCreateTrimsLowercasesAndDedupes(): void
    {
        $tags = model(TagModel::class);
        $ids  = $tags->getOrCreate([' UI ', 'ui', 'Performance', '']);

        $this->assertCount(2, $ids, 'blank skipped, "UI"/"ui" deduped');
        $this->assertSame(2, $tags->countAllResults());
    }

    public function testAttachToIdeaAvoidsDuplicatesAndForIdeaJoins(): void
    {
        $userId = (int) model(UserModel::class)->createUser('A', 'a@example.com', 'secret123', 10, 0);
        $catId  = model(CategoryModel::class)->addCategory('C', 'd');
        $ideaId = model(IdeaModel::class)->addIdea('An idea with tags here', 'a long enough description', $userId, $catId);

        $tags   = model(TagModel::class);
        $tagIds = $tags->getOrCreate(['ui', 'api']);
        $tags->attachToIdea($ideaId, $tagIds);
        $tags->attachToIdea($ideaId, $tagIds); // repeat -> no duplicates

        $this->assertSame(2, $this->db->table('idea_tags')->where('idea_id', $ideaId)->countAllResults());

        // forIdea() uses a prefixed JOIN between tags and idea_tags.
        $forIdea = $tags->forIdea($ideaId);
        $this->assertCount(2, $forIdea);
        $names = array_map(static fn ($t) => $t->name, $forIdea);
        sort($names);
        $this->assertSame(['api', 'ui'], $names);
    }
}
