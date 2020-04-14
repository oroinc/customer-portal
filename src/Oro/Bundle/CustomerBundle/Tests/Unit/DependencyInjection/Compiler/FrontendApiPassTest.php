<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\FrontendApiPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class FrontendApiPassTest extends \PHPUnit\Framework\TestCase
{
    private const PROCESSORS = [
        'oro_organization.api.config.add_owner_validator'
    ];

    /** @var ContainerBuilder */
    private $container;

    /** @var FrontendApiPass */
    private $compilerPass;

    protected function setUp(): void
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
     * @param string|null $serviceIdToBeSkipped
     *
     * @return Definition[]
     */
    private function registerProcessors($serviceIdToBeSkipped = null)
    {
        $definitions = [];
        foreach (self::PROCESSORS as $serviceId) {
            if ($serviceIdToBeSkipped && $serviceId === $serviceIdToBeSkipped) {
                continue;
            }
            $definitions[] = $this->registerProcessor($serviceId);
        }

        return $definitions;
    }

    /**
     * @dataProvider processorsDataProvider
     */
    public function testProcessWhenSomeProcessorDoesNotExist($processorServiceId)
    {
        $this->registerProcessors($processorServiceId);

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('non-existent service "%s"', $processorServiceId));

        $this->compilerPass->process($this->container);
    }

    public function processorsDataProvider()
    {
        return array_map(
            function ($serviceId) {
                return [$serviceId];
            },
            self::PROCESSORS
        );
    }

    public function testProcessWhenAllProcessorsExist()
    {
        $definitions = $this->registerProcessors();

        $this->compilerPass->process($this->container);

        foreach ($definitions as $definition) {
            self::assertEquals(
                [['requestType' => '!frontend']],
                $definition->getTag('oro.api.processor')
            );
        }
    }
}
