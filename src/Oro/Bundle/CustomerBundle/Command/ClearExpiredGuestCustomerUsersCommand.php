<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Command;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CronBundle\Command\CronCommandScheduleDefinitionInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Clears expired guest customer users (and their customers) that have no related business records.
 */
#[AsCommand(
    name: 'oro:cron:customer-user:clear-expired-guests',
    description: 'Clears expired guest customer users (and their customers) that have no related business records.'
)]
class ClearExpiredGuestCustomerUsersCommand extends Command implements CronCommandScheduleDefinitionInterface
{
    private const int CHUNK_SIZE = 10000;

    /**
     * Tables that must NOT reference the guest customer user (by cu.id), otherwise it is
     * considered to still be in use and must not be removed.
     */
    private const array GUARD_TABLES = [
        'oro_order' => 'customer_user_id',
        'oro_invoice' => 'customer_user_id',
        'oro_sale_quote' => 'customer_user_id',
        'oro_rfp_request' => 'customer_user_id',
        'oro_recurring_order' => 'customer_user_id',
        'oro_promotion_coupon_usage' => 'customer_user_id',
        'oro_checkout' => 'customer_user_id',
        'oro_shopping_list' => 'customer_user_id',
    ];

    /**
     * Tables that reference oro_scope.id with ON DELETE NO ACTION, keyed by the scope_id column
     * name. oro_scope rows are created automatically per customer, so their existence alone
     * does not mean the customer is still in use - only a scope referenced by one of these
     * tables does.
     */
    private const array CUSTOMER_SCOPE_GUARD_TABLES = [
        'oro_navigation_menu_upd' => 'scope_id',
        'oro_commerce_menu_upd' => 'scope_id',
    ];

    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly ConfigManager $configManager
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setHelp(
                <<<'HELP'
The <info>%command.name%</info> command clears guest customer users (and their customers) that have not been
updated for the timeframe defined by "Customer visitor cookie lifetime (days)" system configuration setting,
and have no related orders, invoices, quotes, RFP requests, recurring orders, coupon usages, checkouts
or shopping lists.

HELP
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        /** @var Connection $connection */
        $connection = $this->doctrine->getManagerForClass(CustomerUser::class)->getConnection();
        $existingTables = $connection->createSchemaManager()->listTableNames();

        $expiredDate = $this->getExpiredDate();
        do {
            $selectQB = $connection->createQueryBuilder();
            $selectQB
                ->select('cu.id AS customer_user_id, cu.customer_id AS customer_id')
                ->from('oro_customer_user', 'cu')
                ->where($selectQB->expr()->eq('cu.is_guest', 'true'))
                ->andWhere($selectQB->expr()->lte('cu.updated_at', ':expiredDate'))
                ->setParameter('expiredDate', $expiredDate, Types::DATETIME_MUTABLE)
                ->setMaxResults(self::CHUNK_SIZE);

            $this->applyGuardTableConditions($selectQB, $existingTables);
            $this->applyCustomerScopeGuardConditions($selectQB, $existingTables);

            $rows = $selectQB->executeQuery()->fetchAllAssociative();

            if (!$rows) {
                break;
            }

            $customerUserIds = array_column($rows, 'customer_user_id');
            $customerIds = array_values(array_filter(array_column($rows, 'customer_id')));

            $connection->beginTransaction();
            try {
                // oro_email_address.owner_customeruser_id has ON DELETE NO ACTION (and the email address
                // row itself may still be referenced by real oro_email/oro_email_recipient history), so it
                // must be detached, not deleted, before the customer user can be removed.
                $connection->createQueryBuilder()
                    ->update('oro_email_address')
                    ->set('owner_customeruser_id', 'NULL')
                    ->set('has_owner', 'false')
                    ->where('owner_customeruser_id IN (:ids)')
                    ->setParameter('ids', $customerUserIds, ArrayParameterType::INTEGER)
                    ->executeStatement();

                $connection->createQueryBuilder()
                    ->delete('oro_customer_user')
                    ->where('id IN (:ids)')
                    ->setParameter('ids', $customerUserIds, ArrayParameterType::INTEGER)
                    ->executeStatement();

                if ($customerIds) {
                    $connection->createQueryBuilder()
                        ->delete('oro_customer')
                        ->where('id IN (:ids)')
                        ->setParameter('ids', $customerIds, ArrayParameterType::INTEGER)
                        ->executeStatement();
                }

                $connection->commit();
            } catch (\Throwable $e) {
                $connection->rollBack();

                throw $e;
            }
        } while (\count($rows) === self::CHUNK_SIZE);

        $symfonyStyle->success('Clear expired guest customer users completed');

        return Command::SUCCESS;
    }

    private function applyGuardTableConditions(QueryBuilder $selectQB, array $existingTables): void
    {
        foreach (self::GUARD_TABLES as $table => $column) {
            if (!in_array($table, $existingTables, true)) {
                continue;
            }

            $selectQB->andWhere(sprintf(
                'NOT EXISTS (SELECT 1 FROM %1$s WHERE %1$s.%2$s = cu.id)',
                $table,
                $column
            ));
        }
    }

    private function applyCustomerScopeGuardConditions(QueryBuilder $selectQB, array $existingTables): void
    {
        if (!in_array('oro_scope', $existingTables, true)) {
            return;
        }

        foreach (self::CUSTOMER_SCOPE_GUARD_TABLES as $table => $column) {
            if (!in_array($table, $existingTables, true)) {
                continue;
            }

            $selectQB->andWhere(sprintf(
                'NOT EXISTS (
                    SELECT 1 FROM oro_scope s
                    INNER JOIN %1$s ON %1$s.%2$s = s.id
                    WHERE s.customer_id = cu.customer_id
                )',
                $table,
                $column
            ));
        }
    }

    protected function getExpiredDate(): \DateTime
    {
        $cookieLifetime = $this->configManager->get('oro_customer.customer_visitor_cookie_lifetime_days');

        $expiredDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $expiredDate->modify(\sprintf('-%d seconds', $cookieLifetime * 86400));

        return $expiredDate;
    }

    #[\Override]
    public function getDefaultDefinition(): string
    {
        return '0 3 * * *';
    }
}
