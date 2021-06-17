<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CommerceMenuBundle\DependencyInjection\Compiler\ConditionExpressionLanguageProvidersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ConditionExpressionLanguageProvidersCompilerPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $expressionLanguageDef = $container->register('oro_commerce_menu.expression_language');

        $container->register('provider_1')
            ->addTag('oro_commerce_menu.condition.expression_language_provider');
        $container->register('provider_2')
            ->addTag('oro_commerce_menu.condition.expression_language_provider');

        $compiler = new ConditionExpressionLanguageProvidersCompilerPass();
        $compiler->process($container);

        self::assertEquals(
            [
                ['registerProvider', [new Reference('provider_1')]],
                ['registerProvider', [new Reference('provider_2')]],
            ],
            $expressionLanguageDef->getMethodCalls()
        );
    }
}
