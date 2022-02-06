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

    public function testProcessWhenAllProcessorsExist(): void
    {
        $container = new ContainerBuilder();
        $definitions = [];
        foreach (self::PROCESSORS as $serviceId) {
            $definitions[] = $container->register($serviceId)->addTag('oro.api.processor');
        }

        $compiler = new FrontendApiPass();
        $compiler->process($container);

        /** @var Definition $definition */
        foreach ($definitions as $definition) {
            self::assertEquals([['requestType' => '!frontend']], $definition->getTag('oro.api.processor'));
        }
    }

    /**
     * @dataProvider processorsDataProvider
     */
    public function testProcessWhenSomeProcessorDoesNotExist(string $processorServiceId): void
    {
        $container = new ContainerBuilder();
        foreach (self::PROCESSORS as $serviceId) {
            if ($serviceId !== $processorServiceId) {
                $container->register($serviceId)->addTag('oro.api.processor');
            }
        }

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('non-existent service "%s"', $processorServiceId));

        $compiler = new FrontendApiPass();
        $compiler->process($container);
    }

    public function processorsDataProvider(): array
    {
        return array_map(
            function ($serviceId) {
                return [$serviceId];
            },
            self::PROCESSORS
        );
    }
}
