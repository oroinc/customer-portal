<?php

namespace Oro\Bundle\WebsiteBundle\Form\EventSubscriber;

use Oro\Bundle\WebsiteBundle\Entity\WebsiteAwareInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DefaultWebsiteSubscriber implements EventSubscriberInterface
{
    /**
     * @var WebsiteManager
     */
    private $websiteManager;

    /**
     * @param WebsiteManager $websiteManager
     */
    public function __construct(WebsiteManager $websiteManager)
    {
        $this->websiteManager = $websiteManager;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => ['onSubmit', 10]
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if ($data instanceof WebsiteAwareInterface && null === $data->getWebsite()) {
            $data->setWebsite($this->websiteManager->getDefaultWebsite());
        }
    }
}
