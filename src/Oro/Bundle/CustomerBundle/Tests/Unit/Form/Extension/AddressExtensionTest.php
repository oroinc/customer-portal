<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Oro\Bundle\CustomerBundle\Form\Extension\AddressExtension;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressExtensionTest extends AbstractCustomerUserAwareExtensionTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->extension = new AddressExtension($this->tokenStorage);
    }

    public function testGetExtendedType()
    {
        $this->assertEquals(AddressType::class, $this->extension->getExtendedType());
    }

    public function testConfigureOptionsNonCustomerUser()
    {
        $this->assertOptionsNotChangedForNonCustomerUser();
    }

    public function testConfigureOptionsCustomerUser()
    {
        $this->assertCustomerUserTokenCall();

        /** @var \PHPUnit\Framework\MockObject\MockObject|OptionsResolver $resolver */
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->once())
            ->method('setDefault')
            ->with('region_route', 'oro_api_frontend_country_get_regions');

        $this->extension->configureOptions($resolver);
    }

    public function testConfigureOptionsCustomerVisitor()
    {
        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        /** @var \PHPUnit\Framework\MockObject\MockObject|OptionsResolver $resolver */
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->once())
            ->method('setDefault')
            ->with('region_route', 'oro_api_frontend_country_get_regions');

        $this->extension->configureOptions($resolver);
    }
}
