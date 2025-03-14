<?php

namespace Oro\Bundle\CustomerBundle\Doctrine;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

/**
 * Enables {@see SoftDeleteableFilter} for storefront queries.
 */
class DoctrineFiltersListener
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private FrontendHelper $frontendHelper
    ) {
    }

    public function onRequest(): void
    {
        if ($this->frontendHelper->isFrontendRequest()) {
            $em = $this->doctrine->getManager();
            /** @var SoftDeleteableFilter $filter */
            $filter = $em->getFilters()->enable(SoftDeleteableFilter::FILTER_ID);
            $filter->setEm($em);
        }
    }
}
