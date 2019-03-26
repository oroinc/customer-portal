<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Handler\CustomerUserReassignEntityUpdater;
use Oro\Bundle\CustomerBundle\Handler\CustomerUserReassignUpdater;

class CustomerUserReassignUpdaterTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerUserReassignUpdater */
    private $updater;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->updater = new CustomerUserReassignUpdater();
    }

    public function testUpdate()
    {
        /** @var CustomerUserReassignEntityUpdater|\PHPUnit\Framework\MockObject\MockObject $updater1 */
        $updater1 = $this->createMock(CustomerUserReassignEntityUpdater::class);
        /** @var CustomerUserReassignEntityUpdater|\PHPUnit\Framework\MockObject\MockObject $updater2 */
        $updater2 = $this->createMock(CustomerUserReassignEntityUpdater::class);
        $customerUser = new CustomerUser();

        $updater1->expects(self::once())
            ->method('getEntityClass')
            ->willReturn('Related\Entity\Class\Name1');
        $updater1->expects(self::once())
            ->method('update')
            ->with($customerUser);

        $updater2->expects(self::exactly(2))
            ->method('getEntityClass')
            ->willReturn('Related\Entity\Class\Name2');
        $updater2->expects(self::once())
            ->method('update')
            ->with($customerUser);

        $this->updater->addCustomerUserReassignEntityUpdater($updater1);
        $this->updater->addCustomerUserReassignEntityUpdater($updater2);
        $this->updater->addCustomerUserReassignEntityUpdater($updater2);

        $this->updater->update($customerUser);
    }

    public function testGetClassNamesToUpdate()
    {
        $customerUser = new CustomerUser();

        /** @var CustomerUserReassignEntityUpdater|\PHPUnit\Framework\MockObject\MockObject $entityUpdater1 */
        $entityUpdater1 = $this->createMock(CustomerUserReassignEntityUpdater::class);
        /** @var CustomerUserReassignEntityUpdater|\PHPUnit\Framework\MockObject\MockObject $entityUpdater2 */
        $entityUpdater2 = $this->createMock(CustomerUserReassignEntityUpdater::class);
        /** @var CustomerUserReassignEntityUpdater|\PHPUnit\Framework\MockObject\MockObject $entityUpdater3 */
        $entityUpdater3 = $this->createMock(CustomerUserReassignEntityUpdater::class);

        $entityUpdater1->expects(self::once())
            ->method('getEntityClass')
            ->willReturn('Related\Entity\Class\Name1');

        $entityUpdater2->expects(self::exactly(2))
            ->method('getEntityClass')
            ->willReturn('Related\Entity\Class\Name2');

        $entityUpdater3->expects(self::exactly(2))
            ->method('getEntityClass')
            ->willReturn('Related\Entity\Class\Name3');

        $this->updater->addCustomerUserReassignEntityUpdater($entityUpdater1);
        $this->updater->addCustomerUserReassignEntityUpdater($entityUpdater2);
        $this->updater->addCustomerUserReassignEntityUpdater($entityUpdater3);

        $entityUpdater1->expects(self::once())
            ->method('hasEntitiesToUpdate')
            ->willReturn(false);

        $entityUpdater2->expects(self::once())
            ->method('hasEntitiesToUpdate')
            ->willReturn(true);


        $entityUpdater3->expects(self::once())
            ->method('hasEntitiesToUpdate')
            ->willReturn(true);


        $classNamesToUpdate = $this->updater->getClassNamesToUpdate($customerUser);

        self::assertEquals([
            'Related\Entity\Class\Name2',
            'Related\Entity\Class\Name3',
        ], $classNamesToUpdate);
    }
}
