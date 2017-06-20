<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RecordOwnerDataListener
{
    const OWNER_TYPE_USER = 'FRONTEND_USER';
    const OWNER_TYPE_ACCOUNT = 'FRONTEND_CUSTOMER';

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var ConfigProvider */
    protected $configProvider;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param ConfigProvider        $configProvider
     */
    public function __construct(TokenStorageInterface $tokenStorage, ConfigProvider $configProvider)
    {
        $this->tokenStorage = $tokenStorage;
        $this->configProvider = $configProvider;
    }

    /**
     * Handle prePersist.
     *
     * @param LifecycleEventArgs $args
     * @throws \LogicException when getOwner method isn't implemented for entity with ownership type
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }
        $user = $token->getUser();
        if (!($user instanceof CustomerUser)) {
            return;
        }
        $entity    = $args->getEntity();
        $className = ClassUtils::getClass($entity);
        if ($this->configProvider->hasConfig($className)) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $config = $this->configProvider->getConfig($className);
            $frontendOwnerType = $config->get('frontend_owner_type');
            $ownerFieldName = $config->get('frontend_owner_field_name');
            // set default owner for organization and user owning entities
            if ($frontendOwnerType
                && in_array($frontendOwnerType, [self::OWNER_TYPE_USER, self::OWNER_TYPE_ACCOUNT], true)
                && !$accessor->getValue($entity, $ownerFieldName)
            ) {
                $owner = null;
                if ($frontendOwnerType === self::OWNER_TYPE_USER) {
                    $owner = $user;
                }
                if ($frontendOwnerType === self::OWNER_TYPE_ACCOUNT) {
                    $owner = $user->getCustomer();
                }
                $accessor->setValue(
                    $entity,
                    $ownerFieldName,
                    $owner
                );
            }
        }
    }
}
