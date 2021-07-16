<?php

namespace Oro\Bundle\CustomerBundle\Security\Token;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Oro\Bundle\SecurityBundle\Authentication\Token\RolesAndOrganizationAwareTokenTrait;
use Oro\Bundle\SecurityBundle\Authentication\Token\RolesAwareTokenInterface;
use Oro\Bundle\SecurityBundle\Model\Role;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

/**
 * The authentication token for a guest (visitor) for the storefront.
 */
class AnonymousCustomerUserToken extends AnonymousToken implements
    OrganizationAwareTokenInterface,
    RolesAwareTokenInterface
{
    use RolesAndOrganizationAwareTokenTrait;

    /** @var CustomerVisitor */
    private $visitor;

    /** @var array */
    private $credentials = [];

    /**
     * @param string|object $user
     * @param Role[]|string[] $roles
     * @param CustomerVisitor|null $visitor
     * @param Organization|null $organization
     */
    public function __construct(
        $user,
        array $roles = [],
        CustomerVisitor $visitor = null,
        Organization $organization = null
    ) {
        parent::__construct('', $user, $this->initRoles($roles));

        $this->setVisitor($visitor);
        if (null !== $organization) {
            $this->setOrganization($organization);
        }
        $this->setAuthenticated(true);
    }

    /**
     * @return CustomerVisitor|null
     */
    public function getVisitor()
    {
        return $this->visitor;
    }

    public function setVisitor(CustomerVisitor $visitor = null)
    {
        $this->visitor = $visitor;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    public function setCredentials(array $credentials)
    {
        $this->credentials = $credentials;
    }
}
