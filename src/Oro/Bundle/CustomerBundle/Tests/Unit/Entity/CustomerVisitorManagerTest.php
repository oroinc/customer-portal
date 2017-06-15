<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\Testing\Unit\EntityTrait;

class CustomerVisitorManagerTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    const ENTITY_ID = 45;
    const SESSION_ID = 'someSessionId';

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject
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
        $user = new CustomerVisitor();

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
            ->with($user);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->assertEquals($user, $this->manager->findOrCreate(self::ENTITY_ID, self::SESSION_ID));
    }

    public function testFindOrCreateWithoutId()
    {
        $user = new CustomerVisitor();
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->assertEquals($user, $this->manager->findOrCreate());
    }

    public function testUpdateLastVisitTime()
    {
        $user = new CustomerVisitor();
        $this->manager->updateLastVisitTime($user, 400);
    }

    public function testUpdateLastVisitTimeWithoutAction()
    {
        $user = new CustomerVisitor();
        $this->manager->updateLastVisitTime($user, 400);
    }
}
