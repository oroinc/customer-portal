<?php

namespace Oro\Bundle\CustomerBundle\Config;

use Oro\Bundle\ConfigBundle\Config\AbstractScopeManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Customer scope manager for configuration on customer level.
 */
class CustomerScopeManager extends AbstractScopeManager
{
    private ?int $scopeId = 0;

    private TokenStorageInterface $tokenStorage;

    /**
     * {@inheritdoc}
     */
    public function getScopedEntityName(): string
    {
        return 'customer';
    }

    /**
     * {@inheritdoc}
     */
    public function getScopeId(): int
    {
        $this->ensureScopeIdInitialized();

        return $this->scopeId;
    }

    /**
     * {@inheritdoc}
     */
    public function setScopeId($scopeId): void
    {
        $this->dispatchScopeIdChangeEvent();

        $this->scopeId = $scopeId;
    }

    /**
     * {@inheritdoc}
     */
    protected function isSupportedScopeEntity($entity): bool
    {
        return $entity instanceof Customer || $this->isCustomerAware($entity);
    }

    /**
     * @param $entity
     * @return bool
     */
    private function isCustomerAware($entity): bool
    {
        return $entity instanceof CustomerAwareInterface && $entity->getCustomer();
    }

    /**
     * Makes sure that the scope id is set
     */
    protected function ensureScopeIdInitialized(): void
    {
        if (!$this->scopeId) {
            $scopeId = 0;

            $token = $this->tokenStorage->getToken();
            if (null !== $token) {
                $user = $token->getUser();
                if ($user instanceof CustomerUserInterface && $user->getCustomer()) {
                    $scopeId = $user->getCustomer()->getId() ?: $scopeId;
                }
            }

            $this->scopeId = $scopeId;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopeEntityIdValue($entity): int
    {
        if ($this->isCustomerAware($entity)) {
            return $entity->getCustomer()->getId();
        }

        return $entity->getId();
    }

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }
}
