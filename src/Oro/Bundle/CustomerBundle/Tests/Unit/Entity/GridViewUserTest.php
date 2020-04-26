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

        static::assertNull($gridViewUser->getUser());
        static::assertSame($gridViewUser, $gridViewUser->setUser($user));
        static::assertSame($user, $gridViewUser->getUser());

        static::assertPropertyAccessors(
            $gridViewUser,
            [
                ['gridView', new GridView()],
            ]
        );
    }
}
