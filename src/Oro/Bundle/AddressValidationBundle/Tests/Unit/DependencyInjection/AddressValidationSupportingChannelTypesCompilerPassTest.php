<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\AddressValidationBundle\DependencyInjection\AddressValidationSupportingChannelTypesCompilerPass;
use Oro\Bundle\IntegrationBundle\Exception\LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class AddressValidationSupportingChannelTypesCompilerPassTest extends TestCase
{
    public function testProcessWithNoServiceDefinition(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects(self::once())
            ->method('hasDefinition')
            ->with('oro_address_validation.provider.supported_channel_types')
            ->willReturn(false);

        $container
            ->expects(self::never())
            ->method('findTaggedServiceIds');
        $container
            ->expects(self::never())
            ->method('getDefinition');

        $compilerPass = new AddressValidationSupportingChannelTypesCompilerPass();
        $compilerPass->process($container);
    }

    public function testProcessWithNoTaggedServices(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects(self::once())
            ->method('hasDefinition')
            ->with('oro_address_validation.provider.supported_channel_types')
            ->willReturn(true);

        $container
            ->expects(self::once())
            ->method('findTaggedServiceIds')
            ->with('oro_address_validation.channel')
            ->willReturn([]);

        $container
            ->expects(self::never())
            ->method('getDefinition');

        $compilerPass = new AddressValidationSupportingChannelTypesCompilerPass();
        $compilerPass->process($container);
    }

    public function testProcessWithTaggedServices(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects(self::once())
            ->method('hasDefinition')
            ->with('oro_address_validation.provider.supported_channel_types')
            ->willReturn(true);

        $taggedServices = [
            'service1' => [['type' => 'channel_type1']],
            'service2' => [['type' => 'channel_type2']],
        ];

        $container
            ->expects(self::once())
            ->method('findTaggedServiceIds')
            ->with('oro_address_validation.channel')
            ->willReturn($taggedServices);

        $definition = $this->createMock(Definition::class);
        $definition
            ->expects(self::once())
            ->method('setArgument')
            ->with('$channelTypes', ['channel_type1', 'channel_type2']);

        $container
            ->expects(self::once())
            ->method('getDefinition')
            ->with('oro_address_validation.provider.supported_channel_types')
            ->willReturn($definition);

        $compilerPass = new AddressValidationSupportingChannelTypesCompilerPass();
        $compilerPass->process($container);
    }

    public function testProcessWithMissingTypeAttribute(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            '"type" attribute is expected to be set for tag "oro_address_validation.channel" in service "service1"'
        );

        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects(self::once())
            ->method('hasDefinition')
            ->with('oro_address_validation.provider.supported_channel_types')
            ->willReturn(true);

        $taggedServices = [
            'service1' => [['some_other_attribute' => 'value']],
        ];

        $container
            ->expects(self::once())
            ->method('findTaggedServiceIds')
            ->with('oro_address_validation.channel')
            ->willReturn($taggedServices);

        $compilerPass = new AddressValidationSupportingChannelTypesCompilerPass();
        $compilerPass->process($container);
    }
}
