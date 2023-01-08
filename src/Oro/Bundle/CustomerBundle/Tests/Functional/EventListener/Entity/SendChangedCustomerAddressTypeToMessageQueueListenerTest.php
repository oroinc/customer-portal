<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\EventListener\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddressToAddressType;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\EventListener\Entity\SendChangedAddressTypeToMessageQueueListener;
use Oro\Bundle\CustomerBundle\Tests\Functional\TestEntity\TestCustomerAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\TestEntity\TestCustomerAddressToAddressType;
use Oro\Bundle\DataAuditBundle\Provider\AuditConfigProvider;
use Oro\Bundle\DataAuditBundle\Service\EntityToEntityChangeArrayConverter;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\TestUtils\ORM\Mocks\EntityManagerMock;
use Oro\Component\TestUtils\ORM\Mocks\PersistentCollectionMock;
use Oro\Component\TestUtils\ORM\Mocks\UnitOfWork;

class SendChangedCustomerAddressTypeToMessageQueueListenerTest extends WebTestCase
{
    /** @var MessageProducerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $messageProducer;

    /** @var AuditConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $auditConfigProvider;

    /** @var EntityToEntityChangeArrayConverter|\PHPUnit\Framework\MockObject\MockObject */
    private $entityToArrayConverter;

    private EntityManagerMock $flushEventEntityManager;
    private UnitOfWork $flushEventUnitOfWork;
    private SendChangedAddressTypeToMessageQueueListener $listener;

    protected function setUp(): void
    {
        $this->flushEventUnitOfWork = new UnitOfWork();
        $connection = $this->createMock(Connection::class);
        $this->flushEventEntityManager = EntityManagerMock::create($connection);
        $this->flushEventEntityManager->setUnitOfWork($this->flushEventUnitOfWork);

        $this->messageProducer = $this->createMock(MessageProducerInterface::class);
        $this->entityToArrayConverter = $this->createMock(EntityToEntityChangeArrayConverter::class);
        $this->auditConfigProvider = $this->createMock(AuditConfigProvider::class);

        $this->listener = new SendChangedAddressTypeToMessageQueueListener(
            $this->messageProducer,
            $this->getContainer()->get('security.token_storage'),
            $this->entityToArrayConverter,
            $this->auditConfigProvider,
            $this->getContainer()->get('oro_dataaudit.provider.audit_message_body_provider'),
            $this->getContainer()->get('oro_distribution.handler.application_status'),
            CustomerAddress::class,
            CustomerAddressToAddressType::class
        );
    }

    public function testListenerDisabled()
    {
        $this->messageProducer->expects($this->never())->method('send');
        $this->listener->setEnabled(false);
        $this->listener->postFlush(new PostFlushEventArgs($this->flushEventEntityManager));
    }

    public function testUnsupportedChanges()
    {
        $this->auditConfigProvider
            ->method('isAuditableField')
            ->with(CustomerAddress::class, 'types')
            ->willReturn(true);

        $collection = new ArrayCollection([
            new CustomerUser(), new CustomerUser(),
        ]);
        $persistentCollection = new PersistentCollection(
            $this->flushEventEntityManager,
            new ClassMetadata(CustomerUser::class),
            $collection
        );

        $this->flushEventUnitOfWork->addUpdate(new Customer());
        $this->flushEventUnitOfWork->addUpdate(new CustomerUser());
        $this->flushEventUnitOfWork->addCollectionUpdates($persistentCollection);
        $this->messageProducer->expects($this->never())->method('send');

        $this->listener->onFlush(new OnFlushEventArgs($this->flushEventEntityManager));
        $this->listener->postFlush(new PostFlushEventArgs($this->flushEventEntityManager));
    }

    public function testSupportedChanges()
    {
        $this->auditConfigProvider
            ->method('isAuditableField')
            ->with(CustomerAddress::class, 'types')
            ->willReturn(true);

        $owner = new TestCustomerAddress();
        $owner->setId(1);
        $owner->setFrontendOwner(new Customer());
        $addressRelation1 = TestCustomerAddressToAddressType::create(1, 'billing', $owner);
        $addressRelation2 = TestCustomerAddressToAddressType::create(2, 'shipping', $owner);
        $addressRelation3 = TestCustomerAddressToAddressType::create(3, 'shipping2', $owner);

        $persistentCollection = new PersistentCollectionMock(
            [$addressRelation1, $addressRelation2],
            [$addressRelation3]
        );
        $persistentCollection->setOwner($owner, [
            'fieldName' => 'types',
        ]);

        $this->entityToArrayConverter
            ->method('convertNamedEntityToArray')
            ->willReturnCallback(function ($em, $entity, array $changeSet, $entityName) {
                $result = [
                    'entity_id' => $entity->getId(),
                    'entity_class' => $entity::class,
                    'change_set' => $changeSet,
                ];

                if ($entityName) {
                    $result['entity_name'] = $entityName;
                }

                return $result;
            });

        $this->flushEventUnitOfWork->addCollectionUpdates($persistentCollection);
        $this->flushEventUnitOfWork->addCollectionDeletions($persistentCollection);

        $this->messageProducer->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (string $topic, Message $message) {
                $expected = $this->expectedTopicMessage()->getBody();
                $actual = $message->getBody();

                $this->assertEquals(
                    array_values($expected['entities_updated']),
                    array_values($actual['entities_updated'])
                );
                $this->assertEquals(
                    array_values($expected['entities_deleted']),
                    array_values($actual['entities_deleted'])
                );
                $this->assertEquals(
                    array_values($expected['collections_updated']),
                    array_values($actual['collections_updated'])
                );
            })
        ;

        $this->listener->onFlush(new OnFlushEventArgs($this->flushEventEntityManager));
        $this->listener->postFlush(new PostFlushEventArgs($this->flushEventEntityManager));
    }

    private function expectedTopicMessage(): Message
    {
        $deleted = [
            'deleted' => [
                [
                    'entity_id' => 3,
                    'entity_class' => TestCustomerAddressToAddressType::class,
                    'change_set' => [
                        'types' => [
                            'shipping2', null,
                        ]
                    ],
                    'entity_name' => 'shipping2',
                ]
            ]
        ];
        $inserted = [
            'inserted' => [
                [
                    'entity_id' => 1,
                    'entity_class' => TestCustomerAddressToAddressType::class,
                    'change_set' => [],
                    'entity_name' => 'billing',
                ],
                [
                    'entity_id' => 2,
                    'entity_class' => TestCustomerAddressToAddressType::class,
                    'change_set' => [],
                    'entity_name' => 'shipping',
                ],
            ]
        ];

        $updates = [
            '00000000000005430000000000000000' => [
                'entity_id' => null,
                'entity_class' => Customer::class,
                'change_set' => [
                    'addresses' => [
                        $deleted, $inserted
                    ]
                ]
            ]
        ];
        $collectionUpdates = [
            '00000000000005ba0000000000000000' => [
                'entity_id' => 1,
                'entity_class' => TestCustomerAddress::class,
                'change_set' => [
                    'types' => [
                        $deleted, $inserted
                    ]
                ],
            ]
        ];

        $provider = $this->getContainer()->get('oro_dataaudit.provider.audit_message_body_provider');
        return new Message(
            $provider->prepareMessageBody([], $updates, [], $collectionUpdates),
            MessagePriority::VERY_LOW
        );
    }
}
