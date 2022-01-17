<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\CheckoutBundle\Entity\Repository\CheckoutRepository;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Handler\CustomerUserReassignEntityUpdater;
use Oro\Bundle\DataAuditBundle\Async\Topic\AuditChangedEntitiesTopic;
use Oro\Bundle\DataAuditBundle\Provider\AuditMessageBodyProvider;
use Oro\Bundle\DataAuditBundle\Service\EntityToEntityChangeArrayConverter;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CustomerUserReassignEntityUpdaterTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var CustomerUserReassignEntityUpdater */
    private $updater;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var EntityToEntityChangeArrayConverter|\PHPUnit\Framework\MockObject\MockObject */
    private $entityToArrayConverter;

    /** @var AuditMessageBodyProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $auditMessageBodyProvider;

    /** @var MessageProducerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $messageProducer;

    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $em;

    /** @var CheckoutRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $checkoutRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->entityToArrayConverter = $this->createMock(EntityToEntityChangeArrayConverter::class);
        $this->auditMessageBodyProvider = $this->createMock(AuditMessageBodyProvider::class);
        $this->messageProducer = $this->createMock(MessageProducerInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->checkoutRepository = $this->createMock(CheckoutRepository::class);

        $this->updater = new CustomerUserReassignEntityUpdater(
            $this->registry,
            $this->entityToArrayConverter,
            $this->auditMessageBodyProvider,
            $this->messageProducer,
            $this->tokenStorage
        );
    }

    public function testUpdateEmptyUpdatedEntities()
    {
        $entityClass = \stdClass::class;
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 35]);

        $this->expectRepository($entityClass);

        $this->checkoutRepository->expects(self::once())
            ->method('getRelatedEntitiesCount')
            ->with($customerUser)
            ->willReturn(0);

        $this->checkoutRepository->expects(self::never())
            ->method('resetCustomerUser');

        $this->entityToArrayConverter->expects(self::never())
            ->method('convertEntityToArray');

        $this->auditMessageBodyProvider->expects(self::never())
            ->method('prepareMessageBody');

        $this->tokenStorage->expects(self::never())
            ->method('getToken');

        $this->messageProducer->expects(self::never())
            ->method('send');

        $this->updater->setEntityClass($entityClass);

        $this->updater->update($customerUser);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testUpdateWithBatches()
    {
        $entityClass = \stdClass::class;
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 35]);

        $this->registry->expects(self::atLeastOnce())
            ->method('getManagerForClass')
            ->with($entityClass)
            ->willReturn($this->em);

        $this->em->expects(self::atLeastOnce())
            ->method('getRepository')
            ->with($entityClass)
            ->willReturn($this->checkoutRepository);

        /** @var User $updatedEntity1 */
        $updatedEntity1 = $this->getEntity(User::class, ['id' => 1]);
        /** @var User $updatedEntity100 */
        $updatedEntity100 = $this->getEntity(User::class, ['id' => 100]);
        /** @var User $updatedEntity101 */
        $updatedEntity101 = $this->getEntity(User::class, ['id' => 101]);
        /** @var User $updatedEntity200 */
        $updatedEntity200 = $this->getEntity(User::class, ['id' => 200]);
        /** @var User $updatedEntity201 */
        $updatedEntity201 = $this->getEntity(User::class, ['id' => 201]);

        $this->checkoutRepository->expects(self::once())
            ->method('getRelatedEntitiesCount')
            ->with($customerUser)
            ->willReturn(201);

        $this->checkoutRepository->expects(self::exactly(4))
            ->method('findBy')
            ->withConsecutive(
                [['customerUser' => $customerUser], null, 100],
                [['customerUser' => $customerUser], null, 100],
                [['customerUser' => $customerUser], null, 100],
                [['customerUser' => $customerUser], null, 100]
            )
            ->willReturnOnConsecutiveCalls(
                [$updatedEntity1, $updatedEntity100],
                [$updatedEntity101, $updatedEntity200],
                [$updatedEntity201],
                []
            );

        $this->checkoutRepository->expects(self::exactly(3))
            ->method('resetCustomerUser')
            ->withConsecutive(
                [
                    $customerUser,
                    [$updatedEntity1, $updatedEntity100,]
                ],
                [
                    $customerUser,
                    [$updatedEntity101, $updatedEntity200,]
                ],
                [
                    $customerUser,
                    [$updatedEntity201,]
                ]
            )
            ->willReturnOnConsecutiveCalls(
                2,
                2,
                1
            );

        $changeSet = [
            'customerUser' => [
                $customerUser,
                null,
            ]
        ];

        $update1 = [
            'entity_class' => User::class,
            'entity_id' => $updatedEntity1->getId(),
            'change_set' => $changeSet,
            'additional_fields' => [],
        ];

        $update2 = [
            'entity_class' => User::class,
            'entity_id' => $updatedEntity100->getId(),
            'change_set' => $changeSet,
            'additional_fields' => [],
        ];

        $update3 = [
            'entity_class' => User::class,
            'entity_id' => $updatedEntity101->getId(),
            'change_set' => $changeSet,
            'additional_fields' => [],
        ];

        $update4 = [
            'entity_class' => User::class,
            'entity_id' => $updatedEntity200->getId(),
            'change_set' => $changeSet,
            'additional_fields' => [],
        ];

        $update5 = [
            'entity_class' => User::class,
            'entity_id' => $updatedEntity201->getId(),
            'change_set' => $changeSet,
            'additional_fields' => [],
        ];

        $this->entityToArrayConverter->expects(self::exactly(5))
            ->method('convertEntityToArray')
            ->withConsecutive(
                [$this->em, $updatedEntity1, $changeSet],
                [$this->em, $updatedEntity100, $changeSet],
                [$this->em, $updatedEntity101, $changeSet],
                [$this->em, $updatedEntity200, $changeSet],
                [$this->em, $updatedEntity201, $changeSet]
            )
            ->willReturnOnConsecutiveCalls($update1, $update2, $update3, $update4, $update5);

        $messageBody1 = [
            'entities_inserted' => [],
            'entities_updated' => [$update1, $update2],
            'entities_deleted' => [],
            'collections_updated' => [],
        ];

        $messageBody2 = [
            'entities_inserted' => [],
            'entities_updated' => [$update3, $update4],
            'entities_deleted' => [],
            'collections_updated' => [],
        ];

        $messageBody3 = [
            'entities_inserted' => [],
            'entities_updated' => [$update5],
            'entities_deleted' => [],
            'collections_updated' => [],
        ];

        $token = $this->createMock(TokenInterface::class);
        $this->tokenStorage->expects(self::exactly(3))
            ->method('getToken')
            ->willReturn($token);

        $this->auditMessageBodyProvider->expects(self::exactly(3))
            ->method('prepareMessageBody')
            ->withConsecutive(
                [[], [$update1, $update2], [], [], $token],
                [[], [$update3, $update4], [], [], $token],
                [[], [$update5], [], [], $token]
            )
            ->willReturnOnConsecutiveCalls(
                $messageBody1,
                $messageBody2,
                $messageBody3
            );

        $this->messageProducer->expects(self::exactly(3))
            ->method('send')
            ->withConsecutive(
                [
                    AuditChangedEntitiesTopic::getName(),
                    new Message($messageBody1, MessagePriority::VERY_LOW)
                ],
                [
                    AuditChangedEntitiesTopic::getName(),
                    new Message($messageBody2, MessagePriority::VERY_LOW)
                ],
                [
                    AuditChangedEntitiesTopic::getName(),
                    new Message($messageBody3, MessagePriority::VERY_LOW)
                ]
            );

        $this->updater->setEntityClass($entityClass);

        $this->updater->update($customerUser);
    }

    public function testHasEntitiesToUpdateTrue()
    {
        $entityClass = \stdClass::class;
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 35]);

        $this->expectRepository($entityClass);

        $this->checkoutRepository->expects(self::once())
            ->method('getRelatedEntitiesCount')
            ->with($customerUser)
            ->willReturn(3);

        $this->updater->setEntityClass($entityClass);

        self::assertEquals(true, $this->updater->hasEntitiesToUpdate($customerUser));
    }

    public function testHasEntitiesToUpdateFalse()
    {
        $entityClass = \stdClass::class;
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(CustomerUser::class, ['id' => 35]);

        $this->expectRepository($entityClass);

        $this->checkoutRepository->expects(self::once())
            ->method('getRelatedEntitiesCount')
            ->with($customerUser)
            ->willReturn(0);

        $this->updater->setEntityClass($entityClass);

        self::assertEquals(false, $this->updater->hasEntitiesToUpdate($customerUser));
    }

    private function expectRepository(string $entityClass)
    {
        $this->registry->expects(self::once())
            ->method('getManagerForClass')
            ->with($entityClass)
            ->willReturn($this->em);

        $this->em->expects(self::once())
            ->method('getRepository')
            ->with($entityClass)
            ->willReturn($this->checkoutRepository);
    }

    public function testGetEntityClass()
    {
        $entityClass = \stdClass::class;

        $this->updater->setEntityClass($entityClass);

        self::assertEquals($entityClass, $this->updater->getEntityClass());
    }
}
