<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\OwnerTreeListenerPass;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OwnerTreeListenerPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container->register('oro_security.ownership_tree_subscriber');

        $compiler = new OwnerTreeListenerPass();
        $compiler->process($container);

        self::assertEquals(
            [
                ['addSupportedClass', [Customer::class, ['parent', 'organization']]],
                ['addSupportedClass', [CustomerUser::class, ['customer', 'organization']]]
            ],
            $container->getDefinition('oro_security.ownership_tree_subscriber')->getMethodCalls()
        );
    }
}
