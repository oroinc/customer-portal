<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

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
            ]
        );
    }

    public function testLastVisitAccessors()
    {
        $this->assertLessThanOrEqual(
            new \DateTime('now', new \DateTimeZone('UTC')),
            (new CustomerVisitor())->getLastVisit()
        );

        $now = new \DateTime;
        $customerUser = (new CustomerVisitor())->setLastVisit($now);
        $this->assertEquals($now, $customerUser->getLastVisit());
    }
}
