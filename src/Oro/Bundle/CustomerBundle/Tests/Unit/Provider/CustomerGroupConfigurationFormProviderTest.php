<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigBag;
use Oro\Bundle\ConfigBundle\Provider\ChainSearchProvider;
use Oro\Bundle\ConfigBundle\Tests\Unit\Provider\AbstractProviderTest;
use Oro\Bundle\CustomerBundle\Provider\CustomerGroupConfigurationFormProvider;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerGroupConfigurationFormProviderTest extends AbstractProviderTest
{
    const CONFIG_NAME = 'customer_group_configuration';

    /**
     * {@inheritdoc}
     */
    public function getParentCheckboxLabel(): string
    {
        return 'oro.customer.customergroup.customer_group_configuration.use_default';
    }

    /**
     * {@inheritdoc}
     */
    public function getProvider(
        ConfigBag $configBag,
        TranslatorInterface $translator,
        FormFactoryInterface $formFactory,
        AuthorizationCheckerInterface $authorizationChecker,
        ChainSearchProvider $searchProvider,
        FormRegistryInterface $formRegistry
    ): CustomerGroupConfigurationFormProvider {
        return new CustomerGroupConfigurationFormProvider(
            $configBag,
            $translator,
            $formFactory,
            $authorizationChecker,
            $searchProvider,
            $formRegistry
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilePath($fileName): string
    {
        return __DIR__ . '/data/customer_group_configuration/' . $fileName;
    }
}
