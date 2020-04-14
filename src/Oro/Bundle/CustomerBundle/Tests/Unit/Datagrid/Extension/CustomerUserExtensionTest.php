<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Datagrid\Extension;

use Oro\Bundle\CustomerBundle\Datagrid\Extension\CustomerUserExtension;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;

class CustomerUserExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $tokenAccessor;

    /** @var CustomerUserExtension */
    protected $extension;

    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->extension = new CustomerUserExtension($this->tokenAccessor);
        $this->extension->setParameters(new ParameterBag());
    }

    /**
     * @param mixed $user
     * @param bool $expected
     *
     * @dataProvider applicableDataProvider
     */
    public function testIsApplicable($user, $expected)
    {
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertEquals($expected, $this->extension->isApplicable(DatagridConfiguration::create([])));
    }

    public function testProcessConfigs()
    {
        $config = DatagridConfiguration::create([]);
        $this->extension->processConfigs($config);

        $this->assertEquals(CustomerUserExtension::ROUTE, $config->offsetGetByPath('[options][route]'));
    }

    /**
     * @return array
     */
    public function applicableDataProvider()
    {
        return [
            [new User(), false],
            [null, true],
            [new CustomerUser(), true],
        ];
    }
}
