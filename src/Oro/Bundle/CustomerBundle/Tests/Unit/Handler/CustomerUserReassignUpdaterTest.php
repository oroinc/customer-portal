<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Handler\CustomerUserReassignEntityUpdater;
use Oro\Bundle\CustomerBundle\Handler\CustomerUserReassignUpdater;

class CustomerUserReassignUpdaterTest extends \PHPUnit\Framework\TestCase
{
    public function testUpdate()
    {
        $customerUser = new CustomerUser();

        $updater1 = $this->createMock(CustomerUserReassignEntityUpdater::class);
        $updater2 = $this->createMock(CustomerUserReassignEntityUpdater::class);

        $updater1->expects(self::once())
            ->method('update')
            ->with($customerUser);
        $updater2->expects(self::once())
            ->method('update')
            ->with($customerUser);

        $updater = new CustomerUserReassignUpdater([$updater1, $updater2]);
        $updater->update($customerUser);
    }

    public function testGetClassNamesToUpdate()
    {
        $customerUser = new CustomerUser();

        $updater1 = $this->createMock(CustomerUserReassignEntityUpdater::class);
        $updater2 = $this->createMock(CustomerUserReassignEntityUpdater::class);
        $updater3 = $this->createMock(CustomerUserReassignEntityUpdater::class);
        $updater4 = $this->createMock(CustomerUserReassignEntityUpdater::class);

        $updater1->expects(self::once())
            ->method('hasEntitiesToUpdate')
            ->willReturn(false);
        $updater1->expects(self::never())
            ->method('getEntityClass');

        $updater2->expects(self::once())
            ->method('hasEntitiesToUpdate')
            ->willReturn(true);
        $updater2->expects(self::once())
            ->method('getEntityClass')
            ->willReturn('Related\Entity\Class\Name2');

        $updater3->expects(self::once())
            ->method('hasEntitiesToUpdate')
            ->willReturn(true);
        $updater3->expects(self::once())
            ->method('getEntityClass')
            ->willReturn('Related\Entity\Class\Name3');

        $updater4->expects(self::once())
            ->method('hasEntitiesToUpdate')
            ->willReturn(true);
        $updater4->expects(self::once())
            ->method('getEntityClass')
            ->willReturn('Related\Entity\Class\Name3');

        $updater = new CustomerUserReassignUpdater([$updater1, $updater2, $updater3, $updater4]);
        self::assertEquals(
            [
                'Related\Entity\Class\Name2',
                'Related\Entity\Class\Name3'
            ],
            $updater->getClassNamesToUpdate($customerUser)
        );
    }
}
