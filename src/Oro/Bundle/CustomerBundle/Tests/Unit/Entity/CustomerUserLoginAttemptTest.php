<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserLoginAttempt;
use Oro\Bundle\SecurityBundle\Tools\UUIDGenerator;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CustomerUserLoginAttemptTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testProperties(): void
    {
        $properties = [
            'username'  => ['username', 'steve', false],
            'source'    => ['source', 12, false],
            'user'      => ['user', new CustomerUser(), false],
            'ip'        => ['ip', '127.0.0.1', false],
            'userAgent' => ['userAgent', 'Chrome', false],
            'context'   => ['context', ['some' => 'data'], false],
            'attemptAt' => ['attemptAt', new \DateTime(), false]
        ];

        self::assertPropertyAccessors(new CustomerUserLoginAttempt(), $properties);
    }

    public function testGetId(): void
    {
        $id = UUIDGenerator::v4();
        $entity = new CustomerUserLoginAttempt();
        $entity->setId($id);
        self::assertSame($id, $entity->getId());
    }
}
