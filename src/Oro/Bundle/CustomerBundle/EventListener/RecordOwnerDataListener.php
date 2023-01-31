<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\CustomerUserProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Sets storefront owner to new entity if this data was not set yet.
 */
class RecordOwnerDataListener
{
    private const OWNER_TYPE_USER = 'FRONTEND_USER';
    private const OWNER_TYPE_CUSTOMER = 'FRONTEND_CUSTOMER';
    private const FRONTEND_OWNER_TYPE = 'frontend_owner_type';
    private const FRONTEND_OWNER_FIELD_NAME = 'frontend_owner_field_name';
    private const FRONTEND_CUSTOMER_FIELD_NAME = 'frontend_customer_field_name';
    private const CONFIG_SCOPE = 'ownership';

    private CustomerUserProvider $customerUserProvider;
    private ConfigManager $configManager;
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        CustomerUserProvider $customerUserProvider,
        ConfigManager $configManager,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->customerUserProvider = $customerUserProvider;
        $this->configManager = $configManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $user = $this->customerUserProvider->getLoggedUser();
        if (null === $user) {
            return;
        }

        $entity = $args->getEntity();
        $className = ClassUtils::getClass($entity);
        if ($this->configManager->hasConfig($className)) {
            $config = $this->configManager->getEntityConfig(self::CONFIG_SCOPE, $className);
            $ownerType = $config->get(self::FRONTEND_OWNER_TYPE);
            if ($ownerType) {
                $this->setOwner($entity, $ownerType, $config, $user);
            }
        }
    }

    private function setOwner(object $entity, string $ownerType, ConfigInterface $config, CustomerUser $user): void
    {
        $ownerFieldName = $config->get(self::FRONTEND_OWNER_FIELD_NAME);
        if (self::OWNER_TYPE_USER === $ownerType) {
            if (null === $this->propertyAccessor->getValue($entity, $ownerFieldName)) {
                $this->propertyAccessor->setValue($entity, $ownerFieldName, $user);
            }
            $customerFieldName = $config->get(self::FRONTEND_CUSTOMER_FIELD_NAME);
            if ($customerFieldName && null === $this->propertyAccessor->getValue($entity, $customerFieldName)) {
                $this->propertyAccessor->setValue($entity, $customerFieldName, $user->getCustomer());
            }
        } elseif (self::OWNER_TYPE_CUSTOMER === $ownerType
            && null === $this->propertyAccessor->getValue($entity, $ownerFieldName)
        ) {
            $this->propertyAccessor->setValue($entity, $ownerFieldName, $user->getCustomer());
        }
    }
}
