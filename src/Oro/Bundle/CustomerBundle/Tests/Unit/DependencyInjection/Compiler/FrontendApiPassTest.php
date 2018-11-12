<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\FrontendApiPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class FrontendApiPassTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContainerBuilder */
    private $container;

    /** @var FrontendApiPass */
    private $compilerPass;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->compilerPass = new FrontendApiPass();
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage non-existent service "oro_organization.api.config.add_owner_validator"
     */
    public function testProcessWhenAddOwnerValidatorProcessorDoesNotExist()
    {
        $this->compilerPass->process($this->container);
    }

    public function testProcessWhenAddOwnerValidatorProcessorExists()
    {
        $definition = $this->container->setDefinition(
            'oro_organization.api.config.add_owner_validator',
            new Definition()
        );
        $definition->addTag('oro.api.processor', []);

        $this->compilerPass->process($this->container);

        self::assertEquals(
            [
                ['requestType' => '!frontend']
            ],
            $definition->getTag('oro.api.processor')
        );
    }
}
