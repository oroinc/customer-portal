<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_31;

use Doctrine\DBAL\Types\Types;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

/**
 * Insert default customer user auth statuses.
 */
class InsertAuthStatusesQuery extends ParametrizedMigrationQuery
{
    /** @var $extendExtension */
    protected $extendExtension;

    public function __construct(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info('Insert default customer user auth statuses.');
        $this->doExecute($logger, true);

        return $logger->getMessages();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->doExecute($logger);
    }

    private function doExecute(LoggerInterface $logger, bool $dryRun = false): void
    {
        $sql = sprintf(
            'INSERT INTO %s (id, name, priority, is_default) VALUES (:id, :name, :priority, :is_default)',
            $this->extendExtension->getNameGenerator()->generateEnumTableName('cu_auth_status')
        );

        $statuses = [
            [
                ':id' => CustomerUserManager::STATUS_ACTIVE,
                ':name' => 'Active',
                ':priority' => 1,
                ':is_default' => true,
            ],
            [
                ':id' => CustomerUserManager::STATUS_RESET,
                ':name' => 'Reset',
                ':priority' => 2,
                ':is_default' => false,
            ],
        ];

        $types = [
            'id' => Types::STRING,
            'name' => Types::STRING,
            'priority' => Types::INTEGER,
            'is_default' => Types::BOOLEAN,
        ];

        foreach ($statuses as $status) {
            $this->logQuery($logger, $sql, $status, $types);
            if (!$dryRun) {
                $this->connection->executeStatement($sql, $status, $types);
            }
        }

        $defaultStatus = ['default_status' => CustomerUserManager::STATUS_ACTIVE];
        $defaultStatusType = ['default_status' => Types::STRING];
        $sql = 'UPDATE oro_customer_user SET auth_status_id = :default_status';

        $this->logQuery($logger, $sql, $defaultStatus, $types);

        if (!$dryRun) {
            $this->connection->executeStatement($sql, $defaultStatus, $defaultStatusType);
        }
    }
}
