<?php

namespace Oro\Bundle\FrontendBundle;

use Oro\Bundle\ApiBundle\DependencyInjection\Compiler\ApiDocCompilerPass;
use Oro\Bundle\ApiBundle\DependencyInjection\Compiler\ApiTaggedServiceTrait;
use Oro\Bundle\ApiBundle\DependencyInjection\Compiler\ProcessorBagCompilerPass;
use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendApiDocPass;
use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendApiPass;
use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendCurrentApplicationProviderPass;
use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendDatagridTagsFeaturePass;
use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendDebugRoutesPass;
use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendSessionPass;
use Oro\Component\DependencyInjection\Compiler\PriorityNamedTaggedServiceWithHandlerCompilerPass;
use Oro\Component\DependencyInjection\ExtendedContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroFrontendBundle extends Bundle
{
    use ApiTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new FrontendDebugRoutesPass());
        $container->addCompilerPass(new FrontendSessionPass());
        $container->addCompilerPass(new FrontendCurrentApplicationProviderPass());
        $container->addCompilerPass(new FrontendDatagridTagsFeaturePass());
        $container->addCompilerPass(new PriorityNamedTaggedServiceWithHandlerCompilerPass(
            'oro_frontend.api.resource_type_resolver',
            'oro_frontend.api.resource_type_resolver',
            function (array $attributes, string $serviceId): array {
                return [
                    $serviceId,
                    $this->getAttribute($attributes, 'routeName'),
                    $this->getRequestTypeAttribute($attributes)
                ];
            }
        ));
        $container->addCompilerPass(new PriorityNamedTaggedServiceWithHandlerCompilerPass(
            'oro_frontend.api.resource_api_url_resolver',
            'oro_frontend.api.resource_api_url_resolver',
            function (array $attributes, string $serviceId): array {
                return [
                    $serviceId,
                    $this->getAttribute($attributes, 'routeName'),
                    $this->getRequestTypeAttribute($attributes)
                ];
            }
        ));
        if ($container instanceof ExtendedContainerBuilder) {
            $container->addCompilerPass(new FrontendApiPass());
            $container->moveCompilerPassBefore(FrontendApiPass::class, ProcessorBagCompilerPass::class);
            $container->addCompilerPass(new FrontendApiDocPass());
            $container->moveCompilerPassBefore(ApiDocCompilerPass::class, FrontendApiDocPass::class);
        }
    }
}
