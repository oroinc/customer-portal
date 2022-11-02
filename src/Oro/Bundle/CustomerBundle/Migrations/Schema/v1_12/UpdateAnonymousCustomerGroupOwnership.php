<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_12;

use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData;
use Psr\Log\LoggerInterface;

class UpdateAnonymousCustomerGroupOwnership extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Update anonymous customer group ownership';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->doExecute($logger);
    }

    /**
     * {@inheritdoc}
     */
    public function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $selectOrganizationQuery = <<<'SQL'
SELECT id
FROM oro_organization
ORDER BY id ASC 
LIMIT 1;
SQL;

        $this->logQuery($logger, $selectOrganizationQuery);

        $organizationId = $this->connection->fetchColumn($selectOrganizationQuery);

        $selectUserQuery = <<<'SQL'
SELECT u.id FROM oro_user u
INNER JOIN oro_user_access_role ur ON ur.user_id = u.id
INNER JOIN oro_access_role r ON ur.role_id = r.id
WHERE r.role = :administratorRole
ORDER BY u.id ASC
LIMIT 1;
SQL;

        $params = [
            'administratorRole' => LoadRolesData::ROLE_ADMINISTRATOR
        ];
        $types = [
            'administratorRole' => Types::STRING
        ];

        $this->logQuery($logger, $selectUserQuery, $params, $types);

        $userId = $this->connection->fetchColumn($selectUserQuery, $params, 0, $types);

        $selectAnonymousCustomerGroup = <<<'SQL'
SELECT cv.text_value FROM oro_config_value cv
LEFT JOIN oro_config c ON cv.config_id = c.id
WHERE cv.name = :anonymousCustomerGroup AND c.entity = :application
LIMIT 1;
SQL;

        $params = [
            'anonymousCustomerGroup' => 'anonymous_customer_group',
            'application' => 'app'
        ];
        $types = [
            'anonymousCustomerGroup' => Types::STRING,
            'application' => Types::STRING
        ];

        $anonymousCustomerGroupId = $this->connection
            ->fetchColumn($selectAnonymousCustomerGroup, $params, 0, $types);

        $updateQuery = <<<'SQL'
UPDATE oro_customer_group 
SET organization_id = :organizationId, user_owner_id = :userOwnerId 
WHERE id = :anonymousCustomerGroupId AND organization_id is NULL AND user_owner_id is NULL;
SQL;

        $params = [
            'organizationId' => $organizationId,
            'userOwnerId' => $userId,
            'anonymousCustomerGroupId' => $anonymousCustomerGroupId
        ];
        $types = [
            'organizationId' => Types::INTEGER,
            'userOwnerId' => Types::INTEGER,
            'anonymousCustomerGroupId' => Types::INTEGER
        ];
        $this->logQuery($logger, $updateQuery, $params, $types);
        if (!$dryRun) {
            $this->connection->executeStatement($updateQuery, $params, $types);
        }
    }
}
