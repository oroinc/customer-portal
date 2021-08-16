<?php

namespace Oro\Bundle\CustomerBundle\Config;

use Oro\Bundle\ConfigBundle\Config\AbstractScopeManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Scope manager for customerGroup scope.
 */
class CustomerGroupScopeManager extends AbstractScopeManager
{
    private ?int $scopeId = 0;
    private TokenStorageInterface $tokenStorage;

    /**
     * {@inheritdoc}
     */
    public function getScopedEntityName(): string
    {
        return 'customer_group';
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
        return
            $entity instanceof CustomerGroup
            || ($entity instanceof Customer && null !== $entity->getGroup());
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopeEntityIdValue($entity): int
    {
        if ($entity instanceof CustomerGroup) {
            return $entity->getId();
        }
        if ($entity instanceof Customer && $entity->getGroup()) {
            $customerGroup = $entity->getGroup();
            if (null === $customerGroup) {
                throw new \LogicException(sprintf('"%s" does not have a customer group.', \get_class($entity)));
            }

            return $customerGroup->getId();
        }
        throw new \LogicException(sprintf('"%s" is not supported.', \get_class($entity)));
    }

    protected function ensureScopeIdInitialized(): void
    {
        if (!$this->scopeId) {
            $scopeId = 0;

            $token = $this->tokenStorage->getToken();
            if (null !== $token) {
                $user = $token->getUser();
                if ($user instanceof CustomerUserInterface && $user->getCustomer()) {
                    $customerGroup = $user->getCustomer()->getGroup();

                    if ($customerGroup) {
                        $scopeId = $customerGroup->getId() ?: $scopeId;
                    }
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
