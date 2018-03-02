<?php

namespace Oro\Bundle\CustomerBundle\EventListener\Datagrid;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

/**
 * Removes columns from datagrid when front user is able to see only entities which owned by him.
 * List of the columns which should be removed can be specified by setter.
 * By default it will be column with name equals to `customerUserName`.
 */
class CustomerDatagridListener
{
    /**
     * @deprecated Will be removed in 2.0 version
     */
    const ROOT_OPTIONS = '[options][customerUserOwner]';
    /**
     * @deprecated Will be removed in 2.0 version
     */
    const ACCOUNT_USER_COLUMN = '[options][customerUserOwner][customerUserColumn]';

    /**
     * @var string
     *
     * @deprecated Will be removed in 2.0 version
     */
    protected $entityClass;

    /**
     * @var string
     *
     * @deprecated Will be removed in 2.0 version
     */
    protected $entityAlias;

    /** @var CustomerUserProvider */
    protected $securityProvider;

    /**
     * @var CustomerRepository
     *
     * @deprecated Will be removed in 2.0 version
     */
    protected $repository;

    /**
     * @var array
     *
     * @deprecated Will be removed in 2.0 version
     */
    protected $actionCallback;

    /** @var array */
    protected $columns = ['customerUserName'];

    /**
     * @param CustomerUserProvider $securityProvider
     * @param CustomerRepository $repository
     * @param array $actionCallback
     */
    public function __construct(
        CustomerUserProvider $securityProvider,
        CustomerRepository $repository,
        array $actionCallback = null
    ) {
        $this->securityProvider = $securityProvider;
        $this->repository = $repository;
        $this->actionCallback = $actionCallback;
    }

    /**
     * @param array $columns
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBeforeFrontendItems(BuildBefore $event)
    {
        if (!$this->getUser() instanceof CustomerUser) {
            return;
        }

        $config = $event->getConfig();

        if (!$config->isOrmDatasource()) {
            return;
        }

        $entityClass = $config->getOrmQuery()->getRootEntity();

        // left it there only for BC reasons, will be removed in 2.0 version
        $this->entityClass = $entityClass;
        $this->entityAlias = $config->getOrmQuery()->getRootAlias();

        if (!$entityClass || $this->securityProvider->isGrantedViewCustomerUser($entityClass)) {
            return;
        }

        $this->updateConfiguration($config);
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function updateConfiguration(DatagridConfiguration $config)
    {
        foreach ($this->columns as $column) {
            $this->removeCustomerUserColumn($config, $column);
        }
    }

    /**
     * @param DatagridConfiguration $config
     * @param bool $withChildCustomers
     *
     * @deprecated Will be removed in 2.0 version
     */
    protected function showAllCustomerItems(DatagridConfiguration $config, $withChildCustomers = false)
    {
        $config->offsetSetByPath(DatagridConfiguration::DATASOURCE_SKIP_ACL_APPLY_PATH, true);

        $user = $this->getUser();
        $customerId = $user->getCustomer()->getId();
        $ids = [$customerId];

        if ($withChildCustomers) {
            $ids = array_merge($ids, $this->repository->getChildrenIds($customerId));
        }

        $config->getOrmQuery()->addAndWhere(
            sprintf(
                '(%s.customer IN (%s) OR %s.customerUser = %d)',
                $this->entityAlias,
                implode(',', $ids),
                $this->entityAlias,
                $user->getId()
            )
        );
    }

    /**
     * @param DatagridConfiguration $config
     * @param string $column
     */
    protected function removeCustomerUserColumn(DatagridConfiguration $config, $column)
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

    /**
     * @return boolean
     *
     * @deprecated Will be removed in 2.0 version
     */
    protected function permissionShowAllCustomerItems()
    {
        return $this->securityProvider->isGrantedViewLocal($this->entityClass);
    }

    /**
     * @return boolean
     *
     * @deprecated Will be removed in 2.0 version
     */
    protected function permissionShowAllCustomerItemsForChild()
    {
        return $this->securityProvider->isGrantedViewDeep($this->entityClass) ||
            $this->securityProvider->isGrantedViewSystem($this->entityClass);
    }

    /**
     * @return boolean
     *
     * @deprecated Will be removed in 2.0 version
     */
    protected function permissionShowCustomerUserColumn()
    {
        return $this->securityProvider->isGrantedViewCustomerUser($this->entityClass);
    }
}
