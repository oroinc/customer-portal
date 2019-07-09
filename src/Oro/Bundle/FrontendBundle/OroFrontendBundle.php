<?php

namespace Oro\Bundle\FrontendBundle;

use Oro\Bundle\ApiBundle\DependencyInjection\Compiler\ProcessorBagCompilerPass;
use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendApiDocPass;
use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendApiPass;
use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendDebugRoutesPass;
use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendSessionPass;
use Oro\Bundle\FrontendBundle\DependencyInjection\OroFrontendExtension;
use Oro\Component\DependencyInjection\ExtendedContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The FrontendBundle bundle class.
 */
class OroFrontendBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FrontendDebugRoutesPass());
        $container->addCompilerPass(new FrontendSessionPass());
        if ($container instanceof ExtendedContainerBuilder) {
            $container->addCompilerPass(new FrontendApiPass());
            $container->moveCompilerPassBefore(FrontendApiPass::class, ProcessorBagCompilerPass::class);
            $container->addCompilerPass(new FrontendApiDocPass(), PassConfig::TYPE_BEFORE_REMOVING);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new OroFrontendExtension();
        }

        return $this->extension;
    }
}
