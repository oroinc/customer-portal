<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;

class CustomerGroupMenuContextProvider implements CustomerGroupMenuContextProviderInterface
{
    /** @var WebsiteManager */
    protected $websiteManager;

    /**
     * @param WebsiteManager $websiteManager
     */
    public function __construct(WebsiteManager $websiteManager)
    {
        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getContexts(CustomerGroup $customerGroup)
    {
        return [
            [
                ScopeCustomerGroupCriteriaProvider::FIELD_NAME => $customerGroup->getId(),
                ScopeCriteriaProvider::WEBSITE => $this->websiteManager->getDefaultWebsite()->getId()
            ]
        ];
    }
}
