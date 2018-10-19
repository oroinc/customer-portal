<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\GridView;
use Oro\Bundle\CustomerBundle\Entity\GridViewUser;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class GridViewUserTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $gridViewUser = new GridViewUser();
        $user = new CustomerUser();

        $this->assertNull($gridViewUser->getUser());
        $this->assertSame($gridViewUser, $gridViewUser->setUser($user));
        $this->assertAttributeEquals($user, 'customerUser', $gridViewUser);
        $this->assertSame($user, $gridViewUser->getUser());

        $this->assertPropertyAccessors(
            $gridViewUser,
            [
                ['gridView', new GridView()],
            ]
        );
    }
}
