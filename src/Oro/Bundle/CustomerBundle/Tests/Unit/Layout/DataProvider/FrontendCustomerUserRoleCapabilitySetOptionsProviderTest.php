<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserRoleCapabilitySetOptionsProvider;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCapabilityProvider;
use Oro\Bundle\UserBundle\Provider\RolePrivilegeCategoryProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FrontendCustomerUserRoleCapabilitySetOptionsProviderTest extends TestCase
{
    private RolePrivilegeCapabilityProvider&MockObject $capabilityProvider;
    private RolePrivilegeCategoryProvider&MockObject $categoryProvider;
    private FrontendCustomerUserRoleCapabilitySetOptionsProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->capabilityProvider = $this->createMock(RolePrivilegeCapabilityProvider::class);
        $this->categoryProvider = $this->createMock(RolePrivilegeCategoryProvider::class);

        $this->provider = new FrontendCustomerUserRoleCapabilitySetOptionsProvider(
            $this->capabilityProvider,
            $this->categoryProvider
        );
    }

    public function testGetCapabilitySetOptions(): void
    {
        $role = $this->createMock(CustomerUserRole::class);
        $capabilities = ['capabilities_data'];
        $tabIds = ['test_tab'];

        $this->capabilityProvider->expects(self::once())
            ->method('getCapabilities')
            ->with(self::identicalTo($role))
            ->willReturn($capabilities);
        $this->categoryProvider->expects(self::once())
            ->method('getTabIds')
            ->willReturn($tabIds);

        $options = $this->provider->getOptions($role);

        $expected = ['data' => $capabilities, 'tabIds' => $tabIds];

        self::assertEquals($expected, $options);
        // test local cache
        self::assertEquals($expected, $this->provider->getOptions($role));
    }
}
