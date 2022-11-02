<?php

namespace Oro\Bundle\CustomerBundle\EventListener\Datagrid;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;

/**
 * Removes columns from datagrid when front user is able to see only entities which owned by him.
 * List of the columns which should be removed can be passed by class constructor.
 * By default it will be column with name equals to `customerUserName`.
 */
class CustomerDatagridListener
{
    /** @var CustomerUserProvider */
    protected $securityProvider;

    /** @var array */
    protected $columns;

    public function __construct(CustomerUserProvider $securityProvider, array $columns = ['customerUserName'])
    {
        $this->securityProvider = $securityProvider;
        $this->columns = $columns;
    }

    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        if ($config->isDatasourceSkipAclApply()
            || !$config->isOrmDatasource()
            || !$this->getUser() instanceof CustomerUser
        ) {
            return;
        }

        $entityClass = $config->getOrmQuery()->getRootEntity();
        try {
            if (!$entityClass || $this->securityProvider->isGrantedViewCustomerUser($entityClass)) {
                return;
            }
        } catch (InvalidDomainObjectException $e) {
            return;
        }

        $this->updateConfiguration($config);
    }

    protected function updateConfiguration(DatagridConfiguration $config)
    {
        foreach ($this->columns as $column) {
            $this->removeCustomerUserColumn($config, $column);
        }
    }

    protected function removeCustomerUserColumn(DatagridConfiguration $config, string $column)
    {
        $config
            ->offsetUnsetByPath(sprintf('[columns][%s]', $column))
            ->offsetUnsetByPath(sprintf('[sorters][columns][%s]', $column))
            ->offsetUnsetByPath(sprintf('[filters][columns][%s]', $column));
    }

    /**
     * @return null|CustomerUser
     */
    protected function getUser()
    {
        return $this->securityProvider->getLoggedUser();
    }
}
