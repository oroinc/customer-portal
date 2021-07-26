<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\DependencyInjection\CompilerPass;

use Oro\Bundle\WebsiteBundle\DependencyInjection\CompilerPass\TwigSandboxConfigurationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class TwigSandboxConfigurationPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        /** @var Definition|\PHPUnit\Framework\MockObject\MockObject $securityPolicyDefinition */
        $securityPolicyDefinition = $this->getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock();

        $securityPolicyDefinition->expects($this->exactly(2))
            ->method('getArgument')
            ->withConsecutive([4], [1])
            ->willReturnOnConsecutiveCalls(
                ['some_existing_function'],
                ['some_existing_filter']
            );

        $securityPolicyDefinition->expects($this->exactly(2))
            ->method('replaceArgument')
            ->withConsecutive(
                [
                    4,
                    [
                        'some_existing_function',
                        'website_path',
                        'website_secure_path',
                    ]
                ],
                [
                    1,
                    [
                        'some_existing_filter',
                        'oro_format_datetime_by_entity',
                        'oro_format_date_by_entity',
                        'oro_format_day_by_entity',
                        'oro_format_time_by_entity',
                    ]
                ]
            );

        /** @var Definition|\PHPUnit\Framework\MockObject\MockObject $emailRendererDefinition */
        $emailRendererDefinition = $this->getMockBuilder(Definition::class)
            ->disableOriginalConstructor()
            ->getMock();

        $emailRendererDefinition->expects($this->exactly(2))
            ->method('addMethodCall')
            ->withConsecutive(
                ['addExtension', [new Reference(TwigSandboxConfigurationPass::WEBSITE_PATH_EXTENSION_SERVICE_KEY)]],
                ['addExtension', [new Reference('oro_website.twig.entity_date_time_extension')]]
            );

        /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects($this->any())
            ->method('has')
            ->willReturnMap([
                [TwigSandboxConfigurationPass::EMAIL_TEMPLATE_SANDBOX_SECURITY_POLICY_SERVICE_KEY, true],
                [TwigSandboxConfigurationPass::EMAIL_TEMPLATE_RENDERER_SERVICE_KEY, true]
            ]);

        $container->expects($this->any())
            ->method('getDefinition')
            ->willReturnMap([
                [
                    TwigSandboxConfigurationPass::EMAIL_TEMPLATE_SANDBOX_SECURITY_POLICY_SERVICE_KEY,
                    $securityPolicyDefinition
                ],
                [
                    TwigSandboxConfigurationPass::EMAIL_TEMPLATE_RENDERER_SERVICE_KEY,
                    $emailRendererDefinition
                ]
            ]);

        $compilerPass = new TwigSandboxConfigurationPass();
        $compilerPass->process($container);
    }
}
