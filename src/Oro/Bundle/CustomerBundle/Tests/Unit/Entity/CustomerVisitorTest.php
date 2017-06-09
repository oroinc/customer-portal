<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CustomerVisitorTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new CustomerVisitor(), [
            ['id', 42],
            ['lastVisit', new \DateTime()],
            ['sessionId', 'session id'],
        ]);
    }
}
