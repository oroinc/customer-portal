<?php

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\AddressValidationBundle\DependencyInjection\OroAddressValidationExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class OroAddressValidationExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $extension = new OroAddressValidationExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
        self::assertSame(
            [
                [
                    'settings' => [
                        'resolved' => true,
                        'address_validation_service' => ['value' => null, 'scope' => 'app'],
                    ]
                ]
            ],
            $container->getExtensionConfig('oro_address_validation')
        );
    }
}
