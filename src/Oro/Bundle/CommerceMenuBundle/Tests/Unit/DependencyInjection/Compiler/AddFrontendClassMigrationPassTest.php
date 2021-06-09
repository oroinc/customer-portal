<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CommerceMenuBundle\DependencyInjection\Compiler\AddFrontendClassMigrationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddFrontendClassMigrationPassTest extends \PHPUnit\Framework\TestCase
{
    /** @var AddFrontendClassMigrationPass */
    private $compiler;

    protected function setUp(): void
    {
        $this->compiler = new AddFrontendClassMigrationPass();
    }

    public function testProcess()
    {
        $container = new ContainerBuilder();
        $migrationServiceDef = $container->register('oro_frontend.class_migration');

        $this->compiler->process($container);

        self::assertEquals(
            [
                ['append', ['FrontendNavigation', 'CommerceMenu']],
                ['append', ['frontendnavigation', 'commercemenu']],
            ],
            $migrationServiceDef->getMethodCalls()
        );
    }

    public function testProcessNoMigrationService()
    {
        $container = new ContainerBuilder();

        $this->compiler->process($container);
    }
}
