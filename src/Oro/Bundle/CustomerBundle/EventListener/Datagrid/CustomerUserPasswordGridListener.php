<?php

namespace Oro\Bundle\CustomerBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;

/**
 * Removes 'password' column from customer user grid if 'enforce_sso_login' feature is enabled.
 */
class CustomerUserPasswordGridListener
{
    public function __construct(
        private readonly FeatureChecker $featureChecker
    ) {
    }

    public function onBuildAfter(BuildAfter $event): void
    {
        if ($this->featureChecker->isFeatureEnabled('customer_user_login_password')) {
            return;
        }

        $config = $event->getDatagrid()->getConfig();
        $config->removeColumn('auth_status');
    }
}
