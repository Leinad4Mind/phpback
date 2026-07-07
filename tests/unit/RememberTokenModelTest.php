<?php

namespace Tests\Unit;

use App\Models\RememberTokenModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class RememberTokenModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh   = true;
    protected $namespace = 'App';

    public function testIssueThenVerifyReturnsUserId(): void
    {
        $tokens = model(RememberTokenModel::class);
        $cookie = $tokens->issue(42, 30);

        $this->assertStringContainsString(':', $cookie);
        $this->assertSame(42, $tokens->verifyCookie($cookie));

        // Only a hash of the validator is stored, never the raw value.
        [$selector, $validator] = explode(':', $cookie, 2);
        $row                    = $tokens->where('selector', $selector)->first();
        $this->assertNotSame($validator, $row->hashed_validator);
        $this->assertSame(hash('sha256', $validator), $row->hashed_validator);
    }

    public function testTamperedValidatorIsRejected(): void
    {
        $tokens = model(RememberTokenModel::class);
        $cookie = $tokens->issue(7, 30);
        [$selector] = explode(':', $cookie, 2);

        $this->assertNull($tokens->verifyCookie($selector . ':deadbeef'));
    }

    public function testExpiredTokenIsRejectedAndDeleted(): void
    {
        $tokens = model(RememberTokenModel::class);
        $cookie = $tokens->issue(9, 30);
        [$selector] = explode(':', $cookie, 2);

        // Force expiry into the past.
        $tokens->where('selector', $selector)->set('expires', date('Y-m-d H:i:s', time() - 3600))->update();

        $this->assertNull($tokens->verifyCookie($cookie));
        $this->assertSame(0, $tokens->where('selector', $selector)->countAllResults());
    }

    public function testMalformedCookieReturnsNull(): void
    {
        $this->assertNull(model(RememberTokenModel::class)->verifyCookie('no-colon-here'));
    }
}
