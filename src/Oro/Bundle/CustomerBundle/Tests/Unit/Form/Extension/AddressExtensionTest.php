<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Oro\Bundle\CustomerBundle\Form\Extension\AddressExtension;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var AddressExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->extension = new AddressExtension($this->frontendHelper);
    }

    public function testGetExtendedTypes()
    {
        $this->assertEquals([AddressType::class], AddressExtension::getExtendedTypes());
    }

    public function testConfigureOptionsForBackend()
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->never())
            ->method($this->anything());

        $this->extension->configureOptions($resolver);
    }

    public function testConfigureOptionsForFrontend()
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefault')
            ->with('region_route', 'oro_api_frontend_country_get_regions');

        $this->extension->configureOptions($resolver);
    }
}
