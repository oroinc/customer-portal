<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class MigrateFrontendAnonymousUserRolePermissionsQuery extends ParametrizedMigrationQuery
{
    const OLD_ROLE_NAME  = 'PUBLIC_ACCESS';
    const NEW_ROLE_NAME  = 'ROLE_FRONTEND_ANONYMOUS';
    const PRODUCT_CLASS_NAME = 'Oro\Bundle\ProductBundle\Entity\Product';
    const ROOT_CLASS_NAME = '(root)';

    #[\Override]
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->doExecute($logger, true);

        return $logger->getMessages();
    }

    #[\Override]
    public function execute(LoggerInterface $logger)
    {
        $this->doExecute($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     */
    public function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $sql = <<<'SQL'
UPDATE acl_entries 
SET security_identity_id = (SELECT id FROM acl_security_identities WHERE identifier = :role)
WHERE security_identity_id = (SELECT id FROM acl_security_identities WHERE identifier = :old_role)
AND class_id = (SELECT id FROM acl_classes WHERE class_type = :product_class)
SQL;

        $parameters = [
            'old_role' => self::OLD_ROLE_NAME,
            'role' => self::NEW_ROLE_NAME,
            'product_class' => self::PRODUCT_CLASS_NAME,
            'root_class' => self::ROOT_CLASS_NAME
        ];

        $types = [
            'old_role' => Types::STRING,
            'role' => Types::STRING,
            'product_class' => Types::STRING,
            'root_class' => Types::STRING,
        ];

        $this->logQuery($logger, $sql, $parameters, $types);

        if (!$dryRun) {
            $this->connection->executeStatement($sql, $parameters, $types);
        }
    }
}
