<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit;

use Oro\Bundle\WebsiteBundle\OroWebsiteBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroWebsiteBundleTest extends \PHPUnit\Framework\TestCase
{
    public function testBuild()
    {
        $twigSandboxConfigurationPass =
            'Oro\Bundle\WebsiteBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass';

        /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects($this->at(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf($twigSandboxConfigurationPass))
            ->willReturn(false);

        $bundle = new OroWebsiteBundle();
        $bundle->build($container);
    }
}
