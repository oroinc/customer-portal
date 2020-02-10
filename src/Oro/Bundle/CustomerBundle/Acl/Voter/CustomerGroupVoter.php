<?php

namespace Oro\Bundle\CustomerBundle\Acl\Voter;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;

/**
 * Prevents removal of a customer group that is configured to be used for anonymous customers.
 */
class CustomerGroupVoter extends AbstractEntityVoter
{
    /**
     * @var array
     */
    protected $supportedAttributes = [BasicPermission::DELETE];

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function setConfigManager($configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        if ($this->configManager
            && $identifier
            && $this->isAnonymousCustomerGroup($identifier)
        ) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_ABSTAIN;
    }

    /**
     * @param int $identifier
     * @return bool
     */
    protected function isAnonymousCustomerGroup($identifier)
    {
        return $identifier === (int)$this->configManager->get('oro_customer.anonymous_customer_group');
    }
}
