<?php

namespace Oro\Bundle\CustomerBundle\Security\Token;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\AuthenticatedTokenTrait;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenTrait;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Role\Role;

/**
 * The authentication token for a guest (visitor) for the storefront.
 */
class AnonymousCustomerUserToken extends AnonymousToken implements OrganizationAwareTokenInterface
{
    use AuthenticatedTokenTrait;
    use OrganizationAwareTokenTrait;

    /** @var CustomerVisitor */
    private $visitor;

    /** @var array */
    private $credentials = [];

    /**
     * @param string|object        $user
     * @param Role[]               $roles
     * @param CustomerVisitor|null $visitor
     * @param Organization|null    $organization
     */
    public function __construct(
        $user,
        array $roles = [],
        CustomerVisitor $visitor = null,
        Organization $organization = null
    ) {
        parent::__construct('', $user, $roles);

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
