<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_14_1;

use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Oro\Bundle\WorkflowBundle\Acl\Extension\WorkflowMaskBuilder;
use Psr\Log\LoggerInterface;

class UpdateWorkflowACLQuery extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Update workflow permissions';
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
        // find acl class '(root)'
        $sql = 'SELECT id FROM acl_classes WHERE class_type = :class';
        $params = ['class' => '(root)'];
        $types = ['class' => 'string'];
        $this->logQuery($logger, $sql, $params, $types);
        if ($dryRun) {
            return;
        }

        // find oid for 'workflow:(root)'
        $classId = $this->connection->fetchColumn($sql, $params, 0, $types);
        $sql = 'SELECT id FROM acl_object_identities WHERE class_id = :class and object_identifier = :oid';
        $params = ['class' => $classId, 'oid' => 'workflow'];
        $types = ['class' => Types::INTEGER, 'oid' => Types::STRING];
        $oId = $this->connection->fetchColumn($sql, $params, 0, $types);

        // find sid for role IS_AUTHENTICATED_ANONYMOUSLY
        $sql = 'SELECT id FROM acl_security_identities WHERE identifier = :role';
        $params = ['role' => 'IS_AUTHENTICATED_ANONYMOUSLY'];
        $types = ['role' => Types::STRING];
        $this->logQuery($logger, $sql, $params, $types);
        $sId = $this->connection->fetchColumn($sql, $params, 0, $types);

        // reset permissions for 'workflow:(root)' and role IS_AUTHENTICATED_ANONYMOUSLY
        $sql = <<<SQL
UPDATE acl_entries
SET mask = :mask
WHERE object_identity_id = :oid and security_identity_id = :sid
SQL;
        $params = ['mask' => WorkflowMaskBuilder::GROUP_NONE, 'oid' => $oId, 'sid' => $sId];
        $types = ['mask' => Types::INTEGER, 'oid' => Types::INTEGER, 'sid' => Types::INTEGER];
        $this->logQuery($logger, $sql, $params, $types);
        $this->connection->executeStatement($sql, $params, $types);
    }
}
