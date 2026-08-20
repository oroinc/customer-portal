<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Form\Handler;

use Oro\Bundle\CustomerBundle\Async\Topic\CustomerUserPasswordResetRequestTopic;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManagerInterface;
use Oro\Bundle\UserBundle\Form\Handler\AbstractPasswordResetRequestHandler;
use Oro\Bundle\UserBundle\Provider\UserLoggingInfoProviderInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Psr\Log\LoggerInterface;

/**
 * Handles forgot password request submitted in the storefront.
 */
class CustomerUserPasswordRequestHandler extends AbstractPasswordResetRequestHandler
{
    public function __construct(
        MessageProducerInterface $messageProducer,
        UserLoggingInfoProviderInterface $userLoggingInfoProvider,
        LoggerInterface $logger,
        private readonly WebsiteManager $websiteManager,
        private readonly UserLocalizationManagerInterface $userLocalizationManager
    ) {
        parent::__construct($messageProducer, $userLoggingInfoProvider, $logger);
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
