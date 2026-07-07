<?php

namespace Tests\Unit;

use App\Models\CommentModel;
use App\Models\FlagModel;
use App\Models\SettingModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class SettingAndFlagModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh   = true;
    protected $namespace = 'App';

    public function testSettingGetUpdateAndCache(): void
    {
        $settings = model(SettingModel::class);
        $id       = $settings->insert(['name' => 'title', 'value' => 'Hello']);

        $this->assertSame('Hello', $settings->get('title'));
        $this->assertFalse($settings->get('missing-key'));

        $settings->updateValue($id, 'World');
        $this->assertSame('World', $settings->get('title'), 'cache is cleared on update');
    }

    public function testEmailConfigShape(): void
    {
        $settings = model(SettingModel::class);
        $settings->insert(['name' => 'smtp-host', 'value' => 'smtp.example.com']);
        $settings->insert(['name' => 'smtp-port', 'value' => '587']);

        $config = $settings->emailConfig();
        $this->assertSame('smtp', $config['protocol']);
        $this->assertSame('smtp.example.com', $config['SMTPHost']);
        $this->assertSame(587, $config['SMTPPort']);
    }

    public function testFlagOncePerUser(): void
    {
        $flags = model(FlagModel::class);
        $this->assertTrue($flags->flag(5, 1));
        $this->assertFalse($flags->flag(5, 1), 'same user cannot flag twice');
        $this->assertTrue($flags->flag(5, 2));
    }

    public function testFlaggedCommentsAggregation(): void
    {
        $comments = model(CommentModel::class);
        $cid      = $comments->add(3, 'spammy content', 9);

        $flags = model(FlagModel::class);
        $flags->flag($cid, 1);
        $flags->flag($cid, 2);

        $rows = $flags->flaggedComments();
        $this->assertCount(1, $rows);
        $this->assertSame($cid, (int) $rows[0]->id);
        $this->assertSame('spammy content', $rows[0]->content);
        $this->assertSame(9, (int) $rows[0]->userid);
        $this->assertSame(3, (int) $rows[0]->ideaid);
        $this->assertSame(2, (int) $rows[0]->votes);
    }
}
