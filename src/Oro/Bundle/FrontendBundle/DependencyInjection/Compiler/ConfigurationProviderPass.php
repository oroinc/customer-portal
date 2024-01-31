<?php

namespace Oro\Bundle\FrontendBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configures theme aware services. Configures service argument which rely on PHP defined constants.
 */
class ConfigurationProviderPass implements CompilerPassInterface
{
    private const THEME_AWARE_CONFIG_PROVIDER = 'oro_frontend.configuration.theme_aware_provider.raw';
    private const FOLDER_PATTERN = '[a-zA-Z][a-zA-Z0-9_\-:]*';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $def = $container->getDefinition(self::THEME_AWARE_CONFIG_PROVIDER);
        $def->setArgument('$folderPattern', self::FOLDER_PATTERN);
    }
}
