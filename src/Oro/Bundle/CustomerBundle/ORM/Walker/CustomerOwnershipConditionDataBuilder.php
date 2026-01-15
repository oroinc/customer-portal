<?php

namespace Oro\Bundle\CustomerBundle\ORM\Walker;

use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Domain\OneShotIsGrantedObserver;
use Oro\Bundle\SecurityBundle\Acl\Group\AclGroupProviderInterface;
use Oro\Bundle\SecurityBundle\Acl\Voter\AclVoterInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AbstractOwnershipConditionDataBuilder;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclConditionDataBuilderInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use Oro\Bundle\SecurityBundle\Owner\OwnerTreeProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Allows access to to entities with frontend ownerwhip with deep access levels
 */
class CustomerOwnershipConditionDataBuilder extends AbstractOwnershipConditionDataBuilder
{
    /** @var AclConditionDataBuilderInterface */
    protected $ownerConditionBuilder;

    /** @var AclVoterInterface */
    protected $aclVoter;

    /** @var OwnerTreeProviderInterface */
    protected $treeProvider;

    /** @var AclGroupProviderInterface */
    protected $aclGroupProvider;

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
    public function getAclConditionData($entityClassName, $permissions = 'VIEW')
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

    /**
     * @param FrontendOwnershipMetadata $metadata
     * @param int $accessLevel
     *
     * @return array
     */
    protected function getConstraintForAccessLevel(FrontendOwnershipMetadata $metadata, $accessLevel)
    {
        if (!in_array($accessLevel, [AccessLevel::LOCAL_LEVEL, AccessLevel::DEEP_LEVEL], true)) {
            return [];
        }

        $customerId = $this->getUser()?->getCustomer()?->getId();
        if (!$customerId) {
            return [];
        }

        $customersIds = $accessLevel === AccessLevel::DEEP_LEVEL
            ? $this->treeProvider->getTree()->getSubordinateBusinessUnitIds($customerId)
            : [];

        $customersIds[] = $customerId;

        return [
            $metadata->getCustomerFieldName(),
            $customersIds
        ];
    }

    /**
     * @param array $constraint
     *
     * @return bool
     */
    private function isAccessAlreadyDenied(array $constraint)
    {
        return array_key_exists(1, $constraint) && $constraint[1] === null;
    }
}
