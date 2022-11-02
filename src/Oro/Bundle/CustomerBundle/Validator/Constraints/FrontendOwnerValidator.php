<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Validator\Constraints\AbstractOwnerValidator;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataInterface;

/**
 * Validates that the current logged in customer user is granted to change the frontend owner for an entity.
 */
class FrontendOwnerValidator extends AbstractOwnerValidator
{
    /**
     * {@inheritdoc}
     */
    protected function isValidExistingOwner(OwnershipMetadataInterface $ownershipMetadata, $owner, $accessLevel)
    {
        if (AccessLevel::SYSTEM_LEVEL === $accessLevel || AccessLevel::GLOBAL_LEVEL === $accessLevel) {
            return true;
        }

        $currentUser = $this->tokenAccessor->getUser();
        if (null === $currentUser) {
            return true;
        }

        if ($ownershipMetadata->isUserOwned()) {
            return $this->canUserBeSetAsOwner(
                $currentUser,
                $owner,
                $accessLevel,
                $this->getOrganization()
            );
        }

        if ($ownershipMetadata->isBusinessUnitOwned()) {
            return $this->canCustomerBeSetAsOwner(
                $currentUser,
                $owner,
                $accessLevel,
                $this->getOrganization()
            );
        }

        return true;
    }

    /**
     * @param CustomerUser $currentUser
     * @param CustomerUser $newUser
     * @param string       $accessLevel
     * @param Organization $organization
     *
     * @return bool
     */
    private function canUserBeSetAsOwner(
        CustomerUser $currentUser,
        CustomerUser $newUser,
        $accessLevel,
        Organization $organization
    ) {
        if (AccessLevel::BASIC_LEVEL === $accessLevel) {
            return $newUser->getId() == $currentUser->getId();
        }

        $allowedCustomerIds = $this->getAllowedCustomerIds($accessLevel, $currentUser, $organization);
        if (empty($allowedCustomerIds)) {
            return false;
        }

        $newUserCustomerIds = $this->ownerTreeProvider->getTree()->getUserBusinessUnitIds(
            $newUser->getId(),
            $organization->getId()
        );
        $intersectCustomerIds = array_intersect($allowedCustomerIds, $newUserCustomerIds);

        return !empty($intersectCustomerIds);
    }

    /**
     * @param CustomerUser $currentUser
     * @param Customer     $entityOwner
     * @param string       $accessLevel
     * @param Organization $organization
     *
     * @return bool
     */
    private function canCustomerBeSetAsOwner(
        CustomerUser $currentUser,
        Customer $entityOwner,
        $accessLevel,
        Organization $organization
    ) {
        return in_array(
            $entityOwner->getId(),
            $this->getAllowedCustomerIds($accessLevel, $currentUser, $organization),
            true
        );
    }

    /**
     * @param int          $accessLevel
     * @param CustomerUser $user
     * @param Organization $organization
     *
     * @return array
     */
    private function getAllowedCustomerIds($accessLevel, CustomerUser $user, Organization $organization)
    {
        if (AccessLevel::LOCAL_LEVEL === $accessLevel) {
            return $this->ownerTreeProvider->getTree()->getUserBusinessUnitIds(
                $user->getId(),
                $organization->getId()
            );
        }

        if (AccessLevel::DEEP_LEVEL === $accessLevel) {
            return $this->ownerTreeProvider->getTree()->getUserSubordinateBusinessUnitIds(
                $user->getId(),
                $organization->getId()
            );
        }

        return [];
    }
}
