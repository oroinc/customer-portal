<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener\Entity;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\PersistentCollection;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddressToAddressType;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\EventListener\Entity\SendChangedAddressTypeToMessageQueueListener;
use Oro\Bundle\DataAuditBundle\Async\Topic\AuditChangedEntitiesTopic;
use Oro\Bundle\DataAuditBundle\Provider\AuditConfigProvider;
use Oro\Bundle\DataAuditBundle\Provider\AuditMessageBodyProvider;
use Oro\Bundle\DataAuditBundle\Service\EntityToEntityChangeArrayConverter;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\MessageQueueBundle\Test\Unit\MessageQueueExtension;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\ORM\Mocks\EntityManagerMock;
use Oro\Component\Testing\Unit\ORM\Mocks\UnitOfWorkMock;
use Oro\Component\Testing\Unit\ORM\OrmTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SendChangedCustomerAddressTypeToMessageQueueListenerTest extends OrmTestCase
{
    use MessageQueueExtension;

    /** @var EntityManagerMock */
    private $em;

    /** @var UnitOfWorkMock */
    private $uow;

    /** @var AuditConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $auditConfigProvider;

    /** @var EntityToEntityChangeArrayConverter|\PHPUnit\Framework\MockObject\MockObject */
    private $entityToArrayConverter;

    /** @var AuditMessageBodyProvider */
    private $auditMessageBodyProvider;

    /** @var SendChangedAddressTypeToMessageQueueListener */
    private $listener;

    protected function setUp(): void
    {
        $this->em = $this->getTestEntityManager();
        $this->em->getConfiguration()->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));
        $this->uow = new UnitOfWorkMock();
        $this->em->setUnitOfWork($this->uow);

        $this->entityToArrayConverter = $this->createMock(EntityToEntityChangeArrayConverter::class);
        $this->auditConfigProvider = $this->createMock(AuditConfigProvider::class);
        $this->auditMessageBodyProvider = new AuditMessageBodyProvider($this->createMock(EntityNameResolver::class));

        $token = $this->createMock(TokenInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects(self::any())
            ->method('getToken')
            ->willReturn($token);

        $applicationState = $this->createMock(ApplicationState::class);
        $applicationState->expects(self::any())
            ->method('isInstalled')
            ->willReturn(true);

        $this->listener = new SendChangedAddressTypeToMessageQueueListener(
            self::getMessageProducer(),
            $tokenStorage,
            $this->entityToArrayConverter,
            $this->auditConfigProvider,
            $this->auditMessageBodyProvider,
            $applicationState,
            CustomerAddress::class,
            CustomerAddressToAddressType::class
        );
    }

    public function testListenerDisabled(): void
    {
        $this->listener->setEnabled(false);
        $this->listener->postFlush(new PostFlushEventArgs($this->em));

        self::assertMessagesEmpty(AuditChangedEntitiesTopic::getName());
    }

    public function testUnsupportedChanges(): void
    {
        $this->auditConfigProvider->expects(self::any())
            ->method('isAuditableField')
            ->with(CustomerAddress::class, 'types')
            ->willReturn(true);

        $persistentCollection = new PersistentCollection(
            $this->em,
            $this->em->getClassMetadata(CustomerUser::class),
            new ArrayCollection([new CustomerUser()])
        );

        $this->uow->addUpdate(new Customer());
        $this->uow->addUpdate(new CustomerUser());
        $this->uow->addCollectionUpdates($persistentCollection);

        $this->listener->onFlush(new OnFlushEventArgs($this->em));
        $this->listener->postFlush(new PostFlushEventArgs($this->em));

        self::assertMessagesEmpty(AuditChangedEntitiesTopic::getName());
    }

    public function testSupportedChanges(): void
    {
        $this->auditConfigProvider->expects(self::any())
            ->method('isAuditableField')
            ->with(CustomerAddress::class, 'types')
            ->willReturn(true);

        $owner = $this->getCustomerAddress(1, new Customer());
        $addressRelation1 = $this->getCustomerAddressToAddressType(1, 'billing', $owner);
        $addressRelation2 = $this->getCustomerAddressToAddressType(2, 'shipping', $owner);
        $addressRelation3 = $this->getCustomerAddressToAddressType(3, 'shipping2', $owner);

        $persistentCollection = new PersistentCollection(
            $this->em,
            $this->em->getClassMetadata(CustomerAddressToAddressType::class),
            new ArrayCollection([$addressRelation3])
        );
        $persistentCollection->setOwner(
            $owner,
            [
                'fieldName' => 'types',
                'type' => ClassMetadata::MANY_TO_MANY,
                'isOwningSide' => true,
                'mappedBy' => 'address',
                'inversedBy' => null,
                'orphanRemoval' => false
            ]
        );
        $persistentCollection->takeSnapshot();
        $persistentCollection->add($addressRelation1);
        $persistentCollection->add($addressRelation2);
        $persistentCollection->removeElement($addressRelation3);

        $this->entityToArrayConverter->expects(self::any())
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

        $this->uow->addCollectionUpdates($persistentCollection);
        $this->uow->addCollectionDeletions($persistentCollection);

        $this->listener->onFlush(new OnFlushEventArgs($this->em));
        $this->listener->postFlush(new PostFlushEventArgs($this->em));

        self::assertMessagesCount(AuditChangedEntitiesTopic::getName(), 1);

        $sentMessage = self::getSentMessage(AuditChangedEntitiesTopic::getName(), false);
        self::assertEquals(MessagePriority::VERY_LOW, $sentMessage->getPriority());


        $sentMessageBody = $sentMessage->getBody();
        $expectedMessageBody = $this->getExpectedMessageBody();
        self::assertEquals(
            array_values($expectedMessageBody['entities_updated']),
            array_values($sentMessageBody['entities_updated'])
        );
        self::assertEquals(
            array_values($expectedMessageBody['entities_deleted']),
            array_values($sentMessageBody['entities_deleted'])
        );
        self::assertEquals(
            array_values($expectedMessageBody['collections_updated']),
            array_values($sentMessageBody['collections_updated'])
        );
    }

    private function getExpectedMessageBody(): array
    {
        $deleted = [
            'deleted' => [
                [
                    'entity_id' => 3,
                    'entity_class' => CustomerAddressToAddressType::class,
                    'change_set' => ['types' => ['shipping2', null]],
                    'entity_name' => 'shipping2'
                ]
            ]
        ];
        $inserted = [
            'inserted' => [
                [
                    'entity_id' => 1,
                    'entity_class' => CustomerAddressToAddressType::class,
                    'change_set' => [],
                    'entity_name' => 'billing'
                ],
                [
                    'entity_id' => 2,
                    'entity_class' => CustomerAddressToAddressType::class,
                    'change_set' => [],
                    'entity_name' => 'shipping'
                ]
            ]
        ];
        $updates = [
            '00000000000005430000000000000000' => [
                'entity_id' => null,
                'entity_class' => Customer::class,
                'change_set' => ['addresses' => [$deleted, $inserted]]
            ]
        ];
        $collectionUpdates = [
            '00000000000005ba0000000000000000' => [
                'entity_id' => 1,
                'entity_class' => CustomerAddress::class,
                'change_set' => ['types' => [$deleted, $inserted]]
            ]
        ];

        return $this->auditMessageBodyProvider->prepareMessageBody([], $updates, [], $collectionUpdates);
    }

    private function getCustomerAddress(int $id, Customer $frontendOwner): CustomerAddress
    {
        $entity = new CustomerAddress();
        ReflectionUtil::setId($entity, $id);
        $entity->setFrontendOwner($frontendOwner);

        return $entity;
    }

    private function getCustomerAddressToAddressType(
        int $id,
        string $typeName,
        AbstractDefaultTypedAddress $address
    ): CustomerAddressToAddressType {
        $entity = new CustomerAddressToAddressType();
        ReflectionUtil::setId($entity, $id);
        $entity->setType(new AddressType($typeName));
        $entity->setAddress($address);

        return $entity;
    }
}
