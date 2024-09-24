<?php

namespace Oro\Bundle\FrontendBundle\Provider;

use Oro\Bundle\ActionBundle\Provider\RouteProviderInterface;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;

/**
 * The provider for action routes that returns storefront routes for storefront requests
 * and default routes for management console requests.
 */
class RouteProvider implements RouteProviderInterface
{
    private RouteProviderInterface $routeProvider;

    private FrontendHelper $frontendHelper;

    private string $formDialogRoute;

    private string $formPageRoute;

    private string $executionRoute;

    private string $widgetRoute;

    public function __construct(
        RouteProviderInterface $routeProvider,
        FrontendHelper $frontendHelper,
        string $formDialogRoute,
        string $formPageRoute,
        string $executionRoute,
        string $widgetRoute = ''
    ) {
        $this->routeProvider = $routeProvider;
        $this->frontendHelper = $frontendHelper;
        $this->formDialogRoute = $formDialogRoute;
        $this->formPageRoute = $formPageRoute;
        $this->executionRoute = $executionRoute;
        $this->widgetRoute = $widgetRoute;
    }

    #[\Override]
    public function getWidgetRoute(): string
    {
        return $this->frontendHelper->isFrontendRequest()
            ? $this->widgetRoute
            : $this->routeProvider->getWidgetRoute();
    }

    #[\Override]
    public function getFormDialogRoute(): string
    {
        return $this->frontendHelper->isFrontendRequest()
            ? $this->formDialogRoute
            : $this->routeProvider->getFormDialogRoute();
    }

    #[\Override]
    public function getFormPageRoute(): string
    {
        return $this->frontendHelper->isFrontendRequest()
            ? $this->formPageRoute
            : $this->routeProvider->getFormPageRoute();
    }

    #[\Override]
    public function getExecutionRoute(): string
    {
        return $this->frontendHelper->isFrontendRequest()
            ? $this->executionRoute
            : $this->routeProvider->getExecutionRoute();
    }
}
