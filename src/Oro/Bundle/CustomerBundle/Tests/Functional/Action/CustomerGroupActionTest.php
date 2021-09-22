<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Action;

use Oro\Bundle\ActionBundle\Tests\Functional\OperationAwareTestTrait;
use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerGroupActionTest extends WebTestCase
{
    use ConfigManagerAwareTestTrait;
    use OperationAwareTestTrait;

    protected function setUp(): void
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
        $operationName = 'oro_customer_groups_delete';
        $entityId = $entity->getId();
        $entityClass = CustomerGroup::class;
        $this->client->request(
            'POST',
            $this->getUrl(
                'oro_action_operation_execute',
                [
                    'operationName' => $operationName,
                    'entityId[id]' => $entityId,
                    'entityClass' => $entityClass,
                ]
            ),
            $this->getOperationExecuteParams($operationName, ['id' => $entityId], $entityClass),
            [],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );

        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 200);

        static::getContainer()->get('doctrine')->getManagerForClass(CustomerGroup::class)->clear();

        $removedGroup = static::getContainer()
            ->get('doctrine')
            ->getRepository('OroCustomerBundle:CustomerGroup')
            ->find($entityId);

        static::assertNull($removedGroup);
    }

    public function testDeleteAnonymousUserGroup()
    {
        $entityId = self::getConfigManager('global')->get('oro_customer.anonymous_customer_group');

        $operationName = 'oro_customer_groups_delete';
        $entityClass   = CustomerGroup::class;
        $this->client->request(
            'POST',
            $this->getUrl(
                'oro_action_operation_execute',
                [
                    'operationName' => $operationName,
                    'entityId[id]' => $entityId,
                    'entityClass' => $entityClass,
                ]
            ),
            $this->getOperationExecuteParams($operationName, $entityId, $entityClass),
            [],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );
        $result = $this->client->getResponse();
        $this->assertSame(403, $result->getStatusCode());
    }
}
