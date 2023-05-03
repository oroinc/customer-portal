<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\CommerceMenuBundle\DependencyInjection\OroCommerceMenuExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroCommerceMenuExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();

        $extension = new OroCommerceMenuExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
        self::assertSame(
            [
                [
                    'settings' => [
                        'resolved' => true,
                        'main_navigation_menu' => ['value' => 'commerce_main_menu', 'scope' => 'app']
                    ]
                ]
            ],
            $container->getExtensionConfig('oro_commerce_menu')
        );
    }
}
