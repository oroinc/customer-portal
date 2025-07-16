<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CustomerBundle\DependencyInjection\Compiler\OwnerTreeListenerPass;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OwnerTreeListenerPassTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $ownershipTreeSubscriberDef = $container->register('oro_security.ownership_tree_listener');

        $compiler = new OwnerTreeListenerPass();
        $compiler->process($container);

        self::assertEquals(
            [
                ['addSupportedClass', [Customer::class, ['parent', 'organization']]],
                ['addSupportedClass', [CustomerUser::class, ['customer', 'organization']]]
            ],
            $ownershipTreeSubscriberDef->getMethodCalls()
        );
    }
}
