<?php

namespace Oro\Bundle\CustomerBundle\Config;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\ConfigBundle\Config\AbstractScopeManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * The manager for configuration on customer level.
 */
class CustomerScopeManager extends AbstractScopeManager
{
    private TokenStorageInterface $tokenStorage;
    private int $scopeId = 0;

    public function setTokenStorage(TokenStorageInterface $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    #[\Override]
    public function getScopedEntityName(): string
    {
        return 'customer';
    }

    #[\Override]
    public function getScopeId(): int
    {
        $this->ensureScopeIdInitialized();

        return $this->scopeId;
    }

    #[\Override]
    public function setScopeId(?int $scopeId): void
    {
        $this->dispatchScopeIdChangeEvent();

        $this->scopeId = $scopeId ?? 0;
    }

    #[\Override]
    protected function isSupportedScopeEntity(object $entity): bool
    {
        return
            $entity instanceof Customer
            || ($entity instanceof CustomerAwareInterface && null !== $entity->getCustomer());
    }

    #[\Override]
    protected function getScopeEntityIdValue(object $entity): int
    {
        if ($entity instanceof Customer) {
            return (int)$entity->getId();
        }
        if ($entity instanceof CustomerAwareInterface) {
            $customer = $entity->getCustomer();
            if (null !== $customer) {
                return (int)$customer->getId();
            }
            throw new \LogicException(sprintf('"%s" does not have a customer.', ClassUtils::getClass($entity)));
        }
        throw new \LogicException(sprintf('"%s" is not supported.', ClassUtils::getClass($entity)));
    }

    /**
     * Makes sure that the scope id is set
     */
    protected function ensureScopeIdInitialized(): void
    {
        if (0 === $this->scopeId) {
            $token = $this->tokenStorage->getToken();
            if (null !== $token) {
                $user = $token->getUser();
                if ($user instanceof CustomerUserInterface && null !== $user->getCustomer()) {
                    $this->scopeId = $user->getCustomer()->getId();
                }
            }
        }
    }
}
