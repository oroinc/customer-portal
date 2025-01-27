<?php

namespace Oro\Bundle\AddressValidationBundle;

use Oro\Bundle\AddressValidationBundle\DependencyInjection\AddressValidationSupportingChannelTypesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroAddressValidationBundle extends Bundle
{
    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AddressValidationSupportingChannelTypesCompilerPass());
    }
}
