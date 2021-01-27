<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Entity;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->with(CustomerVisitor::class)
            ->willReturn($this->entityManager);
        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->with(CustomerVisitor::class)
            ->willReturn($this->repository);

        $this->manager = new CustomerVisitorManager($doctrine);
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
        /** @var CustomerVisitor $user */
        $user = $this->getEntity(CustomerVisitor::class, ['id' => self::ENTITY_ID]);
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->modify('-1 day');
        $user->setLastVisit($date);

        $qb = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(AbstractQuery::class);
        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($qb);
        $qb->expects($this->once())
            ->method('update')
            ->with(CustomerVisitor::class, 'v')
            ->willReturnSelf();
        $qb->expects($this->once())
            ->method('set')
            ->with('v.lastVisit', ':lastVisit')
            ->willReturnSelf();
        $qb->expects($this->once())
            ->method('where')
            ->with('v.id = :id')
            ->willReturnSelf();
        $qb->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnCallback(function ($key, $value) use ($qb, $user) {
                if ('lastVisit' === $key) {
                    $this->assertEqualsWithDelta(
                        (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp(),
                        $value->getTimestamp(),
                        10
                    );
                } elseif ('id' === $key) {
                    $this->assertSame($user->getId(), $value);
                } else {
                    $this->fail(sprintf('Unexpected parameter: %s', $key));
                }

                return $qb;
            });
        $qb->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $query->expects($this->once())
            ->method('execute');

        $this->manager->updateLastVisitTime($user, 60);

        $this->assertEquals(
            $date->getTimestamp(),
            $user->getLastVisit()->getTimestamp()
        );
    }

    public function testUpdateLastVisitTimeWithoutAction()
    {
        $user = new CustomerVisitor();
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->modify('-1 day');
        $user->setLastVisit($date);

        $this->entityManager->expects($this->never())
            ->method('createQueryBuilder');

        $this->manager->updateLastVisitTime($user, 86460); // 86460 - 1 day and 1 minute

        $this->assertNotEquals(
            (new \DateTime('now', new \DateTimeZone('UTC')))->getTimestamp(),
            $user->getLastVisit()->getTimestamp()
        );
    }
}
