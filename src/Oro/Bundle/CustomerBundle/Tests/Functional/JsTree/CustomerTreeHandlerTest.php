<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\JsTree;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Component\Tree\Handler\AbstractTreeHandler;
use Oro\Component\Tree\Test\AbstractTreeHandlerTestCase;

class CustomerTreeHandlerTest extends AbstractTreeHandlerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getFixtures(): array
    {
        return [LoadCustomers::class];
    }

    /**
     * {@inheritdoc}
     */
    protected function getHandlerId(): string
    {
        return 'oro_customer.customer_tree_handler';
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreateTree(?string $entityReference, bool $includeRoot, array $expectedData)
    {
        $entity = null;
        if (null !== $entityReference) {
            /** @var Customer $entity */
            $entity = $this->getReference($entityReference);
        }

        $expectedData = array_reduce($expectedData, function ($result, $data) {
            /** @var Customer $entity */
            $entity = $this->getReference($data['entity']);
            $data['id'] = $entity->getId();
            $data['text'] = $entity->getName();
            if ($data['parent'] !== AbstractTreeHandler::ROOT_PARENT_VALUE) {
                $data['parent'] = $this->getReference($data['parent'])->getId();
            }
            unset($data['entity']);
            $result[$data['id']] = $data;
            return $result;
        }, []);

        $this->assertTreeCreated($expectedData, $entity, $includeRoot);
    }

    public function createDataProvider(): array
    {
        return [
            [
                'root' => 'customer.level_1.2',
                'includeRoot' => false,
                'expectedData' => [
                    [
                        'entity' => 'customer.level_1.2.1',
                        'parent' => AbstractTreeHandler::ROOT_PARENT_VALUE,
                        'state' => [
                            'opened' => true
                        ],
                    ],
                    [
                        'entity' => 'customer.level_1.2.1.1',
                        'parent' => 'customer.level_1.2.1',
                        'state' => [
                            'opened' => false
                        ],
                    ],
                ]
            ],
            [
                'root' => 'customer.level_1.2',
                'includeRoot' => true,
                'expectedData' => [
                    [
                        'entity' => 'customer.level_1.2',
                        'parent' => AbstractTreeHandler::ROOT_PARENT_VALUE,
                        'state' => [
                            'opened' => true
                        ],
                    ],
                    [
                        'entity' => 'customer.level_1.2.1',
                        'parent' => 'customer.level_1.2',
                        'state' => [
                            'opened' => true
                        ],
                    ],
                    [
                        'entity' => 'customer.level_1.2.1.1',
                        'parent' => 'customer.level_1.2.1',
                        'state' => [
                            'opened' => false
                        ],
                    ],
                ]
            ],
        ];
    }
}
