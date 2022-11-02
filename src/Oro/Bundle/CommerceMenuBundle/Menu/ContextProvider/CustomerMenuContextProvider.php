<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerCriteriaProvider;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;

/**
 * Adds the current customer and the default website to the customer menu context.
 */
class CustomerMenuContextProvider implements CustomerMenuContextProviderInterface
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
    public function getContexts(Customer $customer)
    {
        return [
            [
                ScopeCustomerCriteriaProvider::CUSTOMER => $customer->getId(),
                ScopeCriteriaProvider::WEBSITE => $this->websiteManager->getDefaultWebsite()->getId()
            ]
        ];
    }
}
