<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\DataAuditEntityMappingPass;
use Oro\Bundle\CustomerBundle\Entity\Audit;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataAuditBundle\Entity\AuditField;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DataAuditEntityMappingPassTest extends \PHPUnit\Framework\TestCase
{
    /** @var DataAuditEntityMappingPass */
    private $compiler;

    protected function setUp(): void
    {
        $this->compiler = new DataAuditEntityMappingPass();
    }

    public function testProcessWithoutDefinition()
    {
        $container = new ContainerBuilder();

        $this->compiler->process($container);
    }

    public function testProcess()
    {
        $container = new ContainerBuilder();
        $mapperDef = $container->register('oro_dataaudit.loggable.audit_entity_mapper');

        $this->compiler->process($container);

        self::assertEquals(
            [
                ['addAuditEntryClasses', [CustomerUser::class, Audit::class, AuditField::class]]
            ],
            $mapperDef->getMethodCalls()
        );
    }
}
