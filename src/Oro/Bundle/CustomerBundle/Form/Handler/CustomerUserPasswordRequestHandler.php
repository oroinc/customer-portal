<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\CustomerBundle\Async\Topic\CustomerUserPasswordResetRequestTopic;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Event\PasswordResetRequestContextCollectEvent;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManagerInterface;
use Oro\Bundle\UserBundle\Form\Handler\AbstractPasswordResetRequestHandler;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles forgot password request submitted in the storefront.
 */
class CustomerUserPasswordRequestHandler extends AbstractPasswordResetRequestHandler
{
    private CustomerUserManager $userManager;
    private TranslatorInterface $translator;
    private WebsiteManager $websiteManager;
    private UserLocalizationManagerInterface $userLocalizationManager;
    private ?EventDispatcherInterface $eventDispatcher = null;

    public function __construct(
        CustomerUserManager $userManager,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);

        $this->userManager = $userManager;
        $this->translator = $translator;
    }

    public function setWebsiteManager(WebsiteManager $websiteManager): self
    {
        $this->websiteManager = $websiteManager;

        return $this;
    }

    public function setUserLocalizationManager(UserLocalizationManagerInterface $userLocalizationManager): self
    {
        $this->userLocalizationManager = $userLocalizationManager;

        return $this;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): self
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    #[\Override]
    public function process(FormInterface $form, Request $request)
    {
        return parent::process($form, $request);
    }

    #[\Override]
    protected function getFieldName(): string
    {
        return 'email';
    }

    #[\Override]
    protected function getTopicName(): string
    {
        return CustomerUserPasswordResetRequestTopic::getName();
    }

    #[\Override]
    protected function createMessageBody(string $userIdentifier, Request $request): array
    {
        $messageBody = parent::createMessageBody($userIdentifier, $request);
        $messageBody[CustomerUserPasswordResetRequestTopic::WEBSITE_ID] = $this->websiteManager
            ->getCurrentWebsite()
            ?->getId();
        $messageBody[CustomerUserPasswordResetRequestTopic::LOCALIZATION_ID] = $this->userLocalizationManager
            ->getCurrentLocalization()
            ?->getId();
        $messageBody[CustomerUserPasswordResetRequestTopic::REQUEST_PARAMETERS] = $this
            ->collectRequestParameters($request);

        return $messageBody;
    }

    private function collectRequestParameters(Request $request): array
    {
        if (null === $this->eventDispatcher) {
            return [];
        }

        $event = new PasswordResetRequestContextCollectEvent($request);
        $this->eventDispatcher->dispatch($event);

        return $event->getRequestParameters();
    }
}
