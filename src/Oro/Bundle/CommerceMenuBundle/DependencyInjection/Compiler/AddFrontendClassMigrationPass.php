<?php

namespace Oro\Bundle\CommerceMenuBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass for registering frontend class migrations.
 *
 * This compiler pass registers class name migrations for the CommerceMenu bundle,
 * mapping legacy FrontendNavigation class names to the new CommerceMenu equivalents
 * in both camelCase and lowercase formats.
 */
class AddFrontendClassMigrationPass implements CompilerPassInterface
{
    public const FRONTEND_CLASS_MIGRATION_SERVICE_ID = 'oro_frontend.class_migration';

    #[\Override]
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition(self::FRONTEND_CLASS_MIGRATION_SERVICE_ID)) {
            $definition = $container->findDefinition(self::FRONTEND_CLASS_MIGRATION_SERVICE_ID);

            $definition->addMethodCall('append', ['FrontendNavigation', 'CommerceMenu']);
            $definition->addMethodCall('append', ['frontendnavigation', 'commercemenu']);
        }
    }
}
