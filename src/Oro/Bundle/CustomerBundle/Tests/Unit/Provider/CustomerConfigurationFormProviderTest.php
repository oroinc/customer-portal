<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigBag;
use Oro\Bundle\ConfigBundle\Provider\ChainSearchProvider;
use Oro\Bundle\ConfigBundle\Tests\Unit\Provider\AbstractProviderTest;
use Oro\Bundle\CustomerBundle\Provider\CustomerConfigurationFormProvider;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerConfigurationFormProviderTest extends AbstractProviderTest
{
    protected const CONFIG_SCOPE = 'customer';
    protected const TREE_NAME = 'customer_configuration';

    /**
     * {@inheritdoc}
     */
    protected function getParentCheckboxLabel(): string
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
        FormRegistryInterface $formRegistry,
        AuthorizationCheckerInterface $authorizationChecker,
        ChainSearchProvider $searchProvider,
        FeatureChecker $featureChecker,
        EventDispatcherInterface $eventDispatcher
    ): CustomerConfigurationFormProvider {
        return new CustomerConfigurationFormProvider(
            $configBag,
            $translator,
            $formFactory,
            $formRegistry,
            $authorizationChecker,
            $searchProvider,
            $featureChecker,
            $eventDispatcher
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
