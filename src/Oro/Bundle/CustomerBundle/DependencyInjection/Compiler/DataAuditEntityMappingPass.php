<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\Entity\Audit;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataAuditBundle\Entity\AuditField;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Registers dataaudit mapping for actions performed by customer users.
 */
class DataAuditEntityMappingPass implements CompilerPassInterface
{
    const MAPPER_SERVICE = 'oro_dataaudit.loggable.audit_entity_mapper';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::MAPPER_SERVICE)) {
            return;
        }

        $mapperDefinition = $container->getDefinition(self::MAPPER_SERVICE);
        $mapperDefinition->addMethodCall(
            'addAuditEntryClasses',
            [
                CustomerUser::class,
                Audit::class,
                AuditField::class,
            ]
        );
    }
}
