<?php

namespace Oro\Bundle\CommerceMenuBundle;

use Oro\Bundle\CommerceMenuBundle\DependencyInjection\Compiler\AddFrontendClassMigrationPass;
use Oro\Bundle\CommerceMenuBundle\DependencyInjection\Compiler\ConditionExpressionLanguageProvidersCompilerPass;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\LocaleBundle\DependencyInjection\Compiler\DefaultFallbackExtensionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroCommerceMenuBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddFrontendClassMigrationPass());
        $container->addCompilerPass(new ConditionExpressionLanguageProvidersCompilerPass());
        $container->addCompilerPass(
            new DefaultFallbackExtensionPass([
                MenuUpdate::class => [
                    'title' => 'titles',
                    'description' => 'descriptions',
                ]
            ])
        );
    }
}
