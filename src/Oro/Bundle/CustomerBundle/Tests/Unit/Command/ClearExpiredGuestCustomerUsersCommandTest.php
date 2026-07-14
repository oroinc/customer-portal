<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Command\ClearExpiredGuestCustomerUsersCommand;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Covers the do-while chunk processing loop (multi-batch, transaction commit/rollback).
 */
final class ClearExpiredGuestCustomerUsersCommandTest extends TestCase
{
    private const int CHUNK_SIZE = 10000;

    private const array ALL_GUARD_TABLES = [
        'oro_order',
        'oro_invoice',
        'oro_sale_quote',
        'oro_rfp_request',
        'oro_recurring_order',
        'oro_promotion_coupon_usage',
        'oro_checkout',
        'oro_shopping_list',
        'oro_scope',
    ];

    private ManagerRegistry&MockObject $doctrine;

    private ConfigManager&MockObject $configManager;

    private Connection&MockObject $connection;

    private ClearExpiredGuestCustomerUsersCommand $command;

    #[\Override]
    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->connection->method('getSchemaManager')
            ->willReturn($this->mockSchemaManager(self::ALL_GUARD_TABLES));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')
            ->willReturn($this->connection);

        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->doctrine->method('getManagerForClass')
            ->with(CustomerUser::class)
            ->willReturn($entityManager);

        $this->configManager = $this->createMock(ConfigManager::class);
        $this->configManager->method('get')
            ->with('oro_customer.customer_visitor_cookie_lifetime_days')
            ->willReturn(30);

