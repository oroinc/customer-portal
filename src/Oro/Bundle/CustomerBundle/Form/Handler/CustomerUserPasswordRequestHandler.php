<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\CustomerBundle\Async\Topic\CustomerUserPasswordResetRequestTopic;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManagerInterface;
use Oro\Bundle\UserBundle\Form\Handler\AbstractPasswordResetRequestHandler;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Psr\Log\LoggerInterface;
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
    protected function createMessageBody(string $userIdentifier): array
    {
        $messageBody = parent::createMessageBody($userIdentifier);
        $messageBody[CustomerUserPasswordResetRequestTopic::WEBSITE_ID] = $this->websiteManager
            ->getCurrentWebsite()
            ?->getId();
        $messageBody[CustomerUserPasswordResetRequestTopic::LOCALIZATION_ID] = $this->userLocalizationManager
            ->getCurrentLocalization()
            ?->getId();

        return $messageBody;
    }
}
