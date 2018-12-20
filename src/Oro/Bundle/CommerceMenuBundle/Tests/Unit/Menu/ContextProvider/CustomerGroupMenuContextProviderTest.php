<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Menu\ContextProvider;

use Oro\Bundle\CommerceMenuBundle\Menu\ContextProvider\CustomerGroupMenuContextProvider;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\Testing\Unit\EntityTrait;

class CustomerGroupMenuContextProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    public function testGetContexts()
    {
        $website = $this->getEntity(Website::class, ['id' => 1]);
        $websiteManager = $this->createMock(WebsiteManager::class);
        $websiteManager->expects($this->once())
            ->method('getDefaultWebsite')
            ->willReturn($website);

        $customer = $this->getEntity(CustomerGroup::class, ['id' => 5]);
        $provider = new CustomerGroupMenuContextProvider($websiteManager);

        $this->assertEquals([['customerGroup' => 5, 'website' => 1]], $provider->getContexts($customer));
    }
}
