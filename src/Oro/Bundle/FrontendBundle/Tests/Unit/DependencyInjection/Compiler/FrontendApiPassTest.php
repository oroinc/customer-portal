<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendApiPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class FrontendApiPassTest extends \PHPUnit_Framework_TestCase
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
     * @expectedExceptionMessage non-existent service "oro_api.collect_resources.load_dictionaries"
     */
    public function testProcessWhenLoadDictionariesProcessorDoesNotExist()
    {
        $definition = $this->container->setDefinition(
            'oro_api.collect_resources.load_custom_entities',
            new Definition()
        );
        $definition->addTag('oro.api.processor', ['requestType' => 'json_api']);

        $this->compilerPass->process($this->container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage non-existent service "oro_api.collect_resources.load_custom_entities"
     */
    public function testProcessWhenLoadCustomEntitiesProcessorDoesNotExist()
    {
        $definition = $this->container->setDefinition(
            'oro_api.collect_resources.load_dictionaries',
            new Definition()
        );
        $definition->addTag('oro.api.processor', ['requestType' => 'json_api']);

        $this->compilerPass->process($this->container);
    }

    public function testProcessWhenAllProcessorsExist()
    {
        $loadDictionariesDefinition = $this->container->setDefinition(
            'oro_api.collect_resources.load_dictionaries',
            new Definition()
        );
        $loadDictionariesDefinition->addTag('oro.api.processor', []);

        $loadCustomEntitiesDefinition = $this->container->setDefinition(
            'oro_api.collect_resources.load_custom_entities',
            new Definition()
        );
        $loadCustomEntitiesDefinition->addTag('oro.api.processor', []);

        $this->compilerPass->process($this->container);

        self::assertEquals(
            [
                ['requestType' => '!frontend']
            ],
            $loadDictionariesDefinition->getTag('oro.api.processor')
        );
        self::assertEquals(
            [
                ['requestType' => '!frontend']
            ],
            $loadCustomEntitiesDefinition->getTag('oro.api.processor')
        );
    }
}
