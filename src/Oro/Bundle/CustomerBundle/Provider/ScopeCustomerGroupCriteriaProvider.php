<?php

namespace Oro\Bundle\CustomerBundle\Provider;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\ScopeBundle\Manager\AbstractScopeCriteriaProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ScopeCustomerGroupCriteriaProvider extends AbstractScopeCriteriaProvider
{
    const FIELD_NAME = 'customerGroup';

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var CustomerUserRelationsProvider */
    protected $customerUserProvider;

    /**
     * @param TokenStorageInterface         $tokenStorage
     * @param CustomerUserRelationsProvider $customerUserRelationsProvider
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        CustomerUserRelationsProvider $customerUserRelationsProvider
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->customerUserProvider = $customerUserRelationsProvider;
    }

    /**
     * @return string
     */
    public function getCriteriaField()
    {
        return self::FIELD_NAME;
    }

    /**
     * @return array
     */
    public function getCriteriaForCurrentScope()
    {
        $loggedUser = null;
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser() instanceof CustomerUser) {
            $loggedUser = $token->getUser();
        }

        return [$this->getCriteriaField() => $this->customerUserProvider->getCustomerGroup($loggedUser)];
    }

    /**
     * {@inheritdoc}
     */
    public function getCriteriaValueType()
    {
        return CustomerGroup::class;
    }
}
