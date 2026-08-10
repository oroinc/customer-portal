<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\FrontendAutocompleteCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

class FrontendAutocompleteCompilerPassTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $registry = $container->register('oro_customer.form.autocomplete.search_registry');
        $security = $container->register('oro_customer.form.autocomplete.security');

        $container->register('handler_1')
            ->addTag(
                'oro_customer.form.autocomplete.frontend_search_handler',
                ['alias' => 'tag1', 'acl_resource' => 'acl_resource_1']
            );
        $container->register('handler_2')
            ->addTag('oro_customer.form.autocomplete.frontend_search_handler', ['alias' => 'tag2']);
        $container->register('handler_3')
            ->addTag(
                'oro_customer.form.autocomplete.frontend_search_handler',
                ['acl_resource' => 'acl_resource_3']
            );
        $container->register('handler_4')
            ->addTag('oro_form.autocomplete.search_handler', ['alias' => 'tag3', 'frontend' => true]);

        $compiler = new FrontendAutocompleteCompilerPass();
        $compiler->process($container);

        $calls = $registry->getMethodCalls();
        self::assertCount(1, $calls);
        self::assertSame('setFrontendSearchHandlers', $calls[0][0]);

        $serviceLocatorReference = $calls[0][1][0];
        self::assertInstanceOf(Reference::class, $serviceLocatorReference);

        $serviceLocatorDef = $container->getDefinition((string)$serviceLocatorReference);
        self::assertEquals(ServiceLocator::class, $serviceLocatorDef->getClass());
        self::assertEquals(
            [
                'tag1' => new ServiceClosureArgument(new Reference('handler_1')),
                'handler_3' => new ServiceClosureArgument(new Reference('handler_3')),
                'tag2' => new ServiceClosureArgument(new Reference('handler_2'))
            ],
            $serviceLocatorDef->getArgument(0)
        );

        self::assertSame(
            [['setAutocompleteAclResources', [[
                'tag1' => 'acl_resource_1',
                'handler_3' => 'acl_resource_3'
            ]]]],
            $security->getMethodCalls()
        );
    }
}
