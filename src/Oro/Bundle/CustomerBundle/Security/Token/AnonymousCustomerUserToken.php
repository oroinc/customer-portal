<?php

namespace Oro\Bundle\CustomerBundle\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Role\RoleInterface;

use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;

class AnonymousCustomerUserToken extends AbstractToken
{
    /**
     * @var CustomerVisitor
     */
    private $visitor;

    /**
     * @var array
     */
    protected $credentials = [];

    /**
     * @param string|object        $user
     * @param RoleInterface[]      $roles
     * @param CustomerVisitor|null $visitor
     */
    public function __construct($user, array $roles = [], CustomerVisitor $visitor = null)
    {
        parent::__construct($roles);

        $this->setUser($user);
        $this->setVisitor($visitor);
        $this->setAuthenticated(true);
    }

    /**
     * @return CustomerVisitor
     */
    public function getVisitor()
    {
        return $this->visitor;
    }

    /**
     * @param CustomerVisitor $visitor
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
