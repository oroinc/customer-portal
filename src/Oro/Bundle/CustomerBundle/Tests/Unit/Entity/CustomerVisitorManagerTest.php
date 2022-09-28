<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use Oro\Component\Testing\Unit\EntityTrait;

class CustomerVisitorManagerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    private const ENTITY_ID = 45;
    private const SESSION_ID = 'someSessionId';

    /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject */
    private $entityManager;

    /** @var EntityRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var CustomerVisitorManager */
    private $manager;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var Connection|\PHPUnit\Framework\MockObject\MockObject */
    private $defaultConnection;

    /** @var Connection|\PHPUnit\Framework\MockObject\MockObject */
    private $sessionConnection;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $this->defaultConnection = $this->createMock(Connection::class);
        $this->sessionConnection = $this->createMock(Connection::class);

        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->with(CustomerVisitor::class)
            ->willReturn($this->entityManager);
        $this->doctrine->expects(($this->any()))
            ->method('getConnectionNames')
            ->willReturn(['default' => true, 'session' => true]);
        $this->doctrine->method('getConnection')->willReturnMap([
            ['default', $this->defaultConnection],
            ['session', $this->sessionConnection]
        ]);
        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->with(CustomerVisitor::class)
            ->willReturn($this->repository);
        $this->entityManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->defaultConnection);

        $this->manager = new CustomerVisitorManager($this->doctrine);
    }

    public function testFindOrCreate()
    {
        $user = $this->getEntity(CustomerVisitor::class, ['id' => self::ENTITY_ID, 'sessionId' => self::SESSION_ID]);

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => self::ENTITY_ID, 'sessionId' => self::SESSION_ID])
            ->willReturn($user);

        $this->assertEquals($user, $this->manager->findOrCreate(self::ENTITY_ID, self::SESSION_ID));
    }

    public function testFindOrCreateForNonExistedUser()
    {
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => self::ENTITY_ID, 'sessionId' => self::SESSION_ID])
            ->willReturn(null);

        $this->repository->expects($this->once())
            ->method('find')
            ->with(self::ENTITY_ID)
            ->willReturn($this->getEntity(CustomerVisitor::class, ['id' => self::ENTITY_ID]));

        $this->defaultConnection->expects($this->once())
            ->method('lastInsertId')
            ->with('oro_customer_visitor_id_seq')
            ->willReturn(self::ENTITY_ID);

        $this->defaultConnection->expects($this->once())
            ->method('insert');

        $this->assertInstanceOf(
            CustomerVisitor::class,
            $this->manager->findOrCreate(self::ENTITY_ID, self::SESSION_ID)
        );
    }

    public function testFindOrCreateWithoutId()
    {
        $this->repository->expects($this->never())
            ->method('findOneBy');

        $this->repository->expects($this->once())
            ->method('find')
            ->with(self::ENTITY_ID)
            ->willReturn($this->getEntity(CustomerVisitor::class, ['id' => self::ENTITY_ID]));

        $this->defaultConnection->expects($this->once())
            ->method('lastInsertId')
            ->with('oro_customer_visitor_id_seq')
            ->willReturn(self::ENTITY_ID);

        $this->defaultConnection->expects($this->once())
            ->method('insert');

        $this->assertInstanceOf(CustomerVisitor::class, $this->manager->findOrCreate());
    }

    public function testFindOrCreateWithoutIdWithWriteConnection()
    {
        $this->repository->expects($this->never())
            ->method('findOneBy');

        $this->repository->expects($this->once())
            ->method('find')
            ->with(self::ENTITY_ID)
            ->willReturn($this->getEntity(CustomerVisitor::class, ['id' => self::ENTITY_ID]));

        $this->sessionConnection->expects($this->once())
            ->method('lastInsertId')
            ->with('oro_customer_visitor_id_seq')
            ->willReturn(self::ENTITY_ID);

        $this->sessionConnection->expects($this->once())
            ->method('insert');

        $this->sessionConnection->expects($this->never())
            ->method('beginTransaction');

        $manager = new CustomerVisitorManager($this->doctrine, 'session');

        $this->assertInstanceOf(CustomerVisitor::class, $manager->findOrCreate());
    }
}
