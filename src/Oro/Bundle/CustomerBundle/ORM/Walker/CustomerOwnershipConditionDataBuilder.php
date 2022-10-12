<?php

namespace Oro\Bundle\CustomerBundle\ORM\Walker;

use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Domain\OneShotIsGrantedObserver;
use Oro\Bundle\SecurityBundle\Acl\Group\AclGroupProviderInterface;
use Oro\Bundle\SecurityBundle\Acl\Voter\AclVoterInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclConditionDataBuilderInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Allows access to entities with frontend ownership with deep access levels.
 */
class CustomerOwnershipConditionDataBuilder implements AclConditionDataBuilderInterface
{
    protected AuthorizationCheckerInterface $authorizationChecker;
    protected TokenStorageInterface $tokenStorage;
    protected OwnershipMetadataProviderInterface $metadataProvider;
    protected AclConditionDataBuilderInterface $ownerConditionBuilder;
    protected AclVoterInterface $aclVoter;
    protected OwnerTreeProviderInterface $treeProvider;
    protected AclGroupProviderInterface $aclGroupProvider;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        OwnershipMetadataProviderInterface $metadataProvider,
        OwnerTreeProviderInterface $treeProvider,
        AclVoterInterface $aclVoter,
        AclConditionDataBuilderInterface $ownerConditionBuilder,
        AclGroupProviderInterface $aclGroupProvider
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->metadataProvider = $metadataProvider;
        $this->treeProvider = $treeProvider;
        $this->aclVoter = $aclVoter;
        $this->ownerConditionBuilder = $ownerConditionBuilder;
        $this->aclGroupProvider = $aclGroupProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function getAclConditionData(string $entityClassName, string|array $permissions = 'VIEW'): ?array
    {
        $constraint = $this->ownerConditionBuilder->getAclConditionData($entityClassName, $permissions);

        $metadata = $this->metadataProvider->getMetadata($entityClassName);
        if ($metadata instanceof FrontendOwnershipMetadata
            && $metadata->getCustomerFieldName()
            && !$this->isAccessAlreadyDenied($constraint)
        ) {
            $observer = new OneShotIsGrantedObserver();
            $this->aclVoter->addOneShotIsGrantedObserver($observer);

            $groupedEntityClassName = $entityClassName;
            $group = $this->aclGroupProvider->getGroup();
            if ($group) {
                $groupedEntityClassName = sprintf('%s@%s', $group, $entityClassName);
            }

            if ($this->isEntityGranted($permissions, $groupedEntityClassName)) {
                $constraint = array_replace(
                    $constraint,
                    $this->getConstraintForAccessLevel($metadata, $observer->getAccessLevel())
                );
            }
        }

        return $constraint;
    }

    protected function getConstraintForAccessLevel(FrontendOwnershipMetadata $metadata, int $accessLevel): array
    {
        if (AccessLevel::LOCAL_LEVEL !== $accessLevel && AccessLevel::DEEP_LEVEL !== $accessLevel) {
            return [];
        }

        $customerId = $this->getUser()->getCustomer()->getId();
        $customersIds = $accessLevel === AccessLevel::DEEP_LEVEL
            ? $this->treeProvider->getTree()->getSubordinateBusinessUnitIds($customerId)
            : [];

        $customersIds[] = $customerId;

        return [
            $metadata->getCustomerFieldName(),
            $customersIds
        ];
    }

    private function isAccessAlreadyDenied(array $constraint): bool
    {
        return array_key_exists(1, $constraint) && $constraint[1] === null;
    }

    protected function isEntityGranted(string|array $permissions, string $entityType): bool
    {
        return $this->authorizationChecker->isGranted(
            $permissions,
            new ObjectIdentity('entity', $entityType)
        );
    }

    protected function getUser(): ?UserInterface
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }

        $user = $token->getUser();
        if (!is_object($user) || !is_a($user, $this->metadataProvider->getUserClass())) {
            return null;
        }

        return $user;
    }
}
