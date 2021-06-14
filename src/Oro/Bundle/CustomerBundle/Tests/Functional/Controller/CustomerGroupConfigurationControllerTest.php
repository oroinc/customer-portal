<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Controller;

use Oro\Bundle\ConfigBundle\Tests\Functional\Controller\AbstractConfigurationControllerTest;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Migrations\Data\ORM\LoadAnonymousCustomerGroup;

class CustomerGroupConfigurationControllerTest extends AbstractConfigurationControllerTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequestUrl(array $parameters): string
    {
        $group = $this->getCustomerGroup();
        $parameters['id'] = $group->getId();

        return $this->getUrl('oro_customer_group_config', $parameters);
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

    /**
     * @return CustomerGroup
     */
    private function getCustomerGroup(): CustomerGroup
    {
        $managerRegistry = $this->getContainer()->get('doctrine');
        return $managerRegistry->getRepository(CustomerGroup::class)
            ->findOneBy(['name' => LoadAnonymousCustomerGroup::GROUP_NAME_NON_AUTHENTICATED]);
    }
}
