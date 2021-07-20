<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\ScopeBundle\Manager\ScopeCriteriaProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * The scope criteria provider for the current customer group.
 */
class ScopeCustomerGroupCriteriaProvider implements ScopeCriteriaProviderInterface
{
    public const CUSTOMER_GROUP = 'customerGroup';

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var CustomerUserRelationsProvider */
    private $customerUserProvider;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        CustomerUserRelationsProvider $customerUserRelationsProvider
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->customerUserProvider = $customerUserRelationsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getCriteriaField()
    {
        return self::CUSTOMER_GROUP;
    }

    /**
     * {@inheritdoc}
     */
    public function getCriteriaValue()
    {
        $loggedUser = null;
        $token = $this->tokenStorage->getToken();
        if (null !== $token) {
            $user = $token->getUser();
            if ($user instanceof CustomerUser) {
                $loggedUser = $user;
            }
        }

        return $this->customerUserProvider->getCustomerGroup($loggedUser);
    }

    /**
     * {@inheritdoc}
     */
    public function getCriteriaValueType()
    {
        return CustomerGroup::class;
    }
}
