<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInterface;
use Oro\Bundle\ScopeBundle\Manager\ScopeCriteriaProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * The scope criteria provider for the current customer.
 */
class ScopeCustomerCriteriaProvider implements ScopeCriteriaProviderInterface
{
    public const CUSTOMER = 'customer';

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    #[\Override]
    public function getCriteriaField()
    {
        return self::CUSTOMER;
    }

    #[\Override]
    public function getCriteriaValue()
    {
        $token = $this->tokenStorage->getToken();
        if (null !== $token) {
            $user = $token->getUser();
            if ($user instanceof CustomerUserInterface) {
                return $user->getCustomer();
            }
        }

        return null;
    }

    #[\Override]
    public function getCriteriaValueType()
    {
        return Customer::class;
    }
}
