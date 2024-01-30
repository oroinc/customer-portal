<?php

namespace Oro\Bundle\FrontendBundle\Job;

use Oro\Component\Layout\Exception\NotRequestContextRuntimeException;
use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessageProducerMiddlewareInterface;

/**
 * Sets the current request theme id to any queue message
 */
class ThemeTransferJobMiddleware implements MessageProducerMiddlewareInterface
{
    public const QUEUE_MESSAGE_THEME_ID = 'themeId';

    public function __construct(
        private readonly CurrentThemeProvider $currentThemeProvider,
    ) {
    }

    public function handle(Message $message): void
    {
        if ($message->getProperty(self::QUEUE_MESSAGE_THEME_ID)) {
            return;
        }

        try {
            $themeId = $this->currentThemeProvider->getCurrentThemeId();
            if ($themeId) {
                $message->setProperty(self::QUEUE_MESSAGE_THEME_ID, $themeId);
            }
        } catch (NotRequestContextRuntimeException $e) {
            return;
        }
    }
}
