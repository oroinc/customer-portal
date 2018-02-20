<?php

namespace Oro\Bundle\CustomerBundle\EventListener\Datagrid;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

/**
 * Removes CustomerUser column for datagrid when front user is able to see only entities which owned by him
 */
class CustomerDatagridListener
{
    /** @var CustomerUserProvider */
    protected $securityProvider;

    /** @var array */
    protected $columns = [
        'customerUserName'
    ];

    /**
     * @param CustomerUserProvider $securityProvider
     */
    public function __construct(CustomerUserProvider $securityProvider)
    {
        $this->securityProvider = $securityProvider;
    }

    /**
     * @param string $column
     */
    public function addColumn(string $column)
    {
        $this->columns[] = $column;
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        if (!$config->isOrmDatasource() || !$this->securityProvider->getLoggedUser() instanceof CustomerUser) {
            return;
        }

        $entityClass = $config->getOrmQuery()->getRootEntity();
        if (!$entityClass || $this->securityProvider->isGrantedViewCustomerUser($entityClass)) {
            return;
        }

        foreach ($this->columns as $column) {
            $config
                ->offsetUnsetByPath(sprintf('[columns][%s]', $column))
                ->offsetUnsetByPath(sprintf('[sorters][columns][%s]', $column))
                ->offsetUnsetByPath(sprintf('[filters][columns][%s]', $column));
        }
    }
}
