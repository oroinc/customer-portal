<?php

namespace Oro\Bundle\CustomerBundle\Config;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\ConfigBundle\Config\AbstractScopeManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * The manager for configuration on customer group level.
 */
class CustomerGroupScopeManager extends AbstractScopeManager
{
    private TokenStorageInterface $tokenStorage;
    private int $scopeId = 0;

    public function setTokenStorage(TokenStorageInterface $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritDoc}
     */
    public function getScopedEntityName(): string
    {
        return 'customer_group';
    }

    /**
     * {@inheritDoc}
     */
    public function getScopeId(): int
    {
        $this->ensureScopeIdInitialized();

        return $this->scopeId;
    }

    /**
     * {@inheritDoc}
     */
    public function setScopeId(?int $scopeId): void
    {
        $this->dispatchScopeIdChangeEvent();

        $this->scopeId = $scopeId ?? 0;
    }

    /**
     * {@inheritDoc}
     */
    protected function isSupportedScopeEntity(object $entity): bool
    {
        return
            $entity instanceof CustomerGroup
            || ($entity instanceof Customer && null !== $entity->getGroup());
    }

    /**
     * {@inheritDoc}
     */
    protected function getScopeEntityIdValue(object $entity): int
    {
        if ($entity instanceof CustomerGroup) {
            return (int)$entity->getId();
        }
        if ($entity instanceof Customer && $entity->getGroup()) {
            $customerGroup = $entity->getGroup();
            if (null !== $customerGroup) {
                return (int)$customerGroup->getId();
            }
            throw new \LogicException(sprintf('"%s" does not have a customer group.', Customer::class));
        }
        throw new \LogicException(sprintf('"%s" is not supported.', ClassUtils::getClass($entity)));
    }

    protected function ensureScopeIdInitialized(): void
    {
        if (0 === $this->scopeId) {
            $token = $this->tokenStorage->getToken();
            if (null !== $token) {
                $user = $token->getUser();
                if ($user instanceof CustomerUserInterface && null !== $user->getCustomer()) {
                    $customerGroup = $user->getCustomer()->getGroup();
                    if (null !== $customerGroup) {
                        $this->scopeId = $customerGroup->getId();
                    }
                }
            }
        }
    }
}
