<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\GridView;
use Oro\Bundle\CustomerBundle\Entity\GridViewUser;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class GridViewTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $gridView = new GridView();
        $user1 = new CustomerUser();
        $user2 = new CustomerUser();

        static::assertNull($gridView->getOwner());
        static::assertNull($gridView->getCustomerUserOwner());

        static::assertSame($gridView, $gridView->setOwner($user1));
        static::assertEquals($user1, $gridView->getCustomerUserOwner());
        static::assertSame($user1, $gridView->getOwner());

        static::assertSame($gridView, $gridView->setCustomerUserOwner($user2));
        static::assertEquals($user2, $gridView->getCustomerUserOwner());
        static::assertSame($user2, $gridView->getCustomerUserOwner());

        static::assertPropertyCollections(
            $gridView,
            [
                ['users', new GridViewUser()],
            ]
        );
    }
}
