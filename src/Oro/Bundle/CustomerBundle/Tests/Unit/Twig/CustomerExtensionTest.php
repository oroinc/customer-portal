<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Twig;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Bundle\CustomerBundle\Twig\CustomerExtension;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class CustomerExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;
    use EntityTrait;

    /** @var CustomerUserProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $securityProvider;

    /** @var CustomerExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->securityProvider = $this->createMock(CustomerUserProvider::class);

        $container = $this->getContainerBuilder()
            ->add('oro_customer.security.customer_user_provider', $this->securityProvider)
            ->getContainer($this);

        $this->extension = new CustomerExtension($container);
    }

    public function testIsGrantedViewCustomerUser()
    {
        $object = new \stdClass();

        $this->securityProvider->expects($this->once())
            ->method('isGrantedViewCustomerUser')
            ->with($this->identicalTo($object))
            ->willReturn(true);

        $this->assertTrue(
            $this->callTwigFunction($this->extension, 'is_granted_view_customer_user', [$object])
        );
    }

    public function testGetCustomerParentParst()
    {
        $rootParent = $this->getEntity(Customer::class, ['id' => 111, 'name' => 'rootParent']);
        $parent = $this->getEntity(Customer::class, ['id' => 333, 'name' => 'parent', 'parent' => $rootParent]);
        $customer = $this->getEntity(Customer::class, ['id' => 777, 'name' => 'child', 'parent' => $parent]);
        $expected = [
            [
                'id' => 111,
                'name' => 'rootParent'
            ],
            [
                'id' => 333,
                'name' => 'parent'
            ],
        ];

        $this->assertEquals(
            $expected,
            $this->callTwigFunction($this->extension, 'oro_customer_parent_parts', [$customer])
        );
    }
}
