<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerGroupCriteriaProvider;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;

/**
 * Adds the current customer group and the default website to the customer group menu context.
 */
class CustomerGroupMenuContextProvider implements CustomerGroupMenuContextProviderInterface
{
    /** @var WebsiteManager */
    protected $websiteManager;

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
                ScopeCustomerGroupCriteriaProvider::CUSTOMER_GROUP => $customerGroup->getId(),
                ScopeCriteriaProvider::WEBSITE => $this->websiteManager->getDefaultWebsite()->getId()
            ]
        ];
    }
}
