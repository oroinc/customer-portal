<?php

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserRole;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadataProvider;
use Oro\Bundle\UserBundle\Entity\AbstractRole;

/**
 * Handler for customer user role updates with frontend-specific privilege processing.
 *
 * This handler extends the base customer user role handler to apply customer-specific limits
 * and use frontend ownership metadata during privilege processing, ensuring proper role configuration
 * for customer users.
 */
class CustomerUserRoleUpdateHandler extends AbstractCustomerUserRoleHandler
{
    #[\Override]
    protected function onSuccess(AbstractRole $role, array $appendUsers, array $removeUsers)
    {
        $this->applyCustomerLimits($role, $appendUsers, $removeUsers);

        parent::onSuccess($role, $appendUsers, $removeUsers);
    }

    #[\Override]
    protected function getRolePrivileges(AbstractRole $role)
    {
        $this->startFrontendProviderEmulation();
        $privileges = parent::getRolePrivileges($role);
        $this->stopFrontendProviderEmulation();

        return $privileges;
    }

    #[\Override]
    protected function processPrivileges(AbstractRole $role, $className = null)
    {
        $this->startFrontendProviderEmulation();
        parent::processPrivileges($role);
        $this->stopFrontendProviderEmulation();
    }

    protected function startFrontendProviderEmulation()
    {
        if ($this->chainMetadataProvider) {
            $this->chainMetadataProvider->startProviderEmulation(FrontendOwnershipMetadataProvider::ALIAS);
        }
    }

    protected function stopFrontendProviderEmulation()
    {
        if ($this->chainMetadataProvider) {
            $this->chainMetadataProvider->stopProviderEmulation();
        }
    }

    #[\Override]
    public function process(AbstractRole $role)
    {
        if ($role instanceof CustomerUserRole) {
            $this->originalCustomer = $role->getCustomer();
        }

        return parent::process($role);
    }
}
