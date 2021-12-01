<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigBag;
use Oro\Bundle\ConfigBundle\Provider\ChainSearchProvider;
use Oro\Bundle\ConfigBundle\Tests\Unit\Provider\AbstractProviderTest;
use Oro\Bundle\CustomerBundle\Provider\CustomerConfigurationFormProvider;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerConfigurationFormProviderTest extends AbstractProviderTest
{
    protected const CONFIG_NAME = 'customer_configuration';

    /**
     * {@inheritdoc}
     */
    public function getParentCheckboxLabel(): string
    {
        return 'oro.customer.customer_configuration.use_default';
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
    ): CustomerConfigurationFormProvider {
        return new CustomerConfigurationFormProvider(
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
        return __DIR__ . '/data/customer_configuration/' . $fileName;
    }
}
