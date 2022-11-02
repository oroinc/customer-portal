<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigBag;
use Oro\Bundle\ConfigBundle\Provider\ChainSearchProvider;
use Oro\Bundle\ConfigBundle\Tests\Unit\Provider\AbstractProviderTest;
use Oro\Bundle\CustomerBundle\Provider\CustomerGroupConfigurationFormProvider;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerGroupConfigurationFormProviderTest extends AbstractProviderTest
{
    protected const CONFIG_SCOPE = 'customer_group';
    protected const TREE_NAME = 'customer_group_configuration';

    /**
     * {@inheritdoc}
     */
    protected function getParentCheckboxLabel(): string
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
        FormRegistryInterface $formRegistry,
        AuthorizationCheckerInterface $authorizationChecker,
        ChainSearchProvider $searchProvider,
        FeatureChecker $featureChecker,
        EventDispatcherInterface $eventDispatcher
    ): CustomerGroupConfigurationFormProvider {
        return new CustomerGroupConfigurationFormProvider(
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
        return __DIR__ . '/data/customer_group_configuration/' . $fileName;
    }
}
