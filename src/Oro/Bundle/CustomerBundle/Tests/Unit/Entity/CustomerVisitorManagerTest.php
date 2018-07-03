<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\Testing\Unit\EntityTrait;

class CustomerVisitorManagerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    const ENTITY_ID = 45;
    const SESSION_ID = 'someSessionId';

    /**
     * @var EntityManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityManager;

    /**
     * @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $doctrineHelper;

    /**
     * @var CustomerVisitorManager
     */
    protected $manager;

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper = $doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper->expects($this->any())
            ->method('getEntityManagerForClass')
            ->with(CustomerVisitor::class)
            ->willReturn($this->entityManager);

        $this->manager = new CustomerVisitorManager($this->doctrineHelper);
    }

    public function testFindOrCreate()
    {
        $user = $this->getEntity(CustomerVisitor::class, ['id' => self::ENTITY_ID, 'sessionId' => self::SESSION_ID]);

        $repository = $this->createMock(ObjectRepository::class);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with(CustomerVisitor::class)
            ->willReturn($repository);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => self::ENTITY_ID, 'sessionId' => self::SESSION_ID])
            ->willReturn($user);

        $this->assertEquals($user, $this->manager->findOrCreate(self::ENTITY_ID, self::SESSION_ID));
    }

    public function testFindOrCreateForNonExistedUser()
    {
        $repository = $this->createMock(ObjectRepository::class);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with(CustomerVisitor::class)
            ->willReturn($repository);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => self::ENTITY_ID, 'sessionId' => self::SESSION_ID])
            ->willReturn(null);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(CustomerVisitor::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->assertInstanceOf(
            CustomerVisitor::class,
            $this->manager->findOrCreate(self::ENTITY_ID, self::SESSION_ID)
        );
    }

    public function testFindOrCreateWithoutId()
    {
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(CustomerVisitor::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->assertInstanceOf(CustomerVisitor::class, $this->manager->findOrCreate());
    }

    public function testUpdateLastVisitTime()
    {
        $user = new CustomerVisitor();
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->modify('-1 day');
        $user->setLastVisit($date);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->manager->updateLastVisitTime($user, 60);

        $this->assertEquals(
            (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp(),
            $user->getLastVisit()->getTimestamp(),
            '',
            10
        );
    }

    public function testUpdateLastVisitTimeWithoutAction()
    {
        $user = new CustomerVisitor();
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->modify('-1 day');
        $user->setLastVisit($date);

        $this->entityManager->expects($this->never())
            ->method('flush');

        $this->manager->updateLastVisitTime($user, 86460); // 86460 - 1 day and 1 minute

        $this->assertNotEquals(
            (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp(),
            $user->getLastVisit()->getTimestamp()
        );
    }
}
