<?php

namespace Oro\Bundle\CustomerBundle\Datagrid\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Datagrid extension that filters customer users by a specific customer.
 *
 * This extension applies a filter to the customer user selection grid to show only users
 * belonging to a specific customer, identified by the customer_id request parameter.
 * It ensures that the filter is applied only once per request.
 */
class CustomerUserByCustomerExtension extends AbstractExtension
{
    const SUPPORTED_GRID = 'customer-customer-user-select-grid';
    const ACCOUNT_KEY = 'customer_id';

    /**
     * @var bool
     */
    protected $applied = false;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    #[\Override]
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        if (!$this->isApplicable($config) || !$datasource instanceof OrmDatasource) {
            return;
        }

        $customerId = $this->requestStack->getCurrentRequest()->get(self::ACCOUNT_KEY);

        /** @var OrmDatasource $datasource */
        $qb = $datasource->getQueryBuilder();

        $rootAlias = $qb->getRootAliases()[0];
        $qb->andWhere($qb->expr()->eq(sprintf('IDENTITY(%s.customer)', $rootAlias), ':customer'))
            ->setParameter('customer', $customerId);

        $this->applied = true;
    }

    #[\Override]
    public function isApplicable(DatagridConfiguration $config)
    {
        $request = $this->requestStack->getCurrentRequest();

        return
            parent::isApplicable($config)
            && !$this->applied
            && static::SUPPORTED_GRID === $config->getName()
            && $request
            && $request->get(self::ACCOUNT_KEY);
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack($requestStack)
    {
        $this->requestStack = $requestStack;
    }
}
