<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\CustomerBundle\Validator\Constraints\FrontendOwner;
use Oro\Bundle\CustomerBundle\Validator\Constraints\FrontendOwnerValidator;
use Oro\Bundle\OrganizationBundle\Tests\Unit\Fixture\Entity\Entity;
use Oro\Bundle\OrganizationBundle\Tests\Unit\Fixture\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Tests\Unit\Fixture\Entity\User;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Domain\OneShotIsGrantedObserver;
use Oro\Bundle\SecurityBundle\Acl\Group\AclGroupProviderInterface;
use Oro\Bundle\SecurityBundle\Acl\Voter\AclVoter;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class FrontendOwnerValidatorTest extends ConstraintValidatorTestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry */
    private $doctrine;

    /** @var \PHPUnit\Framework\MockObject\MockObject|OwnershipMetadataProviderInterface */
    private $ownershipMetadataProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject|AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var \PHPUnit\Framework\MockObject\MockObject|TokenAccessorInterface */
    private $tokenAccessor;

    /** @var \PHPUnit\Framework\MockObject\MockObject|OwnerTreeInterface */
    private $ownerTree;

    /** @var \PHPUnit\Framework\MockObject\MockObject|OwnerTreeProviderInterface */
    private $ownerTreeProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject|AclVoter */
    private $aclVoter;

    /** @var \PHPUnit\Framework\MockObject\MockObject|AclGroupProviderInterface */
    private $aclGroupProvider;

    /** @var Entity */
    private $testEntity;

    /** @var CustomerUser */
    private $currentUser;

    /** @var Organization */
    private $currentOrg;

    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->ownershipMetadataProvider = $this->createMock(OwnershipMetadataProviderInterface::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->ownerTree = $this->createMock(OwnerTreeInterface::class);
        $this->ownerTreeProvider = $this->createMock(OwnerTreeProviderInterface::class);
        $this->aclVoter = $this->createMock(AclVoter::class);
        $this->aclGroupProvider = $this->createMock(AclGroupProviderInterface::class);

        $this->testEntity = new Entity();
        $this->currentOrg = new Organization();
        $this->currentOrg->setId(1);
        $this->currentUser = new CustomerUser();
        $this->setEntityId($this->currentUser, 10);

        $this->tokenAccessor->expects(self::any())
            ->method('getUser')
            ->willReturn($this->currentUser);
        $this->tokenAccessor->expects(self::any())
            ->method('getUserId')
            ->willReturn($this->currentUser->getId());
        $this->tokenAccessor->expects(self::any())
            ->method('getOrganization')
            ->willReturn($this->currentOrg);

        $this->ownerTreeProvider->expects(self::any())
            ->method('getTree')
            ->willReturn($this->ownerTree);

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function createValidator()
    {
        return new FrontendOwnerValidator(
            $this->doctrine,
            $this->ownershipMetadataProvider,
            $this->authorizationChecker,
            $this->tokenAccessor,
            $this->ownerTreeProvider,
            $this->aclVoter,
            $this->aclGroupProvider
        );
    }

    /**
     * @param string $ownerType
     *
     * @return FrontendOwnershipMetadata
     */
    private function createOwnershipMetadata($ownerType)
    {
        return new FrontendOwnershipMetadata($ownerType, 'owner', 'owner', 'organization', 'organization');
    }

    /**
     * {@inheritdoc}
     */
    protected function createContext()
    {
        $this->constraint = new FrontendOwner();
        $this->propertyPath = null;

        return parent::createContext();
    }

    /**
     * @param object $entity
     * @param mixed  $id
     */
    private function setEntityId($entity, $id)
    {
        $refl = new \ReflectionClass($entity);
        $prop = $refl->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($entity, $id);
    }

    /**
     * @param int $id
     *
     * @return User
     */
    private function createUser($id)
    {
        $user = new CustomerUser();
        $this->setEntityId($user, $id);

        return $user;
    }

    /**
     * @param int $id
     *
     * @return Customer
     */
    private function createCustomer($id)
    {
        $customer = new Customer();
        $this->setEntityId($customer, $id);

        return $customer;
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject|ClassMetadata $entityMetadata
     * @param array                                                  $originalEntityData
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|UnitOfWork
     */
    private function expectManageableEntity(ClassMetadata $entityMetadata, array $originalEntityData)
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $uow = $this->createMock(UnitOfWork::class);

        $this->doctrine->expects(self::once())
            ->method('getManagerForClass')
            ->with(Entity::class)
            ->willReturn($em);
        $em->expects(self::once())
            ->method('getClassMetadata')
            ->willReturn($entityMetadata);
        $em->expects(self::any())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $uow->expects(self::any())
            ->method('getOriginalEntityData')
            ->with($this->testEntity)
            ->willReturn($originalEntityData);

        return $uow;
    }

    /**
     * @param int $accessLevel
     */
    private function expectAddOneShotIsGrantedObserver($accessLevel)
    {
        $this->aclVoter->expects(self::once())
            ->method('addOneShotIsGrantedObserver')
            ->willReturnCallback(function (OneShotIsGrantedObserver $observer) use ($accessLevel) {
                $observer->setAccessLevel($accessLevel);
            });
    }

    public function testValidateForInvalidConstraintType()
    {
        $this->expectException(\Symfony\Component\Validator\Exception\UnexpectedTypeException::class);
        $this->validator->validate($this->testEntity, $this->createMock(Constraint::class));
    }

    public function testValidateForNull()
    {
        $this->doctrine->expects(self::never())
            ->method('getManagerForClass');

        $this->validator->validate(null, $this->constraint);
        $this->assertNoViolation();
    }

    public function testValidateForNotManageableEntity()
    {
        $this->doctrine->expects(self::once())
            ->method('getManagerForClass')
            ->with(Entity::class)
            ->willReturn(null);
        $this->ownershipMetadataProvider->expects(self::never())
            ->method('getMetadata');

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }

    public function testValidateForNonAclProtectedEntity()
    {
        $ownershipMetadata = new FrontendOwnershipMetadata();

        $this->doctrine->expects(self::once())
            ->method('getManagerForClass')
            ->with(Entity::class)
            ->willReturn($this->createMock(EntityManagerInterface::class));
        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }

    public function testValidWithNullOwner()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_USER');
        $entityMetadata = $this->createMock(ClassMetadata::class);

        $owner = null;
        $this->testEntity->setId(234);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, []);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::never())
            ->method('getIdentifierValues');

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->aclVoter->expects(self::never())
            ->method('addOneShotIsGrantedObserver');
        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }

    public function testValidWithNotChangedOwner()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_USER');
        $entityMetadata = $this->createMock(ClassMetadata::class);

        $owner = $this->createUser(123);
        $this->testEntity->setId(234);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => $owner]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::never())
            ->method('getIdentifierValues');

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->aclVoter->expects(self::never())
            ->method('addOneShotIsGrantedObserver');
        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }

    public function testInvalidBecauseAccessDenied()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_USER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = null;

        $owner = $this->createUser(123);
        $this->testEntity->setId(234);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([$this->testEntity->getId()]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('ASSIGN', 'entity:' . Entity::class)
            ->willReturn(true);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->buildViolation($this->constraint->message)
            ->atPath('owner')
            ->setParameters(['{{ owner }}' => 'owner'])
            ->assertRaised();
    }

    public function testValidExistingEntityWithUserOwnerAndSystemAccessLevel()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_USER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::SYSTEM_LEVEL;

        $owner = $this->createUser(123);
        $this->testEntity->setId(234);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([$this->testEntity->getId()]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('ASSIGN', 'entity:' . Entity::class)
            ->willReturn(true);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }

    public function testValidExistingEntityWithUserOwnerAndBasicAccessLevel()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_USER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::BASIC_LEVEL;

        $owner = clone $this->currentUser;
        $this->testEntity->setId(234);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([$this->testEntity->getId()]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('ASSIGN', 'entity:' . Entity::class)
            ->willReturn(true);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }

    public function testValidExistingEntityWithUserOwnerAndDeepAccessLevel()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_USER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::DEEP_LEVEL;

        $owner = $this->createUser(123);
        $this->testEntity->setId(234);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([$this->testEntity->getId()]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('ASSIGN', 'entity:' . Entity::class)
            ->willReturn(true);
        $this->ownerTree->expects(self::once())
            ->method('getUserSubordinateBusinessUnitIds')
            ->with($this->currentUser->getId(), $this->currentOrg->getId())
            ->willReturn([567]);
        $this->ownerTree->expects(self::once())
            ->method('getUserBusinessUnitIds')
            ->with($owner->getId(), $this->currentOrg->getId())
            ->willReturn([567]);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }

    public function testValidExistingEntityWithCustomerOwnerAndDeepAccessLevel()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_CUSTOMER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::DEEP_LEVEL;

        $owner = $this->createCustomer(123);
        $this->testEntity->setId(234);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([$this->testEntity->getId()]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('ASSIGN', 'entity:' . Entity::class)
            ->willReturn(true);
        $this->ownerTree->expects(self::once())
            ->method('getUserSubordinateBusinessUnitIds')
            ->with($this->currentUser->getId(), $this->currentOrg->getId())
            ->willReturn([$owner->getId()]);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }

    public function testValidExistingEntityWithCustomerOwnerAndLocalAccessLevel()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_CUSTOMER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::LOCAL_LEVEL;

        $owner = $this->createCustomer(123);
        $this->testEntity->setId(234);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([$this->testEntity->getId()]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('ASSIGN', 'entity:' . Entity::class)
            ->willReturn(true);
        $this->ownerTree->expects(self::once())
            ->method('getUserBusinessUnitIds')
            ->with($this->currentUser->getId(), $this->currentOrg->getId())
            ->willReturn([$owner->getId()]);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }

    public function testInvalidExistingEntityWithUserOwnerAndBasicAccessLevel()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_USER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::BASIC_LEVEL;

        $owner = $this->createUser(123);
        $this->testEntity->setId(234);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([$this->testEntity->getId()]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('ASSIGN', 'entity:' . Entity::class)
            ->willReturn(true);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->buildViolation($this->constraint->message)
            ->atPath('owner')
            ->setParameters(['{{ owner }}' => 'owner'])
            ->assertRaised();
    }

    public function testInvalidExistingEntityWithUserOwnerAndDeepAccessLevel()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_USER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::DEEP_LEVEL;

        $owner = $this->createUser(123);
        $this->testEntity->setId(234);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([$this->testEntity->getId()]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('ASSIGN', 'entity:' . Entity::class)
            ->willReturn(true);
        $this->ownerTree->expects(self::once())
            ->method('getUserSubordinateBusinessUnitIds')
            ->with($this->currentUser->getId(), $this->currentOrg->getId())
            ->willReturn([567]);
        $this->ownerTree->expects(self::once())
            ->method('getUserBusinessUnitIds')
            ->with($owner->getId(), $this->currentOrg->getId())
            ->willReturn([678]);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->buildViolation($this->constraint->message)
            ->atPath('owner')
            ->setParameters(['{{ owner }}' => 'owner'])
            ->assertRaised();
    }

    public function testInvalidExistingEntityWithCustomerOwnerAndDeepAccessLevel()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_CUSTOMER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::DEEP_LEVEL;

        $owner = $this->createCustomer(123);
        $this->testEntity->setId(234);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([$this->testEntity->getId()]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('ASSIGN', 'entity:' . Entity::class)
            ->willReturn(true);
        $this->ownerTree->expects(self::once())
            ->method('getUserSubordinateBusinessUnitIds')
            ->with($this->currentUser->getId(), $this->currentOrg->getId())
            ->willReturn([567]);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->buildViolation($this->constraint->message)
            ->atPath('owner')
            ->setParameters(['{{ owner }}' => 'owner'])
            ->assertRaised();
    }

    public function testInvalidExistingEntityWithCustomerOwnerAndLocalAccessLevel()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_CUSTOMER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::LOCAL_LEVEL;

        $owner = $this->createCustomer(123);
        $this->testEntity->setId(234);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([$this->testEntity->getId()]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('ASSIGN', 'entity:' . Entity::class)
            ->willReturn(true);
        $this->ownerTree->expects(self::once())
            ->method('getUserBusinessUnitIds')
            ->with($this->currentUser->getId(), $this->currentOrg->getId())
            ->willReturn([567]);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->buildViolation($this->constraint->message)
            ->atPath('owner')
            ->setParameters(['{{ owner }}' => 'owner'])
            ->assertRaised();
    }

    public function testValidNewEntityWithUserOwner()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_USER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::DEEP_LEVEL;

        $owner = $this->createUser(123);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('CREATE', 'entity:' . Entity::class)
            ->willReturn(true);
        $this->ownerTree->expects(self::once())
            ->method('getUserSubordinateBusinessUnitIds')
            ->with($this->currentUser->getId(), $this->currentOrg->getId())
            ->willReturn([234, 345]);
        $this->ownerTree->expects(self::once())
            ->method('getUserBusinessUnitIds')
            ->with($owner->getId(), $this->currentOrg->getId())
            ->willReturn([345, 456]);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }

    public function testValidNewEntityWithCustomerOwnerAndDeepAccessLevel()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_CUSTOMER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::DEEP_LEVEL;

        $owner = $this->createCustomer(123);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('CREATE', 'entity:' . Entity::class)
            ->willReturn(true);
        $this->ownerTree->expects(self::once())
            ->method('getUserSubordinateBusinessUnitIds')
            ->with($this->currentUser->getId(), $this->currentOrg->getId())
            ->willReturn([234, $owner->getId()]);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }

    public function testValidNewEntityWithCustomerOwnerAndLocalAccessLevel()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_CUSTOMER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::LOCAL_LEVEL;

        $owner = $this->createCustomer(123);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('CREATE', 'entity:' . Entity::class)
            ->willReturn(true);
        $this->ownerTree->expects(self::once())
            ->method('getUserBusinessUnitIds')
            ->with($this->currentUser->getId(), $this->currentOrg->getId())
            ->willReturn([234, $owner->getId()]);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }

    public function testInvalidNewEntityWithUserOwner()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_USER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::DEEP_LEVEL;

        $owner = $this->createUser(123);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('CREATE', 'entity:' . Entity::class)
            ->willReturn(true);
        $this->ownerTree->expects(self::once())
            ->method('getUserSubordinateBusinessUnitIds')
            ->with($this->currentUser->getId(), $this->currentOrg->getId())
            ->willReturn([234]);
        $this->ownerTree->expects(self::once())
            ->method('getUserBusinessUnitIds')
            ->with($owner->getId(), $this->currentOrg->getId())
            ->willReturn([345]);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->buildViolation($this->constraint->message)
            ->atPath('owner')
            ->setParameters(['{{ owner }}' => 'owner'])
            ->assertRaised();
    }

    public function testInvalidNewEntityWithCustomerOwnerAndDeepAccessLevel()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_CUSTOMER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::DEEP_LEVEL;

        $owner = $this->createCustomer(123);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('CREATE', 'entity:' . Entity::class)
            ->willReturn(true);
        $this->ownerTree->expects(self::once())
            ->method('getUserSubordinateBusinessUnitIds')
            ->with($this->currentUser->getId(), $this->currentOrg->getId())
            ->willReturn([234]);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->buildViolation($this->constraint->message)
            ->atPath('owner')
            ->setParameters(['{{ owner }}' => 'owner'])
            ->assertRaised();
    }

    public function testInvalidNewEntityWithCustomerOwnerAndLocalAccessLevel()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_CUSTOMER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::LOCAL_LEVEL;

        $owner = $this->createCustomer(123);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('CREATE', 'entity:' . Entity::class)
            ->willReturn(true);
        $this->ownerTree->expects(self::once())
            ->method('getUserBusinessUnitIds')
            ->with($this->currentUser->getId(), $this->currentOrg->getId())
            ->willReturn([234]);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->buildViolation($this->constraint->message)
            ->atPath('owner')
            ->setParameters(['{{ owner }}' => 'owner'])
            ->assertRaised();
    }

    public function testValidNewEntityWithNewUserOwner()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_USER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::DEEP_LEVEL;

        $owner = new CustomerUser();
        $owner->setOrganization($this->currentOrg);
        $this->testEntity->setOwner($owner);
        $this->testEntity->setOrganization($this->currentOrg);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::exactly(2))
            ->method('getFieldValue')
            ->willReturnMap([
                [$this->testEntity, $ownershipMetadata->getOwnerFieldName(), $owner],
                [
                    $this->testEntity,
                    $ownershipMetadata->getOrganizationFieldName(),
                    $this->testEntity->getOrganization()
                ]
            ]);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('CREATE', 'entity:' . Entity::class)
            ->willReturn(true);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }

    public function testValidNewEntityWithNewCustomerOwner()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_CUSTOMER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::DEEP_LEVEL;

        $owner = new Customer();
        $owner->setOrganization($this->currentOrg);
        $this->testEntity->setOwner($owner);
        $this->testEntity->setOrganization($this->currentOrg);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([]);
        $entityMetadata->expects(self::exactly(2))
            ->method('getFieldValue')
            ->willReturnMap([
                [$this->testEntity, $ownershipMetadata->getOwnerFieldName(), $owner],
                [
                    $this->testEntity,
                    $ownershipMetadata->getOrganizationFieldName(),
                    $this->testEntity->getOrganization()
                ]
            ]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('CREATE', 'entity:' . Entity::class)
            ->willReturn(true);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }

    public function testInvalidNewEntityWithNewUserOwner()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_USER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::DEEP_LEVEL;

        $owner = new CustomerUser();
        $owner->setOrganization(new Organization());
        $this->testEntity->setOwner($owner);
        $this->testEntity->setOrganization($this->currentOrg);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([]);
        $entityMetadata->expects(self::exactly(2))
            ->method('getFieldValue')
            ->willReturnMap([
                [$this->testEntity, $ownershipMetadata->getOwnerFieldName(), $owner],
                [
                    $this->testEntity,
                    $ownershipMetadata->getOrganizationFieldName(),
                    $this->testEntity->getOrganization()
                ]
            ]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('CREATE', 'entity:' . Entity::class)
            ->willReturn(true);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->buildViolation($this->constraint->message)
            ->atPath('owner')
            ->setParameters(['{{ owner }}' => 'owner'])
            ->assertRaised();
    }

    public function testInvalidNewEntityWithNewCustomerOwner()
    {
        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_CUSTOMER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::DEEP_LEVEL;

        $owner = new Customer();
        $owner->setOrganization(new Organization());
        $this->testEntity->setOwner($owner);
        $this->testEntity->setOrganization($this->currentOrg);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::exactly(2))
            ->method('getFieldValue')
            ->willReturnMap([
                [$this->testEntity, $ownershipMetadata->getOwnerFieldName(), $owner],
                [
                    $this->testEntity,
                    $ownershipMetadata->getOrganizationFieldName(),
                    $this->testEntity->getOrganization()
                ]
            ]);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('CREATE', 'entity:' . Entity::class)
            ->willReturn(true);

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->buildViolation($this->constraint->message)
            ->atPath('owner')
            ->setParameters(['{{ owner }}' => 'owner'])
            ->assertRaised();
    }

    public function testValidExistingEntityWithCustomerOwnerAndLocalAccessLevelAndWithoutUserInToken()
    {
        $tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $tokenAccessor->expects(self::any())
            ->method('getUser')
            ->willReturn(null);
        $this->validator = new FrontendOwnerValidator(
            $this->doctrine,
            $this->ownershipMetadataProvider,
            $this->authorizationChecker,
            $tokenAccessor,
            $this->ownerTreeProvider,
            $this->aclVoter,
            $this->aclGroupProvider
        );

        $ownershipMetadata = $this->createOwnershipMetadata('FRONTEND_CUSTOMER');
        $entityMetadata = $this->createMock(ClassMetadata::class);
        $accessLevel = AccessLevel::LOCAL_LEVEL;

        $owner = $this->createCustomer(123);
        $this->testEntity->setId(234);
        $this->testEntity->setOwner($owner);

        $this->expectManageableEntity($entityMetadata, [$ownershipMetadata->getOwnerFieldName() => null]);
        $entityMetadata->expects(self::once())
            ->method('getFieldValue')
            ->with($this->testEntity, $ownershipMetadata->getOwnerFieldName())
            ->willReturn($owner);
        $entityMetadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($this->testEntity)
            ->willReturn([$this->testEntity->getId()]);

        $this->ownershipMetadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with(Entity::class)
            ->willReturn($ownershipMetadata);

        $this->expectAddOneShotIsGrantedObserver($accessLevel);
        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with('ASSIGN', 'entity:' . Entity::class)
            ->willReturn(true);
        $this->ownerTree->expects(self::never())
            ->method('getUserBusinessUnitIds');

        $this->validator->validate($this->testEntity, $this->constraint);
        $this->assertNoViolation();
    }
}
