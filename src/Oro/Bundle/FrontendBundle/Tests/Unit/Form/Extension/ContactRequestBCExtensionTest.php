<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\FrontendBundle\Form\Extension\ContactRequestBCExtension;
use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ContactRequestBCExtensionTest extends TestCase
{
    private $currentThemeProvider;
    private $themeManager;
    private $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->currentThemeProvider = $this->createMock(CurrentThemeProvider::class);
        $this->themeManager = $this->createMock(ThemeManager::class);
        $this->extension = new ContactRequestBCExtension($this->currentThemeProvider, $this->themeManager);
    }

    public function testBuildFormWithoutCustomerName(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())
            ->method('has')
            ->with('customerName')
            ->willReturn(false);

        $this->themeManager->expects(self::never())
            ->method('themeHasParent');

        $this->extension->buildForm($builder, []);
    }

    public function testBuildFormWithUnsupportedTheme(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())
            ->method('has')
            ->with('customerName')
            ->willReturn(true);

        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn('test_theme');

        $this->themeManager->expects(self::once())
            ->method('themeHasParent')
            ->willReturn(false);

        $builder->expects(self::never())
            ->method('remove');

        $this->extension->buildForm($builder, []);
    }

    public function testFormWithSupportedThemeRemovesCustomerNameAndAddsOrganizationName(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())
            ->method('has')
            ->with('customerName')
            ->willReturn(true);

        $this->currentThemeProvider->expects(self::once())
            ->method('getCurrentThemeId')
            ->willReturn('test_theme');

        $this->themeManager->expects(self::once())
            ->method('themeHasParent')
            ->willReturn(true);

        $builder->expects(self::once())
            ->method('remove')
            ->with('customerName');

        $called = false;
        $builder->expects(self::once())
            ->method('add')
            ->willReturnCallback(function ($name, $type, $options) use ($builder, &$called) {
                $called = true;
                self::assertEquals('organizationName', $name);
                self::assertEquals(TextType::class, $type);
                self::assertArrayHasKey('property_path', $options);
                self::assertEquals('customerName', $options['property_path']);

                return $builder;
            });


        $this->extension->buildForm($builder, []);
        self::assertTrue($called);
    }
}
