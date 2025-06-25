<?php

namespace Oro\Bundle\CustomerBundle\Datagrid\Extension;

use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Data grid extension for the frontend grids.
 * Applies filtering to show only entities associated with the current anonymous visitor during frontend requests.
 */
abstract class AbstractCustomerVisitorExtension extends AbstractExtension
{
    public function __construct(
        private TokenAccessorInterface $tokenAccessor,
        private FrontendHelper $frontendHelper
    ) {
    }

    abstract public function getGridName(): string;

    #[\Override]
    public function isApplicable(DatagridConfiguration $config): bool
    {
        return $config->getName() === $this->getGridName()
            && $this->frontendHelper->isFrontendRequest()
            && $this->tokenAccessor->getToken() instanceof AnonymousCustomerUserToken
            && parent::isApplicable($config);
    }

    #[\Override]
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource): void
    {
        if (!$this->isApplicable($config) || !$datasource instanceof OrmDatasource) {
            return;
        }

        /** @var AnonymousCustomerUserToken $token */
        $token = $this->tokenAccessor->getToken();
        $visitor = $token->getVisitor();
        if ($visitor) {
            $qb = $datasource->getQueryBuilder();
            $rootAlias = $qb->getRootAliases()[0];
            $qb
                ->andWhere($qb->expr()->eq(sprintf('IDENTITY(%s.visitor)', $rootAlias), ':visitor'))
                ->setParameter('visitor', $token->getVisitor());
        }
    }
}
