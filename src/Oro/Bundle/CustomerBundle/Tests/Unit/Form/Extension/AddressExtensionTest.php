<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Oro\Bundle\CustomerBundle\Form\Extension\AddressExtension;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressExtensionTest extends TestCase
{
    private FrontendHelper&MockObject $frontendHelper;
    private AddressExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->extension = new AddressExtension($this->frontendHelper);
    }

    public function testGetExtendedTypes(): void
    {
        $this->assertEquals([AddressType::class], AddressExtension::getExtendedTypes());
    }

    public function testConfigureOptionsForBackend(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->never())
            ->method($this->anything());

        $this->extension->configureOptions($resolver);
    }

    public function testConfigureOptionsForFrontend(): void
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
