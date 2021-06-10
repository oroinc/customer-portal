<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\DependencyInjection\CompilerPass;

use Oro\Bundle\WebsiteBundle\DependencyInjection\CompilerPass\AssetsRouterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AssetsRouterPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $assetRouterDef = $container->register('oro_website.asset.router')
            ->setArguments([null, null, null, null]);
        $urlGeneratorDef = $container->register('oro_attachment.url_generator')
            ->setArguments([null]);
        $cacheManagerDef = $container->register('liip_imagine.cache.manager')
            ->setArguments([null, null]);
        $cacheResolverDef = $container->register('liip_imagine.cache.resolver.default')
            ->setArguments([null, null]);
        $consumptionExtensionDef = $container->register('oro_ui.consumption_extension.request_context')
            ->setArguments([null]);

        $compiler = new AssetsRouterPass();
        $compiler->process($container);

        self::assertEquals(
            new Reference('oro_website.asset.request_context'),
            $assetRouterDef->getArgument(3)
        );
        self::assertEquals(
            new Reference('oro_website.asset.router'),
            $urlGeneratorDef->getArgument(0)
        );
        self::assertEquals(
            new Reference('oro_website.asset.router'),
            $cacheManagerDef->getArgument(1)
        );
        self::assertEquals(
            new Reference('oro_website.asset.request_context'),
            $cacheResolverDef->getArgument(1)
        );
        self::assertEquals(
            new Reference('oro_website.asset.request_context'),
            $consumptionExtensionDef->getArgument(0)
        );
    }
}
