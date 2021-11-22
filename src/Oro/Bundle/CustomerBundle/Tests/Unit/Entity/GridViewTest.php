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

        self::assertNull($gridView->getOwner());
        self::assertNull($gridView->getCustomerUserOwner());

        self::assertSame($gridView, $gridView->setOwner($user1));
        self::assertEquals($user1, $gridView->getCustomerUserOwner());
        self::assertSame($user1, $gridView->getOwner());

        self::assertSame($gridView, $gridView->setCustomerUserOwner($user2));
        self::assertEquals($user2, $gridView->getCustomerUserOwner());
        self::assertSame($user2, $gridView->getCustomerUserOwner());

        self::assertPropertyCollections(
            $gridView,
            [
                ['users', new GridViewUser()],
            ]
        );
    }
}
