<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit;

use Oro\Bundle\WebsiteBundle\DependencyInjection\CompilerPass\AssetsRouterPass;
use Oro\Bundle\WebsiteBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass;
use Oro\Bundle\WebsiteBundle\OroWebsiteBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroWebsiteBundleTest extends \PHPUnit\Framework\TestCase
{
    public function testBuild()
    {
        /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects($this->at(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(TwigSandboxConfigurationPass::class));
        $container->expects($this->at(1))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(AssetsRouterPass::class));

        $bundle = new OroWebsiteBundle();
        $bundle->build($container);
    }
}
