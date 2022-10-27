<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\CustomerBundle\Form\Extension\FrontendProductSelectExtension;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\ProductBundle\Form\Type\ProductSelectType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendProductSelectExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var FrontendProductSelectExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $this->extension = new FrontendProductSelectExtension($this->frontendHelper);
    }

    public function testGetExtendedTypes()
    {
        $this->assertEquals([ProductSelectType::class], FrontendProductSelectExtension::getExtendedTypes());
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
            ->with('grid_name', 'products-select-grid-frontend');

        $this->extension->configureOptions($resolver);
    }
}
