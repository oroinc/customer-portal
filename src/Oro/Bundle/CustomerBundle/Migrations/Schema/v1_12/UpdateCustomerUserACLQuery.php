<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_12;

use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityMaskBuilder;
use Psr\Log\LoggerInterface;

/**
 * Changes SYSTEM access level to DEEP access level for all permissions for the following roles:
 * * ROLE_FRONTEND_ADMINISTRATOR
 * * ROLE_FRONTEND_BUYER
 */
class UpdateCustomerUserACLQuery extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
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
        $classId = $this->connection->fetchColumn($sql, $params, 0, $types);

        $sql = 'SELECT id FROM acl_object_identities WHERE class_id = :class and object_identifier = :oid';
        $params = ['class' => $classId, 'oid' => 'entity'];
        $types = ['class' => Types::INTEGER, 'oid' => Types::STRING];
        $oid = $this->connection->fetchColumn($sql, $params, 0, $types);

        $sql = 'SELECT id FROM acl_security_identities WHERE identifier = :role';
        $params = ['role' => 'ROLE_FRONTEND_ADMINISTRATOR'];
        $types = ['role' => Types::STRING];
        $this->logQuery($logger, $sql, $params, $types);
        $adminSid = $this->connection->fetchColumn($sql, $params, 0, $types);

        $sql = 'SELECT id FROM acl_security_identities WHERE identifier = :role';
        $params = ['role' => 'ROLE_FRONTEND_BUYER'];
        $types = ['role' => Types::STRING];
        $this->logQuery($logger, $sql, $params, $types);
        $buyerSid = $this->connection->fetchColumn($sql, $params, 0, $types);

        $this->updateAceMasks($logger, $dryRun, $oid, $adminSid);
        $this->updateAceMasks($logger, $dryRun, $oid, $buyerSid);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     * @param int             $oid
     * @param int             $sid
     */
    private function updateAceMasks(LoggerInterface $logger, $dryRun, $oid, $sid)
    {
        $sql = 'SELECT id, mask FROM acl_entries WHERE object_identity_id = :oid AND security_identity_id = :sid';
        $params = ['oid' => $oid, 'sid' => $sid];
        $types = ['oid' => Types::INTEGER, 'sid' => Types::INTEGER];
        $this->logQuery($logger, $sql, $params, $types);
        $rows = $this->connection->fetchAll($sql, $params, $types);

        $forUpdate = [];
        foreach ($rows as $row) {
            $mask = (int)$row['mask'];
            $newMask = $this->getNewMask($mask);
            if ($newMask !== $mask) {
                $forUpdate[] = ['mask' => $newMask, 'id' => (int)$row['id']];
            }
        }

        $updateSql = 'UPDATE acl_entries SET mask = :mask WHERE id = :id';
        $types = ['mask' => Types::INTEGER, 'id' => Types::INTEGER];
        foreach ($forUpdate as $params) {
            $this->logQuery($logger, $updateSql, $params, $types);
            if (!$dryRun) {
                $this->connection->executeStatement($updateSql, $params, $types);
            }
        }
    }

    /**
     * @param int $mask
     *
     * @return int
     */
    private function getNewMask($mask)
    {
        $system = 16;
        $deep = 4;

        $newMask = 0;
        for ($i = 0; $i < EntityMaskBuilder::MAX_PERMISSIONS_IN_MASK; $i++) {
            $offset = $i * 5;
            $permission = ($mask >> $offset) & 31;
            if ($permission === $system) {
                $permission = $deep;
            }
            $newMask |= $permission << $offset;
        }
        $newMask |= $mask & EntityMaskBuilder::SERVICE_BITS;

        return $newMask;
    }
}
