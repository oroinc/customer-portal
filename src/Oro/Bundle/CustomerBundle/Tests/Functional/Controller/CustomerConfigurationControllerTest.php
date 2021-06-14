<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Oro\Bundle\ConfigBundle\Tests\Functional\Controller\AbstractConfigurationControllerTest;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomer;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class CustomerConfigurationControllerTest extends AbstractConfigurationControllerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadOrganization::class, LoadCustomer::class]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequestUrl(array $parameters): string
    {
        $customer = $this->getReference(LoadCustomer::CUSTOMER);
        $parameters['id'] = $customer->getId();

        return $this->getUrl('oro_customer_config', $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return [
            'main user configuration page' => [
                'parameters' => [
                    'activeGroup' => null,
                    'activeSubGroup' => null,
                ],
                'expected' => [
                    'Product Data Export',
                    'Enable Products Export',
                ]
            ],
            'user configuration sub page' => [
                'parameters' => [
                    'activeGroup' => 'commerce',
                    'activeSubGroup' => 'customer_settings',
                ],
                'expected' => [
                    'Product Data Export',
                    'Enable Products Export',
                ]
            ],
        ];
    }
}
