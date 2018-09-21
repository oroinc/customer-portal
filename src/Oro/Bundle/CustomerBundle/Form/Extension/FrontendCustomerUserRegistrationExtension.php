<?php

namespace Oro\Bundle\CustomerBundle\Form\Extension;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserSettings;
use Oro\Bundle\CustomerBundle\Form\Type\FrontendCustomerUserRegistrationType;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * This extension adds customer user's settings with currently chosen localization during registration process.
 */
class FrontendCustomerUserRegistrationExtension extends AbstractTypeExtension
{
    /**
     * @var LocalizationHelper
     */
    private $localizationHelper;

    /**
     * @var WebsiteManager
     */
    private $websiteManager;

    /**
     * @param LocalizationHelper $localizationHelper
     * @param WebsiteManager $websiteManager
     */
    public function __construct(LocalizationHelper $localizationHelper, WebsiteManager $websiteManager)
    {
        $this->localizationHelper = $localizationHelper;
        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var CustomerUser $customerUser */
            $customerUser = $event->getForm()->getData();

            $settings = $customerUser->getWebsiteSettings($this->websiteManager->getCurrentWebsite())
                ?? new CustomerUserSettings($this->websiteManager->getCurrentWebsite());

            $settings->setLocalization($this->localizationHelper->getCurrentLocalization());

            $customerUser->setWebsiteSettings($settings);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FrontendCustomerUserRegistrationType::class;
    }
}
