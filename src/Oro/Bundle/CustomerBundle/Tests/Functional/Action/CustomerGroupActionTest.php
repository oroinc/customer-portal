<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Action;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerGroupActionTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGroups'
            ]
        );
    }

    public function testDelete()
    {
        /** @var CustomerGroup $entity */
        $entity = $this->getReference('customer_group.group1');
        $id = $entity->getId();
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_action_operation_execute',
                [
                    'operationName' => 'oro_customer_groups_delete',
                    'entityId[id]' => $id,
                    'entityClass' => $this->getContainer()->getParameter('oro_customer.entity.customer_group.class'),
                ]
            ),
            [],
            [],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );

        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 200);

        static::getContainer()->get('doctrine')->getManagerForClass(CustomerGroup::class)->clear();

        $removedGroup = static::getContainer()
            ->get('doctrine')
            ->getRepository('OroCustomerBundle:CustomerGroup')
            ->find($id);

        static::assertNull($removedGroup);
    }

    public function testDeleteAnonymousUserGroup()
    {
        $id = $this->getContainer()
            ->get('oro_config.global')
            ->get('oro_customer.anonymous_customer_group');

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_action_operation_execute',
                [
                    'operationName' => 'oro_customer_groups_delete',
                    'entityId[id]' => $id,
                    'entityClass' => $this->getContainer()->getParameter('oro_customer.entity.customer_group.class'),
                ]
            ),
            [],
            [],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );
        $result = $this->client->getResponse();
        $this->assertSame(403, $result->getStatusCode());
    }
}
