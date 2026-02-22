<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Twig;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Bundle\CustomerBundle\Twig\CustomerExtension;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerExtensionTest extends TestCase
{
    use TwigExtensionTestCaseTrait;

    private CustomerUserProvider&MockObject $securityProvider;
    private CustomerExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->securityProvider = $this->createMock(CustomerUserProvider::class);

        $container = self::getContainerBuilder()
            ->add(CustomerUserProvider::class, $this->securityProvider)
            ->getContainer($this);

        $this->extension = new CustomerExtension($container);
    }

    private function getCustomer(int $id, string $name, ?Customer $parent = null): Customer
    {
        $customer = new Customer();
        ReflectionUtil::setId($customer, $id);
        $customer->setName($name);
        $customer->setParent($parent);

        return $customer;
    }

    public function testIsGrantedViewCustomerUser(): void
    {
        $object = new \stdClass();

        $this->securityProvider->expects($this->once())
            ->method('isGrantedViewCustomerUser')
            ->with($this->identicalTo($object))
            ->willReturn(true);

        $this->assertTrue(
            self::callTwigFunction($this->extension, 'is_granted_view_customer_user', [$object])
        );
    }

    public function testGetCustomerParentParts(): void
    {
        $rootParent = $this->getCustomer(111, 'rootParent');
        $parent = $this->getCustomer(333, 'parent', $rootParent);
        $customer = $this->getCustomer(777, 'child', $parent);

        $this->assertEquals(
            [
                ['id' => 111, 'name' => 'rootParent'],
                ['id' => 333, 'name' => 'parent']
            ],
            self::callTwigFunction($this->extension, 'oro_customer_parent_parts', [$customer])
        );
    }
}
