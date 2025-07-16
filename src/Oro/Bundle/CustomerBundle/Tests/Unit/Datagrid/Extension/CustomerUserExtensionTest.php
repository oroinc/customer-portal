<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid\Extension;

use Oro\Bundle\CustomerBundle\Datagrid\Extension\CustomerUserExtension;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerUserExtensionTest extends TestCase
{
    private TokenAccessorInterface&MockObject $tokenAccessor;
    private CustomerUserExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->extension = new CustomerUserExtension($this->tokenAccessor);
        $this->extension->setParameters(new ParameterBag());
    }

    /**
     * @dataProvider applicableDataProvider
     */
    public function testIsApplicable(?object $user, bool $expected): void
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals($expected, $this->extension->isApplicable(DatagridConfiguration::create([])));
    }

    public function testProcessConfigs(): void
    {
        $config = DatagridConfiguration::create([]);
        $this->extension->processConfigs($config);

        $this->assertEquals(CustomerUserExtension::ROUTE, $config->offsetGetByPath('[options][route]'));
    }

    public function applicableDataProvider(): array
    {
        return [
            [new User(), false],
            [null, true],
            [new CustomerUser(), true],
        ];
    }
}