        $this->command = new ClearExpiredGuestCustomerUsersCommand($this->doctrine, $this->configManager);
    }

    public function testExecuteProcessesMultipleBatchesWhenFirstBatchIsFull(): void
    {
        $selectQb1 = $this->mockSelectQb($this->generateRows(self::CHUNK_SIZE));
        $detachEmailQb1 = $this->mockWriteQb();
        $deleteCustomerUserQb1 = $this->mockWriteQb();
        $deleteCustomerQb1 = $this->mockWriteQb();

        $selectQb2 = $this->mockSelectQb($this->generateRows(3));
        $detachEmailQb2 = $this->mockWriteQb();
        $deleteCustomerUserQb2 = $this->mockWriteQb();
        $deleteCustomerQb2 = $this->mockWriteQb();

        $this->connection->expects(self::exactly(8))
            ->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls(
                $selectQb1,
                $detachEmailQb1,
                $deleteCustomerUserQb1,
                $deleteCustomerQb1,
                $selectQb2,
                $detachEmailQb2,
                $deleteCustomerUserQb2,
                $deleteCustomerQb2
            );

        $this->connection->expects(self::exactly(2))->method('beginTransaction');
        $this->connection->expects(self::exactly(2))->method('commit');
        $this->connection->expects(self::never())->method('rollBack');

        $exitCode = (new CommandTester($this->command))->execute([]);

        self::assertSame(Command::SUCCESS, $exitCode);
    }

    public function testExecuteStopsAfterSingleBatchWhenItIsNotFull(): void
    {
        $selectQb = $this->mockSelectQb($this->generateRows(5));
        $detachEmailQb = $this->mockWriteQb();
        $deleteCustomerUserQb = $this->mockWriteQb();
        $deleteCustomerQb = $this->mockWriteQb();

        $this->connection->expects(self::exactly(4))
            ->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls($selectQb, $detachEmailQb, $deleteCustomerUserQb, $deleteCustomerQb);

        $this->connection->expects(self::once())->method('beginTransaction');
        $this->connection->expects(self::once())->method('commit');

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute([]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString(
            'Clear expired guest customer users completed',
            $commandTester->getDisplay()
        );
    }

    public function testExecuteStopsImmediatelyWhenNothingToDelete(): void
    {
        $selectQb = $this->mockSelectQb([]);

        $this->connection->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($selectQb);

        $this->connection->expects(self::never())->method('beginTransaction');

        $exitCode = (new CommandTester($this->command))->execute([]);

        self::assertSame(Command::SUCCESS, $exitCode);
    }

    public function testExecuteRollsBackAndRethrowsWhenBatchDeletionFails(): void
    {
        $selectQb = $this->mockSelectQb($this->generateRows(5));
        $detachEmailQb = $this->mockWriteQb();

        $deleteCustomerUserQb = $this->createMock(QueryBuilder::class);
        $deleteCustomerUserQb->method('delete')->willReturnSelf();
        $deleteCustomerUserQb->method('where')->willReturnSelf();
        $deleteCustomerUserQb->method('setParameter')->willReturnSelf();
        $deleteCustomerUserQb->method('execute')
            ->willThrowException(new \RuntimeException('DB error'));

        $this->connection->expects(self::exactly(3))
            ->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls($selectQb, $detachEmailQb, $deleteCustomerUserQb);

        $this->connection->expects(self::once())->method('beginTransaction');
        $this->connection->expects(self::once())->method('rollBack');
        $this->connection->expects(self::never())->method('commit');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB error');

        (new CommandTester($this->command))->execute([]);
    }

    public function testExecuteSkipsGuardConditionsForTablesThatDoNotExist(): void
    {
        $result = $this->createMock(Result::class);
        $result->expects(self::once())->method('fetchAllAssociative')->willReturn([]);

        $selectQb = $this->createMock(QueryBuilder::class);
        $selectQb->method('select')->willReturnSelf();
        $selectQb->method('from')->willReturnSelf();
        $selectQb->method('where')->willReturnSelf();
        $selectQb->method('setParameter')->willReturnSelf();
        $selectQb->method('setMaxResults')->willReturnSelf();
        $selectQb->method('expr')->willReturn($this->createMock(ExpressionBuilder::class));
        $selectQb->expects(self::once())->method('execute')->willReturn($result);

        $missingTables = ['oro_invoice', 'oro_scope'];
        $existingTables = array_values(array_diff(self::ALL_GUARD_TABLES, $missingTables));

        // 1 base "updated_at" condition + one NOT EXISTS per still-existing guard table
        $selectQb->expects(self::exactly(8))->method('andWhere')->willReturnSelf();

        $connection = $this->createMock(Connection::class);
        $connection->method('getSchemaManager')->willReturn($this->mockSchemaManager($existingTables));
        $connection->expects(self::once())->method('createQueryBuilder')->willReturn($selectQb);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->method('getManagerForClass')->with(CustomerUser::class)->willReturn($entityManager);

        $command = new ClearExpiredGuestCustomerUsersCommand($doctrine, $this->configManager);

        $exitCode = (new CommandTester($command))->execute([]);

        self::assertSame(Command::SUCCESS, $exitCode);
    }

    private function mockSchemaManager(array $tableNames): AbstractSchemaManager&MockObject
    {
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->method('listTableNames')->willReturn($tableNames);

        return $schemaManager;
    }

    private function mockSelectQb(array $rows): QueryBuilder&MockObject
    {
        $result = $this->createMock(Result::class);
        $result->expects(self::once())->method('fetchAllAssociative')->willReturn($rows);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('setMaxResults')->willReturnSelf();
        $qb->method('expr')->willReturn($this->createMock(ExpressionBuilder::class));
        $qb->expects(self::once())->method('execute')->willReturn($result);

        return $qb;
    }

    private function mockWriteQb(): QueryBuilder&MockObject
    {
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('update')->willReturnSelf();
        $qb->method('set')->willReturnSelf();
        $qb->method('delete')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->expects(self::once())->method('execute')->willReturn(0);

        return $qb;
    }

    private function generateRows(int $count): array
    {
        $rows = [];
        for ($id = 1; $id <= $count; $id++) {
            $rows[] = ['customer_user_id' => $id, 'customer_id' => $id];
        }

        return $rows;
    }
}
