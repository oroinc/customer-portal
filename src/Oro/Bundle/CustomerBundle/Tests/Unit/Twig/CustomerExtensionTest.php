<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Twig;

use Oro\Bundle\CustomerBundle\Twig\CustomerExtension;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class CustomerExtensionTest extends \PHPUnit_Framework_TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var CustomerExtension */
    protected $extension;

    /** @var CustomerUserProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $securityProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->securityProvider = $this->getMockBuilder(CustomerUserProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = self::getContainerBuilder()
            ->add('oro_customer.security.customer_user_provider', $this->securityProvider)
            ->getContainer($this);

        $this->extension = new CustomerExtension($container);
    }

    public function testGetName()
    {
        $this->assertEquals(CustomerExtension::NAME, $this->extension->getName());
    }

    public function testIsGrantedViewCustomerUser()
    {
        $object = new \stdClass();

        $this->securityProvider->expects(self::once())
            ->method('isGrantedViewCustomerUser')
            ->with(self::identicalTo($object))
            ->willReturn(true);

        $this->assertTrue(
            self::callTwigFunction($this->extension, 'is_granted_view_customer_user', [$object])
        );
    }
}
