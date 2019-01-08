<?php

namespace Oro\Bundle\CustomerBundle\Security\Token;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationContextTokenInterface;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationContextTokenSerializerTrait;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * AnonymousCustomerUserToken which represents authenticated CustomerUser
 *
 */
class AnonymousCustomerUserToken extends AnonymousToken implements OrganizationContextTokenInterface
{
    use OrganizationContextTokenSerializerTrait;

    /**
     * @var CustomerVisitor
     */
    private $visitor;

    /**
     * @var array
     */
    protected $credentials = [];

    /**
     * @param string|object $user
     * @param RoleInterface[] $roles
     * @param CustomerVisitor|null $visitor
     * @param Organization|null $organizationContext
     */
    public function __construct(
        $user,
        array $roles = [],
        CustomerVisitor $visitor = null,
        Organization $organizationContext = null
    ) {
        if ($organizationContext) {
            $this->setOrganizationContext($organizationContext);
        }

        parent::__construct('', $user, $roles);

        $this->setUser($user);
        $this->setVisitor($visitor);
        $this->setAuthenticated(true);
    }

    /**
     * @return CustomerVisitor|null
     */
    public function getVisitor()
    {
        return $this->visitor;
    }

    /**
     * @param CustomerVisitor|null $visitor
     */
    public function setVisitor(CustomerVisitor $visitor = null)
    {
        $this->visitor = $visitor;
    }

    /**
     * @param array $credentials
     */
    public function setCredentials(array $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * {@inheritDoc}
     */
    public function getCredentials()
    {
        return $this->credentials;
    }
}
