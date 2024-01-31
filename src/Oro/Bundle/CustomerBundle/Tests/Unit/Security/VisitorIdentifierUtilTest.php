<?php

namespace Oro\Bundle\OAuth2ServerBundle\Tests\Unit\Security;

use Oro\Bundle\CustomerBundle\Security\VisitorIdentifierUtil;

class VisitorIdentifierUtilTest extends \PHPUnit\Framework\TestCase
{
    public function testIsVisitorIdentifier()
    {
        self::assertTrue(VisitorIdentifierUtil::isVisitorIdentifier('visitor:256:sessionId654'));
    }

    public function testIsVisitorIdentifierOnSimpleId()
    {
        self::assertFalse(VisitorIdentifierUtil::isVisitorIdentifier('654'));
    }

    public function testIsVisitorIdentifierOnNonDigitIdInString()
    {
        self::assertFalse(VisitorIdentifierUtil::isVisitorIdentifier('visitor:test:sessionId654'));
    }

    public function testEncodeIdentifier()
    {
        $this->assertEquals(
            'visitor:256:sessionId654',
            VisitorIdentifierUtil::encodeIdentifier(256, 'sessionId654')
        );
    }

    public function testDecodeIdentifier()
    {
        self::assertSame(
            [256, 'sessionId654'],
            VisitorIdentifierUtil::decodeIdentifier('visitor:256:sessionId654')
        );
    }
}
