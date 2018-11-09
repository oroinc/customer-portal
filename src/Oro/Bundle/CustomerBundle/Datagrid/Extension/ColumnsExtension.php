<?php

namespace Oro\Bundle\CustomerBundle\Datagrid\Extension;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\GridView;
use Oro\Bundle\DataGridBundle\Entity\Repository\GridViewRepository;
use Oro\Bundle\DataGridBundle\Extension\Columns\ColumnsExtension as BaseColumnsExtension;
use Oro\Bundle\DataGridBundle\Tools\ColumnsHelper;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Extension inherited from base ColumnExtension and allows to use it on front store
 */
class ColumnsExtension extends BaseColumnsExtension
{
    /**
     * @var FrontendHelper
     */
    private $frontendHelper;

    /**
     * @param ManagerRegistry $registry
     * @param TokenAccessorInterface $tokenAccessor
     * @param AclHelper $aclHelper
     * @param ColumnsHelper $columnsHelper
     * @param FrontendHelper $frontendHelper
     */
    public function __construct(
        ManagerRegistry $registry,
        TokenAccessorInterface $tokenAccessor,
        AclHelper $aclHelper,
        ColumnsHelper $columnsHelper,
        FrontendHelper $frontendHelper
    ) {
        parent::__construct($registry, $tokenAccessor, $aclHelper, $columnsHelper);
        $this->frontendHelper = $frontendHelper;
    }

    /**
     * @return GridViewRepository
     */
    protected function getGridViewRepository()
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            return $this->registry->getRepository(GridView::class);
        }

        return parent::getGridViewRepository();
    }
}
