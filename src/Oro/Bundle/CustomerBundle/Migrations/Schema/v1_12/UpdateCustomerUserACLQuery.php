<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_12;

use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Types\Type;

class UpdateCustomerUserACLQuery extends ParametrizedMigrationQuery
{

    /**
     * Gets a query description
     * If this query has several sub queries you can return an array of descriptions for each sub query
     *
     * @return string|string[]
     */
    public function getDescription()
    {
        return 'Update permission of predefined roles';
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

        $sql = 'SELECT id FROM acl_classes WHERE class_type = :class';
        $params = ['class' => '(root)'];
        $types = ['class' => 'string'];
        $this->logQuery($logger, $sql, $params, $types);
        if ($dryRun) {
            return;
        }

        $classId = $this->connection->fetchColumn($sql, $params, 0, $types);
        $sql = 'SELECT id FROM acl_object_identities WHERE class_id = :class and object_identifier = :oid';
        $params = ['class' => $classId, 'oid' => 'entity'];
        $types = ['class' => Type::INTEGER, 'oid' => Type::STRING];
        $oId = $this->connection->fetchColumn($sql, $params, 0, $types);

        $sql = 'SELECT id FROM acl_security_identities WHERE identifier = :role';
        $params = ['role' => 'ROLE_FRONTEND_ADMINISTRATOR'];
        $types = ['role' => Type::STRING];
        $this->logQuery($logger, $sql, $params, $types);
        $adminID = $this->connection->fetchColumn($sql, $params, 0, $types);

        $sql = 'SELECT id FROM acl_security_identities WHERE identifier = :role';
        $params = ['role' => 'ROLE_FRONTEND_BUYER'];
        $types = ['role' => Type::STRING];
        $this->logQuery($logger, $sql, $params, $types);
        $buyerID = $this->connection->fetchColumn($sql, $params, 0, $types);

        $sql = <<<'SQL'
update acl_entries 
set mask = :mask 
where object_identity_id = :oid and security_identity_id = :sid and mask = :oldMask
SQL;
        $params = ['mask' => 69764, 'oid' => $oId, 'sid' => $buyerID, 'oldMask' => 82448];
        $types = ['mask' => Type::INTEGER, 'oid' => Type::INTEGER, 'sid' => Type::INTEGER, 'oldMask' => Type::INTEGER];
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);

        $params = ['mask' => 36996, 'oid' => $oId, 'sid' => $buyerID, 'oldMask' => 49680];
        $types = ['mask' => Type::INTEGER, 'oid' => Type::INTEGER, 'sid' => Type::INTEGER, 'oldMask' => Type::INTEGER];
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);

        $params = ['mask' => 4228, 'oid' => $oId, 'sid' => $buyerID, 'oldMask' => 16912];
        $types = ['mask' => Type::INTEGER, 'oid' => Type::INTEGER, 'sid' => Type::INTEGER, 'oldMask' => Type::INTEGER];
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);


        $params = ['mask' => 69764, 'oid' => $oId, 'sid' => $adminID, 'oldMask' => 82448];
        $types = ['mask' => Type::INTEGER, 'oid' => Type::INTEGER, 'sid' => Type::INTEGER, 'oldMask' => Type::INTEGER];
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);

        $params = ['mask' => 36996, 'oid' => $oId, 'sid' => $adminID, 'oldMask' => 49680];
        $types = ['mask' => Type::INTEGER, 'oid' => Type::INTEGER, 'sid' => Type::INTEGER, 'oldMask' => Type::INTEGER];
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);

        $params = ['mask' => 4228, 'oid' => $oId, 'sid' => $adminID, 'oldMask' => 16912];
        $types = ['mask' => Type::INTEGER, 'oid' => Type::INTEGER, 'sid' => Type::INTEGER, 'oldMask' => Type::INTEGER];
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeUpdate($sql, $params, $types);
    }
}
