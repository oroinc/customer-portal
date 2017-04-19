<?php

namespace Oro\Bundle\FrontendBundle\Tests\Functional\Provider;

use Oro\Bundle\TranslationBundle\Tests\Functional\Provider\TranslationPackagesProviderExtensionTestAbstract;

class TranslationPackagesProviderExtensionTest extends TranslationPackagesProviderExtensionTestAbstract
{
    /**
     * {@inheritdoc}
     */
    public function expectedPackagesDataProvider()
    {
        yield 'OroCommerce Package' => [
            'packageName' => 'OroCommerce',
            'fileToLocate' => 'Oro/Bundle/CustomerBundle/OroCustomerBundle.php'
        ];

        yield 'OroCustomerPortal Package' => [
            'packageName' => 'OroCustomerPortal',
            'fileToLocate' => 'Oro/Bundle/FrontendBundle/OroFrontendBundle.php'
        ];
    }
}
