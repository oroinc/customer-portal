<?php

namespace Oro\Bundle\FrontendBundle\Consumption\Extension;

use Oro\Bundle\FrontendBundle\Job\ThemeTransferJobMiddleware;
use Oro\Component\Layout\Extension\Theme\Model\CurrentThemeProvider;
use Oro\Component\MessageQueue\Consumption\AbstractExtension;
use Oro\Component\MessageQueue\Consumption\Context;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attach the current theme (received from the queue message) to the request object,
 * and remove it once the message was processed
 */
class ThemeTransferConsumptionExtension extends AbstractExtension
{
    private ?Request $currentProviderRequest = null;
    private ?string $currentProviderThemeId = null;
    private ?bool $providerHasRequest = null;

    public function __construct(private readonly CurrentThemeProvider $themeProvider)
    {
    }

    public function onPreReceived(Context $context): void
    {
        $themeId = $context->getMessage()->getProperty(ThemeTransferJobMiddleware::QUEUE_MESSAGE_THEME_ID);
        if (!$themeId) {
            $this->providerHasRequest = null;
            return;
        }

        $this->currentProviderRequest = $this->themeProvider->getCurrentRequest();

        if ($this->currentProviderRequest) {
            $this->providerHasRequest = true;
            $this->currentProviderThemeId = $this->currentProviderRequest->attributes->get('_theme', '');
            $this->currentProviderRequest->attributes->set('_theme', $themeId);
        } else {
            $request = new Request();
            $this->providerHasRequest = false;
            $request->attributes->set('_theme', $themeId);
            $this->themeProvider->setCurrentRequest($request);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onPostReceived(Context $context): void
    {
        // theme id wasn't manipulated, so do nothing
        if (null === $this->providerHasRequest) {
            return;
        }

        if (!$this->providerHasRequest) {
            // put back null to provider if it was null before
            $this->themeProvider->setCurrentRequest(null);
            return;
        }

        // put back the original themeId if it was set
        if ($this->currentProviderThemeId) {
            $this->currentProviderRequest->attributes->set('_theme', $this->currentProviderThemeId);
        }
    }
}
