<?php

namespace Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Provider\ScopeCustomerCriteriaProvider;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Provider\ScopeCriteriaProvider;

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
                ScopeCustomerCriteriaProvider::ACCOUNT => $customer->getId(),
                ScopeCriteriaProvider::WEBSITE => $this->websiteManager->getDefaultWebsite()->getId()
            ]
        ];
    }
}
