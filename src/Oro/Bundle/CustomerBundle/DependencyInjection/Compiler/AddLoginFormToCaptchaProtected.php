<?php

namespace Oro\Bundle\CustomerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Add Customer User login form to a list of CAPTCHA protected forms.
 *
 * `oro_customer_form_login is` a "fake" form name used to identify form later during checks and allow it to be
 * configured via the system configuration.
 */
class AddLoginFormToCaptchaProtected implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $protectedFormsRegistry = $container->getDefinition('oro_form.captcha.protected_forms_registry');
        $protectedFormsRegistry->addMethodCall('protectForm', ['oro_customer_form_login']);
    }
}
