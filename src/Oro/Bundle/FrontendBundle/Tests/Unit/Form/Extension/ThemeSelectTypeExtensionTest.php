<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\FrontendBundle\Form\Extension\ThemeSelectTypeExtension;
use Oro\Bundle\ThemeBundle\Form\Type\ThemeSelectType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ThemeSelectTypeExtensionTest extends TestCase
{
    private ThemeSelectTypeExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->extension = new ThemeSelectTypeExtension();
    }

    public function testGetExtendedTypes(): void
    {
        self::assertEquals([ThemeSelectType::class], $this->extension::getExtendedTypes());
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();

        $this->extension->configureOptions($resolver);

        $options = $resolver->resolve();

        self::assertArrayHasKey('theme_group', $options);
        self::assertEquals('commerce', $options['theme_group']);
    }
}
