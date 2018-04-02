<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CustomerVisitorTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(
            new CustomerVisitor(),
            [
                ['id', 42],
                ['customerUser', new CustomerUser()]
            ]
        );
    }

    public function testLastVisitAccessors()
    {
        $this->assertEquals(
            (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp(),
            (new CustomerVisitor())->getLastVisit()->getTimestamp(),
            '',
            10
        );

        $now = new \DateTime;
        $customerUser = (new CustomerVisitor())->setLastVisit($now);
        $this->assertEquals($now, $customerUser->getLastVisit());
    }
}
