<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RecordOwnerDataListener
{
    const OWNER_TYPE_USER = 'FRONTEND_USER';
    const OWNER_TYPE_CUSTOMER = 'FRONTEND_CUSTOMER';

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var ConfigProvider */
    protected $configProvider;

    /** @var PropertyAccessor */
    protected $propertyAccessor;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param ConfigProvider $configProvider
     * @param PropertyAccessor $propertyAccessor
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ConfigProvider $configProvider,
        PropertyAccessor $propertyAccessor
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->configProvider = $configProvider;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Handle prePersist.
     *
     * @param LifecycleEventArgs $args
     * @throws \LogicException when getOwner method isn't implemented for entity with ownership type
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $user = $this->getLoggedCustomerUser();
        if (!$user) {
            return;
        }

        $entity    = $args->getEntity();
        $className = ClassUtils::getClass($entity);
        if ($this->configProvider->hasConfig($className)) {
            $config = $this->configProvider->getConfig($className);
            $frontendOwnerType = $config->get('frontend_owner_type');
            $ownerFieldName = $config->get('frontend_owner_field_name');
            // set default owner for organization and user owning entities
            if ($frontendOwnerType
                && in_array($frontendOwnerType, [self::OWNER_TYPE_USER, self::OWNER_TYPE_CUSTOMER], true)
                && !$this->propertyAccessor->getValue($entity, $ownerFieldName)
            ) {
                $this->setOwner($frontendOwnerType, $entity, $user, $ownerFieldName);

                //set customer
                $this->setDefaultCustomer($user, $config, $entity);
            }
        }
    }

    /**
     * @param string $ownerType
     * @param object $entity
     * @param CustomerUser $user
     * @param string $ownerFieldName
     */
    protected function setOwner($ownerType, $entity, $user, $ownerFieldName)
    {
        $owner = null;
        if ($ownerType === self::OWNER_TYPE_USER) {
            $owner = $user;
        }
        if ($ownerType === self::OWNER_TYPE_CUSTOMER) {
            $owner = $user->getCustomer();
        }
        $this->propertyAccessor->setValue($entity, $ownerFieldName, $owner);
    }

    /**
     * @param CustomerUser $user
     * @param ConfigInterface $config
     * @param object $entity
     */
    protected function setDefaultCustomer(CustomerUser $user, ConfigInterface $config, $entity)
    {
        if ($user->getCustomer() && $config->has('frontend_customer_field_name')) {
            $fieldName = $config->get('frontend_customer_field_name');

            if (!$this->propertyAccessor->getValue($entity, $fieldName)) {
                $this->propertyAccessor->setValue($entity, $fieldName, $user->getCustomer());
            }
        }
    }

    /**
     * @return CustomerUser|null
     */
    protected function getLoggedCustomerUser()
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if ($user instanceof CustomerUser) {
            return $user;
        }

        if ($token instanceof AnonymousCustomerUserToken && $token->getVisitor()) {
            return $token->getVisitor()->getCustomerUser();
        }

        return null;
    }
}
