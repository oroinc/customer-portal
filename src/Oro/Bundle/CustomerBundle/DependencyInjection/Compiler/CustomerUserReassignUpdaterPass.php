<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Compiler;

use Oro\Component\DependencyInjection\Compiler\TaggedServicesCompilerPassTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register customer user reassign updaters tagged with oro_customer.updater.customer_user_reassign
 */
class CustomerUserReassignUpdaterPass implements CompilerPassInterface
{
    use TaggedServicesCompilerPassTrait;

    const TAG = 'oro_customer.updater.customer_user_reassign';
    const UPDATER_SERVICE_ID = 'oro_customer.updater.customer_user_reassign_updater';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerTaggedServices(
            $container,
            self::UPDATER_SERVICE_ID,
            self::TAG,
            'addCustomerUserReassignEntityUpdater'
        );
    }
}
