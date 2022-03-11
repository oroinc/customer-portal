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
    private ?int $scopeId = null;

    private TokenStorageInterface $tokenStorage;

    public function getScopedEntityName(): string
    {
        return 'customer';
    }

    public function getScopeId(): ?int
    {
        $this->ensureScopeIdInitialized();

        return $this->scopeId;
    }

    public function setScopeId($scopeId): void
    {
        $this->dispatchScopeIdChangeEvent();

        $this->scopeId = $scopeId;
    }

    protected function isSupportedScopeEntity($entity): bool
    {
        return
            $entity instanceof Customer
            || ($entity instanceof CustomerAwareInterface && null !== $entity->getCustomer());
    }

    protected function getScopeEntityIdValue($entity): int
    {
        if ($entity instanceof Customer) {
            return $entity->getId();
        }
        if ($entity instanceof CustomerAwareInterface) {
            $customer = $entity->getCustomer();
            if (null === $customer) {
                throw new \LogicException(sprintf('"%s" does not have a customer.', \get_class($entity)));
            }

            return $customer->getId();
        }
        throw new \LogicException(sprintf('"%s" is not supported.', \get_class($entity)));
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

    public function setTokenStorage(TokenStorageInterface $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }
}
