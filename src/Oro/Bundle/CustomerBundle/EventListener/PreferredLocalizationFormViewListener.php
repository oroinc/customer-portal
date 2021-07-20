<?php

namespace Oro\Bundle\CustomerBundle\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;

/**
 * This listener adds preferred localization fields to CustomerUser form and view.
 */
class PreferredLocalizationFormViewListener
{
    /**
     * @var WebsiteManager
     */
    protected $websiteManager;

    public function __construct(WebsiteManager $websiteManager)
    {
        $this->websiteManager = $websiteManager;
    }

    public function onEntityEdit(BeforeListRenderEvent $event)
    {
        $template = $event->getEnvironment()->render(
            '@OroCustomer/CustomerUser/widget/preferredLocalizationForm.html.twig',
            ['form' => $event->getFormView()]
        );
        $scrollData = $event->getScrollData();
        $scrollData->addSubBlockData(0, 0, $template);
    }

    public function onEntityView(BeforeListRenderEvent $event)
    {
        /** @var CustomerUser $customerUser */
        $customerUser = $event->getEntity();
        $customerUserSettings = $customerUser->getWebsiteSettings($this->websiteManager->getDefaultWebsite());
        if (!$customerUserSettings || !$customerUserSettings->getLocalization()) {
            return;
        }

        $template = $event->getEnvironment()->render(
            '@OroCustomer/CustomerUser/widget/preferredLocalizationView.html.twig',
            ['preferredLocalization' => $customerUserSettings->getLocalization()]
        );
        $scrollData = $event->getScrollData();
        $scrollData->addSubBlockData(0, 0, $template);
    }
}
