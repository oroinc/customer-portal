<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\ORM\Query\AST\IdentificationVariableDeclaration;
use Doctrine\ORM\Query\AST\SelectStatement;
use Doctrine\ORM\Query\AST\Subselect;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\OrmResultBefore;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\UserBundle\Entity\User;

class OrmDatasourceAclListener
{
    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var OwnershipMetadataProviderInterface */
    protected $metadataProvider;

    public function __construct(
        TokenAccessorInterface $tokenAccessor,
        OwnershipMetadataProviderInterface $metadataProvider
    ) {
        $this->tokenAccessor = $tokenAccessor;
        $this->metadataProvider = $metadataProvider;
    }

    public function onResultBefore(OrmResultBefore $event)
    {
        // listener logic is applied only to frontend part of application
        if ($this->tokenAccessor->getUser() instanceof User) {
            return;
        }

        $config = $event->getDatagrid()->getConfig();
        $query = $event->getQuery();

        /** @var Subselect|SelectStatement $select */
        $select = $query->getAST();
        $fromClause = $select instanceof SelectStatement ? $select->fromClause : $select->subselectFromClause;

        $skipAclCheck = true;

        /** @var IdentificationVariableDeclaration $identificationVariableDeclaration */
        foreach ($fromClause->identificationVariableDeclarations as $identificationVariableDeclaration) {
            $entityName = $identificationVariableDeclaration->rangeVariableDeclaration->abstractSchemaName;
            $metadata = $this->metadataProvider->getMetadata($entityName);

            if ($metadata->hasOwner()) {
                $skipAclCheck = false;
                break;
            }
        }

        if ($skipAclCheck) {
            $config->offsetSetByPath(DatagridConfiguration::DATASOURCE_SKIP_ACL_APPLY_PATH, true);
        }
    }
}
