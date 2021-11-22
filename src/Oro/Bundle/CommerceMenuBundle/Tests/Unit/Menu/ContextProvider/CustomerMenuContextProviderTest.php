<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Menu\ContextProvider;

use Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider\CustomerMenuContextProvider;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\Unit\EntityTrait;

class CustomerMenuContextProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    public function testGetContexts()
    {
        $website = $this->getEntity(Website::class, ['id' => 1]);
        $websiteManager = $this->createMock(WebsiteManager::class);
        $websiteManager->expects($this->once())
            ->method('getDefaultWebsite')
            ->willReturn($website);

        /** @var Customer $customer */
        $customer = $this->getEntity(Customer::class, ['id' => 5]);
        $provider = new CustomerMenuContextProvider($websiteManager);

        $this->assertEquals([['customer' => 5, 'website' => 1]], $provider->getContexts($customer));
    }
}
