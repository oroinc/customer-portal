<?php

namespace Oro\Bundle\CustomerBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

class DoctrineFiltersListener
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var FrontendHelper
     */
    protected $frontendHelper;

    /**
     * @param ManagerRegistry $registry
     * @param FrontendHelper $frontendHelper
     */
    public function __construct(ManagerRegistry $registry, FrontendHelper $frontendHelper)
    {
        $this->registry = $registry;
        $this->frontendHelper = $frontendHelper;
    }

    public function onRequest()
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            $filters = $this->getEntityManager()->getFilters();
            /** @var SoftDeleteableFilter $filter */
            $filter = $filters->enable(SoftDeleteableFilter::FILTER_ID);
            $filter->setEm($this->getEntityManager());
        }
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->registry->getManager();
        }

        return $this->em;
    }
}
