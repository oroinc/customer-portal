<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\CustomerBundle\Controller\Api\Rest as Api;
use Oro\Bundle\CustomerBundle\Controller\Frontend\Api\Rest as FrontendApi;
use Oro\Bundle\CustomerBundle\DependencyInjection\OroCustomerExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroCustomerExtensionTest extends ExtensionTestCase
{
    public function testLoad(): void
    {
        $this->loadExtension(new OroCustomerExtension());

        $expectedDefinitions = [
            // REST API
            Api\CommerceCustomerAddressController::class,
            Api\CustomerUserAddressController::class,
            // Frontend REST API
            FrontendApi\CustomerAddressController::class,
            FrontendApi\CustomerUserAddressController::class,
            FrontendApi\GridViewController::class,
            FrontendApi\NavigationItemController::class,
            FrontendApi\PagestateController::class,
            FrontendApi\SidebarController::class,
            FrontendApi\WidgetController::class,
        ];

        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
