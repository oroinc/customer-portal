<?php

namespace Oro\Bundle\OAuth2ServerBundle\Tests\Unit\Security;

use Oro\Bundle\CustomerBundle\Security\VisitorIdentifierUtil;

class VisitorIdentifierUtilTest extends \PHPUnit\Framework\TestCase
{
    public function testIsVisitorIdentifierForValidIdentifier(): void
    {
        self::assertTrue(VisitorIdentifierUtil::isVisitorIdentifier('visitor:sessionId654'));
    }

    public function testIsVisitorIdentifierForInvalidIdentifier(): void
    {
        self::assertFalse(VisitorIdentifierUtil::isVisitorIdentifier('256'));
    }

    public function testIsVisitorIdentifierForOldIdentifier(): void
    {
        self::assertTrue(VisitorIdentifierUtil::isVisitorIdentifier('visitor:256:sessionId654'));
    }

    public function testIsVisitorIdentifierForOldIdentifierOnNonDigitIdInString(): void
    {
        self::assertFalse(VisitorIdentifierUtil::isVisitorIdentifier('visitor:test:sessionId654'));
    }

    public function testEncodeIdentifier(): void
    {
        $this->assertEquals('visitor:sessionId654', VisitorIdentifierUtil::encodeIdentifier('sessionId654'));
    }

    public function testDecodeIdentifier(): void
    {
        self::assertSame('sessionId654', VisitorIdentifierUtil::decodeIdentifier('visitor:sessionId654'));
    }

    public function testDecodeOldIdentifier(): void
    {
        self::assertSame('sessionId654', VisitorIdentifierUtil::decodeIdentifier('visitor:256:sessionId654'));
    }
}
