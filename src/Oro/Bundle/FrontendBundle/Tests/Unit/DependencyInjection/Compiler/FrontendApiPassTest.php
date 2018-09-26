<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\FrontendBundle\DependencyInjection\Compiler\FrontendApiPass;
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
     * @param string $serviceId
     *
     * @return Definition
     */
    private function registerProcessor($serviceId)
    {
        $definition = $this->container->setDefinition($serviceId, new Definition());
        $definition->addTag('oro.api.processor', []);

        return $definition;
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage non-existent service "oro_api.collect_resources.load_dictionaries"
     */
    public function testProcessWhenLoadDictionariesProcessorDoesNotExist()
    {
        $this->registerProcessor('oro_api.collect_resources.load_custom_entities');
        $this->registerProcessor('oro_api.options.rest.set_cache_control');
        $this->registerProcessor('oro_api.rest.cors.set_allow_origin');
        $this->registerProcessor('oro_api.rest.cors.set_allow_and_expose_headers');
        $this->registerProcessor('oro_api.options.rest.cors.set_max_age');

        $this->compilerPass->process($this->container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage non-existent service "oro_api.collect_resources.load_custom_entities"
     */
    public function testProcessWhenLoadCustomEntitiesProcessorDoesNotExist()
    {
        $this->registerProcessor('oro_api.collect_resources.load_dictionaries');
        $this->registerProcessor('oro_api.options.rest.set_cache_control');
        $this->registerProcessor('oro_api.rest.cors.set_allow_origin');
        $this->registerProcessor('oro_api.rest.cors.set_allow_and_expose_headers');
        $this->registerProcessor('oro_api.options.rest.cors.set_max_age');

        $this->compilerPass->process($this->container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage non-existent service "oro_api.options.rest.set_cache_control"
     */
    public function testProcessWhenSetCacheControlProcessorDoesNotExist()
    {
        $this->registerProcessor('oro_api.collect_resources.load_dictionaries');
        $this->registerProcessor('oro_api.collect_resources.load_custom_entities');
        $this->registerProcessor('oro_api.rest.cors.set_allow_origin');
        $this->registerProcessor('oro_api.rest.cors.set_allow_and_expose_headers');
        $this->registerProcessor('oro_api.options.rest.cors.set_max_age');

        $this->compilerPass->process($this->container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage non-existent service "oro_api.rest.cors.set_allow_origin"
     */
    public function testProcessWhenSetAllowOriginProcessorDoesNotExist()
    {
        $this->registerProcessor('oro_api.collect_resources.load_dictionaries');
        $this->registerProcessor('oro_api.collect_resources.load_custom_entities');
        $this->registerProcessor('oro_api.options.rest.set_cache_control');
        $this->registerProcessor('oro_api.rest.cors.set_allow_and_expose_headers');
        $this->registerProcessor('oro_api.options.rest.cors.set_max_age');

        $this->compilerPass->process($this->container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage non-existent service "oro_api.rest.cors.set_allow_and_expose_headers"
     */
    public function testProcessWhenSetAllowAndExposeHeadersProcessorDoesNotExist()
    {
        $this->registerProcessor('oro_api.collect_resources.load_dictionaries');
        $this->registerProcessor('oro_api.collect_resources.load_custom_entities');
        $this->registerProcessor('oro_api.options.rest.set_cache_control');
        $this->registerProcessor('oro_api.rest.cors.set_allow_origin');
        $this->registerProcessor('oro_api.options.rest.cors.set_max_age');

        $this->compilerPass->process($this->container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage non-existent service "oro_api.options.rest.cors.set_max_age"
     */
    public function testProcessWhenSetMaxAgeProcessorDoesNotExist()
    {
        $this->registerProcessor('oro_api.collect_resources.load_dictionaries');
        $this->registerProcessor('oro_api.collect_resources.load_custom_entities');
        $this->registerProcessor('oro_api.options.rest.set_cache_control');
        $this->registerProcessor('oro_api.rest.cors.set_allow_origin');
        $this->registerProcessor('oro_api.rest.cors.set_allow_and_expose_headers');

        $this->compilerPass->process($this->container);
    }

    public function testProcessWhenAllProcessorsExist()
    {
        $loadDictionariesDefinition = $this->registerProcessor('oro_api.collect_resources.load_dictionaries');
        $loadCustomEntitiesDefinition = $this->registerProcessor('oro_api.collect_resources.load_custom_entities');
        $setCacheControlDefinition = $this->registerProcessor('oro_api.options.rest.set_cache_control');
        $setAllowOriginDefinition = $this->registerProcessor('oro_api.rest.cors.set_allow_origin');
        $setAllowAndExposeHeadersDefinition = $this
            ->registerProcessor('oro_api.rest.cors.set_allow_and_expose_headers');
        $setMaxAgeDefinition = $this->registerProcessor('oro_api.options.rest.cors.set_max_age');

        $this->compilerPass->process($this->container);

        self::assertEquals(
            [['requestType' => '!frontend']],
            $loadDictionariesDefinition->getTag('oro.api.processor')
        );
        self::assertEquals(
            [['requestType' => '!frontend']],
            $loadCustomEntitiesDefinition->getTag('oro.api.processor')
        );
        self::assertEquals(
            [['requestType' => '!frontend']],
            $setCacheControlDefinition->getTag('oro.api.processor')
        );
        self::assertEquals(
            [['requestType' => '!frontend']],
            $setAllowOriginDefinition->getTag('oro.api.processor')
        );
        self::assertEquals(
            [['requestType' => '!frontend']],
            $setAllowAndExposeHeadersDefinition->getTag('oro.api.processor')
        );
        self::assertEquals(
            [['requestType' => '!frontend']],
            $setMaxAgeDefinition->getTag('oro.api.processor')
        );
    }
}
