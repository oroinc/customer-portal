<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitorManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerVisitorManagerTest extends TestCase
{
    private const SESSION_ID = 'someSessionId';

    private EntityManagerInterface&MockObject $entityManager;
    private EntityRepository&MockObject $repository;
    private CustomerVisitorManager $visitorManager;

    #[\Override]
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects(self::any())
            ->method('getManagerForClass')
            ->with(CustomerVisitor::class)
            ->willReturn($this->entityManager);
        $doctrine->expects(self::any())
            ->method('getRepository')
            ->with(CustomerVisitor::class)
            ->willReturn($this->repository);

        $this->visitorManager = new CustomerVisitorManager($doctrine);
    }

    public function testFindOrCreateForExistingVisitor(): void
    {
        $visitor = new CustomerVisitor();
        $visitor->setSessionId(self::SESSION_ID);

        $this->repository->expects(self::once())
            ->method('findOneBy')
            ->with(['sessionId' => self::SESSION_ID])
            ->willReturn($visitor);

        self::assertSame($visitor, $this->visitorManager->findOrCreate(self::SESSION_ID));
    }

    public function testFindOrCreateForNotExistingVisitor(): void
    {
        $this->repository->expects(self::once())
            ->method('findOneBy')
            ->with(['sessionId' => self::SESSION_ID])
            ->willReturn(null);

        self::assertInstanceOf(CustomerVisitor::class, $this->visitorManager->findOrCreate(self::SESSION_ID));
    }

    public function testFindOrCreateWithEmptySessionId(): void
    {
        $this->repository->expects(self::never())
            ->method(self::anything());

        self::assertInstanceOf(CustomerVisitor::class, $this->visitorManager->findOrCreate(null));
    }

    public function testFindForExistingVisitor(): void
    {
        $visitor = new CustomerVisitor();
        $visitor->setSessionId(self::SESSION_ID);

        $this->repository->expects(self::once())
            ->method('findOneBy')
            ->with(['sessionId' => self::SESSION_ID])
            ->willReturn($visitor);

        self::assertSame($visitor, $this->visitorManager->find(self::SESSION_ID));
    }

    public function testFindForNotExistingVisitor(): void
    {
        $this->repository->expects(self::once())
            ->method('findOneBy')
            ->with(['sessionId' => self::SESSION_ID])
            ->willReturn(null);

        self::assertNull($this->visitorManager->find(self::SESSION_ID));
    }

    public function testFindWithEmptySessionId(): void
    {
        $this->repository->expects(self::never())
            ->method(self::anything());

        self::assertNull($this->visitorManager->find(null));
    }

    public function testGenerateSessionId(): void
    {
        $sessionId = $this->visitorManager->generateSessionId();
        self::assertNotEmpty($sessionId);
        self::assertNotEquals($sessionId, $this->visitorManager->generateSessionId());
    }
}
