<?php

namespace Oro\Bundle\CustomerBundle\Security\Token;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\AnonymousToken;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Oro\Bundle\SecurityBundle\Authentication\Token\RolesAndOrganizationAwareTokenTrait;
use Oro\Bundle\SecurityBundle\Authentication\Token\RolesAwareTokenInterface;

/**
 * The authentication token for a guest (visitor) for the storefront.
 */
class AnonymousCustomerUserToken extends AnonymousToken implements
    OrganizationAwareTokenInterface,
    RolesAwareTokenInterface
{
    use RolesAndOrganizationAwareTokenTrait;

    private array $credentials = [];

    public function __construct(
        CustomerVisitor $visitor,
        array $roles = [],
        Organization $organization = null
    ) {
        parent::__construct('', $visitor, $this->initRoles($roles));

        $this->setUser($visitor);
        if (null !== $organization) {
            $this->setOrganization($organization);
        }
    }

    public function getVisitor(): ?CustomerVisitor
    {
        // customer visitor === anonymous user
        return $this->getUser();
    }

    public function getCredentials(): array
    {
        return $this->credentials;
    }

    public function setCredentials(array $credentials): void
    {
        $this->credentials = $credentials;
    }
}
