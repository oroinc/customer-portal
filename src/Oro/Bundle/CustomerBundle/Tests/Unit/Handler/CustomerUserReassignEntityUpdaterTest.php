<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Handler\CustomerUserReassignEntityUpdater;
use Oro\Bundle\CustomerBundle\Tests\Unit\Stub\ResettableCustomerUserRepositoryStub;
use Oro\Bundle\DataAuditBundle\Async\Topic\AuditChangedEntitiesTopic;
use Oro\Bundle\DataAuditBundle\Provider\AuditMessageBodyProvider;
use Oro\Bundle\DataAuditBundle\Service\EntityToEntityChangeArrayConverter;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CustomerUserReassignEntityUpdaterTest extends \PHPUnit\Framework\TestCase
{
    private const ENTITY_CLASS = 'Test\Entity';

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

    /** @var ResettableCustomerUserRepositoryStub|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var CustomerUserReassignEntityUpdater */
    private $updater;

    protected function setUp(): void
    {
        $this->entityToArrayConverter = $this->createMock(EntityToEntityChangeArrayConverter::class);
        $this->auditMessageBodyProvider = $this->createMock(AuditMessageBodyProvider::class);
        $this->messageProducer = $this->createMock(MessageProducerInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(ResettableCustomerUserRepositoryStub::class);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects(self::any())
            ->method('getManagerForClass')
            ->with(self::ENTITY_CLASS)
            ->willReturn($this->em);
        $this->em->expects(self::any())
            ->method('getRepository')
            ->with(self::ENTITY_CLASS)
            ->willReturn($this->repository);

        $this->updater = new CustomerUserReassignEntityUpdater(
            $doctrine,
            $this->entityToArrayConverter,
            $this->auditMessageBodyProvider,
            $this->messageProducer,
            $this->tokenStorage
        );
        $this->updater->setEntityClass(self::ENTITY_CLASS);
    }

    private function getCustomerUser(int $id): CustomerUser
    {
        $customerUser = new CustomerUser();
        ReflectionUtil::setId($customerUser, $id);

        return $customerUser;
    }

    private function getUser(int $id): User
    {
        $user = new User();
        ReflectionUtil::setId($user, $id);

        return $user;
    }

    public function testUpdateEmptyUpdatedEntities()
    {
        $customerUser = $this->getCustomerUser(35);

        $this->repository->expects(self::once())
            ->method('getRelatedEntitiesCount')
            ->with($customerUser)
            ->willReturn(0);

        $this->repository->expects(self::never())
            ->method('resetCustomerUser');

        $this->entityToArrayConverter->expects(self::never())
            ->method('convertEntityToArray');

        $this->auditMessageBodyProvider->expects(self::never())
            ->method('prepareMessageBody');

        $this->tokenStorage->expects(self::never())
            ->method('getToken');

        $this->messageProducer->expects(self::never())
            ->method('send');

        $this->updater->update($customerUser);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testUpdateWithBatches()
    {
        $customerUser = $this->getCustomerUser(35);

        $updatedEntity1 = $this->getUser(1);
        $updatedEntity100 = $this->getUser(100);
        $updatedEntity101 = $this->getUser(101);
        $updatedEntity200 = $this->getUser(200);
        $updatedEntity201 = $this->getUser(201);

        $this->repository->expects(self::once())
            ->method('getRelatedEntitiesCount')
            ->with($customerUser)
            ->willReturn(201);

        $this->repository->expects(self::exactly(4))
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

        $this->repository->expects(self::exactly(3))
            ->method('resetCustomerUser')
            ->withConsecutive(
                [$customerUser, [$updatedEntity1, $updatedEntity100,]],
                [$customerUser, [$updatedEntity101, $updatedEntity200,]],
                [$customerUser, [$updatedEntity201,]]
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

        $this->updater->update($customerUser);
    }

    public function testHasEntitiesToUpdateTrue()
    {
        $customerUser = $this->getCustomerUser(35);

        $this->repository->expects(self::once())
            ->method('getRelatedEntitiesCount')
            ->with($customerUser)
            ->willReturn(3);

        self::assertEquals(true, $this->updater->hasEntitiesToUpdate($customerUser));
    }

    public function testHasEntitiesToUpdateFalse()
    {
        $customerUser = $this->getCustomerUser(35);

        $this->repository->expects(self::once())
            ->method('getRelatedEntitiesCount')
            ->with($customerUser)
            ->willReturn(0);

        self::assertEquals(false, $this->updater->hasEntitiesToUpdate($customerUser));
    }

    public function testGetEntityClass()
    {
        self::assertEquals(self::ENTITY_CLASS, $this->updater->getEntityClass());
    }
}
