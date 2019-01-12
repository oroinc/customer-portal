<?php

namespace Oro\Bundle\CustomerBundle\EventListener\Datagrid;

use Doctrine\Common\Collections\Criteria;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * CustomerUserRole`s frontend datagrid listener that displays correct roles to current user
 */
class CustomerUserRoleDatagridListener
{
    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * @var TokenAccessorInterface
     */
    protected $tokenAccessor;

    /**
     * @param AclHelper $aclHelper
     */
    public function __construct(AclHelper $aclHelper)
    {
        $this->aclHelper = $aclHelper;
    }

    /**
     * @param TokenAccessorInterface $tokenAccessor
     */
    public function setTokenAccessor(TokenAccessorInterface $tokenAccessor)
    {
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $dataGrid = $event->getDatagrid();

        $datasource = $dataGrid->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            $qb = $datasource->getQueryBuilder();
            $alias = $qb->getRootAliases()[0];
            $criteria = new Criteria();
            $this->aclHelper->applyAclToCriteria(
                CustomerUserRole::class,
                $criteria,
                'VIEW',
                ['customer' => $alias.'.customer', 'organization' => $alias.'.organization']
            );
            $criteria->andWhere(Criteria::expr()->eq($alias . '.selfManaged', true));
            $criteria->andWhere(Criteria::expr()->eq($alias . '.public', true));

            $qb->addCriteria($criteria);

            $expr = $qb->expr()->andX(
                $qb->expr()->eq($alias . '.selfManaged', ':isActive'),
                $qb->expr()->eq($alias . '.public', ':isActive'),
                $qb->expr()->isNull($alias . '.customer')
            );
            $qb->orWhere($expr);
            $qb->setParameter('isActive', true, \PDO::PARAM_BOOL);

            $organizationId = $this->tokenAccessor->getOrganizationId();
            if ($organizationId) {
                $expr->add($qb->expr()->eq($alias . '.organization', ':currentCustomerOrganization'));
                $qb->setParameter('currentCustomerOrganization', $organizationId);
            }
        }
    }
}
