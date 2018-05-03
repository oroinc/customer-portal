<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit;

use Oro\Bundle\ApiBundle\DependencyInjection\Compiler\ProcessorBagCompilerPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\DataAuditEntityMappingPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\FrontendApiPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\LoginManagerPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\OwnerTreeListenerPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\WindowsStateManagerPass;
use Oro\Bundle\CustomerBundle\DependencyInjection\OroCustomerExtension;
use Oro\Bundle\CustomerBundle\DependencyInjection\Security\AnonymousCustomerUserFactory;
use Oro\Bundle\CustomerBundle\OroCustomerBundle;
use Oro\Component\DependencyInjection\ExtendedContainerBuilder;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;

class OroCustomerBundleTest extends \PHPUnit_Framework_TestCase
{
    /** @var OroCustomerBundle */
    private $bundle;

    protected function setUp()
    {
        $this->bundle = new OroCustomerBundle();
    }

    public function testBuild()
    {
        $container = $this->createMock(ExtendedContainerBuilder::class);
        $securityExtension = $this->createMock(SecurityExtension::class);

        $container->expects(self::at(0))
            ->method('addCompilerPass')
            ->with(self::isInstanceOf(OwnerTreeListenerPass::class));
        $container->expects(self::at(1))
            ->method('addCompilerPass')
            ->with(self::isInstanceOf(DataAuditEntityMappingPass::class));
        $container->expects(self::at(2))
            ->method('addCompilerPass')
            ->with(self::isInstanceOf(WindowsStateManagerPass::class));
        $container->expects(self::at(3))
            ->method('addCompilerPass')
            ->with(self::isInstanceOf(LoginManagerPass::class));
        $container->expects(self::at(4))
            ->method('addCompilerPass')
            ->with(self::isInstanceOf(FrontendApiPass::class));

        $container->expects(self::once())
            ->method('moveCompilerPassBefore')
            ->with(FrontendApiPass::class, ProcessorBagCompilerPass::class);

        $container->expects(self::once())
            ->method('getExtension')
            ->with('security')
            ->willReturn($securityExtension);
        $securityExtension->expects(self::once())
            ->method('addSecurityListenerFactory')
            ->with(self::isInstanceOf(AnonymousCustomerUserFactory::class));

        $this->bundle->build($container);
    }

    public function testGetContainerExtension()
    {
        self::assertInstanceOf(OroCustomerExtension::class, $this->bundle->getContainerExtension());
    }
}
