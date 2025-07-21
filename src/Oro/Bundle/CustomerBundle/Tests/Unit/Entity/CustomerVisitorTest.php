<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use PHPUnit\Framework\TestCase;

class CustomerVisitorTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors(): void
    {
        self::assertPropertyAccessors(
            new CustomerVisitor(),
            [
                ['id', 42],
                ['customerUser', new CustomerUser()]
            ]
        );
    }

    public function testLastVisitAccessors(): void
    {
        self::assertEqualsWithDelta(
            (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp(),
            (new CustomerVisitor())->getLastVisit()->getTimestamp(),
            10
        );

        $now = new \DateTime();
        $customerUser = (new CustomerVisitor())->setLastVisit($now);
        self::assertEquals($now, $customerUser->getLastVisit());
    }

    public function testGetUserIdentifier(): void
    {
        $customerUser = new CustomerVisitor();
        self::assertSame('', $customerUser->getUserIdentifier());

        $customerUser->setSessionId('test');
        self::assertSame('visitor:test', $customerUser->getUserIdentifier());
    }
}
