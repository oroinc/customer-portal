<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\WebsiteBundle\DependencyInjection\OroWebsiteExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroWebsiteExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();

        $extension = new OroWebsiteExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
        self::assertSame(
            [
                [
                    'settings' => [
                        'resolved' => true,
                        'url' => ['value' => '', 'scope' => 'app'],
                        'secure_url' => ['value' => '', 'scope' => 'app'],
                    ]
                ]
            ],
            $container->getExtensionConfig('oro_website')
        );
    }
}
