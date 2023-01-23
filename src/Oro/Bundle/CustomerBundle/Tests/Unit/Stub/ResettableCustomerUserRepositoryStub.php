<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Stub;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CustomerBundle\Entity\Repository\ResetCustomerUserTrait;
use Oro\Bundle\CustomerBundle\Entity\Repository\ResettableCustomerUserRepositoryInterface;

class ResettableCustomerUserRepositoryStub extends EntityRepository implements
    ResettableCustomerUserRepositoryInterface
{
    use ResetCustomerUserTrait;
}
