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

        $this->assertNull($gridView->getOwner());
        $this->assertNull($gridView->getCustomerUserOwner());

        $this->assertSame($gridView, $gridView->setOwner($user1));
        $this->assertAttributeEquals($user1, 'customerUserOwner', $gridView);
        $this->assertSame($user1, $gridView->getOwner());

        $this->assertSame($gridView, $gridView->setCustomerUserOwner($user2));
        $this->assertAttributeEquals($user2, 'customerUserOwner', $gridView);
        $this->assertSame($user2, $gridView->getCustomerUserOwner());

        $this->assertPropertyCollections(
            $gridView,
            [
                ['users', new GridViewUser()],
            ]
        );
    }
}
