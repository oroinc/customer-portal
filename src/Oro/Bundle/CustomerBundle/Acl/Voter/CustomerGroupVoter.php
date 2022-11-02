<?php

namespace Oro\Bundle\CustomerBundle\Acl\Voter;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Prevents removal of a customer group that is configured to be used for anonymous customers.
 */
class CustomerGroupVoter extends AbstractEntityVoter implements ServiceSubscriberInterface
{
    /** {@inheritDoc} */
    protected $supportedAttributes = [BasicPermission::DELETE];

    private ContainerInterface $container;

    public function __construct(DoctrineHelper $doctrineHelper, ContainerInterface $container)
    {
        parent::__construct($doctrineHelper);
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_config.global' => ConfigManager::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        return $identifier && $this->isAnonymousCustomerGroup($identifier)
            ? self::ACCESS_DENIED
            : self::ACCESS_ABSTAIN;
    }

    private function isAnonymousCustomerGroup(int $identifier): bool
    {
        return $identifier === (int)$this->getConfigManager()->get('oro_customer.anonymous_customer_group');
    }

    private function getConfigManager(): ConfigManager
    {
        return $this->container->get('oro_config.global');
    }
}
