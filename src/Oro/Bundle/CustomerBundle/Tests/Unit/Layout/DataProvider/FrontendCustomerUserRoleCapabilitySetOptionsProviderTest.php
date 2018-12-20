<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserRoleCapabilitySetOptionsProvider;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCapabilityProvider;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider;
use Oro\Component\Testing\Unit\EntityTrait;

class FrontendCustomerUserRoleCapabilitySetOptionsProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var RolePrivilegeCapabilityProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    private $capabilityProvider;

    /**
     * @var RolePrivilegeCategoryProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    private $categoryProvider;

    /**
     * @var FrontendCustomerUserRoleCapabilitySetOptionsProvider
     */
    private $provider;

    /**
     * @var CustomerUserRole
     */
    private $role;

    protected function setUp()
    {
        $this->capabilityProvider = $this->createMock(RolePrivilegeCapabilityProvider::class);
        $this->categoryProvider = $this->createMock(RolePrivilegeCategoryProvider::class);
        $this->role = $this->getEntity(CustomerUserRole::class);

        $this->provider = new FrontendCustomerUserRoleCapabilitySetOptionsProvider(
            $this->capabilityProvider,
            $this->categoryProvider
        );
    }

    public function testGetCapabilitySetOptions()
    {
        $this->capabilityProvider->expects(static::once())
            ->method('getCapabilities')
            ->with($this->role)
            ->willReturn(['capabilities_data']);

        $this->categoryProvider->expects(static::once())
            ->method('getTabList')
            ->willReturn(['tab_list_data']);

        $firstResult = $this->provider->getOptions($this->role);

        $this->assertArrayHasKey('data', $firstResult);
        $this->assertArrayHasKey('tabIds', $firstResult);

        $this->assertEquals(['capabilities_data'], $firstResult['data']);
        $this->assertEquals(['tab_list_data'], $firstResult['tabIds']);

        //expected result from cache
        $secondResult = $this->provider->getOptions($this->role);
        $this->assertEquals($secondResult, $firstResult);
    }
}
