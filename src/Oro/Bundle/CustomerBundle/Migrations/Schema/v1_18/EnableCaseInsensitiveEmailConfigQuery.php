<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_18;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

/**
 * Enables case-insensitive option in system configuration in case when is used MySql database and current collation
 * for table `oro_customer_user` not case sensitive.
 */
class EnableCaseInsensitiveEmailConfigQuery extends ParametrizedMigrationQuery
{
    #[\Override]
    public function getDescription()
    {
        return 'Enable case insensitive email option for Customer Users';
    }

    #[\Override]
    public function execute(LoggerInterface $logger)
    {
        if (!$this->connection->getDatabasePlatform() instanceof MySQLPlatform) {
            return;
        }

        $result = $this->connection->fetchAllAssociative(
            'SELECT 1
            FROM information_schema.columns
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
            AND COLLATION_NAME LIKE ?
            LIMIT 1;',
            [
                $this->connection->getDatabase(),
                'oro_customer_user',
                'email',
                '%_ci'
            ]
        );

        if (!$result) {
            return;
        }

        $stmt = $this->connection->prepare(
            'INSERT INTO oro_config_value 
            (config_id, name, section, text_value, object_value, array_value, type, created_at, updated_at) 
            VALUES (
              (SELECT id from oro_config WHERE entity = :entityName LIMIT 1),
              :fieldName,
              :section,
              :textValue,
              :objectValue,
              :arrayValue,
              :type,
              :createdAt,
              :updatedAt
            )'
        );

        $stmt->bindValue('entityName', 'app', 'string');
        $stmt->bindValue('fieldName', 'case_insensitive_email_addresses_enabled', 'string');
        $stmt->bindValue('section', 'oro_customer', 'string');
        $stmt->bindValue('textValue', 1, 'text');
        $stmt->bindValue('objectValue', null, 'object');
        $stmt->bindValue('arrayValue', null, 'array');
        $stmt->bindValue('type', 'scalar', 'string');

        $now = new \DateTime();
        $stmt->bindValue('createdAt', $now, 'datetime');
        $stmt->bindValue('updatedAt', $now, 'datetime');

        $stmt->executeQuery();
    }
}
